<?php

namespace Tests\Unit;

use App\Support\SiteSpec\SiteSpecRequestContext;
use App\Support\SiteSpec\SiteSpecSemanticValidator;
use App\Support\SiteSpec\SiteSpecViolation;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Synthetic SiteSpec fixtures. These are not execution #80 and not an official W1 fixture.
 */
class SiteSpecSemanticValidatorTest extends TestCase
{
    private SiteSpecSemanticValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SiteSpecSemanticValidator;
    }

    public function test_page_plans_for_one_two_and_three_pages_pass(): void
    {
        $this->assertTrue($this->validator->validate($this->plan(1), $this->context(1))->valid);
        $this->assertTrue($this->validator->validate($this->plan(2), $this->context(2))->valid);
        $this->assertTrue($this->validator->validate($this->plan(3), $this->context(3))->valid);
    }

    public function test_trimmed_length_accepts_the_maximum_and_rejects_one_more(): void
    {
        foreach ($this->lengthCases() as $label => [$assign, $path, $maximum]) {
            $atMaximum = $this->plan(1);
            $assign($atMaximum, str_repeat('a', $maximum));
            $this->assertTrue(
                $this->validator->validate($atMaximum, $this->context())->valid,
                $label.' at the maximum should pass',
            );

            $over = $this->plan(1);
            $assign($over, str_repeat('a', $maximum + 1));
            $result = $this->validator->validate($over, $this->context());
            $this->assertFalse($result->valid, $label.' over the maximum should fail');
            $this->assertSame($path, $this->violation($result->violations, 'maxLength', $path)->path);
        }
    }

    public function test_whitespace_is_measured_after_trimming_and_the_stored_value_stays(): void
    {
        $spec = $this->plan(1);
        $spec->site->name = '  '.str_repeat('a', 120).'  ';
        $before = $spec->site->name;

        $this->assertTrue($this->validator->validate($spec, $this->context())->valid);
        $this->assertSame($before, $spec->site->name);

        $spec->site->name = '  '.str_repeat('a', 121)."\n";
        $before = $spec->site->name;
        $result = $this->validator->validate($spec, $this->context());

        $this->assertFalse($result->valid);
        $this->assertSame('/site/name', $this->violation($result->violations, 'maxLength', '/site/name')->path);
        $this->assertSame($before, $spec->site->name);
    }

    public function test_empty_and_whitespace_only_strings_pass_od_1_alone(): void
    {
        foreach (['', '   ', "\n\t"] as $value) {
            $spec = $this->plan(1);
            $spec->site->name = $value;
            $spec->pages[0]->sections[0]->description = $value;

            $this->assertTrue($this->validator->validate($spec, $this->context())->valid);
            $this->assertSame($value, $spec->site->name);
            $this->assertSame($value, $spec->pages[0]->sections[0]->description);
        }
    }

    public function test_hex_color_accepts_both_cases_and_rejects_invalid_hex(): void
    {
        foreach (['#AABBCC', '#aabbcc', '#AbCdEf'] as $color) {
            $spec = $this->plan(1);
            $spec->design->theme->primary_color = $color;
            $this->assertTrue($this->validator->validate($spec, $this->context())->valid, $color);
            $this->assertSame($color, $spec->design->theme->primary_color);
        }

        foreach (['#abc', '#aabbccdd', 'aabbcc', '#gg0000'] as $color) {
            $spec = $this->plan(1);
            $spec->design->theme->primary_color = $color;
            $result = $this->validator->validate($spec, $this->context());
            $this->assertFalse($result->valid);
            $this->assertSame('pattern', $this->violation($result->violations, 'pattern', '/design/theme/primary_color')->keyword);
            $this->assertSame($color, $spec->design->theme->primary_color);
        }
    }

    public function test_section_ids_match_only_the_approved_pattern(): void
    {
        $valid = ['abc', 'a_b', 'a-b', 'a'.str_repeat('b', 39)];
        foreach ($valid as $id) {
            $spec = $this->plan(1);
            $spec->pages[0]->sections[0]->id = $id;
            $spec->pages[0]->sections[0]->cta = $this->cta('#'.$id);
            $this->assertTrue($this->validator->validate($spec, $this->context())->valid, $id);
        }

        foreach (['ab', 'a'.str_repeat('b', 40), 'Abc', '_ab'] as $id) {
            $spec = $this->plan(1);
            $spec->pages[0]->sections[0]->id = $id;
            $result = $this->validator->validate($spec, $this->context());
            $this->assertFalse($result->valid, $id);
            $this->assertSame('pattern', $this->violation($result->violations, 'pattern', '/pages/0/sections/0/id')->keyword);
        }
    }

    public function test_a_slug_outside_slug_strict_is_not_rejected_for_that_pattern(): void
    {
        $spec = $this->plan(1);
        $spec->pages[0]->slug = 'Home_Page';
        $result = $this->validator->validate($spec, $this->context());

        $this->assertFalse($result->valid);
        $this->assertSame('pagePlan', $result->violations[0]->keyword);
        foreach ($result->violations as $violation) {
            $this->assertSame('pagePlan', $violation->keyword);
            $this->assertStringNotContainsString('SLUG_STRICT', $violation->message);
        }
        $this->assertSame('Home_Page', $spec->pages[0]->slug);
    }

    public function test_image_ref_uses_allowlist_membership_and_not_a_regex(): void
    {
        $spec = $this->plan(1);
        $spec->pages[0]->sections[0]->image_ref = 'HAS SPACE';
        $this->assertTrue($this->validator->validate($spec, $this->context(1, ['tpl-a'], ['HAS SPACE']))->valid);

        $missing = $this->plan(1);
        $missing->pages[0]->sections[0]->image_ref = 'HAS SPACE';
        $result = $this->validator->validate($missing, $this->context());
        $this->assertSame(
            'mediaAllowlist',
            $this->violation($result->violations, 'mediaAllowlist', '/pages/0/sections/0/image_ref')->keyword,
        );
        foreach ($result->violations as $violation) {
            $this->assertNotSame('pattern', $violation->keyword);
        }
    }

    public function test_wrong_order_wrong_count_and_unsupported_page_counts_are_rejected(): void
    {
        $wrongOrder = $this->plan(3);
        $wrongOrder->pages[1]->slug = 'contact';
        $wrongOrder->pages[2]->slug = 'about';
        $order = $this->validator->validate($wrongOrder, $this->context(3));
        $this->assertSame('pagePlan', $this->violation($order->violations, 'pagePlan', '/pages')->keyword);

        $short = $this->plan(1);
        $shortResult = $this->validator->validate($short, $this->context(2));
        $this->assertSame('pagePlan', $this->violation($shortResult->violations, 'pagePlan', '/pages')->keyword);
        $this->assertSame('home', $short->pages[0]->slug);

        foreach ([null, '2', 2.0, 0, 4] as $pageCount) {
            $spec = $this->plan(1);
            $result = $this->validator->validate($spec, $this->context($pageCount));
            $this->assertFalse($result->valid);
            $this->assertSame('context', $this->violation($result->violations, 'context', '/context/page_count')->keyword);
            $this->assertSame('home', $spec->pages[0]->slug);
            $this->assertFalse(property_exists($spec, 'page_count'));
        }
    }

    public function test_section_ids_are_unique_across_the_whole_sitespec(): void
    {
        $samePage = $this->plan(1);
        $samePage->pages[0]->sections[] = $this->section('home-hero');
        $same = $this->validator->validate($samePage, $this->context());
        $this->assertSame('unique', $this->violation($same->violations, 'unique', '/pages/0/sections/1/id')->keyword);

        $across = $this->plan(2);
        $across->pages[1]->sections[0]->id = 'home-hero';
        $other = $this->validator->validate($across, $this->context(2));
        $this->assertSame('unique', $this->violation($other->violations, 'unique', '/pages/1/sections/0/id')->keyword);
    }

    public function test_section_and_item_counts_use_the_approved_bounds(): void
    {
        $this->assertTrue($this->validator->validate($this->withSections(1), $this->context())->valid);
        $this->assertTrue($this->validator->validate($this->withSections(8), $this->context())->valid);
        $this->assertSame('sectionCount', $this->only('sectionCount', $this->withSections(0)));
        $this->assertSame('sectionCount', $this->only('sectionCount', $this->withSections(9)));

        $this->assertTrue($this->validator->validate($this->withItems(0), $this->context())->valid);
        $this->assertTrue($this->validator->validate($this->withItems(8), $this->context())->valid);
        $absent = $this->plan(1);
        $this->assertFalse(property_exists($absent->pages[0]->sections[0], 'items'));
        $this->assertTrue($this->validator->validate($absent, $this->context())->valid);
        $this->assertSame('itemCount', $this->only('itemCount', $this->withItems(9)));
    }

    public function test_template_ids_are_checked_against_the_supplied_list(): void
    {
        $spec = $this->plan(1);
        $spec->design->recommended_template_id = 'tpl-a';
        $spec->design->selected_template_id = 'tpl-b';
        $this->assertTrue($this->validator->validate($spec, $this->context(1, ['tpl-a', 'tpl-b']))->valid);

        $result = $this->validator->validate($spec, $this->context(1, ['tpl-a']));
        $this->assertSame('template', $this->violation($result->violations, 'template', '/design/selected_template_id')->keyword);
        $this->assertSame('tpl-b', $spec->design->selected_template_id);
        $this->assertSame('ai', $spec->design->selection_source);
        $this->assertSame('ai', $spec->design->theme_source);
    }

    public function test_missing_runtime_context_is_rejected_and_not_bypassed(): void
    {
        $spec = $this->plan(1);
        $noTemplates = $this->validator->validate($spec, $this->context(1, null, ['img-1']));
        $this->assertSame('context', $this->violation($noTemplates->violations, 'context', '/context/active_templates')->keyword);

        $spec->pages[0]->sections[0]->image_ref = 'img-1';
        $noMedia = $this->validator->validate($spec, $this->context(1, ['tpl-a'], null));
        $this->assertSame('context', $this->violation($noMedia->violations, 'context', '/context/media_allowlist')->keyword);
        foreach ($noMedia->violations as $violation) {
            $this->assertNotSame('mediaAllowlist', $violation->keyword);
        }
    }

    public function test_cta_targets_stay_inside_the_sitespec(): void
    {
        $validSection = $this->plan(2);
        $validSection->pages[0]->sections[0]->cta = $this->cta('  #home-hero  ');
        $this->assertTrue($this->validator->validate($validSection, $this->context(2))->valid);
        $this->assertSame('  #home-hero  ', $validSection->pages[0]->sections[0]->cta->href);

        $validPage = $this->plan(2);
        $validPage->pages[0]->sections[0]->cta = $this->cta('/contact');
        $this->assertTrue($this->validator->validate($validPage, $this->context(2))->valid);

        $missingSection = $this->plan(2);
        $missingSection->pages[0]->sections[0]->cta = $this->cta('#contact-hero');
        $this->assertStringContainsString(
            'section on this page',
            $this->violation(
                $this->validator->validate($missingSection, $this->context(2))->violations,
                'cta',
                '/pages/0/sections/0/cta/href',
            )->message,
        );

        $missingPage = $this->plan(1);
        $missingPage->pages[0]->sections[0]->cta = $this->cta('/contact');
        $this->assertStringContainsString(
            'page in this SiteSpec',
            $this->violation(
                $this->validator->validate($missingPage, $this->context())->violations,
                'cta',
                '/pages/0/sections/0/cta/href',
            )->message,
        );

        foreach (['https://example.com', '//cdn.example.com'] as $href) {
            $spec = $this->plan(1);
            $spec->pages[0]->sections[0]->cta = $this->cta($href);
            $result = $this->validator->validate($spec, $this->context());
            $this->assertStringContainsString('not a same-site target', $this->violation($result->violations, 'cta', '/pages/0/sections/0/cta/href')->message);
            $this->assertSame($href, $spec->pages[0]->sections[0]->cta->href);
        }
    }

    public function test_validation_does_not_mutate_a_passing_or_failing_candidate(): void
    {
        $passing = $this->plan(1);
        $passing->site->name = '  Kedai Contoh  ';
        $passing->design->theme->primary_color = '#AbCdEf';
        $passing->pages[0]->sections[0]->cta = $this->cta('  #home-hero  ');
        $before = json_encode($passing);
        $this->assertTrue($this->validator->validate($passing, $this->context())->valid);
        $this->assertSame($before, json_encode($passing));
        $this->assertFalse(property_exists($passing, 'page_count'));
        $this->assertFalse(property_exists($passing, 'active_templates'));
        $this->assertFalse(property_exists($passing, 'media_allowlist'));

        $failing = $this->plan(1);
        $failing->design->theme->primary_color = '#ABCDEF';
        $failing->design->selected_template_id = 'not-listed';
        $before = json_encode($failing);
        $this->assertFalse($this->validator->validate($failing, $this->context())->valid);
        $this->assertSame($before, json_encode($failing));
        $this->assertSame('#ABCDEF', $failing->design->theme->primary_color);
        $this->assertSame('not-listed', $failing->design->selected_template_id);
        $this->assertSame('ai', $failing->design->selection_source);
    }

    private function only(string $keyword, stdClass $spec): string
    {
        $result = $this->validator->validate($spec, $this->context());
        $this->assertFalse($result->valid);

        return $this->violation($result->violations, $keyword, $result->violations[0]->path)->keyword;
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function violation(array $violations, string $keyword, string $path): SiteSpecViolation
    {
        foreach ($violations as $violation) {
            if ($violation->keyword === $keyword && $violation->path === $path) {
                return $violation;
            }
        }

        $this->fail("Missing {$keyword} violation at {$path}.");
    }

    private function context(mixed $pageCount = 1, ?array $templates = ['tpl-a'], ?array $media = ['img-1']): SiteSpecRequestContext
    {
        return new SiteSpecRequestContext($pageCount, $templates, $media);
    }

    private function plan(int $count): stdClass
    {
        $slugs = [1 => ['home'], 2 => ['home', 'contact'], 3 => ['home', 'about', 'contact']][$count];
        $pages = [];
        foreach ($slugs as $slug) {
            $pages[] = [
                'title' => 'Halaman',
                'slug' => $slug,
                'sections' => [$this->sectionArray($slug.'-hero')],
            ];
        }

        return $this->decode([
            'schema_version' => '1.1',
            'site' => [
                'name' => 'Kedai Contoh',
                'language' => 'ms',
                'category' => 'retail',
            ],
            'design' => [
                'recommended_template_id' => 'tpl-a',
                'selected_template_id' => 'tpl-a',
                'selection_source' => 'ai',
                'theme_source' => 'ai',
                'theme' => [
                    'primary_color' => '#112233',
                    'font_token' => 'sans-modern',
                ],
            ],
            'pages' => $pages,
        ]);
    }

    private function withSections(int $count): stdClass
    {
        $spec = $this->plan(1);
        $sections = [];
        for ($index = 1; $index <= $count; $index++) {
            $sections[] = $this->sectionArray(sprintf('sec-%02d', $index));
        }
        $spec->pages[0]->sections = $this->decodeList($sections);

        return $spec;
    }

    private function withItems(int $count): stdClass
    {
        $spec = $this->plan(1);
        $items = [];
        for ($index = 1; $index <= $count; $index++) {
            $items[] = ['heading' => 'Item '.$index];
        }
        $spec->pages[0]->sections[0]->items = $this->decodeList($items);

        return $spec;
    }

    /**
     * @return array<string, array{0: callable(stdClass, string): void, 1: string, 2: int}>
     */
    private function lengthCases(): array
    {
        return [
            'site.name' => [fn (stdClass $spec, string $value) => $spec->site->name = $value, '/site/name', 120],
            'site.category' => [fn (stdClass $spec, string $value) => $spec->site->category = $value, '/site/category', 80],
            'page title' => [fn (stdClass $spec, string $value) => $spec->pages[0]->title = $value, '/pages/0/title', 120],
            'section heading' => [fn (stdClass $spec, string $value) => $spec->pages[0]->sections[0]->heading = $value, '/pages/0/sections/0/heading', 180],
            'section description' => [fn (stdClass $spec, string $value) => $spec->pages[0]->sections[0]->description = $value, '/pages/0/sections/0/description', 1000],
            'item heading' => [function (stdClass $spec, string $value): void {
                $item = new stdClass;
                $item->heading = $value;
                $spec->pages[0]->sections[0]->items = [$item];
            }, '/pages/0/sections/0/items/0/heading', 180],
            'item description' => [function (stdClass $spec, string $value): void {
                $item = new stdClass;
                $item->heading = 'Item';
                $item->description = $value;
                $spec->pages[0]->sections[0]->items = [$item];
            }, '/pages/0/sections/0/items/0/description', 1000],
            'cta label' => [function (stdClass $spec, string $value): void {
                $spec->pages[0]->sections[0]->cta = $this->cta('#home-hero', $value);
            }, '/pages/0/sections/0/cta/label', 60],
        ];
    }

    private function section(string $id): stdClass
    {
        return $this->decode($this->sectionArray($id));
    }

    /**
     * @return array<string, string>
     */
    private function sectionArray(string $id): array
    {
        return [
            'id' => $id,
            'type' => 'hero',
            'heading' => 'Bahagian',
        ];
    }

    private function cta(string $href, string $label = 'Teruskan'): stdClass
    {
        return $this->decode([
            'label' => $label,
            'href' => $href,
        ]);
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function decode(array $value): stdClass
    {
        $decoded = json_decode(json_encode($value, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(stdClass::class, $decoded);

        return $decoded;
    }

    /**
     * @param  list<array<string, mixed>>  $values
     * @return list<stdClass>
     */
    private function decodeList(array $values): array
    {
        $decoded = json_decode(json_encode($values, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);

        return $decoded;
    }
}
