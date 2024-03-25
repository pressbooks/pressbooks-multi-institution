<?php

namespace Tests\Feature;

use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Services\PermissionsManager;
use Tests\TestCase;
use Tests\Traits\CreatesModels;
use Tests\Traits\Utils;

class PermissionsManagerTest extends TestCase
{
    use CreatesModels;
    use Utils;
    private $redirect_url = '';

    public function setUp(): void
    {
        parent::setUp();
        // Override the redirect function to capture the URL and not actually redirect
        add_filter('wp_redirect', [$this, 'captureRedirect'], 10, 2);
    }

    public function captureRedirect($location, $status): bool
    {
        $this->redirect_url = $location;
        return false;
    }

    /**
     * @group pressbooks-multi-institution
     * @test
     */
    public function it_test_institutional_managers_hooks(): void
    {

        $userId = $this->newUser();

        $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        $this->assertFalse(has_filter('pb_institution'));

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        // Associate user with institution
        $institution->users()->create([
            'user_id' => $userId,
            'manager' => true,
        ]);

        $permissionsManager = new PermissionsManager;
        $permissionsManager->syncRestrictedUsers([
            $userId
        ], []);

        $permissionsManager->setupInstitutionalFilters();

        wp_set_current_user(1);
        $this->assertFalse(apply_filters('pb_institution', false));

        wp_set_current_user($userId);
        $permissionsManager->setupInstitutionalFilters();

        $this->assertNotFalse(apply_filters('pb_institution', false));
        $this->assertTrue(has_filter('pb_institutional_users'));
    }
    /**
     * @group pressbooks-multi-institution
     * @test
     */
    public function it_redirects_super_admins_if_tries_to_reach_institutional_manager_dashboard(): void
    {
        $institutionalManagerDashboard = new InstitutionalManagerDashboard;
        $institutionalManagerDashboard->hooks();

        $userId = $this->newUser();
        wp_set_current_user($userId);
        grant_super_admin($userId);

        set_current_screen('wp-admin/index.php?page=pb_institutional_manager');
        $_GET['page'] = 'pb_institutional_manager';

        ob_start();
        do_action('admin_init');
        ob_get_clean();

        $this->assertStringContainsString('index.php?page=pb_network_page', $this->redirect_url);
    }
}
