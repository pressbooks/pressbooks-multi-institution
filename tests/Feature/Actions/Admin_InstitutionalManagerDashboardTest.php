<?php

use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;

use Tests\TestCase;
use Tests\Traits\CreatesModels;

class Admin_InstitutionalManagerDashboardTest extends TestCase
{
    use utilsTrait;
    use CreatesModels;

    /**
     * @test
     * @group institutional-manager-dashboard
     */
    public function it_checks_instance(): void
    {
        $this->assertInstanceOf(InstitutionalManagerDashboard::class, InstitutionalManagerDashboard::init());
    }

    /**
     * @test
     * @group institutional-manager-dashboard
     */
    public function it_checks_hooks(): void
    {
        global $wp_filter;

        InstitutionalManagerDashboard::init()->hooks();

        $this->assertArrayHasKey('load-index.php', $wp_filter);
        $this->assertArrayHasKey('admin_menu', $wp_filter);
    }

    /**
     * @test
     * @group institutional-manager-dashboard
     */
    public function it_renders_home_page(): void
    {
        wp_set_current_user($institutionalManagerFromFirstInstitution);

        add_action('pb_institution', function () {
            return ['name' => 'Fake First Institution'];
        });

        add_action('pb_institutional_books', function () {
            return [1,2];
        });

        add_action('pb_institutional_users', function () {
            return [1];
        });

        ob_start();
        InstitutionalManagerDashboard::init()->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to', $output);
        $this->assertStringContainsString('Administer Fake First Institution', $output);
        $this->assertStringContainsString('Support resources', $output);
        $this->assertStringContainsString('Fake First Institution has <strong>2</strong> books and <strong>1</strong> users', $output);
    }

}
