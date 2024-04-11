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
    }

    /**
     * @test
     */
    public function it_deletes_institutions(): void
    {
        $this->createInstitutionsUsers(2, 10);

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

        $this->assertEquals(1, Institution::all()->count());
        $this->assertStringContainsString('Institution has been added.', $form);
    }

    /**
     * @test
     */
    public function it_does_not_save_duplicated_institution_name(): void
    {
        $this->createInstitution();

        $_POST['name'] = 'Fake Institution';
        $_POST['domains'] = ['pressbooks.test', 'institution.pressbooks.test'];
        $_REQUEST['_wpnonce'] = wp_create_nonce('pb_multi_institution_form');

        $response = $this->institutionsController->save(isSuperAdmin: true);

        $expected = <<<HTML
<p>
	An institution with the name Fake Institution already exists.
	<span class="red">Please use a different name.</span>
</p>

HTML;

        $this->assertEquals(1, Institution::query()->count());
        $this->assertFalse($response['success']);
        $this->assertEquals('The form is invalid.', $response['message']);
        $this->assertEquals($expected, $response['errors']['name'][0]);
    }

    /**
     * @test
     */
    public function it_updates_the_institution_when_using_the_same_name(): void
    {
        $institution = $this->createInstitution();

        $_POST['name'] = 'Fake Institution';
        $_POST['ID'] = $institution->id;

        $_REQUEST['_wpnonce'] = wp_create_nonce('pb_multi_institution_form');

        $response = $this->institutionsController->save(isSuperAdmin: true);

        $this->assertEquals(1, Institution::query()->count());
        $this->assertTrue($response['success']);
        $this->assertEquals('Institution has been updated.', $response['message']);
    }
}
