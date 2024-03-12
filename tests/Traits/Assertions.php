<?php

namespace Tests\Traits;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Builder;
use ReflectionFunction;

trait Assertions
{
    protected function assertTableMissing(string $table): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $this->assertFalse($schema->hasTable($table));
    }

    protected function assertTableExists(string $table, ?array $columns = null): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $this->assertTrue($schema->hasTable($table));

        if ($columns) {
            $this->assertEquals($columns, $schema->getColumnListing($table));
        }
    }

    protected function assertDatabaseCount(string $table, int $count): void
    {
        /** @var Manager $db */
        $db = app('db');

        $this->assertEquals($count, $db->table($table)->count());
    }

    protected function assertDatabaseEmpty(string $table): void
    {
        $this->assertDatabaseCount($table, 0);
    }

    protected function assertHasCallbackAction(string $hook, string $expectedClass): void
    {
        global $wp_filter;

        $this->assertTrue(has_action($hook));

        $hasHandler = false;

        foreach ($wp_filter[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function'])) {
                    continue;
                }

                $closure = new ReflectionFunction($callback['function']);

                if($closure->getClosureScopeClass()?->getName() === $expectedClass) {
                    $hasHandler = true;

                    break;
                }
            }
        }

        $this->assertTrue($hasHandler);
    }
}
