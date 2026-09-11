<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Runtime settings an administrator edits, as key/value rows.
 *
 * Pinned to the production connection for the same reason DemoSession is:
 * these are global settings about the product, and a demo request that reads
 * one must read the real value rather than whatever sits in the sandbox
 * database. Reads across the switch are allowed; only writes are guarded, and
 * nothing writes here outside the admin panel.
 *
 * Reads are memoised for the request and never allowed to throw — a missing
 * table (the migration has not run yet) must not take the public pages down.
 */
class Setting extends Model
{
    /** Never follows the demo connection switch. */
    protected $connection = 'mysql';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    /** All rows, loaded once per request. Null until the first read. */
    protected static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_()[$key] ?? $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** Write one setting and drop the memo so the next read sees it. */
    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );

        static::$cache = null;
    }

    /** Forget the memo — for tests, and after a bulk write. */
    public static function flushCache(): void
    {
        static::$cache = null;
    }

    /** @return array<string, string|null> */
    protected static function all_(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        try {
            return static::$cache = static::query()->pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            // Before the migration runs, or if the table is unreachable: every
            // caller falls back to its default rather than erroring out.
            Log::warning('Settings table unavailable', ['error' => $e->getMessage()]);

            return static::$cache = [];
        }
    }
}
