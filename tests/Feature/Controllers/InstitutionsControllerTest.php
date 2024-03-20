<?php

namespace Tests\Feature\Controllers;

use PressbooksMultiInstitution\Controllers\InstitutionsController;
use PressbooksMultiInstitution\Models\Institution;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

use function Pressbooks\Admin\NetworkManagers\_restricted_users;

/**
 * @group institutions-controller
 */
class InstitutionsControllerTest extends TestCase
{
    use CreatesModels;

    private InstitutionsController $institutionsController;

    public function setUp(): void
    {
        parent::setUp();

        $this->institutionsController = new InstitutionsController;

        $this->createInstitutionsUsers(2, 10);
    }

    /**
     * @test
     */
    public function it_deletes_institutions(): void
    {
        $institution = Institution::query()->first();

        $userManager = $this->assignAnInstitutionalManager($institution);

        $this->assertTrue(is_super_admin($userManager->user_id));

        $this->assertContains($userManager->user_id, _restricted_users());

        $this->assertEquals(2, Institution::all()->count());

        $_REQUEST['action'] = 'delete';
        $_REQUEST['ID'] = [$institution->id];
        $_REQUEST['_wpnonce'] = wp_create_nonce('bulk-institutions');

        ob_start();
        $this->institutionsController->index();
        ob_get_clean();

        $this->assertEquals(1, Institution::all()->count());

        $this->assertNotContains($userManager->user_id, _restricted_users());

        $this->assertFalse(is_super_admin($userManager->user_id));

    }
}
