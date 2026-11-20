<?php

namespace App\Support\SiteSpec;

/**
 * Outcome of one structural SiteSpec check.
 */
class SiteSpecValidationResult
{
    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    public function __construct(
        public bool $valid,
        public array $violations,
    ) {}

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        return array_map(
            fn (SiteSpecViolation $violation) => $violation->message,
            $this->violations,
        );
    }
}
