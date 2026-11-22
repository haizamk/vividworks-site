<?php

namespace App\Support;

use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Models\GenerationContext;
use App\Models\GenerationOutbox;
use App\Support\DesignAgent\DesignInvokeStub;
use App\Support\SiteSpec\ActiveTemplateCatalogue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * F1 mock invoke. The parent, attempt, and outbox commit before the stub runs.
 * The stub does not open a network connection. SiteSpec is written only by a later callback.
 */
class DesignGenerationService
{
    /**
     * @var list<string>
     */
    private const PACKAGES = ['free', 'grow', 'pro', 'elite'];

    /**
     * @var list<string>
     */
    private const MODES = ['template_inspired', 'from_scratch'];

    /**
     * @var list<string>
     */
    private const LANGUAGES = ['ms', 'en'];

    public function __construct(
        private DesignInvokeStub $stub,
        private F1MockDispatcher $dispatcher,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function generate(array $input): DesignCallbackResult
    {
        $errors = $this->validate($input);
        if ($errors !== []) {
            return $this->invalid($errors);
        }

        try {
            [$generation, $attempt] = $this->persist($input);
        } catch (QueryException $exception) {
            if ($this->isDuplicate($exception)) {
                return new DesignCallbackResult(409, [
                    'received' => false,
                    'error_code' => 'identity_conflict',
                    'message' => 'A generation already exists for this project_id and request_id.',
                ]);
            }

            throw $exception;
        }

        $payload = $this->webhookBody($input);

        try {
            $classification = $this->stub->classify($payload);
        } catch (Throwable $exception) {
            report($exception);

            return $this->dispatcher->recordUnknown($generation, $attempt);
        }

        return $this->dispatcher->apply($generation, $attempt, $classification);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: Generation, 1: GenerationAttempt}
     */
    private function persist(array $input): array
    {
        return DB::transaction(function () use ($input): array {
            $generationId = (string) Str::uuid();
            $attemptId = (string) Str::uuid();
            $body = $this->webhookBody($input);

            $generation = Generation::query()->create([
                'id' => $generationId,
                'project_id' => $input['project_id'],
                'request_id' => $input['request_id'],
                'status' => 'pending_invoke',
                'current_attempt_id' => null,
            ]);

            $attempt = GenerationAttempt::query()->create([
                'id' => $attemptId,
                'generation_id' => $generationId,
                'job_id' => null,
                'attempt_number' => 1,
                'status' => 'pending_invoke',
                'invoke_correlation_key' => null,
            ]);

            $generation->current_attempt_id = $attemptId;
            $generation->save();

            GenerationOutbox::query()->create([
                'id' => (string) Str::uuid(),
                'attempt_id' => $attemptId,
                'state' => 'pending',
                'body' => $body,
            ]);

            GenerationContext::query()->create([
                'generation_id' => $generationId,
                'project_id' => $input['project_id'],
                'request_id' => $input['request_id'],
                'page_count' => $input['page_count'],
                'active_templates' => ActiveTemplateCatalogue::ids(),
                'media_allowlist' => [],
            ]);

            DesignAudit::record('parent_created', [
                'project_id' => $input['project_id'],
                'request_id' => $input['request_id'],
                'status' => 'pending_invoke',
            ], $generationId, $attemptId);
            DesignAudit::record('attempt_created', [
                'attempt_number' => 1,
            ], $generationId, $attemptId);

            return [$generation, $attempt];
        });
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function webhookBody(array $input): array
    {
        $body = [
            'project_id' => $input['project_id'],
            'request_id' => $input['request_id'],
            'page_count' => $input['page_count'],
            'active_templates' => ActiveTemplateCatalogue::ids(),
            'package' => $input['package'],
            'design_mode' => $input['design_mode'],
            'language' => $input['language'],
            'business' => [
                'name' => $input['business']['name'],
                'category' => $input['business']['category'],
                'description' => $input['business']['description'],
            ],
        ];

        if (is_string($input['template_id'] ?? null)) {
            $body['template_id'] = $input['template_id'];
        }

        if (is_array($input['template_inspiration'] ?? null)) {
            $body['template_inspiration'] = $input['template_inspiration'];
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    private function validate(array $input): array
    {
        $errors = [];

        $projectId = $this->text($input, 'project_id', 64);
        if ($projectId === null) {
            $errors['project_id'] = 'project_id is required.';
        }

        $requestId = $this->text($input, 'request_id', 100);
        if ($requestId === null) {
            $errors['request_id'] = 'request_id is required.';
        }

        if (! array_key_exists('page_count', $input) || $input['page_count'] === null) {
            $errors['page_count'] = 'page_count is required.';
        } elseif (! is_int($input['page_count']) || $input['page_count'] < 1 || $input['page_count'] > 3) {
            $errors['page_count'] = 'page_count must be an integer from 1 to 3.';
        }

        $package = $input['package'] ?? null;
        if (! is_string($package) || ! in_array($package, self::PACKAGES, true)) {
            $errors['package'] = 'package must be one of free, grow, pro or elite.';
        }

        $mode = $input['design_mode'] ?? null;
        if (! is_string($mode) || ! in_array($mode, self::MODES, true)) {
            $errors['design_mode'] = 'design_mode must be template_inspired or from_scratch.';
        }

        $language = $input['language'] ?? null;
        if (! is_string($language) || ! in_array($language, self::LANGUAGES, true)) {
            $errors['language'] = 'language must be ms or en.';
        }

        $business = $input['business'] ?? null;
        if (! is_array($business)) {
            $errors['business'] = 'business must be an object.';
        } else {
            foreach (['name' => 120, 'category' => 120, 'description' => 4000] as $field => $max) {
                $value = $business[$field] ?? null;
                if (! is_string($value) || trim($value) === '' || strlen(trim($value)) > $max) {
                    $errors['business.'.$field] = 'business.'.$field.' is required.';
                }
            }
        }

        if (array_key_exists('active_templates', $input) && $input['active_templates'] !== ActiveTemplateCatalogue::ids()) {
            $errors['active_templates'] = 'active_templates must be the official Laravel catalogue.';
        }

        if (array_key_exists('template_id', $input) && $input['template_id'] !== null) {
            if (! is_string($input['template_id']) || ! ActiveTemplateCatalogue::contains($input['template_id'])) {
                $errors['template_id'] = 'template_id must be one of the official active templates.';
            }
        }

        $inspiration = $input['template_inspiration'] ?? null;
        if ($mode === 'template_inspired') {
            $templateId = is_array($inspiration) ? ($inspiration['template_id'] ?? null) : null;
            if (! is_string($templateId) || ! ActiveTemplateCatalogue::contains($templateId)) {
                $errors['template_inspiration.template_id'] = 'template_inspiration.template_id must be one of the official active templates.';
            }
        } elseif ($inspiration !== null) {
            $errors['template_inspiration'] = 'template_inspiration is not allowed for design_mode from_scratch.';
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function text(array $input, string $field, int $max): ?string
    {
        $value = $input[$field] ?? null;
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' || strlen($trimmed) > $max ? null : $trimmed;
    }

    private function isDuplicate(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $message = $exception->getMessage();

        return ($sqlState === '23505' || $sqlState === '23000')
            && (str_contains($message, 'project_request') || str_contains($message, 'project_id'));
    }

    /**
     * @param  array<string, string>  $errors
     */
    private function invalid(array $errors): DesignCallbackResult
    {
        return new DesignCallbackResult(422, [
            'received' => false,
            'error_code' => 'invalid_payload',
            'message' => 'Generation request is invalid.',
            'errors' => $errors,
        ]);
    }
}
