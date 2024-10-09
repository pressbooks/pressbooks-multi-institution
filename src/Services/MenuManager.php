<?php

namespace PressbooksMultiInstitution\Services;

use PressbooksMultiInstitution\Controllers\AssignBooksController;
use PressbooksMultiInstitution\Controllers\AssignUsersController;
use PressbooksMultiInstitution\Controllers\InstitutionsController;
use PressbooksMultiInstitution\Models\Institution;
use WP_Admin_Bar;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class MenuManager
{
    private string $slug = 'pb_multi_institution';

    private int $position = 3;

    public function registerMenus(): void
    {
        if (!is_main_site() && !is_network_admin()) {
            return;
        }
        add_menu_page(
            page_title: __('Institutions', 'pressbooks-multi-institution'),
            menu_title: __('Institutions', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: $this->slug,
            icon_url: 'dashicons-building',
            position: 4,
        );

        add_action('admin_bar_init', fn () => remove_submenu_page($this->slug, $this->slug));

        add_submenu_page(
            parent_slug: $this->slug,
            page_title: __('Institution List', 'pressbooks-multi-institution'),
            menu_title: __('Institution List', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institutions',
            callback: function () {
                echo app(InstitutionsController::class)->index();
            },
        );

        add_submenu_page(
            parent_slug: $this->slug,
            page_title: isset($_REQUEST['action']) && $_REQUEST['action'] === 'new' ? __('Add Institution', 'pressbooks-multi-institution') : __('Edit Institution', 'pressbooks-multi-institution'),
            menu_title: __('Add Institution', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_multi_institution_form',
            callback: function () {
                echo app(InstitutionsController::class)->form();
            },
        );

        add_submenu_page(
            parent_slug: $this->slug,
            page_title: __('Assign Users', 'pressbooks-multi-institution'),
            menu_title: __('Assign Users', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_assign_users',
            callback: function () {
                echo app(AssignUsersController::class)->assign();
            },
        );

        add_submenu_page(
            parent_slug: $this->slug,
            page_title: __('Assign Books', 'pressbooks-multi-institution'),
            menu_title: __('Assign Books', 'pressbooks-multi-institution'),
            capability: 'manage_network',
            menu_slug: 'pb_assign_books',
            callback: function () {
                echo app(AssignBooksController::class)->index();
            }
        );
    }

    /**
     * @return void
     */
    public function handleMenus(): void
    {
        $this->registerMenus();

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
            remove_menu_page($this->slug);
            add_action('admin_bar_menu', [$this, 'modifyAdminBarMenus'], 1000);
        }
    }

    public function modifyAdminBarMenus(WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('pb-administer-appearance');
        $wp_admin_bar->remove_node('pb-administer-pages');
        $wp_admin_bar->remove_node('pb-administer-settings');
        $mainMenu = $wp_admin_bar->get_node('pb-administer-network');
        if ($mainMenu) {
            $title = __('Administer Institution', 'pressbooks-multi-institution');
            $mainMenu->title = "<i class='pb-heroicons pb-heroicons-building-library' aria-hidden='true'></i><span>{$title}</span>";
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

    public function handleItems(array $menu): array
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
        $key = array_search($this->slug, $menu);
        if (! $key) {
            return $menu;
        }

        unset($menu[$key]);
        array_splice($menu, $this->position, 0, $this->slug);

        $pageSlug = $this->getContextSlug('edit.php?post_type=page', true);
        $key = array_search($pageSlug, $menu);
        if (! $key) {
            $menu[5] = $pageSlug;
        }

        return $menu;
    }
}
