<?php
/**
 * Plugin Name: Pressbooks Plugin Scaffold
 * Plugin URI: https://pressbooks.org
 * Description: A scaffold for Pressbooks plugins.
 * Version: 0.0.1
 * Author: Pressbooks (Book Oven Inc.)
 * Author URI: https://pressbooks.org
 * Requires PHP: 8.1
 * Pressbooks tested up to: 6.16.0
 * Text Domain: pressbooks-plugin-scaffold
 * License: GPL v3 or later
 * Network: True
 */

use PressbooksPluginScaffold\Bootstrap;
use PressbooksPluginScaffold\Database\Migration;

// TODO: Check if this is the best way to check for Pressbooks.
if (!class_exists('Pressbooks\Book')) {
    if (file_exists(__DIR__.'/vendor/autoload.php')) {
        require_once __DIR__.'/vendor/autoload.php';
    } else {
        $title = __('Missing dependencies', 'PressbooksPluginScaffold');
        $body = __(
            'Please run <code>composer install</code> from the root of the plugin directory.',
            'pressbooks-plugin-scaffold'
        );

        wp_die("<h1>{$title}</h1><p>{$body}</p>");
    }
}

register_activation_hook(__FILE__, [Migration::class, 'migrate']);

add_action('plugins_loaded', [Bootstrap::class, 'run']);
