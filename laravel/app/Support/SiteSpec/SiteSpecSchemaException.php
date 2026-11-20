<?php

namespace App\Support\SiteSpec;

use RuntimeException;

/**
 * The canonical schema file cannot be evaluated by the isolated structural checker.
 */
class SiteSpecSchemaException extends RuntimeException
{
}
