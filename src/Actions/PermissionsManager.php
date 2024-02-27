<?php

namespace PressbooksMultiInstitution\Actions;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\InstitutionUser;
use WP_Admin_Bar;

use function Pressbooks\Admin\NetworkManagers\is_restricted;
use function PressbooksMultiInstitution\Support\get_institution_by_manager;
use function Pressbooks\Admin\NetworkManagers\_restricted_users;
use function PressbooksMultiInstitution\Support\is_network_manager;

class PermissionsManager
{
    /**
     * @return void
     */
    public function handleMenus(): void
    {
        if (get_institution_by_manager() !== 0) {
            remove_menu_page($this->getContextSlug('customize.php', true));
            remove_menu_page($this->getContextSlug('edit.php?post_type=page', true));
            remove_menu_page($this->getContextSlug('admin.php?page=pb_network_integrations', false));
            remove_menu_page('settings.php');
            remove_menu_page('pb_network_integrations');
            remove_menu_page('pb_multi_institution');
            add_action('admin_bar_menu', [$this, 'modifyAdminBarMenus'], 1000);
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
        add_filter('pb_institutional_users', function ($users) use ($institutionalUsers) {
            return [...$users, ...array_map('intval', $institutionalUsers)];
        });
        add_filter('pb_institutional_managers', function ($managers) use ($institutionalManagers) {
            return [...$managers, ...array_map('intval', $institutionalManagers)];
        });

        add_filter('pb_network_analytics_filter_userslist', [$this, 'filterUsersList']);

        add_filter('pb_network_analytics_userslist_columns', [$this, 'addInstitutionColumnToUsersList']);

        add_filter('pb_network_analytics_userslist', [$this, 'addInstitutionFieldToUsers']);

        add_filter('pb_network_analytics_userslist_filters_input', [$this, 'addInstitutionsFilterForUsersList']);

        add_filter('pb_network_analytics_userslist_filters_event', [$this, 'addInstitutionsFilterAttributesForUsersList']);

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

    public function filterUsersList(): array
    {
        $filtered = isset($_GET['institution']);

        if ($filtered && is_super_admin() && ! get_institution_by_manager()) {
            if ($_GET['institution'] === 'unassigned-institution') {
                $wpUsers = get_users(['exclude' => InstitutionUser::get()->pluck('user_id')->toArray()]);
                return array_map(fn ($user) => $user->ID, $wpUsers);
            }

            $institution = Institution::query()->where('name', sanitize_text_field($_GET['institution']))->first();
            if (!$institution) {
                return [];
            }

            $userIds = InstitutionUser::query()->byInstitution($institution->id)->pluck('user_id')->toArray();
            return $userIds ?? [];
        }

        if (is_super_admin() && !is_restricted()) {
            return array_map(fn ($user) => $user->ID, get_users());
        }

        $institutionalUsers = InstitutionUser::query()->byInstitution(get_institution_by_manager())->pluck('user_id')->toArray();

        return array_map('intval', $institutionalUsers);
    }

    public function addInstitutionsFilterForUsersList(): array
    {
        if (! is_super_admin() || ! is_network_manager()) {
            return [];
        }
        return [
            [
                'partial' => 'PressbooksMultiInstitution::partials.userslist.filters',
                'data' => [
                    'institutions' => Institution::all(),
                ]
            ]
        ];
    }

    public function addInstitutionsFilterAttributesForUsersList(): array
    {
        return [
            [
                'field' => 'institution',
                'id' => 'institutions-dropdown',
            ]
        ];
    }

    public function addInstitutionColumnToUsersList(array $columns): array
    {
        array_splice($columns, 5, 0, [
            [
                'title' => __('Institution', 'pressbooks-multi-institution'),
                'field' => 'institution',
            ]
        ]);
        return $columns;
    }

    public function addInstitutionFieldToUsers(array $users): array
    {
        $institutionUsers = InstitutionUser::query()->with('institution')->get();

        return array_map(function ($user) use ($institutionUsers) {
            $institutionUser = $institutionUsers->where('user_id', $user->id)->first();
            $properties = get_object_vars($user);
            $propertiesBeforeEmail = array_slice($properties, 0, 3, true);
            $propertiesAfterEmail = array_slice($properties, 3, null, true);
            $properties = array_merge(
                $propertiesBeforeEmail,
                ['institution' => $institutionUser?->institution->name ?? __('Unassigned', 'pressbooks-multi-institution')],
                $propertiesAfterEmail
            );

            return (object) $properties;
        }, $users);
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
                if (empty($allowedBooks)) {
                    return $clauses;
                }

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
                    'admin.php' => ['pb_network_analytics_booklist', 'pb_network_analytics_userlist', 'pb_network_analytics_admin', 'pb_cloner'],
                    'sites.php' => ['confirm', 'delete','pb_network_analytics_booklist','pb_network_analytics_userlist','pb_network_analytics_admin','pb_cloner'],
                    'users.php' => ['user_bulk_new','pb_network_analytics_userlist'],
                    'admin-ajax.php' => ['pb_network_analytics_books','pb_network_analytics_users', 'pb_network_analytics_users_csv'],
                    'index.php' => ['', 'book_dashboard','pb_institutional_manager','pb_home_page'],
                    'profile.php' => [''],
                ];

                $currentPage = $pagenow;
                $currentPageParam = $_GET['page'] ?? '';
                $currentPageParam = $_GET['action'] ?? $currentPageParam;

                // Flag to check if the current access is allowed
                $currentBlogId = get_current_blog_id();

                // Check if the current page is in the allowed list and has the allowed query parameter
                $isAccessAllowed = array_key_exists($currentPage, $allowed_pages) &&
                    in_array($currentPageParam, $allowed_pages[$currentPage]);

                if ($currentBlogId !== 1 && !in_array($currentBlogId, $allowedBooks)) {
                    $isAccessAllowed = false;
                }

                if (
                    $currentPage === 'user-edit.php' ||
                    ($currentPage === 'users.php' && $currentPageParam === 'deleteuser')
                ) {
                    $isAccessAllowed = $this->canManageUser();
                }

                // If access is not allowed, redirect or deny access
                if (!$isAccessAllowed) {
                    wp_die(__('Sorry, you are not allowed to access this page.', 'pressbooks-multi-institution'), 403);
                }
            });
        }
    }

    private function canManageUser(): bool
    {
        $userId = $_GET['user_id'] ?? null;

        $institution = get_institution_by_manager();
        if (! $userId || (is_super_admin() && ! $institution)) {
            return true;
        }
        $institutionalUsers = InstitutionUser::query()
            ->byInstitution($institution)
            ->pluck('user_id')
            ->toArray();

        return in_array($userId, $institutionalUsers);
    }

    public function createInstitutionFilters()
    {

    }

    /**
     * @param WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function modifyAdminBarMenus($wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('pb-administer-appearance');
        $wp_admin_bar->remove_node('pb-administer-pages');
        $wp_admin_bar->remove_node('pb-administer-settings');
        $mainMenu = $wp_admin_bar->get_node('pb-administer-network');
        if ($mainMenu) {
            $mainMenu->href = admin_url('index.php?page=pb_institutional_manager');
            $title = __('Administer Institution', 'pressbooks-multi-institution');
            $mainMenu->title = "<i class='pb-heroicons pb-heroicons-building-library'></i><span>{$title}</span>";
            $subMenu = $wp_admin_bar->get_node('pb-administer-network-d');
            if ($subMenu) {
                $subMenu->href = admin_url('index.php?page=pb_institutional_manager');
                $wp_admin_bar->add_node($subMenu);
            }
            $wp_admin_bar->add_node($mainMenu);
        }
    }

    public function getContextSlug(string $page, bool $is_main_site_page): string
    {
        return is_network_admin() ?
            ($is_main_site_page ? admin_url($page) : $page) :
            ($is_main_site_page ? $page : network_admin_url($page));
    }
}
