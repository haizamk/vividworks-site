<?php

namespace App\Support;

use JsonException;
use stdClass;

class CallbackPayloadHasher
{
    /**
     * SHA-256 of the canonical callback. Object keys are sorted. Array order is preserved.
     * The callback token is not part of DesignCallbackData, so it cannot enter the hash.
     */
    public static function hash(DesignCallbackData $callback): string
    {
        $canonical = self::canonicalize($callback->canonicalFields());

        try {
            $json = json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw $exception;
        }

        return hash('sha256', $json);
    }

    public static function canonicalize(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            $value = get_object_vars($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(self::canonicalize(...), $value);
        }

        ksort($value, SORT_STRING);
        $object = new stdClass;
        foreach ($value as $key => $child) {
            $object->{$key} = self::canonicalize($child);
        }

        return $object;
    }
}
