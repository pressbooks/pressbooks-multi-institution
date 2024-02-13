<?php

namespace PressbooksMultiInstitution;

use Kucrut\Vite;
use PressbooksMultiInstitution\Actions\AssignBookToInstitution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Controllers\AssignBooksController;
use PressbooksMultiInstitution\Actions\ManagerPermissions;
use PressbooksMultiInstitution\Controllers\InstitutionsController;
use PressbooksMultiInstitution\Controllers\InstitutionsUsersController;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\InstitutionUser;

use function Pressbooks\Admin\NetworkManagers\_restricted_users;
use function PressbooksMultiInstitution\Support\get_institution_by_manager;

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
        add_action('network_admin_menu', fn () => app(ManagerPermissions::class)->handleMenus(), 1000);
        add_action('admin_menu', fn () => app(ManagerPermissions::class)->handleMenus(), 1000);
        add_action('pb_multi_institution_after_save', function ($institution, $newManagers, $revokedManagers) {
            $restricted = _restricted_users();
            // Grant super admin privileges to new institution managers and add them to the restricted users list
            foreach ($newManagers as $manager) {
                $restricted[] = absint($manager);
                grant_super_admin($manager);
            }
            // Update the restricted users list
            update_site_option('pressbooks_network_managers', $restricted);
            // Remove institution managers from the restricted users list and revoke their super admin privileges
            foreach ($revokedManagers as $managerToBeRevoked) {
                $key = array_search(absint($managerToBeRevoked['user_id']), $restricted, true);
                if ($key !== false) {
                    unset($restricted[$key]);
                    revoke_super_admin($managerToBeRevoked['user_id']);
                }
            }
        }, 10, 3);
        // Only manage books from the current institution
        add_action('init', function () {
            global $pagenow;
            $institution = get_institution_by_manager();
            $institutionalManagers = InstitutionUser::query()->managers()->pluck('user_id')->toArray();
            $institutionalUsers = InstitutionUser::query()->byInstitution($institution)->pluck('user_id')->toArray();
            add_filter('pb_institutional_managers', function ($managers) use ($institutionalManagers) {
                return [...$managers, ...array_map('intval', $institutionalManagers)];
            });
            add_filter('pb_institutional_users', function ($users) use ($institutionalUsers) {
                return [...$users, ...array_map('intval', $institutionalUsers)];
            });
            if ($pagenow == 'settings.php' && isset($_GET['page']) && $_GET['page'] == 'pb_network_managers') {
                add_filter('site_option_site_admins', function ($admins) use ($institutionalManagers) {
                    $adminIds = array_map(function ($login) {
                        $user = get_user_by('login', $login);
                        return [
                            'id' => $user->ID,
                            'login' => $login,
                        ];
                    }, $admins);
                    $adminsToShow = array_filter($adminIds, function ($id) use ($institutionalManagers) {
                        return !in_array($id['id'], $institutionalManagers);
                    });
                    return array_map(fn ($admin) => $admin['login'], $adminsToShow);
                });
            }
            /*
             * Only show books from the current institution if the user is an institution manager
             * Prevent access to any page that is not allowed
            */
            if ($institution !== 0) {
                $allowedBooks = InstitutionBook::query()->select('blog_id')->where('institution_id', $institution)->get()->map(fn ($book) => $book->blog_id)->toArray();

                add_filter('pb_filter_books', function ($books) use ($allowedBooks) {
                    return [...$books, ...array_map('intval', $allowedBooks)];
                });

                add_filter('pb_filter_users', function ($users) use ($institutionalUsers) {
                    return [...$users, ...array_map('intval', $institutionalUsers)];
                });

                add_filter('sites_clauses', function ($clauses) use ($allowedBooks) {
                    global $wpdb;

                    $clauses['where'] .= " AND {$wpdb->blogs}.blog_id IN (" . implode(',', $allowedBooks) . ")";

                    return $clauses;
                });

                add_filter('can_edit_network', function ($canEdit) use ($allowedBooks) {
                    if (is_network_admin() && !in_array($_REQUEST['id'], $allowedBooks)) {
                        $canEdit = false;
                    }
                    return $canEdit;
                });

                add_action('admin_init', function () use ($allowedBooks) {
                    global $pagenow;

                    $allowed_pages = [
                        'admin.php' => ['pb_network_page', 'pb_network_analytics_booklist', 'pb_network_analytics_userlist', 'pb_network_analytics_admin', 'pb_cloner'],
                        'sites.php' => ['confirm', 'delete'],
                        'users.php' => ['user_bulk_new'],
                        'admin-ajax.php' => ['pb_network_analytics_books','pb_network_analytics_users'],
                        'index.php' => ['', 'book_dashboard','pb_home_page','pb_network_page'],
                        'profile.php' => [''],
                    ];

                    $currentPage = $pagenow;
                    $currentPageParam = $_GET['page'] ?? '';
                    $currentPageParam = $_GET['action'] ?? $currentPageParam;

                    // Flag to check if the current access is allowed
                    $isAccessAllowed = false;
                    $currentBlogId = get_current_blog_id();

                    // Check if the current page is in the allowed list and has the allowed query parameter
                    if (array_key_exists($currentPage, $allowed_pages)) {
                        if (in_array($currentPageParam, $allowed_pages[$currentPage])) {
                            $isAccessAllowed = true;
                        }
                    }

                    if ($currentBlogId !== 1 && !in_array($currentBlogId, $allowedBooks)) {
                        $isAccessAllowed = false;
                    }

                    // If access is not allowed, redirect or deny access
                    if (!$isAccessAllowed) {
                        wp_die(__('Sorry, you are not allowed to access this page.', 'pressbooks-multi-institution'), 403);
                    }
                });
            }
        });
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
                plugin_dir_path(__DIR__).'dist',
                'resources/assets/js/pressbooks-multi-institution.js',
                ['handle' => 'pressbooks-multi-institution']
            );

            Vite\enqueue_asset(
                plugin_dir_path(__DIR__).'dist',
                'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
                ['handle' => 'pressbooks-multi-select'],
            );

            wp_localize_script('pressbooks-multi-institution', 'context', $context[$page] ?? []);
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
