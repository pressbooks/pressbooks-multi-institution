<?php

/**
 * Plugin Name: Pressbooks Multi Institution
 * Plugin URI: https://pressbooks.org
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
use PressbooksMultiInstitution\Database\Migration;

// TODO: Check if this is the best way to check for Pressbooks.
if (!class_exists('Pressbooks\Book')) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    } else {
        $title = __('Missing dependencies', 'PressbooksMultiInstitution');
        $body = __(
            'Please run <code>composer install</code> from the root of the plugin directory.',
            'pressbooks-multi-institution'
        );

        wp_die("<h1>{$title}</h1><p>{$body}</p>");
    }
}

register_activation_hook(__FILE__, [Migration::class, 'migrate']);
register_deactivation_hook(__FILE__, [Migration::class, 'rollback']);

add_action('plugins_loaded', [Bootstrap::class, 'run']);
add_action('init', function () {
    load_plugin_textdomain('pressbooks-multi-institution', false, 'pressbooks-multi-institution/languages/');
});
