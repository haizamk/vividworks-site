<?php

namespace App\Support\SiteSpec;

use JsonException;
use LogicException;
use stdClass;

/**
 * Semantic checks for Canonical SiteSpec v1.1.
 *
 * This class does not parse the canonical schema and does not repeat the
 * closed-object rules. OD-9 and OD-10 stay in SiteSpecStructuralValidator.
 *
 * OD-6: the candidate is only accepted or rejected. Nothing is written back.
 * page_count, active_templates, and media_allowlist stay in the request
 * context. A missing context value is a violation. It is not treated as a pass.
 *
 * DesignCallbackSemanticGate loads the stored context and calls this class
 * after structural validation. The callback envelope does not carry the context.
 */
class SiteSpecSemanticValidator
{
    private const COLOR = '/^#[0-9a-fA-F]{6}$/';

    private const SECTION_ID = '/^[a-z0-9][a-z0-9_-]{2,39}$/';

    /**
     * @var array<int, list<string>>
     */
    private const PAGE_PLANS = [
        1 => ['home'],
        2 => ['home', 'contact'],
        3 => ['home', 'about', 'contact'],
    ];

    public function validate(mixed $candidate, SiteSpecRequestContext $context): SiteSpecValidationResult
    {
        $before = $this->snapshot($candidate);
        $violations = [];

        if (! $candidate instanceof stdClass) {
            $violations[] = new SiteSpecViolation('/', 'type', '/: expected object.');
        } else {
            $this->checkContext($context, $violations);
            $this->checkLengths($candidate, $violations);
            $this->checkPatterns($candidate, $violations);
            $this->checkPagePlan($candidate, $context, $violations);
            $this->checkTemplates($candidate, $context, $violations);
            $this->checkPages($candidate, $context, $violations);
        }

        if ($this->snapshot($candidate) !== $before) {
            throw new LogicException('Semantic validator mutated the candidate.');
        }

        return new SiteSpecValidationResult($violations === [], $violations);
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkContext(SiteSpecRequestContext $context, array &$violations): void
    {
        if ($context->pageCount === null) {
            $violations[] = new SiteSpecViolation(
                '/context/page_count',
                'context',
                '/context/page_count: page_count was not supplied.',
            );
        } elseif (! is_int($context->pageCount)) {
            $violations[] = new SiteSpecViolation(
                '/context/page_count',
                'context',
                '/context/page_count: page_count must be an integer.',
            );
        } elseif (! array_key_exists($context->pageCount, self::PAGE_PLANS)) {
            $violations[] = new SiteSpecViolation(
                '/context/page_count',
                'context',
                "/context/page_count: page_count {$context->pageCount} is not a supported plan.",
            );
        }

        if ($this->stringList($context->activeTemplates) === null) {
            $violations[] = new SiteSpecViolation(
                '/context/active_templates',
                'context',
                '/context/active_templates: active_templates was not supplied.',
            );
        }

        if ($this->stringList($context->mediaAllowlist) === null) {
            $violations[] = new SiteSpecViolation(
                '/context/media_allowlist',
                'context',
                '/context/media_allowlist: media_allowlist was not supplied.',
            );
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkLengths(stdClass $candidate, array &$violations): void
    {
        $site = $this->object($candidate, 'site');
        if ($site !== null) {
            $this->length($site, 'name', '/site/name', 120, $violations);
            $this->length($site, 'category', '/site/category', 80, $violations);
        }

        foreach ($this->pages($candidate) as $pageIndex => $page) {
            $this->length($page, 'title', "/pages/{$pageIndex}/title", 120, $violations);
            foreach ($this->sections($page) as $sectionIndex => $section) {
                $base = "/pages/{$pageIndex}/sections/{$sectionIndex}";
                $this->length($section, 'heading', "{$base}/heading", 180, $violations);
                $this->length($section, 'description', "{$base}/description", 1000, $violations);
                $cta = $this->object($section, 'cta');
                if ($cta !== null) {
                    $this->length($cta, 'label', "{$base}/cta/label", 60, $violations);
                }
                foreach ($this->items($section) as $itemIndex => $item) {
                    $this->length($item, 'heading', "{$base}/items/{$itemIndex}/heading", 180, $violations);
                    $this->length($item, 'description', "{$base}/items/{$itemIndex}/description", 1000, $violations);
                }
            }
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkPatterns(stdClass $candidate, array &$violations): void
    {
        $theme = $this->object($this->object($candidate, 'design') ?? new stdClass, 'theme');
        if ($theme !== null && property_exists($theme, 'primary_color') && is_string($theme->primary_color)) {
            if (preg_match(self::COLOR, $theme->primary_color) !== 1) {
                $violations[] = new SiteSpecViolation(
                    '/design/theme/primary_color',
                    'pattern',
                    '/design/theme/primary_color: "'.$theme->primary_color.'" must match '.self::COLOR.'.',
                );
            }
        }

        foreach ($this->pages($candidate) as $pageIndex => $page) {
            foreach ($this->sections($page) as $sectionIndex => $section) {
                if (! property_exists($section, 'id') || ! is_string($section->id)) {
                    continue;
                }
                if (preg_match(self::SECTION_ID, $section->id) !== 1) {
                    $path = "/pages/{$pageIndex}/sections/{$sectionIndex}/id";
                    $violations[] = new SiteSpecViolation(
                        $path,
                        'pattern',
                        "{$path}: \"{$section->id}\" must match ".self::SECTION_ID.'.',
                    );
                }
            }
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkPagePlan(stdClass $candidate, SiteSpecRequestContext $context, array &$violations): void
    {
        if (! is_int($context->pageCount) || ! array_key_exists($context->pageCount, self::PAGE_PLANS)) {
            return;
        }

        $expected = self::PAGE_PLANS[$context->pageCount];
        $actual = [];
        foreach ($this->pages($candidate) as $page) {
            $actual[] = property_exists($page, 'slug') && is_string($page->slug) ? $page->slug : '';
        }

        if ($actual !== $expected) {
            $violations[] = new SiteSpecViolation(
                '/pages',
                'pagePlan',
                '/pages: page_count '.$context->pageCount.' requires slugs '.implode(', ', $expected).' in that order.',
            );
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkTemplates(stdClass $candidate, SiteSpecRequestContext $context, array &$violations): void
    {
        $allowed = $this->stringList($context->activeTemplates);
        if ($allowed === null) {
            return;
        }

        $design = $this->object($candidate, 'design');
        if ($design === null) {
            return;
        }

        foreach (['recommended_template_id', 'selected_template_id'] as $field) {
            if (! property_exists($design, $field) || ! is_string($design->{$field})) {
                continue;
            }
            if (! in_array($design->{$field}, $allowed, true)) {
                $violations[] = new SiteSpecViolation(
                    '/design/'.$field,
                    'template',
                    '/design/'.$field.': "'.$design->{$field}.'" is not in active_templates.',
                );
            }
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkPages(stdClass $candidate, SiteSpecRequestContext $context, array &$violations): void
    {
        $allowlist = $this->stringList($context->mediaAllowlist);
        $seenIds = [];
        $slugs = [];
        foreach ($this->pages($candidate) as $page) {
            if (property_exists($page, 'slug') && is_string($page->slug)) {
                $slugs[] = $page->slug;
            }
        }

        foreach ($this->pages($candidate) as $pageIndex => $page) {
            $sections = $this->sections($page);
            $count = count($sections);
            if (property_exists($page, 'sections') && is_array($page->sections) && array_is_list($page->sections) && ($count < 1 || $count > 8)) {
                $violations[] = new SiteSpecViolation(
                    "/pages/{$pageIndex}/sections",
                    'sectionCount',
                    "/pages/{$pageIndex}/sections: expected 1 to 8 sections, got {$count}.",
                );
            }

            $pageIds = [];
            foreach ($sections as $section) {
                if (property_exists($section, 'id') && is_string($section->id)) {
                    $pageIds[] = $section->id;
                }
            }

            foreach ($sections as $sectionIndex => $section) {
                $base = "/pages/{$pageIndex}/sections/{$sectionIndex}";
                if (property_exists($section, 'id') && is_string($section->id)) {
                    if (isset($seenIds[$section->id])) {
                        $violations[] = new SiteSpecViolation(
                            "{$base}/id",
                            'unique',
                            "{$base}/id: duplicate section id \"{$section->id}\".",
                        );
                    } else {
                        $seenIds[$section->id] = true;
                    }
                }

                $this->checkImage($section, 'image_ref', "{$base}/image_ref", $allowlist, $violations);
                $this->checkItems($section, $base, $allowlist, $violations);
                $this->checkCta($section, $base, $pageIds, $slugs, $violations);
            }
        }
    }

    /**
     * @param  list<string>|null  $allowlist
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkItems(stdClass $section, string $base, ?array $allowlist, array &$violations): void
    {
        if (! property_exists($section, 'items')) {
            return;
        }
        if (! is_array($section->items) || ! array_is_list($section->items)) {
            return;
        }

        $count = count($section->items);
        if ($count > 8) {
            $violations[] = new SiteSpecViolation(
                "{$base}/items",
                'itemCount',
                "{$base}/items: expected at most 8 items, got {$count}.",
            );
        }

        foreach ($this->items($section) as $itemIndex => $item) {
            $this->checkImage($item, 'image_ref', "{$base}/items/{$itemIndex}/image_ref", $allowlist, $violations);
        }
    }

    /**
     * @param  list<string>|null  $allowlist
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkImage(stdClass $owner, string $field, string $path, ?array $allowlist, array &$violations): void
    {
        if (! property_exists($owner, $field) || $allowlist === null || ! is_string($owner->{$field})) {
            return;
        }
        if (! in_array($owner->{$field}, $allowlist, true)) {
            $violations[] = new SiteSpecViolation(
                $path,
                'mediaAllowlist',
                "{$path}: \"{$owner->{$field}}\" is not in media_allowlist.",
            );
        }
    }

    /**
     * @param  list<string>  $pageIds
     * @param  list<string>  $slugs
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkCta(stdClass $section, string $base, array $pageIds, array $slugs, array &$violations): void
    {
        $cta = $this->object($section, 'cta');
        if ($cta === null || ! property_exists($cta, 'href') || ! is_string($cta->href)) {
            return;
        }

        $examined = trim($cta->href);
        $path = "{$base}/cta/href";
        if (str_starts_with($examined, '//') || str_contains($examined, '://') || (! str_starts_with($examined, '#') && ! str_starts_with($examined, '/'))) {
            $violations[] = new SiteSpecViolation(
                $path,
                'cta',
                "{$path}: \"{$cta->href}\" is not a same-site target.",
            );

            return;
        }

        if (str_starts_with($examined, '#')) {
            $target = substr($examined, 1);
            if (! in_array($target, $pageIds, true)) {
                $violations[] = new SiteSpecViolation(
                    $path,
                    'cta',
                    "{$path}: \"{$cta->href}\" does not identify a section on this page.",
                );
            }

            return;
        }

        $slug = substr($examined, 1);
        if (! in_array($slug, $slugs, true)) {
            $violations[] = new SiteSpecViolation(
                $path,
                'cta',
                "{$path}: \"{$cta->href}\" does not identify a page in this SiteSpec.",
            );
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function length(stdClass $owner, string $field, string $path, int $maximum, array &$violations): void
    {
        if (! property_exists($owner, $field) || ! is_string($owner->{$field})) {
            return;
        }

        $measured = mb_strlen(trim($owner->{$field}));
        if ($measured > $maximum) {
            $violations[] = new SiteSpecViolation(
                $path,
                'maxLength',
                "{$path}: trimmed length {$measured} exceeds {$maximum}.",
            );
        }
    }

    private function object(stdClass $owner, string $field): ?stdClass
    {
        if (! property_exists($owner, $field) || ! $owner->{$field} instanceof stdClass) {
            return null;
        }

        return $owner->{$field};
    }

    /**
     * @return list<stdClass>
     */
    private function pages(stdClass $candidate): array
    {
        if (! property_exists($candidate, 'pages') || ! is_array($candidate->pages) || ! array_is_list($candidate->pages)) {
            return [];
        }

        return array_values(array_filter(
            $candidate->pages,
            fn (mixed $page) => $page instanceof stdClass,
        ));
    }

    /**
     * @return list<stdClass>
     */
    private function sections(stdClass $page): array
    {
        if (! property_exists($page, 'sections') || ! is_array($page->sections) || ! array_is_list($page->sections)) {
            return [];
        }

        return array_values(array_filter(
            $page->sections,
            fn (mixed $section) => $section instanceof stdClass,
        ));
    }

    /**
     * @return list<stdClass>
     */
    private function items(stdClass $section): array
    {
        if (! property_exists($section, 'items') || ! is_array($section->items) || ! array_is_list($section->items)) {
            return [];
        }

        return array_values(array_filter(
            $section->items,
            fn (mixed $item) => $item instanceof stdClass,
        ));
    }

    /**
     * @return list<string>|null
     */
    private function stringList(mixed $value): ?array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            return null;
        }

        foreach ($value as $item) {
            if (! is_string($item)) {
                return null;
            }
        }

        return $value;
    }

    private function snapshot(mixed $candidate): string
    {
        try {
            return json_encode($candidate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new LogicException('Semantic candidate cannot be encoded.', 0, $exception);
        }
    }
}
