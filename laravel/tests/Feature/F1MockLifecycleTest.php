<?php

namespace Tests\Feature;

use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Models\GenerationAuditEvent;
use App\Models\GenerationOutbox;
use App\Models\GenerationReopen;
use App\Support\DesignAgent\DesignInvokeStub;
use App\Support\DesignAgent\InvokeClassification;
use App\Support\F1MockDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class F1MockLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-callback-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.vividai.callback_token' => self::TOKEN]);
    }

    public function test_confirmed_not_sent_requires_positive_proof_and_resend_keeps_the_attempt(): void
    {
        $this->bindStub(fn (): InvokeClassification => new InvokeClassification(InvokeClassification::CONFIRMED_NOT_SENT));

        $this->postJson('/api/ai/design-generations', $this->request('project-proof', 'request-proof'))
            ->assertOk()
            ->assertJsonPath('delivery_classification', 'unknown');

        $this->assertSame('unknown', GenerationOutbox::query()->firstOrFail()->state);
        $this->assertSame(1, GenerationAttempt::query()->count());

        $this->bindStub(fn (): InvokeClassification => InvokeClassification::confirmedNotSent());

        $this->postJson('/api/ai/design-generations', $this->request('project-sent', 'request-sent'))
            ->assertOk()
            ->assertJsonPath('delivery_classification', 'confirmed_not_sent')
            ->assertJsonPath('status', 'pending_invoke');

        $attempt = Generation::query()->where('request_id', 'request-sent')->firstOrFail()->attempts()->firstOrFail();
        $this->assertSame('pending_invoke', $attempt->status);
        $this->assertSame('confirmed_not_sent', GenerationOutbox::query()->where('attempt_id', $attempt->id)->firstOrFail()->state);

        $this->bindStub(fn (): InvokeClassification => InvokeClassification::accepted202('job-resend'));
        $result = app(F1MockDispatcher::class)->resend($attempt->fresh(), app(DesignInvokeStub::class), ['project_id' => 'project-sent']);

        $this->assertSame(202, $result->status);
        $this->assertSame(1, $attempt->generation()->firstOrFail()->attempts()->count());
        $this->assertSame('accepted', $attempt->fresh()->status);
        $this->assertSame('job-resend', $attempt->fresh()->job_id);
    }

    public function test_operational_unconfirmed_does_not_collapse_into_the_attempt_status(): void
    {
        $this->bindStub(fn (): InvokeClassification => InvokeClassification::accepted202('job-open'));
        $this->postJson('/api/ai/design-generations', $this->request('project-open', 'request-open'))->assertStatus(202);

        $generation = Generation::query()->firstOrFail();
        app(F1MockDispatcher::class)->recordOperationalUnconfirmed($generation);

        $generation->refresh();
        $attempt = GenerationAttempt::query()->firstOrFail();
        $this->assertSame('invoke_unconfirmed', $generation->status);
        $this->assertSame('ambiguous', $attempt->status);
        $this->assertNotSame($generation->status, $attempt->status);
    }

    public function test_manual_reopen_is_explicit_and_does_not_retry_from_retryable(): void
    {
        $this->bindStub(fn (): InvokeClassification => InvokeClassification::accepted202('job-reopen'));
        $this->postJson('/api/ai/design-generations', $this->request('project-reopen', 'request-reopen'))->assertStatus(202);

        $generation = Generation::query()->firstOrFail();
        $dispatcher = app(F1MockDispatcher::class);
        $dispatcher->recordOperationalUnconfirmed($generation);

        $denied = $dispatcher->reopen($generation->fresh(), 'owner', 'need another look', false);
        $this->assertSame(422, $denied->status);
        $this->assertSame(1, GenerationAttempt::query()->count());

        $opened = $dispatcher->reopen($generation->fresh(), 'owner', 'need another look', true);
        $this->assertSame(200, $opened->status);
        $this->assertSame(2, GenerationAttempt::query()->count());
        $this->assertSame(1, GenerationReopen::query()->count());
        $this->assertTrue(GenerationReopen::query()->firstOrFail()->duplicate_risk_acknowledged);
        $this->assertSame('superseded', GenerationAttempt::query()->where('attempt_number', 1)->firstOrFail()->status);
        $this->assertSame('pending_invoke', GenerationAttempt::query()->where('attempt_number', 2)->firstOrFail()->status);
        $this->assertSame(1, GenerationAuditEvent::query()->where('event', 'manual_reopen')->count());
    }

    public function test_callback_without_attempt_id_matches_the_recorded_job_and_retryable_creates_no_attempt(): void
    {
        $this->bindStub(fn (): InvokeClassification => InvokeClassification::accepted202('job-callback'));
        $this->postJson('/api/ai/design-generations', $this->request('project-callback', 'request-callback'))->assertStatus(202);

        $body = $this->successBody();
        unset($body['attempt_id'], $body['generation_id']);

        $this->postJson('/api/ai/design-callback', $body, [
            'X-VividAI-Callback-Token' => self::TOKEN,
        ])->assertOk()->assertJsonPath('applied', true)->assertJsonPath('status', 'completed');

        $this->assertSame(1, GenerationAttempt::query()->count());
        $this->assertArrayNotHasKey('media_allowlist', Generation::query()->firstOrFail()->sitespec);

        $this->bindStub(fn (): InvokeClassification => InvokeClassification::accepted202('job-error'));
        $this->postJson('/api/ai/design-generations', $this->request('project-error', 'request-error'))->assertStatus(202);

        $error = $body;
        $error['status'] = 'error';
        $error['job_id'] = 'job-error';
        $error['project_id'] = 'project-error';
        $error['request_id'] = 'request-error';
        unset($error['sitespec'], $error['revision_metadata'], $error['usage']);
        $error['error'] = [
            'error_code' => 'contoh',
            'message' => 'contoh',
            'details' => ['source' => 'mock'],
            'retryable' => true,
        ];

        $this->postJson('/api/ai/design-callback', $error, [
            'X-VividAI-Callback-Token' => self::TOKEN,
        ])->assertOk()->assertJsonPath('applied', true)->assertJsonPath('status', 'failed');

        $attempt = GenerationAttempt::query()->where('job_id', 'job-error')->firstOrFail();
        $this->assertTrue($attempt->retryable);
        $this->assertSame(1, GenerationAttempt::query()->where('generation_id', $attempt->generation_id)->count());
    }

    public function test_duplicate_callback_id_does_not_apply_twice(): void
    {
        $this->bindStub(fn (): InvokeClassification => InvokeClassification::accepted202('job-dup'));
        $this->postJson('/api/ai/design-generations', $this->request('project-dup', 'request-dup'))->assertStatus(202);

        $body = $this->successBody();
        $body['callback_id'] = 'cb-stable-1';
        $body['job_id'] = 'job-dup';
        $body['project_id'] = 'project-dup';
        $body['request_id'] = 'request-dup';
        unset($body['attempt_id'], $body['generation_id']);

        $headers = ['X-VividAI-Callback-Token' => self::TOKEN];
        $this->postJson('/api/ai/design-callback', $body, $headers)->assertOk()->assertJsonPath('applied', true);

        $sitespec = Generation::query()->firstOrFail()->sitespec;
        $body['sitespec']['site']['name'] = 'Kedai Lain';

        $this->postJson('/api/ai/design-callback', $body, $headers)
            ->assertOk()
            ->assertJsonPath('applied', false)
            ->assertJsonPath('reason', 'duplicate');

        $this->assertSame($sitespec, Generation::query()->firstOrFail()->sitespec);
        $this->assertSame(1, GenerationAttempt::query()->count());
    }

    /**
     * @param  callable(array<string, mixed>): InvokeClassification  $classifier
     */
    private function bindStub(callable $classifier): void
    {
        $this->app->instance(DesignInvokeStub::class, new class($classifier) implements DesignInvokeStub
        {
            public function __construct(private $classifier) {}

            public function classify(array $payload): InvokeClassification
            {
                return ($this->classifier)($payload);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function request(string $projectId, string $requestId): array
    {
        return [
            'project_id' => $projectId,
            'request_id' => $requestId,
            'page_count' => 1,
            'package' => 'free',
            'design_mode' => 'from_scratch',
            'language' => 'ms',
            'business' => [
                'name' => 'Kedai Contoh',
                'category' => 'retail',
                'description' => 'Kedai contoh untuk ujian generation.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function successBody(): array
    {
        return [
            'schema_version' => '1.1',
            'status' => 'ok',
            'project_id' => 'project-callback',
            'request_id' => 'request-callback',
            'job_id' => 'job-callback',
            'stage' => 'design_agent',
            'http_status' => 200,
            'sitespec' => [
                'schema_version' => '1.1',
                'site' => [
                    'name' => 'Kedai Contoh',
                    'language' => 'ms',
                    'category' => 'retail',
                ],
                'design' => [
                    'recommended_template_id' => 'minimal-clean',
                    'selected_template_id' => 'minimal-clean',
                    'selection_source' => 'ai',
                    'theme_source' => 'ai',
                    'theme' => [
                        'primary_color' => '#112233',
                        'font_token' => 'sans-modern',
                    ],
                ],
                'pages' => [[
                    'title' => 'Utama',
                    'slug' => 'home',
                    'sections' => [[
                        'id' => 'hero-1',
                        'type' => 'hero',
                        'heading' => 'Selamat datang',
                    ]],
                ]],
            ],
            'revision_metadata' => ['prompt_version' => 'v1.1.0'],
        ];
    }
}
