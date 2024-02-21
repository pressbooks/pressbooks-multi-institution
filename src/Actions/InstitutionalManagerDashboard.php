<?php

namespace PressbooksMultiInstitution\Actions;

use Pressbooks\Admin\Dashboard\Dashboard;

class InstitutionalManagerDashboard extends Dashboard
{
    protected string $page_name = 'pb_institutional_manager';

    public function render(): void
    {
        echo app('Blade')->addNamespace(
            'PressbooksMultiInstitution',
            WP_PLUGIN_DIR.'/pressbooks-multi-institution/resources/views'
        )->render('PressbooksMultiInstitution::dashboard.institutional');
    }

    protected function shouldRedirect(): bool
    {
        // TODO: Implement shouldRedirect() method.
    }
}
