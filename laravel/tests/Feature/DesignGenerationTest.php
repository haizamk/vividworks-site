<?php

namespace Tests\Feature;

use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Models\GenerationContext;
use App\Models\GenerationOutbox;
use App\Support\DesignAgent\DesignAgentClient;
use App\Support\DesignAgent\DesignAgentResponse;
use App\Support\DesignAgent\DesignInvokeStub;
use App\Support\DesignAgent\InvokeClassification;
use App\Support\SiteSpec\ActiveTemplateCatalogue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DesignGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_counts_one_two_and_three_are_accepted_without_calling_the_network(): void
    {
        $this->bindThrowingAgent();

        foreach ([1, 2, 3] as $pageCount) {
            $captured = null;
            $this->bindStub(function (array $payload) use ($pageCount, &$captured): InvokeClassification {
                $captured = $payload;
                $context = GenerationContext::query()
                    ->where('project_id', $payload['project_id'])
                    ->where('request_id', $payload['request_id'])
                    ->firstOrFail();
                $this->assertSame($pageCount, $context->page_count);
                $this->assertSame([], $context->media_allowlist);
                $this->assertSame(ActiveTemplateCatalogue::ids(), $context->active_templates);

                return InvokeClassification::accepted202('job-'.$pageCount);
            });

            $this->postJson('/api/ai/design-generations', $this->request("project-{$pageCount}", "request-{$pageCount}", [
                'page_count' => $pageCount,
            ]))
                ->assertStatus(202)
                ->assertJsonPath('applied', false)
                ->assertJsonPath('delivery_classification', 'accepted_202')
                ->assertJsonPath('status', 'accepted')
                ->assertJsonPath('job_id', 'job-'.$pageCount)
                ->assertJsonPath('request_id', "request-{$pageCount}");

            $this->assertSame($pageCount, $captured['page_count']);
            $this->assertSame(ActiveTemplateCatalogue::ids(), $captured['active_templates']);

            $generation = Generation::query()->where('request_id', "request-{$pageCount}")->firstOrFail();
            $attempt = $generation->attempts()->firstOrFail();

            $this->assertSame('accepted', $generation->status);
            $this->assertSame('accepted', $attempt->status);
            $this->assertSame('job-'.$pageCount, $attempt->job_id);
            $this->assertNull($attempt->invoke_correlation_key);
            $this->assertNull($generation->sitespec);
            $this->assertSame('accepted_202', GenerationOutbox::query()->where('attempt_id', $attempt->id)->firstOrFail()->delivery_classification);
            $this->assertSame(1, GenerationAttempt::query()->where('generation_id', $generation->id)->count());
        }
    }

    public function test_missing_non_integer_and_out_of_range_page_counts_are_rejected(): void
    {
        $this->bindStub(function (): InvokeClassification {
            $this->fail('The stub was called for an invalid page_count.');
        });

        $missing = $this->request('project-001', 'request-001');
        unset($missing['page_count']);
        $this->postJson('/api/ai/design-generations', $missing)
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'invalid_payload')
            ->assertJsonPath('errors.page_count', 'page_count is required.');

        $this->postJson('/api/ai/design-generations', $this->request('project-001', 'request-001', [
            'page_count' => null,
        ]))
            ->assertStatus(422)
            ->assertJsonPath('errors.page_count', 'page_count is required.');

        foreach (['2', 0, 4, 1.5, true] as $pageCount) {
            $this->postJson('/api/ai/design-generations', $this->request('project-001', 'request-001', [
                'page_count' => $pageCount,
            ]))
                ->assertStatus(422)
                ->assertJsonPath('error_code', 'invalid_payload')
                ->assertJsonPath('errors.page_count', 'page_count must be an integer from 1 to 3.');
        }

        $this->assertSame(0, Generation::query()->count());
        $this->assertSame(0, GenerationContext::query()->count());
    }

    public function test_official_templates_are_sent_and_unknown_templates_are_rejected(): void
    {
        $this->bindStub(function (): InvokeClassification {
            $this->fail('The stub was called for an unknown template.');
        });

        $this->postJson('/api/ai/design-generations', $this->request('project-002', 'request-002', [
            'page_count' => 1,
            'template_id' => 'food-cafe',
        ]))
            ->assertStatus(422)
            ->assertJsonPath('errors.template_id', 'template_id must be one of the official active templates.');

        $this->bindStub(function (array $payload): InvokeClassification {
            $this->assertSame(ActiveTemplateCatalogue::ids(), $payload['active_templates']);
            $this->assertSame('food-restaurant', $payload['template_id']);

            return InvokeClassification::accepted202('job-template');
        });

        $this->postJson('/api/ai/design-generations', $this->request('project-001', 'request-001', [
            'page_count' => 1,
            'template_id' => 'food-restaurant',
        ]))->assertStatus(202);

        $this->postJson('/api/ai/design-generations', $this->request('project-003', 'request-003', [
            'page_count' => 1,
            'active_templates' => array_merge(ActiveTemplateCatalogue::ids(), ['food-cafe']),
        ]))
            ->assertStatus(422)
            ->assertJsonPath('errors.active_templates', 'active_templates must be the official Laravel catalogue.');

        $this->assertNull(GenerationContext::query()->where('request_id', 'request-002')->first());
        $this->assertNull(GenerationContext::query()->where('request_id', 'request-003')->first());
    }

    public function test_stub_failure_after_commit_stays_unknown_and_keeps_the_attempt(): void
    {
        $this->bindStub(function (): InvokeClassification {
            throw new RuntimeException('stub failed after commit');
        });

        $this->postJson('/api/ai/design-generations', $this->request('project-001', 'request-001', [
            'page_count' => 1,
        ]))
            ->assertOk()
            ->assertJsonPath('delivery_classification', 'unknown')
            ->assertJsonPath('attempt_status', 'ambiguous');

        $generation = Generation::query()->firstOrFail();
        $attempt = GenerationAttempt::query()->firstOrFail();
        $outbox = GenerationOutbox::query()->firstOrFail();

        $this->assertSame('invoking', $generation->status);
        $this->assertNotSame('invoke_unconfirmed', $generation->status);
        $this->assertSame('ambiguous', $attempt->status);
        $this->assertSame('unknown', $outbox->state);
        $this->assertSame('unknown', $outbox->delivery_classification);
        $this->assertNotSame('confirmed_not_sent', $outbox->state);
        $this->assertSame(1, GenerationAttempt::query()->count());
        $this->assertNull($generation->sitespec);
    }

    public function test_http_400_invalid_input_fails_the_attempt_without_a_new_attempt(): void
    {
        $this->bindStub(fn (): InvokeClassification => InvokeClassification::rejected400('page_count is not accepted'));

        $this->postJson('/api/ai/design-generations', $this->request('project-001', 'request-001', [
            'page_count' => 1,
        ]))
            ->assertStatus(400)
            ->assertJsonPath('error_code', 'invalid_input')
            ->assertJsonPath('http_status', 400)
            ->assertJsonPath('retryable', false);

        $this->assertSame('failed', GenerationAttempt::query()->firstOrFail()->status);
        $this->assertSame('failed', Generation::query()->firstOrFail()->status);
        $this->assertSame(1, GenerationAttempt::query()->count());
        $this->assertSame('rejected_400', GenerationOutbox::query()->firstOrFail()->delivery_classification);
    }

    public function test_other_http_status_is_not_a_business_failure_and_does_not_retry(): void
    {
        $this->bindStub(fn (): InvokeClassification => InvokeClassification::rejectedOther(401));

        $this->postJson('/api/ai/design-generations', $this->request('project-001', 'request-001', [
            'page_count' => 1,
        ]))
            ->assertOk()
            ->assertJsonPath('delivery_classification', 'rejected_other')
            ->assertJsonPath('http_status', 401)
            ->assertJsonPath('status', 'invoking');

        $this->assertSame('invoking', Generation::query()->firstOrFail()->status);
        $this->assertSame('invoking', GenerationAttempt::query()->firstOrFail()->status);
        $this->assertNotSame('failed', Generation::query()->firstOrFail()->status);
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

    private function bindThrowingAgent(): void
    {
        $this->app->instance(DesignAgentClient::class, new class implements DesignAgentClient
        {
            public function send(array $payload): DesignAgentResponse
            {
                throw new RuntimeException('W2 must not be called from the F1 mock.');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function request(string $projectId, string $requestId, array $overrides = []): array
    {
        return array_merge([
            'project_id' => $projectId,
            'request_id' => $requestId,
            'package' => 'free',
            'design_mode' => 'from_scratch',
            'language' => 'ms',
            'business' => [
                'name' => 'Kedai Contoh',
                'category' => 'retail',
                'description' => 'Kedai contoh untuk ujian generation.',
            ],
        ], $overrides);
    }
}
