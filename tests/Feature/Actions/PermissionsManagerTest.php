<?php

namespace Tests\Feature\Actions;

use PressbooksMultiInstitution\Actions\PermissionsManager;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use Tests\TestCase;
use Tests\Traits\CreatesModels;
use Tests\Traits\Utils;

/**
 * @group permissions-manager
 */
class PermissionsManagerTest extends TestCase
{
    use CreatesModels;
    use Utils;

    private PermissionsManager $permissionsManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->permissionsManager = new PermissionsManager;
    }

    private function setSuperAdminUser(): int
    {
        $superAdminUserId = $this->newUser([
            'user_login' => 'superadmin',
            'user_email' => 'superadmin@test.com',
        ]);

        grant_super_admin($superAdminUserId);
        wp_set_current_user($superAdminUserId);

        return $superAdminUserId;
    }

    /**
     * @test
     */
    public function it_filters_users_lists(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $this->setSuperAdminUser();

        $institution = Institution::query()->first();
        $_GET['institution'] = [$institution->id];

        $expectedUsers = InstitutionUser::query()
            ->where('institution_id', $institution->id)
            ->get()
            ->pluck('user_id')
            ->toArray();

        $this->assertCount(count($expectedUsers), $this->permissionsManager->filterUsersList());
    }

    /**
     * @test
     */
    public function it_filters_by_unexisting_institution(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $this->setSuperAdminUser();

        $_GET['institution'] = [-1];

        $this->assertCount(0, $this->permissionsManager->filterUsersList());
    }

    /**
     * @test
     */
    public function it_filters_users_by_institution_without_users(): void
    {
        $this->createInstitutionsUsers(2, 0);

        $this->setSuperAdminUser();

        $_GET['institution'] = [Institution::query()->first()->id];

        $this->assertCount(0, $this->permissionsManager->filterUsersList());
    }

    /**
     * @test
     */
    public function it_filters_by_manager_institution_logged_user(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $institutionalManagerId = $this->setSuperAdminUser();
        update_network_option(null, 'pressbooks_network_managers', [$institutionalManagerId]);

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
    public function it_filters_for_network_managers(): void
    {
        $networkManager = $this->setSuperAdminUser();
        update_network_option(null, 'pressbooks_network_managers', [$networkManager]);

        $this->createInstitutionsUsers(2, 10);

        $institutions = Institution::query()->pluck('id')->toArray();

        $_GET['institution'] = [$institutions[0], $institutions[1]];

        $this->assertCount(
            InstitutionUser::query()->whereIn('institution_id', $institutions)->count(),
            $this->permissionsManager->filterUsersList()
        );
    }

    /**
     * @test
     */
    public function it_filters_for_network_managers_without_institutions(): void
    {
        $this->setSuperAdminUser();

        $this->newUser([
            'user_login' => 'user1',
            'user_email' => 'user1@test.pb',
        ]);
        $this->newUser([
            'user_login' => 'user2',
            'user_email' => 'user2@test.pb',
        ]);

        $_GET['institution'] = [0];
        $this->assertCount(count(get_users(['blog_id' => 0])), $this->permissionsManager->filterUsersList());
    }

    /**
     * @test
     */
    public function it_gets_all_users_filtered_for_super_admins(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $superAdminId = $this->setSuperAdminUser();

        $institutionId = Institution::query()->first()->id;
        InstitutionUser::create([
            'user_id' => $superAdminId,
            'institution_id' => $institutionId,
        ]);

        $users = $this->permissionsManager->filterUsersList();

        $this->assertCount(count(get_users(['blog_id' => 0])), $users);
    }

    /**
     * @test
     */
    public function it_filters_users_lists_by_unassigned(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $this->setSuperAdminUser();

        $wpUsers = get_users(['blog_id' => 0]);
        $wpUserIds = array_map(fn ($user) => $user->ID, $wpUsers);

        $_GET['institution'] = [0];

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
        $this->setSuperAdminUser();
        $this->createInstitutionsUsers(2, 10);

        $bookId1 = $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook(
            ['path' => '/book1', 'title' => 'Book 1']
        ));
        $bookId2 = $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook(
            ['path' => '/book2', 'title' => 'Book 2']
        ));

        $institutions = Institution::query()->get();

        $institution1 = $institutions[0];
        $institution2 = $institutions[1];

        $institution1->books()->create(['blog_id' => $bookId1]);
        $institution2->books()->create(['blog_id' => $bookId2]);

        $data = $this->permissionsManager->addInstitutionsFilterTab([])[0];

        $this->assertArrayHasKey('tab', $data);
        $this->assertArrayHasKey('content', $data);

        // asssert that tab content template is rendered with regex
        $this->assertMatchesRegularExpression(
            '/<a href="#institutions-tab">/',
            $data['tab']
        );

        $this->assertMatchesRegularExpression(
            '/<div id="institutions-tab" class="table-controls">/',
            $data['content']
        );
        $this->assertMatchesRegularExpression(
            '/<input\\s+[^>]*?name="institution\\[\\]"\\s+[^>]*?type="checkbox"\\s+[^>]*?value="0"\\s*\\/?>\\s*Unassigned/',
            $data['content']
        );

        foreach ($institutions as $institution) {
            $this->assertMatchesRegularExpression(
                '/<input\\s+[^>]*?name="institution\\[\\]"\\s+[^>]*?type="checkbox"\\s+[^>]*?value="' . $institution->id . '"\\s*\\/?>\\s*' . $institution->name . '/',
                $data['content']
            );
        }


        $regularUserId = $this->newUser([
            'user_login' => 'regularuser',
            'user_email' => 'test@regular.com',
        ]);
        wp_set_current_user($regularUserId);

        $this->assertEmpty($this->permissionsManager->addInstitutionsFilterTab([]));
    }

    /**
     * @test
     */
    public function it_adds_institutions_filter_attribute_to_users_list_table(): void
    {
        $this->assertEquals([
            [
                'field' => 'institution',
                'name' => 'institution[]',
                'counterId' => 'institutions-tab-counter',
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

        $wpUsers = get_users(['blog_id' => 0]);

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

    /**
     * @test
     */
    public function it_returns_custom_text_for_users_list(): void
    {
        $this->createInstitutionsUsers(2, 10);

        $IMId = $this->setSuperAdminUser();
        update_network_option(null, 'pressbooks_network_managers', [$IMId]);

        $institution = Institution::query()->first();
        InstitutionUser::create([
            'user_id' => $IMId,
            'institution_id' => $institution->id,
            'manager' => true,
        ]);

        $customTextArray = $this->permissionsManager->addCustomTextForUsersList([]);

        $this->assertArrayHasKey('title', $customTextArray);
        $this->assertArrayHasKey('count', $customTextArray);

        $totalUsers = InstitutionUser::query()->byInstitution($institution->id)->count();

        $this->assertEquals($institution->name . "'s User List", $customTextArray['title']);
        $this->assertEquals(
            'There are ' . $totalUsers . ' users assigned to ' . $institution->name . '.',
            $customTextArray['count']
        );
    }

    public function tearDown(): void
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->usermeta}");
        $wpdb->query("DELETE FROM {$wpdb->users}");

        parent::tearDown();
    }
}
