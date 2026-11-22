<?php

namespace Tests\Feature;

use App\Http\Middleware\RejectOversizedDesignCallback;
use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Models\GenerationContext;
use App\Support\SiteSpec\ActiveTemplateCatalogue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class DesignCallbackTest extends TestCase
{
    use RefreshDatabase;

    private const GENERATION = '6f1c2a40-7b7e-4d1a-9c2e-0a1b2c3d4e5f';

    private const ATTEMPT = 'a1b2c3d4-e5f6-4789-a012-3456789abcde';

    private const ATTEMPT_TWO = 'b1b2c3d4-e5f6-4789-a012-3456789abcde';

    private const TOKEN = 'test-callback-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.vividai.callback_token' => self::TOKEN]);
    }

    public function test_valid_success_callback_stores_sitespec_and_completes_the_generation(): void
    {
        $this->seedGeneration();

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertExactJson([
                'received' => true,
                'applied' => true,
                'generation_id' => self::GENERATION,
                'request_id' => 'request-001',
                'attempt_id' => self::ATTEMPT,
                'status' => 'completed',
            ]);

        $generation = Generation::query()->findOrFail(self::GENERATION);
        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);

        $this->assertSame($this->validSiteSpec(), $generation->sitespec);
        $this->assertSame(['prompt_version' => 'v1.1.0'], $generation->revision_metadata);
        $this->assertArrayNotHasKey('schema_version', $generation->revision_metadata);
        $this->assertSame(['tokens' => 12], $generation->usage);
        $this->assertSame('completed', $generation->status);
        $this->assertSame('completed', $attempt->status);
        $this->assertSame('n8n-job-001', $attempt->job_id);
        $this->assertSame(200, $attempt->http_status);
        $this->assertSame(94468, $attempt->latency_ms);
        $this->assertNotNull($attempt->callback_payload_hash);
        $this->assertSame(64, strlen((string) $attempt->callback_payload_hash));
    }

    public function test_semantic_failure_is_rejected_before_the_store(): void
    {
        $this->seedGeneration();
        $sitespec = $this->validSiteSpec();
        $sitespec['pages'][0]['slug'] = 'utama';

        $this->postCallback($this->successBody(['sitespec' => $sitespec]))
            ->assertStatus(422)
            ->assertJsonPath('received', false)
            ->assertJsonPath('error_code', 'invalid_payload')
            ->assertJsonPath('errors./pages', '/pages: page_count 1 requires slugs home in that order.');

        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertSame('accepted', $attempt->status);
        $this->assertNull($attempt->callback_payload_hash);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_root_schema_version_1_2_is_rejected_before_the_store(): void
    {
        $this->seedGeneration();

        $this->postInvalid($this->successBody(['schema_version' => '1.2']))
            ->assertJsonPath('errors.schema_version', 'schema_version must be 1.1.');
    }

    public function test_inner_sitespec_schema_version_other_than_1_1_is_rejected_before_the_store(): void
    {
        $this->seedGeneration();
        $sitespec = $this->validSiteSpec();
        $sitespec['schema_version'] = '1.2';

        $this->postInvalid($this->successBody(['sitespec' => $sitespec]))
            ->assertJsonPath('errors.sitespec/schema_version', '/schema_version: "1.2" is not an allowed value (1.1).');
    }

    public function test_additional_property_is_rejected_before_the_store(): void
    {
        $this->seedGeneration();
        $sitespec = $this->validSiteSpec();
        $sitespec['design']['theme']['secondary_color'] = '#000000';

        $this->postInvalid($this->successBody(['sitespec' => $sitespec]))
            ->assertJsonPath('errors.sitespec/design/theme', '/design/theme: unexpected field "secondary_color".');
    }

    public function test_invalid_enum_is_rejected_before_the_store(): void
    {
        $this->seedGeneration();
        $sitespec = $this->validSiteSpec();
        $sitespec['design']['theme']['font_token'] = 'serif-modern';

        $this->postInvalid($this->successBody(['sitespec' => $sitespec]))
            ->assertJsonPath(
                'errors.sitespec/design/theme/font_token',
                '/design/theme/font_token: "serif-modern" is not an allowed value (sans-modern, sans-classic, serif-elegant).',
            );
    }

    public function test_invalid_type_is_rejected_before_the_store(): void
    {
        $this->seedGeneration();
        $sitespec = $this->validSiteSpec();
        $sitespec['site']['name'] = 12345;

        $this->postInvalid($this->successBody(['sitespec' => $sitespec]))
            ->assertJsonPath('errors.sitespec/site/name', '/site/name: expected string, got integer.');
    }

    public function test_error_callback_with_null_http_status_fails_the_generation_without_a_retry(): void
    {
        $this->seedGeneration();

        $this->postCallback($this->errorBody())
            ->assertOk()
            ->assertExactJson($this->applied('failed'));

        $generation = Generation::query()->findOrFail(self::GENERATION);
        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);

        $this->assertSame('failed', $generation->status);
        $this->assertNull($generation->sitespec);
        $this->assertNull($attempt->http_status);
        $this->assertSame('agent_unreachable', $attempt->error_code);
        $this->assertSame('Design agent could not be reached', $attempt->error_message);
        $this->assertTrue($attempt->retryable);
        $this->assertSame(1, GenerationAttempt::query()->count());
    }

    public function test_error_callback_with_http_status_omitted_stores_null(): void
    {
        $this->seedGeneration();
        $body = $this->errorBody();
        unset($body['http_status'], $body['error']['retryable']);

        $this->postCallback($body)->assertOk()->assertExactJson($this->applied('failed'));

        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertNull($attempt->http_status);
        $this->assertNull($attempt->retryable);
    }

    public function test_success_callback_requires_http_status(): void
    {
        $this->seedGeneration();
        $body = $this->successBody();
        unset($body['http_status']);

        $this->postCallback($body)
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'invalid_payload')
            ->assertJsonPath('errors.http_status', 'http_status must be an integer from 100 to 599.');

        $this->assertSame('accepted', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
    }

    public function test_http_status_zero_is_rejected(): void
    {
        $this->seedGeneration();

        $this->postCallback($this->errorBody(['http_status' => 0]))
            ->assertStatus(422)
            ->assertJsonPath('errors.http_status', 'http_status must be an integer from 100 to 599.');

        $this->postCallback($this->successBody(['http_status' => 0]))
            ->assertStatus(422);

        $this->assertSame('n8n-job-001', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->job_id);
    }

    public function test_missing_or_invalid_token_returns_401_without_a_lookup(): void
    {
        $this->seedGeneration();

        $this->postCallback($this->successBody(), null)
            ->assertStatus(401)
            ->assertExactJson([
                'received' => false,
                'error_code' => 'unauthorized',
                'message' => 'Invalid callback credentials',
            ]);

        $this->postCallback($this->successBody(), 'wrong-token')->assertStatus(401);
        config(['services.vividai.callback_token' => '']);
        $this->postCallback($this->successBody(), self::TOKEN)->assertStatus(401);

        $this->assertSame('accepted', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
        $this->assertSame('n8n-job-001', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->job_id);
    }

    public function test_unknown_attempt_returns_404_and_inserts_nothing(): void
    {
        $beforeGenerations = Generation::query()->count();
        $beforeAttempts = GenerationAttempt::query()->count();

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertExactJson([
                'received' => true,
                'applied' => false,
                'reason' => 'unmatched_pending_reconciliation',
            ]);

        $this->assertSame($beforeGenerations, Generation::query()->count());
        $this->assertSame($beforeAttempts, GenerationAttempt::query()->count());
    }

    public function test_mismatched_project_returns_409_without_changes(): void
    {
        $this->seedGeneration();

        $this->postCallback($this->successBody(['project_id' => 'project-999']))
            ->assertStatus(409)
            ->assertExactJson([
                'received' => false,
                'error_code' => 'identity_conflict',
                'message' => 'Callback identity does not match the generation.',
            ]);

        $this->assertSame('processing', Generation::query()->findOrFail(self::GENERATION)->status);
        $this->assertSame('n8n-job-001', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->job_id);
    }

    public function test_job_id_already_assigned_to_another_attempt_returns_409(): void
    {
        $this->seedGeneration(attempt: ['job_id' => null]);
        GenerationAttempt::query()->create([
            'id' => self::ATTEMPT_TWO,
            'generation_id' => self::GENERATION,
            'job_id' => 'n8n-job-001',
            'attempt_number' => 2,
            'status' => 'failed',
        ]);

        $this->postCallback($this->successBody())
            ->assertStatus(409)
            ->assertJsonPath('message', 'job_id is already assigned to another attempt.');

        $this->assertNull(GenerationAttempt::query()->findOrFail(self::ATTEMPT)->job_id);
    }

    public function test_same_attempt_with_a_different_job_id_returns_409(): void
    {
        $this->seedGeneration(attempt: ['job_id' => 'n8n-job-001', 'status' => 'accepted']);

        $this->postCallback($this->successBody(['job_id' => 'n8n-job-002']))
            ->assertStatus(409)
            ->assertJsonPath('message', 'attempt is already bound to a different job_id.');

        $this->assertSame('n8n-job-001', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->job_id);
        $this->assertSame('accepted', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
    }

    public function test_callback_does_not_bind_a_job_id_that_was_not_recorded(): void
    {
        $this->seedGeneration(attempt: ['job_id' => null, 'status' => 'pending_invoke']);

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertJsonPath('applied', false)
            ->assertJsonPath('reason', 'unmatched_pending_reconciliation');

        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertNull($attempt->job_id);
        $this->assertSame('pending_invoke', $attempt->status);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_identical_callback_is_a_duplicate_and_reordered_keys_match(): void
    {
        $this->seedGeneration();
        $this->postCallback($this->successBody())->assertOk();
        $hash = GenerationAttempt::query()->findOrFail(self::ATTEMPT)->callback_payload_hash;
        $sitespec = Generation::query()->findOrFail(self::GENERATION)->sitespec;

        $this->callbackRaw($this->reorderedSuccessJson())
            ->assertOk()
            ->assertExactJson([
                'received' => true,
                'applied' => false,
                'reason' => 'duplicate',
                'generation_id' => self::GENERATION,
                'request_id' => 'request-001',
                'attempt_id' => self::ATTEMPT,
                'status' => 'completed',
            ]);

        $this->assertSame($hash, GenerationAttempt::query()->findOrFail(self::ATTEMPT)->callback_payload_hash);
        $this->assertSame($sitespec, Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_different_payload_for_a_terminal_attempt_returns_409(): void
    {
        $this->seedGeneration();
        $this->postCallback($this->successBody())->assertOk();

        $this->postCallback($this->successBody([
            'sitespec' => $this->validSiteSpec('Kedai Lain'),
        ]))->assertStatus(409)->assertJsonPath('message', 'Terminal attempt received a different callback payload.');

        $this->assertSame($this->validSiteSpec(), Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_stale_callback_updates_only_the_old_attempt(): void
    {
        $this->seedGeneration();
        $second = GenerationAttempt::query()->create([
            'id' => self::ATTEMPT_TWO,
            'generation_id' => self::GENERATION,
            'job_id' => null,
            'attempt_number' => 2,
            'status' => 'failed',
        ]);
        $generation = Generation::query()->findOrFail(self::GENERATION);
        $generation->current_attempt_id = $second->id;
        $generation->save();

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertExactJson([
                'received' => true,
                'applied' => false,
                'reason' => 'stale_attempt',
                'generation_id' => self::GENERATION,
                'request_id' => 'request-001',
                'attempt_id' => self::ATTEMPT,
                'status' => 'processing',
            ]);

        $this->assertSame('accepted', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
        $this->assertSame('failed', GenerationAttempt::query()->findOrFail(self::ATTEMPT_TWO)->status);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
        $this->assertSame('processing', Generation::query()->findOrFail(self::GENERATION)->status);
        $this->assertSame(self::ATTEMPT_TWO, Generation::query()->findOrFail(self::GENERATION)->current_attempt_id);
    }

    public function test_current_attempt_callback_updates_the_generation_result(): void
    {
        $this->seedGeneration();

        $this->postCallback($this->successBody())->assertJsonPath('applied', true);

        $this->assertSame(self::ATTEMPT, Generation::query()->findOrFail(self::GENERATION)->current_attempt_id);
        $this->assertSame('completed', Generation::query()->findOrFail(self::GENERATION)->status);
        $this->assertSame($this->validSiteSpec(), Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_null_payload_hash_on_a_terminal_attempt_is_not_treated_as_a_duplicate(): void
    {
        $this->seedGeneration(attempt: [
            'status' => 'completed',
            'job_id' => 'n8n-job-001',
            'callback_payload_hash' => null,
        ]);

        $this->postCallback($this->successBody())
            ->assertStatus(409)
            ->assertJsonPath('message', 'Terminal attempt received a different callback payload.');
    }

    public function test_oversized_callback_returns_json_413_and_contact_stays_plain_text(): void
    {
        $this->seedGeneration();
        $oversized = $this->validSiteSpec();
        $oversized['site']['name'] = str_repeat('x', 30000);
        $body = json_encode($this->successBody([
            'sitespec' => $oversized,
        ]), JSON_THROW_ON_ERROR);

        $this->callbackRaw($body)
            ->assertStatus(413)
            ->assertExactJson([
                'received' => false,
                'error_code' => 'payload_too_large',
                'message' => 'Callback payload is too large',
            ]);

        $request = Request::create('/api/ai/design-callback', 'POST', content: $body);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Content-Length', '10');
        $response = app(RejectOversizedDesignCallback::class)->handle($request, fn () => response('next', 200));
        $this->assertSame(413, $response->getStatusCode());

        $this->call(
            'POST',
            '/api/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_LENGTH' => (string) (20 * 1024 + 1),
            ],
            content: str_repeat('a', 20 * 1024 + 1),
        )->assertStatus(413)->assertContent('Payload Too Large');

        $this->assertSame('accepted', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
    }

    public function test_forced_persistence_failure_rolls_back_the_attempt(): void
    {
        $this->seedGeneration();
        Generation::updating(function (): void {
            throw new RuntimeException('forced persistence failure');
        });

        try {
            $response = $this->postCallback($this->successBody());
        } finally {
            Generation::flushEventListeners();
            Generation::clearBootedModels();
        }

        $response->assertStatus(500)->assertExactJson([
            'received' => false,
            'error_code' => 'internal_error',
            'message' => 'Callback could not be stored',
        ]);
        $this->assertStringNotContainsString('forced persistence failure', $response->getContent());
        $this->assertStringNotContainsString(self::TOKEN, $response->getContent());

        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertSame('accepted', $attempt->status);
        $this->assertSame('n8n-job-001', $attempt->job_id);
        $this->assertNull($attempt->callback_payload_hash);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_null_current_attempt_id_is_allowed_and_does_not_receive_the_result(): void
    {
        $this->seedGeneration(linkCurrent: false);

        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->current_attempt_id);

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertJsonPath('applied', false)
            ->assertJsonPath('reason', 'stale_attempt')
            ->assertJsonPath('status', 'processing');

        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->current_attempt_id);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
        $this->assertSame('accepted', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
    }

    public function test_composite_foreign_key_rejects_another_generations_attempt(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Composite foreign key is added only on PostgreSQL. SQLite cannot add a foreign key after the table is created. No local PostgreSQL instance is available.');
        }

        $this->seedGeneration();
        $otherGeneration = '11111111-1111-4111-8111-111111111111';
        $otherAttempt = '22222222-2222-4222-8222-222222222222';
        Generation::query()->create([
            'id' => $otherGeneration,
            'project_id' => 'project-002',
            'request_id' => 'request-002',
            'status' => 'accepted',
            'current_attempt_id' => null,
        ]);
        GenerationAttempt::query()->create([
            'id' => $otherAttempt,
            'generation_id' => $otherGeneration,
            'attempt_number' => 1,
            'status' => 'submitted',
        ]);

        $this->expectException(QueryException::class);
        Generation::query()->whereKey(self::GENERATION)->update(['current_attempt_id' => $otherAttempt]);
    }

    public function test_a_second_open_attempt_for_the_same_generation_is_rejected(): void
    {
        $this->seedGeneration();
        GenerationAttempt::query()->create([
            'id' => self::ATTEMPT_TWO,
            'generation_id' => self::GENERATION,
            'attempt_number' => 2,
            'status' => 'completed',
        ]);

        try {
            GenerationAttempt::query()->create([
                'id' => 'c1b2c3d4-e5f6-4789-a012-3456789abcde',
                'generation_id' => self::GENERATION,
                'attempt_number' => 3,
                'status' => 'accepted',
            ]);
            $this->fail('A second open attempt was stored.');
        } catch (QueryException) {
            $this->assertSame(2, GenerationAttempt::query()->count());
        }
    }

    public function test_callback_records_the_attempt_without_changing_a_completed_generation(): void
    {
        $this->seedGeneration();
        $this->markGenerationTerminal('completed', ['page' => ['title' => 'Kept']], ['revision' => 7], ['tokens' => 3]);

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertExactJson($this->notApplied('generation_terminal', 'completed'));

        $generation = Generation::query()->findOrFail(self::GENERATION);
        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertSame('completed', $generation->status);
        $this->assertSame(['page' => ['title' => 'Kept']], $generation->sitespec);
        $this->assertSame(['revision' => 7], $generation->revision_metadata);
        $this->assertSame(['tokens' => 3], $generation->usage);
        $this->assertSame('completed', $attempt->status);
        $this->assertSame('n8n-job-001', $attempt->job_id);

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertJsonPath('reason', 'duplicate')
            ->assertJsonPath('status', 'completed');

        $this->postCallback($this->successBody([
            'sitespec' => $this->validSiteSpec('Kedai Lain'),
        ]))->assertStatus(409)->assertJsonPath('error_code', 'identity_conflict');

        $this->assertSame(['page' => ['title' => 'Kept']], Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    public function test_callback_does_not_reopen_an_expired_generation(): void
    {
        $this->seedGeneration();
        $this->markGenerationTerminal('expired', ['page' => ['title' => 'Kept']], ['revision' => 1], null);

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertExactJson($this->notApplied('generation_terminal', 'expired'));

        $generation = Generation::query()->findOrFail(self::GENERATION);
        $this->assertSame('expired', $generation->status);
        $this->assertSame(['page' => ['title' => 'Kept']], $generation->sitespec);
        $this->assertSame('completed', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
    }

    public function test_callback_does_not_change_a_failed_generation(): void
    {
        $this->seedGeneration();
        $this->markGenerationTerminal('failed', null, null, null);

        $this->postCallback($this->successBody())
            ->assertOk()
            ->assertExactJson($this->notApplied('generation_terminal', 'failed'));

        $this->assertSame('failed', Generation::query()->findOrFail(self::GENERATION)->status);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
        $this->assertSame('completed', GenerationAttempt::query()->findOrFail(self::ATTEMPT)->status);
    }

    public function test_job_id_owned_by_another_generation_returns_409_without_partial_writes(): void
    {
        $this->seedGeneration(attempt: ['job_id' => null]);
        $ownerGeneration = '11111111-1111-4111-8111-111111111111';
        $ownerAttempt = '22222222-2222-4222-8222-222222222222';
        Generation::query()->create([
            'id' => $ownerGeneration,
            'project_id' => 'project-002',
            'request_id' => 'request-002',
            'status' => 'failed',
            'current_attempt_id' => null,
        ]);
        GenerationAttempt::query()->create([
            'id' => $ownerAttempt,
            'generation_id' => $ownerGeneration,
            'job_id' => 'n8n-job-001',
            'attempt_number' => 1,
            'status' => 'failed',
        ]);

        $this->postCallback($this->successBody())
            ->assertStatus(409)
            ->assertExactJson([
                'received' => false,
                'error_code' => 'identity_conflict',
                'message' => 'job_id is already assigned to another attempt.',
            ]);

        $this->assertSame('n8n-job-001', GenerationAttempt::query()->findOrFail($ownerAttempt)->job_id);
        $this->assertSame('failed', Generation::query()->findOrFail($ownerGeneration)->status);
        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertNull($attempt->job_id);
        $this->assertSame('accepted', $attempt->status);
        $this->assertNull($attempt->callback_payload_hash);
        $this->assertSame('processing', Generation::query()->findOrFail(self::GENERATION)->status);
        $this->assertNull(Generation::query()->findOrFail(self::GENERATION)->sitespec);
    }

    /**
     * @param  array<string, mixed>|null  $sitespec
     * @param  array<string, mixed>|null  $revision
     * @param  array<string, mixed>|null  $usage
     */
    private function markGenerationTerminal(string $status, ?array $sitespec, ?array $revision, ?array $usage): void
    {
        Generation::query()->whereKey(self::GENERATION)->update([
            'status' => $status,
            'sitespec' => $sitespec === null ? null : json_encode($sitespec, JSON_THROW_ON_ERROR),
            'revision_metadata' => $revision === null ? null : json_encode($revision, JSON_THROW_ON_ERROR),
            'usage' => $usage === null ? null : json_encode($usage, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function notApplied(string $reason, string $status): array
    {
        return [
            'received' => true,
            'applied' => false,
            'reason' => $reason,
            'generation_id' => self::GENERATION,
            'request_id' => 'request-001',
            'attempt_id' => self::ATTEMPT,
            'status' => $status,
        ];
    }

    /**
     * @param  array<string, mixed>  $generation
     * @param  array<string, mixed>  $attempt
     */
    private function seedGeneration(array $generation = [], array $attempt = [], bool $linkCurrent = true): void
    {
        Generation::query()->create(array_merge([
            'id' => self::GENERATION,
            'project_id' => 'project-001',
            'request_id' => 'request-001',
            'status' => 'processing',
            'current_attempt_id' => null,
        ], $generation, ['current_attempt_id' => null]));

        GenerationAttempt::query()->create(array_merge([
            'id' => self::ATTEMPT,
            'generation_id' => self::GENERATION,
            'job_id' => 'n8n-job-001',
            'attempt_number' => 1,
            'status' => 'accepted',
        ], $attempt, ['generation_id' => self::GENERATION]));

        if ($linkCurrent) {
            Generation::query()->whereKey(self::GENERATION)->update([
                'current_attempt_id' => self::ATTEMPT,
            ]);
        }

        $stored = Generation::query()->findOrFail(self::GENERATION);
        GenerationContext::query()->create([
            'generation_id' => $stored->id,
            'project_id' => $stored->project_id,
            'request_id' => $stored->request_id,
            'page_count' => 1,
            'active_templates' => ActiveTemplateCatalogue::ids(),
            'media_allowlist' => [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function successBody(array $overrides = []): array
    {
        return array_merge([
            'schema_version' => '1.1',
            'status' => 'ok',
            'generation_id' => self::GENERATION,
            'project_id' => 'project-001',
            'request_id' => 'request-001',
            'attempt_id' => self::ATTEMPT,
            'job_id' => 'n8n-job-001',
            'stage' => 'design_agent',
            'http_status' => 200,
            'accepted_at' => '2026-11-16T12:44:07.512Z',
            'completed_at' => '2026-11-16T12:45:41.980Z',
            'latency_ms' => 94468,
            'sitespec' => $this->validSiteSpec(),
            'revision_metadata' => ['prompt_version' => 'v1.1.0'],
            'usage' => ['tokens' => 12],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function errorBody(array $overrides = []): array
    {
        return array_merge([
            'schema_version' => '1.1',
            'status' => 'error',
            'generation_id' => self::GENERATION,
            'project_id' => 'project-001',
            'request_id' => 'request-001',
            'attempt_id' => self::ATTEMPT,
            'job_id' => 'n8n-job-001',
            'stage' => 'design_agent',
            'http_status' => null,
            'error' => [
                'error_code' => 'agent_unreachable',
                'message' => 'Design agent could not be reached',
                'retryable' => true,
            ],
            'accepted_at' => '2026-11-16T12:50:03.001Z',
            'completed_at' => '2026-11-16T12:54:03.061Z',
            'latency_ms' => 240060,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function applied(string $status): array
    {
        return [
            'received' => true,
            'applied' => true,
            'generation_id' => self::GENERATION,
            'request_id' => 'request-001',
            'attempt_id' => self::ATTEMPT,
            'status' => $status,
        ];
    }

    private function reorderedSuccessJson(): string
    {
        return json_encode(array_reverse($this->successBody(), true), JSON_THROW_ON_ERROR);
    }

    /**
     * Synthetic SiteSpec v1.1. This is not execution #80 and not an official W1 fixture.
     *
     * @return array<string, mixed>
     */
    private function validSiteSpec(?string $name = null): array
    {
        $spec = [
            'schema_version' => '1.1',
            'site' => [
                'name' => $name ?? 'Kedai Contoh',
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
            'pages' => [
                [
                    'title' => 'Utama',
                    'slug' => 'home',
                    'sections' => [
                        [
                            'id' => 'hero-1',
                            'type' => 'hero',
                            'heading' => 'Selamat datang',
                        ],
                    ],
                ],
            ],
        ];

        return $spec;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    /**
     * @param  array<string, mixed>  $body
     */
    private function postInvalid(array $body)
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->postCallback($body)
            ->assertStatus(422)
            ->assertJsonPath('received', false)
            ->assertJsonPath('error_code', 'invalid_payload')
            ->assertJsonPath('message', 'Callback payload is invalid.');

        $this->assertSame([], DB::getQueryLog());
        $attempt = GenerationAttempt::query()->findOrFail(self::ATTEMPT);
        $this->assertSame('accepted', $attempt->status);
        $this->assertSame('n8n-job-001', $attempt->job_id);
        $this->assertNull($attempt->callback_payload_hash);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function postCallback(array $body, ?string $token = self::TOKEN)
    {
        $headers = ['Accept' => 'application/json'];
        if ($token !== null) {
            $headers['X-VividAI-Callback-Token'] = $token;
        }

        return $this->postJson('/api/ai/design-callback', $body, $headers);
    }

    private function callbackRaw(string $json, ?string $token = self::TOKEN)
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];
        if ($token !== null) {
            $server['HTTP_X_VIVIDAI_CALLBACK_TOKEN'] = $token;
        }

        return $this->call('POST', '/api/ai/design-callback', server: $server, content: $json);
    }
}
