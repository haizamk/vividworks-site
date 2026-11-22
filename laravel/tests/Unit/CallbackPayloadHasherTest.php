<?php

namespace Tests\Unit;

use App\Support\CallbackPayloadHasher;
use App\Support\DesignCallbackData;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;

class CallbackPayloadHasherTest extends TestCase
{
    public function test_object_key_order_does_not_change_the_hash_and_array_order_does(): void
    {
        $first = $this->payload($this->sitespec(['b' => 1, 'a' => ['z' => true, 'y' => false]], ['one', 'two']));
        $second = $this->payload($this->sitespec(['a' => ['y' => false, 'z' => true], 'b' => 1], ['one', 'two']));
        $reorderedList = $this->payload($this->sitespec(['a' => ['y' => false, 'z' => true], 'b' => 1], ['two', 'one']));

        $this->assertSame(CallbackPayloadHasher::hash($first), CallbackPayloadHasher::hash($second));
        $this->assertNotSame(CallbackPayloadHasher::hash($first), CallbackPayloadHasher::hash($reorderedList));
        $this->assertSame(64, strlen(CallbackPayloadHasher::hash($first)));
    }

    public function test_envelope_schema_version_1_1_changes_the_callback_hash(): void
    {
        $callback = $this->payload($this->sitespec(['name' => 'Kedai Contoh'], []));
        $fields = $callback->canonicalFields();

        $this->assertSame('1.1', $fields['schema_version']);

        $current = CallbackPayloadHasher::hash($callback);
        $fields['schema_version'] = '1.2';
        $previous = hash('sha256', json_encode(
            CallbackPayloadHasher::canonicalize($fields),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));

        $this->assertNotSame($previous, $current);
        $this->assertSame(64, strlen($current));
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  list<string>  $items
     */
    private function sitespec(array $fields, array $items): stdClass
    {
        $sitespec = new stdClass;
        foreach ($fields as $key => $value) {
            if (is_array($value) && ! array_is_list($value)) {
                $nested = new stdClass;
                foreach ($value as $childKey => $child) {
                    $nested->{$childKey} = $child;
                }
                $sitespec->{$key} = $nested;
                continue;
            }
            $sitespec->{$key} = $value;
        }
        $sitespec->items = $items;

        return $sitespec;
    }

    private function payload(stdClass $sitespec): DesignCallbackData
    {
        $revision = new stdClass;
        $revision->revision = 1;

        return new DesignCallbackData(
            status: 'ok',
            generationId: '6f1c2a40-7b7e-4d1a-9c2e-0a1b2c3d4e5f',
            projectId: 'project-001',
            requestId: 'request-001',
            attemptId: 'a1b2c3d4-e5f6-4789-a012-3456789abcde',
            jobId: 'n8n-job-001',
            httpStatus: 200,
            acceptedAt: CarbonImmutable::parse('2026-11-16T12:44:07.512Z'),
            completedAt: CarbonImmutable::parse('2026-11-16T12:45:41.980Z'),
            latencyMs: 94468,
            sitespec: $sitespec,
            revisionMetadata: $revision,
            usage: null,
            errorCode: null,
            message: null,
            details: null,
            retryable: null,
        );
    }
}
