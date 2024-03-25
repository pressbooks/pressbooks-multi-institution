<?php

namespace Tests\Feature\Support;

use Pressbooks\Container;
use PressbooksMultiInstitution\Views\BookList;
use Tests\TestCase;
use Tests\Traits\Assertions;
use Tests\Traits\CreatesModels;

class BookListTest extends TestCase
{
    // TODO: include tests for the where clause when user is an institutional manager

    use Assertions;
    use CreatesModels;

    /**
     * @test
     */
    public function it_registers_hook_actions(): void
    {
        $this->assertFalse(has_action('pb_network_analytics_book_list_custom_texts'));
        $this->assertFalse(has_action('pb_network_analytics_book_list_columns'));
        $this->assertFalse(has_action('pb_network_analytics_book_list_select_clause'));
        $this->assertFalse(has_action('pb_network_analytics_book_list_where_clause'));
        //        $this->assertFalse(has_action('pb_network_analytics_filter_tabs'));
        $this->assertFalse(has_action('pb_network_analytics_book_list_filter'));

        Container::get(BookList::class)->init();

        $this->assertTrue(has_action('pb_network_analytics_book_list_custom_texts'));
        $this->assertTrue(has_action('pb_network_analytics_book_list_columns'));
        $this->assertTrue(has_action('pb_network_analytics_book_list_select_clause'));
        $this->assertTrue(has_action('pb_network_analytics_book_list_where_clause'));
        //        $this->assertTrue(has_action('pb_network_analytics_filter_tabs'));
        $this->assertTrue(has_action('pb_network_analytics_book_list_filter'));
    }

    /**
     * @test
     */
    public function it_returns_custom_texts(): void
    {
        wp_set_current_user(
            $this->newInstitutionalManager(institution: $this->createInstitution()),
        );

        Container::get(BookList::class)->init();

        $expected = [
            'title' => 'Fake Institution\'s Book List',
            'count' => 'There are 0 books owned by Fake Institution. They use 0 B of storage.',
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_custom_texts', [
            'title' => 'Book List',
            'count' => 'There are 5 books on this network. They use 50.46 MB of storage.',
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

        Container::get(BookList::class)->init();

        $expected = [
            'title' => 'Book List',
            'count' => 'There are 5 books on this network. They use 50.46 MB of storage.',
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_custom_texts', [
                'title' => 'Book List',
                'count' => 'There are 5 books on this network. They use 50.46 MB of storage.',
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

        Container::get(BookList::class)->init();

        $expected = [
            'title' => 'Book List',
            'count' => 'There are 5 books on this network. They use 50.46 MB of storage.',
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_custom_texts', [
                'title' => 'Book List',
                'count' => 'There are 5 books on this network. They use 50.46 MB of storage.',
        ]));
    }

    /**
     * @test
     */
    public function it_returns_institution_column(): void
    {
        Container::get(BookList::class)->init();

        $expected = [
            [
                'title' => 'Institution',
                'field' => 'institution',
            ]
        ];

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_columns', []));
    }

    /**
     * @test
     */
    public function it_returns_additional_select_clauses_to_the_query(): void
    {
        Container::get(BookList::class)->init();

        $expected = '(select `wptests_institutions`.`id` from `wptests_institutions` inner join `wptests_institutions_blogs` on `wptests_institutions`.`id` = `wptests_institutions_blogs`.`institution_id` where wptests_institutions_blogs.blog_id = b.blog_id) as institution_id, (select `wptests_institutions`.`name` from `wptests_institutions` inner join `wptests_institutions_blogs` on `wptests_institutions`.`id` = `wptests_institutions_blogs`.`institution_id` where wptests_institutions_blogs.blog_id = b.blog_id) as institution';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_select_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_assigned_where_clause_to_the_query(): void
    {
        $_GET['institution'] = [1, 2, 3];

        Container::get(BookList::class)->init();

        $expected = ' AND (institution_id IN (1, 2, 3))';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_where_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_unassigned_where_clause_to_the_query(): void
    {
        $_GET['institution'] = [0];

        Container::get(BookList::class)->init();

        $expected = ' AND (institution_id IS NULL)';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_where_clause', ''));
    }

    /**
     * @test
     */
    public function it_returns_additional_assigned_and_unassigned_where_clause_to_the_query(): void
    {
        $_GET['institution'] = [0, 1, 2];

        Container::get(BookList::class)->init();

        $expected = ' AND (institution_id IN (1, 2) OR institution_id IS NULL)';

        $this->assertEquals($expected, apply_filters('pb_network_analytics_book_list_where_clause', ''));
    }

    //    /**
    //     * @test
    //     */
    //    public function it_returns_additional_filter_tabs_to_super_admins(): void
    //    {
    //        wp_set_current_user(
    //            $this->newSuperAdmin(),
    //        );
    //
    //        $this->createInstitution();
    //
    //        Container::get(BookList::class)->init();
    //
    //        $this->assertTabsExist(
    //            apply_filters('pb_network_analytics_filter_tabs', [])
    //        );
    //    }

    //    /**
    //     * @test
    //     */
    //    public function it_returns_additional_filter_tabs_to_network_managers(): void
    //    {
    //        wp_set_current_user(
    //            $this->newNetworkManager(),
    //        );
    //
    //        $this->createInstitution();
    //
    //        Container::get(BookList::class)->init();
    //
    //        $this->assertTabsExist(
    //            apply_filters('pb_network_analytics_filter_tabs', [])
    //        );
    //    }

    //    /**
    //     * @test
    //     */
    //    public function it_does_not_return_additional_filter_tabs_to_institutional_managers(): void
    //    {
    //        wp_set_current_user(
    //            $this->newInstitutionalManager(institution: $this->createInstitution()),
    //        );
    //
    //        Container::get(BookList::class)->init();
    //
    //        $this->assertEmpty(apply_filters('pb_network_analytics_filter_tabs', []));
    //    }

    /**
     * @test
     */
    public function it_returns_additional_filters_to_super_admins(): void
    {
        wp_set_current_user(
            $this->newSuperAdmin(),
        );

        $this->createInstitution();

        Container::get(BookList::class)->init();

        $this->assertFilterExists(
            apply_filters('pb_network_analytics_book_list_filter', [])
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

        Container::get(BookList::class)->init();

        $this->assertFilterExists(
            apply_filters('pb_network_analytics_book_list_filter', [])
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

        Container::get(BookList::class)->init();

        $this->assertEmpty(
            apply_filters('pb_network_analytics_book_list_filter', [])
        );
    }

    //    protected function assertTabsExist(array $tabs): void
    //    {
    //        $expected = [
    //            'tab' => <<<HTML
    //<li>
    //    <a href="#institutions-tab">
    //        Institution (<span id="institutions-tab-counter">0</span>)
    //    </a>
    //</li>
    //
    //HTML,
    //            'content' => <<<HTML
    //<div id="institutions-tab" class="table-controls">
    //    <fieldset>
    //        <legend>Institution</legend>
    //        <div class="grid-container">
    //                            <label>
    //                    <input name="institution[]" type="checkbox" value="1" /> Fake Institution
    //                </label>
    //                        <label>
    //                <input name="institution[]" type="checkbox" value="0" /> Unassigned
    //            </label>
    //        </div>
    //    </fieldset>
    //</div>
    //
    //HTML,
    //        ];
    //
    //        $result = $tabs[0] ?? [];
    //
    //        foreach ($expected as $key => $value) {
    //            $this->assertArrayHasKey($key, $result);
    //
    //            $this->assertEquals($value, $result[$key]);
    //        }
    //    }

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
