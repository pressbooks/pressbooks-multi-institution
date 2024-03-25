<?php

namespace PressbooksMultiInstitution\Services;

use Pressbooks\Container;
use PressbooksMultiInstitution\Actions\TableViews;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Views\BookList;
use PressbooksMultiInstitution\Views\UserList;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class PermissionsManager
{
    public function setupFilters(): void
    {
        $institution = get_institution_by_manager();

        $institutionalManagers = InstitutionUser::query()->managers()->pluck('user_id')->toArray();
        $institutionalUsers = InstitutionUser::query()->byInstitution($institution)->pluck('user_id')->toArray();

        add_filter('pb_institution', function () use ($institution) {
            return Institution::find($institution)?->toArray() ?? false;
        });

        add_filter('pb_institutional_users', function ($users) use ($institutionalUsers) {
            return [...$users, ...array_map('intval', $institutionalUsers)];
        });

        add_filter('pb_institutional_managers', function ($managers) use ($institutionalManagers) {
            return [...$managers, ...array_map('intval', $institutionalManagers)];
        });

        Container::get(TableViews::class)->init();
        Container::get(BookList::class)->init();
        Container::get(UserList::class)->init();

        do_action('pb_institutional_filters_created', $institution, $institutionalManagers, $institutionalUsers);
    }

    public function handlePagesPermissions($institution, $institutionalManagers, $institutionalUsers): void
    {
        if ($institution === 0) {
            return;
        }

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

            $allowedPages = [
                'admin.php' => ['pb_network_analytics_booklist', 'pb_network_analytics_userlist', 'pb_network_analytics_admin', 'pb_cloner'],
                'sites.php' => ['confirm', 'delete', 'pb_network_analytics_booklist', 'pb_network_analytics_userlist', 'pb_network_analytics_admin', 'pb_cloner'],
                'index.php' => ['', 'book_dashboard', 'pb_institutional_manager', 'pb_home_page', 'pb_catalog'],
                'tools.php',
                'users.php',
                'admin-ajax.php',
                'options-general.php',
                'profile.php' => [''],
                'post-new.php',
                'edit.php',
                'edit-tags.php',
                'upload.php',
                'post.php',
                'themes.php',
                'plugins.php',
                'media-new.php',
                'users.php',
                'export-personal-data.php',
                'erase-personal-data.php',
                'options-privacy.php'
            ];

            $bookPages = [
                'site-info.php',
                'site-settings.php',
                'site-themes.php',
                'site-users.php',
            ];

            $currentPage = $pagenow;
            $currentPageParam = $_GET['page'] ?? '';
            $currentPageParam = $_GET['action'] ?? $currentPageParam;
            $isAccessAllowed = false;

            if (empty($allowedPages[$currentPage]) && in_array($currentPage, $allowedPages)) {
                $isAccessAllowed = true;
            }

            if ($pagenow == 'settings.php' && isset($_GET['page']) && $_GET['page'] == 'pb_network_managers') {

                add_filter('site_option_site_admins', function ($admins) {
                    $adminIds = array_map(function ($login) {
                        $user = get_user_by('login', $login);
                        return [
                            'id' => $user->ID,
                            'login' => $login,
                        ];
                    }, $admins);
                    $adminsToShow = array_filter($adminIds, function ($id) {
                        return !in_array($id['id'], apply_filters('pb_institutional_managers', []));
                    });
                    return array_map(fn ($admin) => $admin['login'], $adminsToShow);
                });
            }

            $currentBlogId = get_current_blog_id();

            // Check if the current page is in the allowed list and has the allowed query parameter
            if (array_key_exists($currentPage, $allowedPages)) {
                if (in_array($currentPageParam, $allowedPages[$currentPage])) {
                    $isAccessAllowed = true;
                }
            }

            if ($currentBlogId !== 1 && !in_array($currentBlogId, $allowedBooks)) {
                $isAccessAllowed = false;
            }

            // Check if the current page is a book page and if the user has access to it
            $userBooks = array_slice(array_keys(get_blogs_of_user(get_current_user_id())), 1); // remove the main site

            if (in_array($currentBlogId, $userBooks) && !in_array($currentPage, $bookPages)) {
                $isAccessAllowed = true;
            }

            $institutionalUsers = apply_filters('pb_institutional_users', []);

            if ($currentPageParam === 'pb_network_analytics_userlist' || $currentPage === 'users.php' || $currentPage === 'user-edit.php') {
                if (isset($_GET['user_id']) && in_array($_GET['user_id'], $institutionalUsers)) {
                    $isAccessAllowed = true;
                }

                if (isset($_GET['id']) && !in_array($_GET['id'], $institutionalUsers)) {
                    $isAccessAllowed = false;
                }
            }

            // hack to redirect to the dashboard because the institutional manager check is done after the redirect
            if ($currentPageParam === 'pb_network_page') {
                wp_redirect(network_site_url('wp-admin/index.php?page=pb_institutional_manager'));
                exit;
            }

            // If access is not allowed, redirect or deny access
            if (!$isAccessAllowed) {
                wp_die(__('Sorry, you are not allowed to access this page.', 'pressbooks-multi-institution'), 403);
            }
        });
    }

    /**
     * This method is called after an institution is updated or deleted, and it updates the restricted users list
     *
     * @param array<int, int> $newManagers
     * @param array<int, int> $revokedManagers
     */
    public static function syncRestrictedUsers(array $newManagers, array $revokedManagers): void
    {
        $restricted = _restricted_users();

        // Grant super admin privileges to new institution managers and add them to the restricted users list
        foreach ($newManagers as $id) {
            $restricted[] = $id;

            grant_super_admin($id);
        }

        $restricted = array_diff(array_unique($restricted), $revokedManagers);

        // Remove institution managers from the restricted users list and revoke their super admin privileges
        foreach ($revokedManagers as $id) {
            revoke_super_admin($id);
        }

        // Update the restricted users list
        update_site_option('pressbooks_network_managers', $restricted);
    }

    public static function revokeInstitutionalManagersPrivileges(): void
    {
        $managerIds = InstitutionUser::query()->managers()->pluck('user_id')->toArray();

        self::syncRestrictedUsers([], $managerIds);
    }
}
