<?php

namespace PressbooksMultiInstitution;

use Kucrut\Vite;
use Pressbooks\Container;
use PressbooksMultiInstitution\Actions\AssignBookToInstitution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;
use PressbooksMultiInstitution\Controllers\AssignBooksController;
use PressbooksMultiInstitution\Actions\PermissionsManager;
use PressbooksMultiInstitution\Controllers\InstitutionsController;
use PressbooksMultiInstitution\Controllers\AssignUsersController;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Services\InstitutionStatsService;
use PressbooksMultiInstitution\Support\BookList;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

/**
 * Class Bootstrap
 * @package PressbooksMultiInstitution
 *
 */
final class Bootstrap
{
    private static ?Bootstrap $instance = null;

    private array $menuItem = [
        'slug' => 'pb_multi_institution',
        'position' => 3
    ];

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

        Container::getInstance()->singleton(BookList::class, fn () => new BookList(app('db')));
    }

    public function registerMenus(): void
    {
        add_menu_page(
            page_title: __('Institutions', 'pressbooks-multi-institution'),
            menu_title: __('Institutions', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: $this->menuItem['slug'],
            icon_url: 'dashicons-building',
        );

        add_action('admin_bar_init', fn () => remove_submenu_page($this->menuItem['slug'], $this->menuItem['slug']));

        add_submenu_page(
            parent_slug: $this->menuItem['slug'],
            page_title: __('Institution List', 'pressbooks-multi-institution'),
            menu_title: __('Institution List', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institutions',
            callback: function () {
                echo app(InstitutionsController::class)->index();
            },
        );

        add_submenu_page(
            parent_slug: $this->menuItem['slug'],
            page_title: __('Add Institution', 'pressbooks-multi-institution'),
            menu_title: __('Add Institution', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institution_form',
            callback: function () {
                echo app(InstitutionsController::class)->form();
            },
        );

        add_submenu_page(
            parent_slug: $this->menuItem['slug'],
            page_title: __('Assign Users', 'pressbooks-multi-institution'),
            menu_title: __('Assign Users', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_assign_users',
            callback: function () {
                echo app(AssignUsersController::class)->assign();
            },
        );

        add_submenu_page(
            parent_slug: $this->menuItem['slug'],
            page_title: __('Assign Books', 'pressbooks-multi-institution'),
            menu_title: __('Assign Books', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_assign_books',
            callback: function () {
                echo app(AssignBooksController::class)->index();
            }
        );
    }

    public function handleMenu(array $menu): array
    {
        $this->handleStatsMenuItem();

        return $this->moveInstitutionsItem($menu);
    }

    private function handleStatsMenuItem(): void
    {
        $institutionId = get_institution_by_manager();
        if ($institutionId === 0) {
            return;
        }

        global $submenu;

        $analyticsStatsSlug = is_network_admin() ?
            'pb_network_analytics_admin' : network_admin_url('admin.php?page=pb_network_analytics_admin');

        $kokoSlug = ! is_network_admin() ?
            'koko-analytics' : admin_url('admin.php?page=koko-analytics');

        if (! isset($submenu[$analyticsStatsSlug])) {
            return;
        }

        foreach ($submenu[$analyticsStatsSlug] as &$submenuItem) {
            if (in_array($analyticsStatsSlug, $submenuItem)) {
                $institutionName = Institution::find($institutionId)?->name ?? '';
                $submenuItem[0] = sprintf(__('%s Stats', 'pressbooks-multi-institution'), $institutionName);
            }

            if (in_array($kokoSlug, $submenuItem)) {
                remove_submenu_page($analyticsStatsSlug, $kokoSlug);
            }
        }
    }

    private function moveInstitutionsItem(array $menu): array
    {
        $key = array_search($this->menuItem['slug'], $menu);
        if (! $key) {
            return $menu;
        }

        unset($menu[$key]);

        array_splice($menu, $this->menuItem['position'], 0, $this->menuItem['slug']);

        return $menu;
    }

    private function registerActions(): void
    {
        add_action('network_admin_menu', [$this, 'registerMenus'], 11);
        if (is_main_site()) {
            add_action('admin_menu', [$this, 'registerMenus'], 11);
        }
        add_filter('custom_menu_order', '__return_true');
        add_action('menu_order', [$this, 'handleMenu'], 999);

        add_action('user_register', fn (int $id) => app(AssignUserToInstitution::class)->handle($id));
        add_action('pb_new_blog', fn () => app(AssignBookToInstitution::class)->handle());
        add_action('network_admin_menu', fn () => app(PermissionsManager::class)->handleMenus(), 1000);
        add_action('admin_menu', fn () => app(PermissionsManager::class)->handleMenus(), 1000);
        add_action('init', fn () => app(PermissionsManager::class)->setupInstitutionalFilters());
        add_action('pb_institutional_after_save', [PermissionsManager::class, 'syncRestrictedUsers'], 10, 2);
        add_action('pb_institutional_after_delete', [PermissionsManager::class, 'syncRestrictedUsers'], 10, 2);
        add_action(
            'pb_institutional_filters_created',
            fn ($institution, $institutionalManagers, $institutionalUsers) => app(PermissionsManager::class)->handlePagesPermissions($institution, $institutionalManagers, $institutionalUsers),
            10,
            3
        );
        add_action('init', [InstitutionalManagerDashboard::class, 'init']);
        add_action('init', fn () => app(InstitutionStatsService::class)->setupHooks());
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
