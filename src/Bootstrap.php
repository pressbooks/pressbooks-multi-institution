<?php

namespace PressbooksMultiInstitution;

use Kucrut\Vite;
use PressbooksMultiInstitution\Controllers\InstitutionsController;

/**
 * Class Bootstrap
 * @package PressbooksMultiInstitution
 *
 */
final class Bootstrap
{
    private static ?Bootstrap $instance = null;

    public static function run(): void
    {
        if (!self::$instance) {
            self::$instance = new self;

            self::$instance->setUp();
        }
    }

    public function setUp(): void
    {
        $this->registerActions();
        $this->registerBlade();
        $this->enqueueScripts();
        $this->fixSymlinks();
    }

    public function registerMenus(): void
    {
        $slug = is_network_admin() ? 'pb_multi_institution' : network_admin_url('admin.php?page=pb_multi_institution');

        add_menu_page(
            __('Institutions', 'pressbooks-multi-institution'),
            __('Institutions', 'pressbooks-multi-institution'),
            'manage_network',
            $slug,
            '',
            'dashicons-building',
        );

        add_action(
            'admin_bar_init',
            fn () => remove_submenu_page('pb_multi_institution', 'pb_multi_institution')
        );

        add_submenu_page(
            parent_slug: $slug,
            page_title: __('Institution List', 'pressbooks-multi-institution'),
            menu_title: __('Institution List', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institution',
            callback: function () {
                echo app(InstitutionsController::class)->index();
            },
        );
    }

    private function registerActions(): void
    {
        //TODO: Register actions here.
        add_action('network_admin_menu', [$this, 'registerMenus'], 11);
    }

    private function registerBlade(): void
    {
        app('Blade')->addNamespace(
            'PressbooksMultiInstitution',
            dirname(__DIR__).'/resources/views'
        );
    }

    private function enqueueScripts(): void
    {
        add_action('admin_enqueue_scripts', function () {
            Vite\enqueue_asset(
                plugin_dir_path(__DIR__).'dist',
                'resources/assets/js/pressbooks-multi-institution.js',
                ['handle' => 'pressbooks-multi-institution']
            );

            Vite\enqueue_asset(
                plugin_dir_path(__DIR__).'dist',
                'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
                ['handle' => 'pressbooks-multi-select'],
            );
        });
    }

    /**
     * This is a hack to fix the symlinks in the generated script tags meanwhile we found a better way to enqueue Vite assets
     * plugin_dir_path(__DIR__).'dist'
     * @return void
     */
    private function fixSymlinks(): void
    {
        add_filter('script_loader_src', function ($src, $handle) {
            if ($handle === 'pressbooks-multi-institution' || $handle === 'pressbooks-multi-select') {
                $src = preg_replace('|/app/srv/www/bedrocks/[^/]+/releases/\d+/web/|', '/', $src);
            }
            // If the handle doesn't match, return the original $src
            return $src;
        }, 10, 2);
        add_filter('style_loader_src', function ($src, $handle) {
            if (str_starts_with($handle, 'pressbooks-multi-institution') || str_starts_with($handle, 'pressbooks-multi-select')) {
                $src = preg_replace('|/app/srv/www/bedrocks/[^/]+/releases/\d+/web/|', '/', $src);
            }
            // If the handle or conditions don't match, return the original $src
            return $src;
        }, 10, 2);
    }
}
