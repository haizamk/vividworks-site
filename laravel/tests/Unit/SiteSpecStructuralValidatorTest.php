<?php

namespace Tests\Unit;

use App\Support\SiteSpec\SiteSpecSchemaException;
use App\Support\SiteSpec\SiteSpecStructuralValidator;
use PHPUnit\Framework\TestCase;
use stdClass;

class SiteSpecStructuralValidatorTest extends TestCase
{
    private SiteSpecStructuralValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = SiteSpecStructuralValidator::fromCanonicalFile();
    }

    public function test_canonical_schema_file_keeps_the_approved_hash(): void
    {
        $path = dirname(__DIR__, 2).'/resources/sitespec/canonical-sitespec-schema-v1.1.json';
        $hash = hash('sha256', file_get_contents($path));

        $this->assertSame(SiteSpecStructuralValidator::SCHEMA_SHA256, $hash);
    }

    public function test_accepts_the_synthetic_valid_fixture(): void
    {
        $result = $this->validator->validate($this->fixture('valid.json'));

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->violations);
        $this->assertSame([], $result->messages());
    }

    public function test_rejects_an_unexpected_additional_field(): void
    {
        $result = $this->validator->validate($this->fixture('additional-field.json'));

        $this->assertFalse($result->valid);
        $this->assertCount(1, $result->violations);
        $this->assertSame('/design/theme', $result->violations[0]->path);
        $this->assertSame('additionalProperties', $result->violations[0]->keyword);
        $this->assertSame(
            '/design/theme: unexpected field "secondary_color".',
            $result->violations[0]->message,
        );
    }

    public function test_rejects_an_invalid_enum_value(): void
    {
        $result = $this->validator->validate($this->fixture('invalid-enum.json'));

        $this->assertFalse($result->valid);
        $this->assertCount(1, $result->violations);
        $this->assertSame('/design/theme/font_token', $result->violations[0]->path);
        $this->assertSame('enum', $result->violations[0]->keyword);
        $this->assertSame(
            '/design/theme/font_token: "serif-modern" is not an allowed value (sans-modern, sans-classic, serif-elegant).',
            $result->violations[0]->message,
        );
    }

    public function test_rejects_an_invalid_data_type(): void
    {
        $result = $this->validator->validate($this->fixture('invalid-type.json'));

        $this->assertFalse($result->valid);
        $this->assertCount(1, $result->violations);
        $this->assertSame('/site/name', $result->violations[0]->path);
        $this->assertSame('type', $result->violations[0]->keyword);
        $this->assertSame(
            '/site/name: expected string, got integer.',
            $result->violations[0]->message,
        );
    }

    public function test_rejects_an_associative_array_instead_of_decoding_it_as_an_object(): void
    {
        $decoded = json_decode($this->fixtureBytes('valid.json'), true);
        $result = $this->validator->validate($decoded);

        $this->assertFalse($result->valid);
        $this->assertSame('type', $result->violations[0]->keyword);
        $this->assertStringContainsString('stdClass', $result->violations[0]->message);
    }

    public function test_unsupported_schema_keyword_fails_closed(): void
    {
        $schema = json_decode((string) file_get_contents(
            dirname(__DIR__, 2).'/resources/sitespec/canonical-sitespec-schema-v1.1.json',
        ));
        $this->assertInstanceOf(stdClass::class, $schema);
        $schema->pattern = '.*';

        $this->expectException(SiteSpecSchemaException::class);
        $this->expectExceptionMessage("unsupported keyword 'pattern'");

        SiteSpecStructuralValidator::fromDecodedSchema($schema);
    }

    private function fixture(string $name): stdClass
    {
        $decoded = json_decode($this->fixtureBytes($name));
        $this->assertInstanceOf(stdClass::class, $decoded);

        return $decoded;
    }

    private function fixtureBytes(string $name): string
    {
        $path = dirname(__DIR__).'/Fixtures/SiteSpec/'.$name;
        $bytes = file_get_contents($path);
        $this->assertNotFalse($bytes);

        return $bytes;
    }
}
