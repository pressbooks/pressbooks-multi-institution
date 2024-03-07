<?php

namespace Tests\Traits;

use WP_Hook;

trait Utils
{
    /**
     * This method runs a callback without triggering a given filter.
     * It returns the expected value from the callback.
     *
     * @param  string  $hook The hook name to be skipped
     * @param  callable  $callback The method that should run
     * @return mixed The value from the callback method
     */
    protected function runWithoutFilter(string $hook, callable $callback): mixed
    {
        global $wp_filter;

        $handler = $wp_filter[$hook] ?? null;

        if ($handler instanceof WP_Hook) {
            unset($wp_filter[$hook]);
        }

        $response = call_user_func($callback);

        if ($handler instanceof WP_Hook) {
            $wp_filter[$hook] = $handler;
        }

        return $response;
    }
}
