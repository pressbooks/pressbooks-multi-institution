<?php

namespace Tests\Unit;

use PressbooksMultiInstitution\Services\MenuManager;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group menu-manager
 */
class MenuManagerTest extends TestCase
{
    use CreatesModels;

    /**
     * @test
     */
    public function it_adds_the_menu_page(): void
    {
        (new MenuManager)->registerMenus();

        global $menu;

        $this->assertContains('Institutions', $menu[4]);
    }

    /**
     * @test
     */
    public function it_adds_menu_item_in_the_4th_position(): void
    {
        $menuManager = new MenuManager;

        $networkAdminMenu = [
            'index.php',
            'pb_network_analytics_booklist',
            'pb_network_analytics_userlist',
            'https://pressbooks.test/wp/wp-admin/customize.php',
            'https://pressbooks.test/wp/wp-admin/edit.php?post_type=page',
            'plugins.php',
            'settings.php',
            'pb_network_analytics_admin',
            'pb_network_integrations',
            'pb_multi_institution',
        ];

        $menu = $menuManager->handleItems($networkAdminMenu);
        $this->assertEquals('pb_multi_institution', $menu[3]);

        $rootSiteMenu = [
            'https://pressbooks.test/wp-admin/network/index.php',
            'pb_network_analytics_booklist',
            'pb_network_analytics_userlist',
            'customize.php',
            'edit.php?post_type=page',
            'https://pressbooks.test/wp-admin/network/plugins.php',
            'https://pressbooks.test/wp-admin/network/settings.php',
            'https://pressbooks.test/wp-admin/network/admin.php?page=pb_network_analytics_admin',
            'h5p',
            'pb_multi_institution',
            'https://pressbooks.test/wp-admin/network/admin.php?page=pb_network_integrations',
        ];

        $menu = $menuManager->handleItems($rootSiteMenu);
        $this->assertEquals('pb_multi_institution', $menu[3]);
    }

    /**
     * @test
     */
    public function it_does_not_change_menu_order_without_institutions_item(): void
    {
        $institutionalManagerMenu = [
            'https://pressbooks.test/wp-admin/network/index.php',
            'pb_network_analytics_booklist',
            'pb_network_analytics_userlist',
            'https://pressbooks.test/wp-admin/network/admin.php?page=pb_network_analytics_admin',
            'h5p',
            'https://pressbooks.test/wp-admin/network/admin.php?page=pb_network_integrations',
        ];

        $this->assertNotContains('pb_multi_institution', (new MenuManager)->handleItems($institutionalManagerMenu));
    }

    /**
     * @test
     */
    public function it_removes_koko_analytics_item(): void
    {
        $institution = $this->createInstitution();
        wp_set_current_user($this->newInstitutionalManager($institution));


        $menuSlug = network_admin_url('admin.php?page=pb_network_analytics_admin');

        add_menu_page(
            'Network Stats',
            'Network Stats',
            'manage_network',
            $menuSlug,
            '',
            'dashicons-chart-area',
            2
        );

        add_submenu_page(
            $menuSlug,
            'Network Stats',
            'Network Stats',
            'manage_network',
            $menuSlug,
            '',
        );

        add_submenu_page(
            $menuSlug,
            'Analytics',
            'Analytics',
            'view_koko_analytics',
            'koko-analytics',
            '',
        );


        (new MenuManager)->handleItems([]);
        global $submenu;

        $this->assertArrayHasKey($menuSlug, $submenu);
        $this->assertCount(1, $submenu[$menuSlug]);
        $this->assertNotContains('koko-analytics', $submenu[$menuSlug][0]);
        $this->assertEquals($institution->name . ' Stats', $submenu[$menuSlug][0][0]);
    }
}
