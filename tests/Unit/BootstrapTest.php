<?php

namespace Tests\Unit;

use PressbooksMultiInstitution\Bootstrap;
use Tests\TestCase;

/**
 * @group bootstrap
 */
class BootstrapTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_the_menu_page(): void
    {
        (new Bootstrap)->registerMenus();

        global $menu;

        $this->assertContains('Institutions', $menu[0]);
    }

    /**
     * @test
     */
    public function it_adds_menu_item_in_the_4th_position(): void
    {
        $bootstrap = new Bootstrap;

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

        $menu = $bootstrap->reOrderMenuItems($networkAdminMenu);
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

        $menu = $bootstrap->reOrderMenuItems($rootSiteMenu);
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

        $this->assertNotContains('pb_multi_institution', (new Bootstrap)->reOrderMenuItems($institutionalManagerMenu));
    }
}
