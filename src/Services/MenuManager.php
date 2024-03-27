<?php

namespace PressbooksMultiInstitution\Services;

use WP_Admin_Bar;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class MenuManager
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
