<?php

namespace Tests\Feature;

use PressbooksMultiInstitution\Actions\PermissionsManager;

class PermissionsManagerTest
{
    /**
     * Test permissions manager register
     * @group pressbooks-multi-institution
     * @test
     */
    public function it_register_menus(): void
    {
        $permissionsManager = new PermissionsManager;
        $permissionsManager->setupInstitutionalFilters();
        $this->assertTrue(has_action('admin_menu', [$permissionsManager, 'registerMenu']));
    }
}
