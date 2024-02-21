<?php

namespace PressbooksMultiInstitution;

use Kucrut\Vite;
use PressbooksMultiInstitution\Actions\AssignBookToInstitution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;
use PressbooksMultiInstitution\Controllers\AssignBooksController;
use PressbooksMultiInstitution\Actions\ManagerPermissions;
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
        $this->loadTranslations();
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
        add_action('network_admin_menu', fn () => app(ManagerPermissions::class)->handleMenus(), 1000);
        add_action('admin_menu', fn () => app(ManagerPermissions::class)->handleMenus(), 1000);
        add_action('init', fn () => app(ManagerPermissions::class)->setupInstitutionalFilters());
        add_action(
            'pb_institutional_after_save',
            fn ($newManagers, $revokedManagers) => app(ManagerPermissions::class)->afterSaveInstitution($newManagers, $revokedManagers),
            10,
            2
        );
        add_action(
            'pb_institutional_filters_created',
            fn ($institution, $institutionalManagers, $institutionalUsers)
            => app(ManagerPermissions::class)->handlePagesPermissions($institution, $institutionalManagers, $institutionalUsers),
            10,
            3
        );
        add_action('init', [InstitutionalManagerDashboard::class, 'init']);
    }

    private function registerBlade(): void
    {
        app('Blade')->addNamespace(
            'PressbooksMultiInstitution',
            dirname(__DIR__) . '/resources/views'
        );
    }

    private function enqueueScripts(): void
    {
        add_action('admin_enqueue_scripts', function ($page) {
            $context = [
                'institutions_page_pb_multi_institutions' => [
                    'formSelector' => '#pressbooks-multi-institution-admin',
                    'confirmationMessage' => __('Are you sure you want to delete the selected institutions?', 'pressbooks-multi-institution'),
                ],
            ];

            Vite\enqueue_asset(
                WP_PLUGIN_DIR . '/pressbooks-multi-institution/dist',
                'resources/assets/js/pressbooks-multi-institution.js',
                ['handle' => 'pressbooks-multi-institution']
            );

            Vite\enqueue_asset(
                WP_PLUGIN_DIR . '/pressbooks-multi-institution/dist',
                'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
                ['handle' => 'pressbooks-multi-select'],
            );

            wp_localize_script('pressbooks-multi-institution', 'context', $context[$page] ?? []);
        });
    }

    /**
     * Load the plugin translations
     * @return void
     */
    private function loadTranslations(): void
    {
        add_action('init', function () {
            load_plugin_textdomain('pressbooks-multi-institution', false, 'pressbooks-multi-institution/languages/');
        });
    }
}
