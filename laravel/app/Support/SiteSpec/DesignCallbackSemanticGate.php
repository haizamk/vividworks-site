<?php

namespace App\Support\SiteSpec;

use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Models\GenerationContext;
use App\Support\DesignCallbackData;

/**
 * Semantic checks for a callback whose identity matches one stored generation.
 *
 * An unknown attempt, or a project_id/request_id that does not match that
 * attempt, is left to DesignCallbackStore. Those results are 404 and 409.
 * A matched generation without stored context is rejected here.
 */
class DesignCallbackSemanticGate
{
    /**
     * @return array<string, string>
     */
    public static function errors(DesignCallbackData $callback): array
    {
        if ($callback->status !== 'ok' || $callback->sitespec === null) {
            return [];
        }

        $attempt = $callback->attemptId !== null
            ? GenerationAttempt::query()->whereKey($callback->attemptId)->first(['id', 'generation_id'])
            : null;
        if ($attempt === null) {
            $attempt = GenerationAttempt::query()->where('job_id', $callback->jobId)->first(['id', 'generation_id']);
        }
        if ($attempt === null) {
            return [];
        }

        $generation = Generation::query()->whereKey($attempt->generation_id)->first(['id', 'project_id', 'request_id']);
        if ($generation === null) {
            return [];
        }

        if (
            ($callback->generationId !== null && strcasecmp($callback->generationId, (string) $generation->id) !== 0)
            || $callback->projectId !== $generation->project_id
            || $callback->requestId !== $generation->request_id
        ) {
            return [];
        }

        $stored = GenerationContext::query()
            ->where('project_id', $generation->project_id)
            ->where('request_id', $generation->request_id)
            ->first();

        if ($stored === null || strcasecmp((string) $stored->generation_id, (string) $generation->id) !== 0) {
            return [
                'context' => 'No generation context is stored for this project_id and request_id.',
            ];
        }

        $result = (new SiteSpecSemanticValidator)->validate(
            $callback->sitespec,
            new SiteSpecRequestContext(
                $stored->page_count,
                is_array($stored->active_templates) ? $stored->active_templates : null,
                $stored->media_allowlist,
            ),
        );

        $errors = [];
        foreach ($result->violations as $violation) {
            $errors[$violation->path] = $violation->message;
        }

        return $errors;
    }
}
