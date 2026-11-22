<?php

namespace App\Support\SiteSpec;

/**
 * Runtime inputs for semantic checks. These values are not SiteSpec fields.
 *
 * Null means the value was not supplied. An empty list is a supplied list.
 */
class SiteSpecRequestContext
{
    /**
     * @param  list<string>|null  $activeTemplates
     * @param  list<string>|null  $mediaAllowlist
     */
    public function __construct(
        public mixed $pageCount,
        public ?array $activeTemplates,
        public ?array $mediaAllowlist,
    ) {}
}
