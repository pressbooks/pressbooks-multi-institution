<?php

namespace PressbooksMultiInstitution\Actions;

use Illuminate\Database\Capsule\Manager;
use Pressbooks\Container;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Support\BookList;
use WP_Admin_Bar;

use function Pressbooks\Admin\NetworkManagers\is_restricted;
use function PressbooksMultiInstitution\Support\get_institution_by_manager;

use function Pressbooks\Admin\NetworkManagers\_restricted_users;

class PermissionsManager
{
    /**
     * @return void
     */
    public function handleMenus(): void
    {
        global $menu, $submenu;
        // Remove the Home page from the books menu and root site
        if (!is_main_site() || !is_super_admin()) {
            unset($submenu['index.php'][0]);
        }
        if (get_institution_by_manager() !== 0) {
            remove_menu_page($this->getContextSlug('customize.php', true));
            remove_menu_page($this->getContextSlug('edit.php?post_type=page', true));
            // Remove the default dashboard page and point to the institutional dashboard
            foreach ($menu as &$item) {
                if ($item[2] == network_admin_url('index.php')) {
                    $item[2] = network_site_url('wp-admin/index.php?page=pb_institutional_manager');
                    break;
                }
            }
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
            return Institution::find($institution)?->toArray() ?? false;
        });

        add_filter('pb_institutional_users', function ($users) use ($institutionalUsers) {
            return [...$users, ...array_map('intval', $institutionalUsers)];
        });

        /** Book List */
        Container::get(BookList::class)->init();

        add_filter('pb_institutional_managers', function ($managers) use ($institutionalManagers) {
            return [...$managers, ...array_map('intval', $institutionalManagers)];
        });

        add_filter('pb_network_analytics_filter_userslist', [$this, 'filterUsersList']);

        add_filter('pb_network_analytics_userslist_columns', [$this, 'addInstitutionColumnToUsersList']);

        add_filter('pb_network_analytics_userslist', [$this, 'addInstitutionFieldToUsers']);

        add_filter('pb_network_analytics_userslist_filters_event', [$this, 'addInstitutionsFilterAttributesForUsersList']);

        add_filter('pb_network_analytics_userslist_custom_text', [$this, 'addCustomTextForUsersList']);

        add_filter('pb_network_analytics_filter_tabs', [$this, 'addInstitutionsFilterTab']);

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

    public function addCustomTextForUsersList(array $customText): array
    {
        $institutionId = get_institution_by_manager();
        if (! is_super_admin() || $institutionId === 0) {
            return $customText;
        }

        $institution = Institution::query()
            ->where('id', $institutionId)
            ->withCount('users')
            ->first();

        return [
            'title' => sprintf(__("%s's User List", 'pressbooks-multi-institution'), $institution->name),
            'count' => sprintf(
                _n(
                    'There is %s user assigned to %s.',
                    'There are %s users assigned to %s.',
                    $institution->users_count,
                    'pressbooks-multi-institution'
                ),
                $institution->users_count,
                $institution->name
            ),
        ];
    }

    public function filterUsersList(): array
    {
        $filtered = isset($_GET['institution']) && is_array($_GET['institution']) && count($_GET['institution']) > 0;

        if ($filtered && is_super_admin() && ! get_institution_by_manager()) {
            $institutionIds = array_map('intval', $_GET['institution']);
            if (in_array(0, $institutionIds)) {
                $wpUsers = get_users([
                    'blog_id' => 0,
                    'fields' => ['ID'],
                    'exclude' => InstitutionUser::get()->pluck('user_id')->toArray(),
                ]);
                $userIds = array_map(fn ($user) => is_object($user) ? $user->ID : $user, $wpUsers);
            }

            return array_merge(
                $userIds ?? [],
                InstitutionUser::query()->whereIn('institution_id', $institutionIds)->pluck('user_id')->toArray()
            );
        }

        if (is_super_admin() && !is_restricted()) {
            return array_map(fn ($user) => $user->ID, get_users(['blog_id' => 0]));
        }

        $institutionalUsers = InstitutionUser::query()->byInstitution(get_institution_by_manager())->pluck('user_id')->toArray();

        return array_map('intval', $institutionalUsers);
    }

    public function addInstitutionsFilterTab(array $filters): array
    {
        if (! is_super_admin() || get_institution_by_manager() > 0) {
            return $filters;
        }

        return [
            ...$filters,
            [
                'tab' => app('Blade')->render('PressbooksMultiInstitution::partials.filters.institutions.tab'),
                'content' => app('Blade')->render('PressbooksMultiInstitution::partials.filters.institutions.content', [
                    'institutions' => Institution::query()->orderBy('name')->get(),
                ])
            ]
        ];
    }

    public function addInstitutionsFilterAttributesForUsersList(): array
    {
        return [
            [
                'field' => 'institution',
                'name' => 'institution[]',
                'counterId' => 'institutions-tab-counter',
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
                if (isset($_GET['user_id']) && !in_array($_GET['user_id'], $institutionalUsers)) {
                    $isAccessAllowed = false;
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

    public function modifyAdminBarMenus(WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('pb-administer-appearance');
        $wp_admin_bar->remove_node('pb-administer-pages');
        $wp_admin_bar->remove_node('pb-administer-settings');
        $mainMenu = $wp_admin_bar->get_node('pb-administer-network');
        if ($mainMenu) {
            $title = __('Administer Institution', 'pressbooks-multi-institution');
            $mainMenu->title = "<i class='pb-heroicons pb-heroicons-building-library'></i><span>{$title}</span>";
            $mainMenu->href = network_site_url('wp-admin/index.php?page=pb_institutional_manager');
            $subMenu = $wp_admin_bar->get_node('pb-administer-network-d');
            if ($subMenu) {
                $subMenu->href = network_site_url('wp-admin/index.php?page=pb_institutional_manager');
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
