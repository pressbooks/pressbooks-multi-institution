<?php

/**
 * Plugin Name: Pressbooks Shared Network
 * Plugin URI: https://github.com/pressbooks/pressbooks-multi-institution
 * Description: Tools for managing Pressbooks networks shared by multiple institutions
 * Version: 0.1.0
 * Author: Pressbooks (Book Oven Inc.)
 * Author URI: https://pressbooks.org
 * Requires PHP: 8.1
 * Pressbooks tested up to: 6.16.0
 * Text Domain: pressbooks-multi-institution
 * License: GPL v3 or later
 * Network: True
 */

use PressbooksMultiInstitution\Bootstrap;
use PressbooksMultiInstitution\Commands\ResetDbSchemaCommand;
use PressbooksMultiInstitution\Database\Migration;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Services\PermissionsManager;

// TODO: Check if this is the best way to check for Pressbooks.
if (!class_exists('Pressbooks\Book')) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    } else {
        $title = __('Missing dependencies', 'pressbooks-multi-institution');
        $body = __(
            'Please run <code>composer install</code> from the root of the plugin directory.',
            'pressbooks-multi-institution'
        );

        wp_die("<h1>{$title}</h1><p>{$body}</p>");
    }
}

register_activation_hook(__FILE__, [Migration::class, 'migrate']);
register_activation_hook(__FILE__, function () {
    $managerIds = InstitutionUser::query()->managers()->pluck('user_id')->toArray();
    PermissionsManager::syncRestrictedUsers($managerIds, []);
});

register_deactivation_hook(__FILE__, [PermissionsManager::class, 'revokeInstitutionalManagersPrivileges']);

add_action('plugins_loaded', [Bootstrap::class, 'run']);

add_action('cli_init', function () {
    if (! defined('WP_CLI')) {
        return;
    }

    WP_CLI::add_command('pb:reset-db-schema', function ($args, $assoc_args) {
        (new ResetDbSchemaCommand)->__invoke($args, $assoc_args) ?
            WP_CLI::success('Database successfully reset.') : WP_CLI::error('Error resetting database.');
    });
});
