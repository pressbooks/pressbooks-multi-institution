<?php

namespace Tests\Feature\Controllers;

use PressbooksMultiInstitution\Controllers\AssignUsersController;
use PressbooksMultiInstitution\Models\InstitutionUser;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group assign-users-controller
 */
class AssignUsersControllerTest extends TestCase
{
    use CreatesModels;

    private AssignUsersController $controller;

    private array $institutions;

    private array $users;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new AssignUsersController;

        $this->createInstitutionsUsers(2, 10);
    }

    /**
     * @test
     */
    public function it_renders_index_with_params(): void
    {
        $_REQUEST['orderby'] = 'username';
        $_REQUEST['order'] = 'ASC';
        $index = $this->controller->assign();

        $this->assertMatchesRegularExpression('/johndoe0/', $index);
        $this->assertMatchesRegularExpression('/johndoe9/', $index);

        $_REQUEST['s'] = 'johndoe3';
        $index = $this->controller->assign();

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
        $this->controller->assign();

        $institutionUser = InstitutionUser::query()->where('user_id', $users[0])->first();
        $this->assertEquals($institution, $institutionUser->institution_id);

        $institutionUser = InstitutionUser::query()->where('user_id', $users[1])->first();
        $this->assertEquals($institution, $institutionUser->institution_id);

        $_REQUEST['action'] = '0';
        $this->controller->assign();

        $this->assertEquals(0, InstitutionUser::query()->where('user_id', $users[0])->count());
        $this->assertEquals(0, InstitutionUser::query()->where('user_id', $users[1])->count());
    }

    /**
     * @test
     */
    public function it_keeps_ordering_after_bulk_action(): void
    {
        $_REQUEST['orderby'] = 'username';
        $_REQUEST['order'] = 'DESC';
        $index = $this->controller->assign();

        $this->assertMatchesRegularExpression('/johndoe9/', $index);
        $this->assertMatchesRegularExpression('/johndoe0/', $index);

        $institutionsUsers = InstitutionUser::all();
        $users = $institutionsUsers->pluck('user_id')->toArray();

        $_REQUEST['ID'] = [$users[0], $users[1]];
        $_REQUEST['action'] = $institutionsUsers->first()->institution_id;
        $this->controller->assign();

        $this->assertMatchesRegularExpression('/johndoe9/', $index);
        $this->assertMatchesRegularExpression('/johndoe0/', $index);
    }

    /**
     * @test
     */
    public function it_renders_total_users_and_unassigned_total_users(): void
    {
        $this->newUser();

        $index = $this->controller->assign();

        $this->assertMatchesRegularExpression('/All[\s\S]*<span class="count">[\s\S]*\(11\)[\s\S]*<\/span>/i', $index);
        $this->assertMatchesRegularExpression('/Unassigned[\s\S]*<span class="count">[\s\S]*\(1\)[\s\S]*<\/span>/i', $index);
    }

    /**
     * @test
     */
    public function it_filters_by_unassigned_users(): void
    {
        $this->newUser([
            'user_login' => 'unassigned_user1',
            'user_email' => 'unassigned1@pressbooks.test',
        ]);
        $this->newUser([
            'user_login' => 'unassigned_user2',
            'user_email' => 'unassigned2@pressbooks.test',
        ]);

        $_REQUEST['unassigned'] = '1';

        $index = $this->controller->assign();

        $this->assertDoesNotMatchRegularExpression('/johndoe1/', $index);
        $this->assertDoesNotMatchRegularExpression('/johndoe5/', $index);
        $this->assertDoesNotMatchRegularExpression('/johndoe9/', $index);

        $this->assertMatchesRegularExpression('/unassigned_user1/', $index);
        $this->assertMatchesRegularExpression('/unassigned_user2/', $index);
    }
}
