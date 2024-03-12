<?php

namespace PressbooksMultiInstitution\Actions;

use Pressbooks\Admin\Dashboard\Dashboard;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class InstitutionalManagerDashboard extends Dashboard
{
    protected string $page_name = 'pb_institutional_manager';

    public function hooks(): void
    {
        add_action('load-index.php', [$this, 'redirect'], 1001);
        add_action('admin_menu', [$this, 'removeDefaultPage']);
        add_action('admin_menu', [$this, 'addNewPage']);
        add_action('admin_init', [$this, 'superAdminSafeRedirect'], 1);
    }

    public function render(): void
    {
        echo app('Blade')->render(
            'PressbooksMultiInstitution::dashboard.institutional',
            [
                'network_name' => get_bloginfo('name'),
                'network_url' => network_home_url(),
                'institution_name' => apply_filters('pb_institution', [])['name'],
                'total_books' => count(apply_filters('pb_institutional_books', [])),
                'total_users' => count(apply_filters('pb_institutional_users', [])),
                'network_analytics_active' => is_plugin_active('pressbooks-network-analytics/pressbooks-network-analytics.php')
            ]
        );
    }

    protected function shouldRedirect(): bool
    {
        return is_main_site() && 0 !== get_institution_by_manager();
    }

    public function getURL(): string
    {
        return network_site_url('wp-admin/index.php?page=pb_institutional_manager');
    }

    public function superAdminSafeRedirect(): void
    {
        $currentPage = $_GET['page'] ?? '';
        if (get_institution_by_manager() === 0 && is_super_admin() && $currentPage === 'pb_institutional_manager') {
            wp_redirect(network_site_url('wp-admin/network/index.php?page=pb_network_page'));
        }
    }
}
