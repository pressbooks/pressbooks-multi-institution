<?php

namespace PressbooksMultiInstitution;

use Kucrut\Vite;
use PressbooksMultiInstitution\Actions\AssignBookToInstitution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Controllers\AssignBooksController;
use PressbooksMultiInstitution\Controllers\InstitutionsController;
use PressbooksMultiInstitution\Controllers\InstitutionsUsersController;

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
        $slug = 'pb_multi_institution';

        add_menu_page(
            page_title: __('Institutions', 'pressbooks-multi-institution'),
            menu_title: __('Institutions', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: $slug,
            icon_url: 'dashicons-building',
        );

        add_action('admin_bar_init', fn () => remove_submenu_page($slug, $slug));

        add_submenu_page(
            parent_slug: $slug,
            page_title: __('Institution List', 'pressbooks-multi-institution'),
            menu_title: __('Institution List', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institutions',
            callback: function () {
                echo app(InstitutionsController::class)->index();
            },
        );

        add_submenu_page(
            parent_slug: $slug,
            page_title: __('Add Institution', 'pressbooks-multi-institution'),
            menu_title: __('Add Institution', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institution_form',
            callback: function () {
                echo app(InstitutionsController::class)->form();
            },
        );

        add_submenu_page(
            parent_slug: $slug,
            page_title: __('Assign Users', 'pressbooks-multi-institution'),
            menu_title: __('Assign Users', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institutions_users',
            callback: function () {
                echo app(InstitutionsUsersController::class)->assign();
            },
        );

		add_submenu_page(
			parent_slug: $slug,
			page_title: __('Assign Books', 'pressbooks-multi-institution'),
			menu_title: __('Assign Books', 'pressbooks-multi-institution'),
			capability: 'manage_network',
			menu_slug: 'pb_multi_institution_assign_book',
			callback: function () {
				echo app(AssignBooksController::class)->index();
			}
		);
    }

    private function registerActions(): void
    {
        add_action('network_admin_menu', [$this, 'registerMenus'], 11);
        // TODO: register menu at the main site level

        add_action('user_register', fn (int $id) => app(AssignUserToInstitution::class)->handle($id));
        add_action('pb_new_blog', fn () => app(AssignBookToInstitution::class)->handle());
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
        add_action('admin_enqueue_scripts', function ($page) {
            if ($page === 'institutions_page_pb_multi_institutions') {
                Vite\enqueue_asset(
                    plugin_dir_path(__DIR__).'dist',
                    'resources/assets/js/pressbooks-multi-institution.js',
                    ['handle' => 'pressbooks-multi-institution']
                );

                wp_localize_script(
                    'pressbooks-multi-institution',
                    'Msg',
                    [
                        'text' => __('Are you sure you want to delete these institutions?', 'pressbooks-multi-institution'),
                    ]
                );
            }

            if ($page === 'institutions_page_pb_multi_institutions_users') {
                Vite\enqueue_asset(
                    plugin_dir_path(__DIR__).'dist',
                    'resources/assets/js/pressbooks-multi-institutions-users.js',
                    ['handle' => 'pressbooks-multi-institutions-users']
                );

                wp_localize_script(
                    'pressbooks-multi-institutions-users',
                    'Custom',
                    [
                        'text' => __('Are you sure you want to re-assign the user/s?', 'pressbooks-multi-institution'),
                        'defaultOptionText' => __('- Set Institution -', 'pressbooks-multi-institution'),
                    ]
                );
            }

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
