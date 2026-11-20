<?php

namespace App\Support\SiteSpec;

/**
 * One structural mismatch between a SiteSpec instance and the canonical schema.
 */
class SiteSpecViolation
{
    public function __construct(
        public string $path,
        public string $keyword,
        public string $message,
    ) {}
}
