<?php

namespace Tests\Feature\Actions;

use PressbooksMultiInstitution\Actions\PermissionsManager;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group permissions-manager
 */
class PermissionsManagerTest extends TestCase
{
    use CreatesModels;

    private PermissionsManager $permissionsManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->permissionsManager = new PermissionsManager;
    }

    /**
     * @test
     */
    public function it_filters_users_lists(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $superAdminUserId = $this->newUser([
            'user_login' => 'superadmin',
            'user_email' => 'superadmin@test.com',
        ]);

        grant_super_admin($superAdminUserId);
        wp_set_current_user($superAdminUserId);

        $institution = Institution::query()->first();
        $_GET['institution'] = $institution->name;

        $users = $this->permissionsManager->filterUsersList();

        $expectedUsers = InstitutionUser::query()
            ->where('institution_id', $institution->id)
            ->get()
            ->pluck('user_id')
            ->toArray();

        $this->assertCount(count($expectedUsers), $users);
    }

    /**
     * @test
     */
    public function it_filters_by_manager_institution_logged_user(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $institutionalManagerId = $this->newUser([
            'user_login' => 'subscriber',
            'user_email' => 'subs@subsc.com',
        ]);
        grant_super_admin($institutionalManagerId);
        update_network_option(null, 'pressbooks_network_managers', [$institutionalManagerId]);

        wp_set_current_user($institutionalManagerId);

        $institutionId = Institution::query()->first()->id;
        InstitutionUser::create([
            'user_id' => $institutionalManagerId,
            'institution_id' => $institutionId,
            'manager' => true,
        ]);

        $users = $this->permissionsManager->filterUsersList();

        $this->assertCount(InstitutionUser::byInstitution($institutionId)->count(), $users);
    }

    /**
     * @test
     */
    public function it_gets_all_users_filtered_for_super_admins(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $superAdminId = $this->newUser([
            'user_login' => 'subscriber',
            'user_email' => 'subs@subsc.com',
        ]);
        grant_super_admin($superAdminId);
        wp_set_current_user($superAdminId);

        $institutionId = Institution::query()->first()->id;
        InstitutionUser::create([
            'user_id' => $superAdminId,
            'institution_id' => $institutionId,
        ]);

        $users = $this->permissionsManager->filterUsersList();

        $this->assertCount(count(get_users()), $users);
    }

    /**
     * @test
     */
    public function it_filters_users_lists_by_unassigned(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $superAdminUserId = $this->newUser([
            'user_login' => 'superadmin',
            'user_email' => 'superadmin@test.com',
        ]);

        grant_super_admin($superAdminUserId);
        wp_set_current_user($superAdminUserId);

        $wpUsers = get_users();
        $wpUserIds = array_map(fn ($user) => $user->ID, $wpUsers);

        $_GET['institution'] = 'unassigned-institution';

        $users = $this->permissionsManager->filterUsersList();

        $institutionUsers = InstitutionUser::query()
            ->get()
            ->pluck('user_id')
            ->toArray();

        $expected = array_diff($wpUserIds, $institutionUsers);

        $this->assertCount(count($expected), $users);
    }

    /**
     * @test
     */
    public function it_adds_institutions_filter_to_users_list_for_super_admins(): void
    {
        $superAdminUserId = $this->newUser([
            'user_login' => 'superadmin',
            'user_email' => 'test@superadmin.com',
        ]);

        grant_super_admin($superAdminUserId);
        wp_set_current_user($superAdminUserId);

        $data = $this->permissionsManager->addInstitutionsFilterForUsersList()[0];

        $this->assertArrayHasKey('partial', $data);
        $this->assertArrayHasKey('data', $data);

        $institutions = $data['data']['institutions'];
        $this->assertCount(Institution::query()->count(), $institutions);

        $this->assertEquals('PressbooksMultiInstitution::partials.userslist.filters', $data['partial']);

        $superAdminUserId = $this->newUser([
            'user_login' => 'regularuser',
            'user_email' => 'test@regular.com',
        ]);
        wp_set_current_user($superAdminUserId);

        $this->assertEmpty($this->permissionsManager->addInstitutionsFilterForUsersList());
    }

    /**
     * @test
     */
    public function it_adds_institutions_filter_attribute_to_users_list_table(): void
    {
        $this->assertEquals([
            [
                'field' => 'institution',
                'id' => 'institutions-dropdown',
            ]
        ], $this->permissionsManager->addInstitutionsFilterAttributesForUsersList());
    }

    /**
     * @test
     */
    public function it_adds_institution_column_to_users_list_before_email_column(): void
    {
        $columns = $this->permissionsManager->addInstitutionColumnToUsersList([
            [
                'title' => 'Bulk action',
                'field' => '_bulkAction',
                'formatter' => 'rowSelection',
                'titleFormatter' => 'rowSelection',
                'align' => 'center',
                'headerSort' => false,
                'cellClick' => true,
            ],
            [
                'title' => 'id',
                'field' => 'id',
                'visible' => false,
            ],
            [
                'title' => 'Username',
                'field' => 'username',
                'formatter' => 'html',
            ],
            [
                'title' => 'Name',
                'field' => 'name',
            ],
            [
                'title' => 'Email',
                'field' => 'email',
            ],
            [
                'title' => 'Registered',
                'field' => 'registered',
                'formatter' => 'datetime',
                'formatterParams' => [
                    'inputFormat' => 'YYYY-MM-DD hh:mm:ss',
                    'outputFormat' => 'YYYY-MM-DD',
                    'invalidPlaceholder' => 'N/A',
                ],
            ],
        ]);

        $this->assertEquals($columns[4], [
            'title' => 'Email',
            'field' => 'email',
        ]);

        $this->assertEquals($columns[5], [
            'title' => 'Institution',
            'field' => 'institution',
        ]);
    }

    /**
     * @test
     */
    public function it_adds_institution_field_to_users(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $wpUsers = get_users();

        $wpUsers = array_map(fn ($user) => (object) [
            'id' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
        ], $wpUsers);

        $users = $this->permissionsManager->addInstitutionFieldToUsers($wpUsers);

        foreach ($users as $user) {
            $properties = array_keys(get_object_vars($user));
            $this->assertGreaterThan(array_search('email', $properties), array_search('institution', $properties));

            $institutionUser = InstitutionUser::query()->where('user_id', $user->id)->first();

            $institutionUser ? $this->assertEquals($institutionUser->institution->name, $user->institution) :
                $this->assertEquals('Unassigned', $user->institution);
        }
    }

    public function tearDown(): void
    {
        InstitutionUser::query()->delete();
        Institution::query()->delete();

        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->usermeta}");
        $wpdb->query("DELETE FROM {$wpdb->users}");

        parent::tearDown();
    }
}
