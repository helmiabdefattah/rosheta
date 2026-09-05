<?php

namespace App\Demo;

use RuntimeException;

/**
 * Thrown when a demo request tries to write to the production database.
 *
 * This is never expected in normal operation: seeing it means a model,
 * repository or raw query escaped the request-scoped connection switch. The
 * write is prevented, not merely reported.
 */
class DemoProductionWriteException extends RuntimeException
{
    public function __construct(public readonly string $sql, public readonly string $connectionName)
    {
        parent::__construct(
            "A demo request attempted to write to the production connection [{$connectionName}]. "
            ."The write was blocked. SQL: {$sql}"
        );
    }
}
