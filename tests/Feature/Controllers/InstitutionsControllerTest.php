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

    /**
     * @test
     */
    public function it_saves_institution(): void
    {
        $_POST['name'] = 'New Institution';
        $_POST['domains'] = ['pressbooks.test', 'institution.pressbooks.test'];
        $_REQUEST['_wpnonce'] = wp_create_nonce('pb_multi_institution_form');

        $form = $this->institutionsController->form();

        $this->assertEquals(3, Institution::all()->count());
        $this->assertStringContainsString('Institution has been added.', $form);
    }

    /**
     * @test
     */
    public function it_does_not_save_duplicated_institution_name(): void
    {
        $institutionName = Institution::query()->first()->name;

        $this->assertEquals(2, Institution::all()->count());

        $_POST['name'] = $institutionName;
        $_POST['domains'] = ['pressbooks.test', 'institution.pressbooks.test'];
        $_REQUEST['_wpnonce'] = wp_create_nonce('pb_multi_institution_form');

        $form = $this->institutionsController->form();

        $this->assertEquals(2, Institution::all()->count());
        $this->assertStringContainsString('The form is invalid.', $form);
        $this->assertStringContainsString($institutionName . ' institution already exists. Please, choose another name.', $form);
    }
}
