<?php

namespace PressbooksMultiInstitution\Actions;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\InstitutionUser;
use WP_Admin_Bar;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;
use function Pressbooks\Admin\NetworkManagers\_restricted_users;

class ManagerPermissions
{
    /**
     * @return void
     */
    public function removeMenus(): void
    {
        if (get_institution_by_manager() !== 0) {
            remove_menu_page($this->getContextSlug('customize.php', true));
            remove_menu_page($this->getContextSlug('edit.php?post_type=page', true));
            remove_menu_page($this->getContextSlug('admin.php?page=pb_network_integrations', false));
            remove_menu_page('settings.php');
            remove_menu_page('pb_network_integrations');
            remove_menu_page('pb_multi_institution');
            add_action('admin_bar_menu', [$this, 'removeAdminBarMenus'], 1000);
        }
    }

    /**
     * This method is called after an institution is saved, and it updates the restricted users list
     * @param array $newManagers
     * @param array $revokedManagers
     * @return void
     */

    public function afterSaveInstitution(array $newManagers, array $revokedManagers): void
    {
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
    }

    public function setupInstitutionalFilters(): void
    {
        global $pagenow;
        $institution = get_institution_by_manager();
        $institutionalManagers = InstitutionUser::query()->managers()->pluck('user_id')->toArray();
        $institutionalUsers = InstitutionUser::query()->byInstitution($institution)->pluck('user_id')->toArray();
        add_filter('pb_institution', function () use ($institution) {
            return Institution::find($institution)?->toArray() ?? [];
        });
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
        do_action('pb_institutional_filters_created', $institution, $institutionalManagers, $institutionalUsers);
    }

    public function handlePagesPermissions($institution, $institutionalManagers, $institutionalUsers): void
    {
        if($institution !== 0) {

            $allowedBooks = InstitutionBook::query()
                ->select('blog_id')
                ->where('institution_id', $institution)
                ->get()
                ->map(fn ($book) => $book->blog_id)
                ->toArray();

            /*
             * Filter to return IDs of books that belong to the institution
             */

            add_filter('pb_institutional_books', function ($books) use ($allowedBooks) {
                return [...$books, ...array_map('intval', $allowedBooks)];
            });


            /*
             * Add filters to restrict access to books and users on the network admin pages
             */

            add_filter('sites_clauses', function ($clauses) use ($allowedBooks) {
                global $wpdb;

                $clauses['where'] .= " AND {$wpdb->blogs}.blog_id IN (" . implode(',', $allowedBooks) . ")";

                return $clauses;
            });

            /*
             * Add filters to restrict access to books and users on the network admin pages
             */

            add_filter('can_edit_network', function ($canEdit) use ($allowedBooks) {
                if (is_network_admin() && !in_array($_REQUEST['id'], $allowedBooks)) {
                    $canEdit = false;
                }
                return $canEdit;
            });

            /*
             * Restrict access to the network admin pages and allow only some pages
             */

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
    }

    public function createInstitutionFilters()
    {

    }

    /**
     * @param WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function removeAdminBarMenus($wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('pb-administer-appearance');
        $wp_admin_bar->remove_node('pb-administer-pages');
        $wp_admin_bar->remove_node('pb-administer-settings');
    }

    public function getContextSlug(string $page, bool $is_main_site_page): string
    {
        return is_network_admin() ?
            ($is_main_site_page ? admin_url($page) : $page) :
            ($is_main_site_page ? $page : network_admin_url($page));
    }
}
