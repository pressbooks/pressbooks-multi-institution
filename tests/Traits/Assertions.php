<?php

namespace Tests\Traits;

use Illuminate\Database\Capsule\Manager;
use ReflectionFunction;

trait Assertions
{
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

    protected function assertHasCallbackAction(string $hook, string $expectedClass): bool
    {
        global $wp_filter;

        $this->assertTrue(has_action($hook));

        foreach ($wp_filter[$hook] ?? [] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function'])) {
                    continue;
                }

                $closure = new ReflectionFunction($callback['function']);

                if($closure->getClosureScopeClass()?->getName() === $expectedClass) {
                    return true;
                }
            }
        }

        return false;
    }
}
