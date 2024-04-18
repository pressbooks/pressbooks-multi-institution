<?php

namespace Tests\Feature\Views;

use Pressbooks\Container;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Views\WpUserList;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group wp-user-list
 */
class WpUserListTest extends TestCase
{
    use CreatesModels;

    /**
     * @test
     */
    public function it_registers_hook_actions(): void
    {
        $this->assertFalse(has_filter('wpmu_users_columns'));
        $this->assertFalse(has_filter('manage_users_custom_column'));
        $this->assertFalse(has_filter('manage_users-network_sortable_columns'));
        $this->assertFalse(has_action('pre_user_query'));
        $this->assertFalse(has_filter('views_users-network'));

        Container::get(WpUserList::class)->setupHooks();

        $this->assertTrue(has_filter('wpmu_users_columns'));
        $this->assertTrue(has_filter('manage_users_custom_column'));
        $this->assertTrue(has_filter('manage_users-network_sortable_columns'));
        $this->assertTrue(has_action('pre_user_query'));
        $this->assertTrue(has_filter('views_users-network'));
    }

    /**
     * @test
     */
    public function it_displays_institution_value(): void
    {
        $this->createInstitutionsUsers(1, 1);

        $institution = Institution::query()->first();
        $userId =  $institution->users()->first()->user_id;

        $this->assertEquals(
            $institution->name,
            Container::get(WpUserList::class)->displayInstitutionValue('', 'institution', $userId)
        );

        $this->assertEquals(
            'Unassigned',
            Container::get(WpUserList::class)->displayInstitutionValue('', 'institution', 99)
        );
    }

    /**
     * @test
     */
    public function it_adds_institution_column(): void
    {
        $columns = ['name' => 'Name', 'email' => 'Email', 'registered' => 'Registered'];
        $expected = ['name' => 'Name', 'email' => 'Email', 'institution' => 'Institution', 'registered' => 'Registered'];

        $this->assertEquals(
            $expected,
            Container::get(WpUserList::class)->addInstitutionColumn($columns)
        );
    }

    /**
     * @test
     */
    public function it_makes_institution_column_sortable(): void
    {
        $columns = ['name' => 'name', 'email' => 'email', 'registered' => 'registered'];
        $expected = ['name' => 'name', 'email' => 'email', 'registered' => 'registered', 'institution' => 'institution'];

        $this->assertEquals(
            $expected,
            Container::get(WpUserList::class)->makeInstitutionColumnSortable($columns)
        );
    }

    /**
     * @test
     */
    public function it_modifies_user_query(): void
    {
        $query = new \WP_User_Query(['blog_id' => 1]);

        global $pagenow;
        $pagenow = 'users.php';
        $userId = $this->newSuperAdmin();
        wp_set_current_user($userId);
        $_GET['order'] = 'desc';

        Container::get(WpUserList::class)->modifyUserQuery($query);

        $this->assertStringContainsString('LEFT JOIN', $query->query_from);
        $this->assertStringContainsString('institutions_users', $query->query_from);
        $this->assertStringContainsString('institutions', $query->query_from);
        $this->assertStringContainsString('ORDER BY i.name DESC', $query->query_orderby);
    }

    /**
     * @test
     */
    public function it_add_where_conditions_to_query_for_ims(): void
    {
        $query = new \WP_User_Query(['blog_id' => 1]);

        $this->createInstitutionsUsers(1, 3);

        $institution = Institution::query()->first();
        $userId = $this->newInstitutionalManager($institution);

        grant_super_admin($userId);
        wp_set_current_user($userId);

        global $pagenow;
        $pagenow = 'users.php';

        Container::get(WpUserList::class)->modifyUserQuery($query);

        $this->assertStringContainsString('LEFT JOIN', $query->query_from);
        $this->assertStringContainsString('institutions_users', $query->query_from);
        $this->assertStringContainsString('institutions', $query->query_from);
        $this->assertStringContainsString('AND iu.institution_id = ' . $institution->id, $query->query_where);
    }

    /**
     * @test
     */
    public function it_removes_super_admin_filter(): void
    {
        $this->createInstitutionsUsers(1, 3);

        $institution = Institution::query()->first();
        $userId = $this->newInstitutionalManager($institution);
        wp_set_current_user($userId);

        $views = ['all' => 'All', 'super' => 'Super'];
        $totalUsers = $institution->users()->count();

        $this->assertEquals(
            ['all' => "<a href='#' class='current' aria-current='page'> All <span class='count'>({$totalUsers})</span></a>"],
            Container::get(WpUserList::class)->removeSuperAdminFilter($views)
        );
    }
}
