<?php

namespace Tests\Feature\Controllers;

use PressbooksMultiInstitution\Controllers\InstitutionsUsersController;
use PressbooksMultiInstitution\Models\InstitutionUser;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group institutions-users-controller
 */
class InstitutionsUsersControllerTest extends TestCase
{
    use CreatesModels;

    private InstitutionsUsersController $institutionsUsersController;

    private array $institutions;

    private array $users;

    public function setUp(): void
    {
        parent::setUp();
        $this->institutionsUsersController = new InstitutionsUsersController;

        $this->createInstitutionsUsers(2, 10);
    }

    /**
     * @test
     */
    public function it_renders_index_with_params(): void
    {
        $_REQUEST['orderby'] = 'username';
        $_REQUEST['order'] = 'ASC';
        $index = $this->institutionsUsersController->index();

        $this->assertMatchesRegularExpression('/johndoe0/', $index);
        $this->assertMatchesRegularExpression('/johndoe9/', $index);

        $_REQUEST['s'] = 'johndoe3';
        $index = $this->institutionsUsersController->index();

        $this->assertMatchesRegularExpression('/johndoe3/', $index);
    }

    /**
     * @test
     */
    public function it_processes_bulk_actions(): void
    {
        $institutionsUsers = InstitutionUser::all();
        $users = $institutionsUsers->pluck('user_id')->toArray();
        $institution = $institutionsUsers->first()->institution_id;

        InstitutionUser::query()->whereIn('user_id', [$users[0], $users[1]])->delete();

        $_REQUEST['ID'] = [$users[0], $users[1]];
        $_REQUEST['action'] = $institution;
        $this->institutionsUsersController->index();

        $institutionUser = InstitutionUser::query()->where('user_id', $users[0])->first();
        $this->assertEquals($institution, $institutionUser->institution_id);

        $institutionUser = InstitutionUser::query()->where('user_id', $users[1])->first();
        $this->assertEquals($institution, $institutionUser->institution_id);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($_REQUEST['s'], $_REQUEST['orderby'], $_REQUEST['order']);

        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->usermeta}");
        $wpdb->query("DELETE FROM {$wpdb->users}");
    }
}
