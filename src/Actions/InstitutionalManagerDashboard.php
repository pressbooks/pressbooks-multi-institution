<?php

namespace PressbooksMultiInstitution\Actions;

use Pressbooks\Admin\Dashboard\Dashboard;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class InstitutionalManagerDashboard extends Dashboard
{
    protected string $page_name = 'pb_institutional_manager';

    public function hooks(): void
    {
        add_action('load-index.php', [$this, 'redirect'], 1000);
        add_action('admin_menu', [$this, 'removeDefaultPage']);
        add_action('admin_menu', [$this, 'addNewPage']);
    }

    public function render(): void
    {
        echo app('Blade')->addNamespace(
            'PressbooksMultiInstitution',
            WP_PLUGIN_DIR.'/pressbooks-multi-institution/resources/views'
        )->render(
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
        return 0 !== get_institution_by_manager() && 'index' === get_current_screen()->base;
    }

    public function getURL(): string
    {
        return network_admin_url("index.php?page={$this->page_name}");
    }
}
