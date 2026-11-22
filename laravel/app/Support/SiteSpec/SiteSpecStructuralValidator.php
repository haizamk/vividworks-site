<?php

namespace App\Support\SiteSpec;

use stdClass;

/**
 * Structural check for Canonical SiteSpec Schema v1.1.
 *
 * The schema file declares no dialect. SD-02 stays deferred, so this class
 * does not load a JSON Schema library and does not assign Draft-07 or
 * Draft 2020-12. It evaluates only the keywords that appear in the canonical
 * file: type, properties, required, additionalProperties set to false, items
 * as one schema, and enum. Any other keyword fails closed.
 *
 * Pass a stdClass produced by json_decode() without the associative flag.
 * An associative array collapses JSON objects and arrays, so it is rejected
 * with an explicit message instead of being treated as an object.
 *
 * DesignCallbackRules calls this validator for a success sitespec before it
 * returns a callback. The envelope schema_version is 1.1. prompt_version
 * stays on revision_metadata and is not read here.
 *
 * Technical debt:
 * TD-SITESPEC-1 Replace this walker after SD-02 names a dialect and a library.
 *   Opis 2.6.2 can load this file, and its parser defaults to draft 2020-12
 *   when $schema is absent. That evidence is not a dialect decision.
 * TD-SITESPEC-2 The callback path calls this walker. It is not a JSON Schema
 *   library, and the dialect remains deferred.
 * TD-SITESPEC-3 OD-1 through OD-12 semantic checks are outside this class.
 */
class SiteSpecStructuralValidator
{
    public const SCHEMA_SHA256 = '0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21';

    private const OBJECT_KEYS = ['type', 'additionalProperties', 'required', 'properties'];

    private const ARRAY_KEYS = ['type', 'items'];

    private const STRING_KEYS = ['type', 'enum'];

    private function __construct(private stdClass $schema) {}

    public static function fromCanonicalFile(?string $path = null): self
    {
        $path ??= dirname(__DIR__, 3).'/resources/sitespec/canonical-sitespec-schema-v1.1.json';
        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            throw new SiteSpecSchemaException("Schema file is unreadable: {$path}");
        }

        $hash = hash('sha256', $bytes);
        if ($hash !== self::SCHEMA_SHA256) {
            throw new SiteSpecSchemaException("Schema SHA-256 {$hash} does not match the approved file.");
        }

        $schema = json_decode($bytes);
        if (! $schema instanceof stdClass) {
            throw new SiteSpecSchemaException('Schema root must be a JSON object.');
        }

        $validator = new self($schema);
        $validator->assertSupported($schema, '');

        return $validator;
    }

    /**
     * Evaluate a decoded schema that is not the canonical file.
     * Used to prove an unsupported keyword fails closed.
     */
    public static function fromDecodedSchema(stdClass $schema): self
    {
        $validator = new self($schema);
        $validator->assertSupported($schema, '');

        return $validator;
    }

    public function validate(mixed $instance): SiteSpecValidationResult
    {
        $violations = [];
        $this->check($instance, $this->schema, '', $violations);

        return new SiteSpecValidationResult($violations === [], $violations);
    }

    private function assertSupported(stdClass $schema, string $pointer): void
    {
        if (! isset($schema->type) || ! is_string($schema->type)) {
            throw new SiteSpecSchemaException("Schema at '{$pointer}' has no string type.");
        }

        $allowed = match ($schema->type) {
            'object' => self::OBJECT_KEYS,
            'array' => self::ARRAY_KEYS,
            'string' => self::STRING_KEYS,
            default => throw new SiteSpecSchemaException("Schema at '{$pointer}' uses unsupported type '{$schema->type}'."),
        };

        foreach (array_keys(get_object_vars($schema)) as $key) {
            if (! in_array($key, $allowed, true)) {
                throw new SiteSpecSchemaException("Schema at '{$pointer}' uses unsupported keyword '{$key}'.");
            }
        }

        if ($schema->type === 'object') {
            $this->assertObjectSchema($schema, $pointer);
        } elseif ($schema->type === 'array') {
            $this->assertArraySchema($schema, $pointer);
        } elseif (property_exists($schema, 'enum')) {
            $this->assertStringEnum($schema->enum, $pointer);
        }
    }

    private function assertObjectSchema(stdClass $schema, string $pointer): void
    {
        if (! property_exists($schema, 'additionalProperties') || $schema->additionalProperties !== false) {
            throw new SiteSpecSchemaException("Schema at '{$pointer}' must set additionalProperties to false.");
        }
        if (! property_exists($schema, 'properties') || ! $schema->properties instanceof stdClass) {
            throw new SiteSpecSchemaException("Schema at '{$pointer}' must define properties as an object.");
        }
        if (property_exists($schema, 'required')) {
            $this->assertStringList($schema->required, $pointer, 'required');
        }

        foreach (get_object_vars($schema->properties) as $name => $child) {
            if (! $child instanceof stdClass) {
                throw new SiteSpecSchemaException("Schema at '{$pointer}' has a non-object property '{$name}'.");
            }
            $this->assertSupported($child, $pointer.'/properties/'.$name);
        }
    }

    private function assertArraySchema(stdClass $schema, string $pointer): void
    {
        if (! property_exists($schema, 'items') || ! $schema->items instanceof stdClass) {
            throw new SiteSpecSchemaException("Schema at '{$pointer}' must define items as one schema object.");
        }

        $this->assertSupported($schema->items, $pointer.'/items');
    }

    /**
     * @return void
     */
    private function assertStringEnum(mixed $enum, string $pointer): void
    {
        $this->assertStringList($enum, $pointer, 'enum');
    }

    private function assertStringList(mixed $value, string $pointer, string $keyword): void
    {
        if (! is_array($value) || ! array_is_list($value)) {
            throw new SiteSpecSchemaException("Schema at '{$pointer}' must define {$keyword} as a list.");
        }
        foreach ($value as $item) {
            if (! is_string($item)) {
                throw new SiteSpecSchemaException("Schema at '{$pointer}' has a non-string {$keyword} entry.");
            }
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function check(mixed $instance, stdClass $schema, string $path, array &$violations): void
    {
        if ($schema->type === 'object') {
            $this->checkObject($instance, $schema, $path, $violations);

            return;
        }
        if ($schema->type === 'array') {
            $this->checkArray($instance, $schema, $path, $violations);

            return;
        }

        $this->checkString($instance, $schema, $path, $violations);
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkObject(mixed $instance, stdClass $schema, string $path, array &$violations): void
    {
        if (! $instance instanceof stdClass) {
            $message = "{$this->displayPath($path)}: expected object, got {$this->typeName($instance)}.";
            if (is_array($instance)) {
                $message .= ' Decode JSON objects as stdClass, without the associative flag.';
            }
            $violations[] = new SiteSpecViolation($this->displayPath($path), 'type', $message);

            return;
        }

        $required = $schema->required ?? [];
        foreach ($required as $name) {
            if (! property_exists($instance, $name)) {
                $fieldPath = $this->join($path, $name);
                $violations[] = new SiteSpecViolation(
                    $fieldPath,
                    'required',
                    "{$fieldPath}: required field \"{$name}\" is missing.",
                );
            }
        }

        foreach (get_object_vars($instance) as $name => $value) {
            if (! property_exists($schema->properties, $name)) {
                $violations[] = new SiteSpecViolation(
                    $this->displayPath($path),
                    'additionalProperties',
                    "{$this->displayPath($path)}: unexpected field \"{$name}\".",
                );

                continue;
            }
            if (property_exists($instance, $name)) {
                $this->check($value, $schema->properties->{$name}, $this->join($path, $name), $violations);
            }
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkArray(mixed $instance, stdClass $schema, string $path, array &$violations): void
    {
        if (! is_array($instance) || ! array_is_list($instance)) {
            $violations[] = new SiteSpecViolation(
                $this->displayPath($path),
                'type',
                "{$this->displayPath($path)}: expected array, got {$this->typeName($instance)}.",
            );

            return;
        }

        foreach ($instance as $index => $item) {
            $this->check($item, $schema->items, $this->join($path, (string) $index), $violations);
        }
    }

    /**
     * @param  list<SiteSpecViolation>  $violations
     */
    private function checkString(mixed $instance, stdClass $schema, string $path, array &$violations): void
    {
        if (! is_string($instance)) {
            $violations[] = new SiteSpecViolation(
                $this->displayPath($path),
                'type',
                "{$this->displayPath($path)}: expected string, got {$this->typeName($instance)}.",
            );

            return;
        }

        if (property_exists($schema, 'enum') && ! in_array($instance, $schema->enum, true)) {
            $allowed = implode(', ', $schema->enum);
            $violations[] = new SiteSpecViolation(
                $this->displayPath($path),
                'enum',
                "{$this->displayPath($path)}: \"{$instance}\" is not an allowed value ({$allowed}).",
            );
        }
    }

    private function join(string $path, string $segment): string
    {
        return $path.'/'.str_replace(['~', '/'], ['~0', '~1'], $segment);
    }

    private function displayPath(string $path): string
    {
        return $path === '' ? '/' : $path;
    }

    private function typeName(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'number',
            is_string($value) => 'string',
            $value instanceof stdClass => 'object',
            is_array($value) => 'array',
            default => get_debug_type($value),
        };
    }
}
