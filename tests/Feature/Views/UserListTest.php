<?php

namespace Tests\Feature\Views;

use Pressbooks\Container;
use PressbooksMultiInstitution\Views\UserList;
use Tests\TestCase;
use Tests\Traits\Assertions;
use Tests\Traits\CreatesModels;

class UserListTest extends TestCase
{
    use Assertions;
    use CreatesModels;

    /**
     * @test
     */
    public function it_registers_hook_actions(): void
    {
        $this->assertFalse(has_action('pb_network_analytics_user_list_custom_texts'));
        $this->assertFalse(has_action('pb_network_analytics_user_list_columns'));
        $this->assertFalse(has_action('pb_network_analytics_user_list_select_clause'));
        $this->assertFalse(has_action('pb_network_analytics_user_list_where_clause'));
        $this->assertFalse(has_action('pb_network_analytics_user_list_filter'));

        Container::get(UserList::class)->init();

        $this->assertTrue(has_action('pb_network_analytics_user_list_custom_texts'));
        $this->assertTrue(has_action('pb_network_analytics_user_list_columns'));
        $this->assertTrue(has_action('pb_network_analytics_user_list_select_clause'));
        $this->assertTrue(has_action('pb_network_analytics_user_list_where_clause'));
        $this->assertTrue(has_action('pb_network_analytics_user_list_filter'));
    }

    /**
     * @test
     */
    public function it_returns_custom_texts(): void
    {
        wp_set_current_user(
            $this->newInstitutionalManager(institution: $this->createInstitution()),
        );

        Container::get(UserList::class)->init();

        $expected = [
            'title' => 'Fake Institution\'s User List',
            'count' => 'There is 1 user assigned to Fake Institution.',
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_custom_texts', [
            'title' => 'User List',
            'count' => 'There are 16 Users.',
        ]));
    }

    /**
     * @test
     */
    public function it_does_not_return_custom_texts_to_super_admins(): void
    {
        wp_set_current_user(
            $this->newSuperAdmin(),
        );

        $this->createInstitution();

        Container::get(UserList::class)->init();

        $expected = [
            'title' => 'User List',
            'count' => 'There are 16 users on this network.',
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_custom_texts', [
                'title' => 'User List',
                'count' => 'There are 16 users on this network.',
        ]));
    }

    /**
     * @test
     */
    public function it_does_not_return_custom_texts_to_network_managers(): void
    {
        wp_set_current_user(
            $this->newNetworkManager(),
        );

        $this->createInstitution();

        Container::get(UserList::class)->init();

        $expected = [
            'title' => 'User List',
            'count' => 'There are 4 users on this network.',
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_custom_texts', [
            'title' => 'User List',
            'count' => 'There are 4 users on this network.',
        ]));
    }

    /**
     * @test
     */
    public function it_returns_institution_column(): void
    {
        Container::get(UserList::class)->init();

        $expected = [
            [
                'title' => 'Institution',
                'field' => 'institution',
            ]
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_columns', []));
    }

    /**
     * @test
     */
    public function it_returns_additional_select_clauses_to_the_query(): void
    {
        Container::get(UserList::class)->init();

        $expected = "(select `wptests_institutions`.`id` from `wptests_institutions` left join `wptests_institutions_blogs` on `wptests_institutions`.`id` = `wptests_institutions_blogs`.`institution_id` inner join `wptests_institutions_users` on `wptests_institutions`.`id` = `wptests_institutions_users`.`institution_id` where wptests_institutions_users.user_id = us.id limit 1) as institution_id, (select `wptests_institutions`.`name` from `wptests_institutions` left join `wptests_institutions_blogs` on `wptests_institutions`.`id` = `wptests_institutions_blogs`.`institution_id` inner join `wptests_institutions_users` on `wptests_institutions`.`id` = `wptests_institutions_users`.`institution_id` where wptests_institutions_users.user_id = us.id limit 1) as institution";
        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_select_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_assigned_where_clause_to_the_query(): void
    {
        $_GET['institution'] = [1, 2, 3];

        Container::get(UserList::class)->init();

        $expected = ' AND (institution_id IN (1, 2, 3))';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_where_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_unassigned_where_clause_to_the_query(): void
    {
        $_GET['institution'] = [0];

        Container::get(UserList::class)->init();

        $expected = ' AND (institution_id IS NULL)';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_where_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_assigned_and_unassigned_where_clause_to_the_query(): void
    {
        $_GET['institution'] = [0, 1, 2];

        Container::get(UserList::class)->init();

        $expected = ' AND (institution_id IN (1, 2) OR institution_id IS NULL)';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_user_list_where_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_filters_to_super_admins(): void
    {
        wp_set_current_user(
            $this->newSuperAdmin(),
        );

        $this->createInstitution();

        Container::get(UserList::class)->init();

        $this->assertFilterExists(
            apply_filters('pb_network_analytics_user_list_filter', [])
        );
    }

    /**
     * @test
     */
    public function it_returns_additional_filters_to_network_managers(): void
    {
        wp_set_current_user(
            $this->newNetworkManager(),
        );

        $this->createInstitution();

        Container::get(UserList::class)->init();

        $this->assertFilterExists(
            apply_filters('pb_network_analytics_user_list_filter', [])
        );
    }

    /**
     * @test
     */
    public function it_does_not_return_additional_filters_to_institutional_managers(): void
    {
        wp_set_current_user(
            $this->newInstitutionalManager(institution: $this->createInstitution()),
        );

        Container::get(UserList::class)->init();

        $this->assertEmpty(
            apply_filters('pb_network_analytics_user_list_filter', [])
        );
    }

    protected function assertFilterExists(array $filters): void
    {
        $expected = [
            'field' => 'institution',
            'name' => 'institution[]',
            'counterId' => 'institutions-tab-counter'
        ];

        $result = $filters[0] ?? [];

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);

            $this->assertEquals($value, $result[$key]);
        }
    }
}
