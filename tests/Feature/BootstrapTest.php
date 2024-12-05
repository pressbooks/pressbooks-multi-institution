<?php

namespace Tests\Feature;

use PressbooksMultiInstitution\Models\Institution;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

class BootstrapTest extends TestCase
{
    use CreatesModels;

    /**
     * @test
     */
    public function it_registers_get_institution_id_hook(): void
    {
        global $wp_filter;

        $this->assertArrayHasKey('pressbooks_get_institution_id', $wp_filter);
    }

    /**
     * @test
     */
    public function it_registers_get_institution_dropdown_hook(): void
    {
        global $wp_filter;

        $this->assertArrayHasKey('pressbooks_get_institution_dropdown', $wp_filter);
    }

    /**
     * @test
     */
    public function it_registers_get_institution_by_id_hook(): void
    {
        global $wp_filter;

        $this->assertArrayHasKey('pressbooks_get_institution_by_id', $wp_filter);
    }


    /**
     * @test
     */
    public function it_registers_append_institution_to_query_hook(): void
    {
        global $wp_filter;

        $this->assertArrayHasKey('pressbooks_append_institution_to_query', $wp_filter);
    }

    /**
     * @test
     */
    public function it_retrieves_the_institution_id_for_the_given_user(): void
    {
        $userId = $this->newUser();

        $institution = $this->createInstitution();

        $institution->users()->create([
            'user_id' => $userId,
        ]);

        $id = apply_filters('pressbooks_get_institution_id', $userId);

        $this->assertEquals($institution->id, $id);
    }

    /**
     * @test
     */
    public function it_returns_zero_when_user_is_not_associated(): void
    {
        $userId = $this->newUser();

        $institution = $this->createInstitution();

        $id = apply_filters('pressbooks_get_institution_id', $userId);

        $this->assertEquals(0, $id);
    }

    /**
     * @test
     */
    public function it_retrieves_a_dropdown_list_of_institutions(): void
    {
        $first = $this->createInstitution([
            'name' => 'Fake Institution',
        ]);

        $second = $this->createInstitution([
            'name' => 'Another Fake Institution',
        ]);

        $response = apply_filters('pressbooks_get_institution_dropdown', []);

        $this->assertEquals([
            0 => 'Unassigned',
            $second->id => $second->name,
            $first->id => $first->name,
        ], $response);
    }

    /**
     * @test
     */
    public function it_retrieves_an_empty_dropdown_list_of_institutions_when_no_institutions_are_registered(): void
    {
        $response = apply_filters('pressbooks_get_institution_dropdown', []);

        $this->assertEquals([
            0 => 'Unassigned',
        ], $response);
    }

    /**
     * @test
     */
    public function it_retrieves_the_institution_id(): void
    {
        $institution = $this->createInstitution([
            'name' => 'Fake Institution',
        ]);

        $id = apply_filters('pressbooks_get_institution_by_id', null, $institution->id);

        $this->assertEquals($institution->id, $id);
    }

    /**
     * @test
     */
    public function it_returns_default_value_if_institution_does_not_exist(): void
    {
        $id = apply_filters('pressbooks_get_institution_by_id', null, 999);

        $this->assertNull($id);
    }

    /**
     * @test
     */
    public function it_appends_institution_subquery_to_query(): void
    {
        $query = Institution::query();

        $columnToCompare = 'column_name';

        $this->assertStringNotContainsString('select `name` from `wptests_institutions` where `id` = `column_name`', $query->toSql());

        $query = apply_filters('pressbooks_append_institution_to_query', $query, $columnToCompare);

        $this->assertStringContainsString('select `name` from `wptests_institutions` where `id` = `column_name`', $query->toSql());
    }

    /**
     * @test
     */
    public function it_appends_exists_clause_when_searching(): void
    {
        $query = Institution::query();

        $columnToCompare = 'column_name';

        $searchValue = 'foo';

        $this->assertStringNotContainsString('exists (select 1 from `wptests_institutions` where `id` = `column_name` and `name` like ?)', $query->toSql());

        $this->assertEmpty($query->getBindings());

        $query = apply_filters('pressbooks_append_institution_to_query', $query, $columnToCompare, $searchValue);

        $this->assertStringContainsString('exists (select 1 from `wptests_institutions` where `id` = `column_name` and `name` like ?)', $query->toSql());

        $this->assertEquals([
            '%foo%'
        ], $query->getBindings());
    }

    /**
     * @test
     */
    public function it_appends_order_by_clause_ascending_when_sorting_by_institution(): void
    {
        $query = Institution::query();

        $columnToCompare = 'column_name';

        $sort = 'institution';

        $direction = 'asc';

        $this->assertStringNotContainsString('order by institution IS NOT NULL, `institution` asc', $query->toSql());

        $query = apply_filters('pressbooks_append_institution_to_query', $query, $columnToCompare, '', $sort, $direction);

        $this->assertStringContainsString('order by institution IS NOT NULL, `institution` asc', $query->toSql());
    }

    /**
     * @test
     */
    public function it_appends_order_by_clause_descending_when_sorting_by_institution(): void
    {
        $query = Institution::query();

        $columnToCompare = 'column_name';

        $sort = 'institution';

        $direction = 'desc';

        $this->assertStringNotContainsString('order by institution IS NULL, `institution` desc', $query->toSql());

        $query = apply_filters('pressbooks_append_institution_to_query', $query, $columnToCompare, '', $sort, $direction);

        $this->assertStringContainsString('order by institution IS NULL, `institution` desc', $query->toSql());
    }

    /**
     * @test
     */
    public function it_does_not_append_order_by_clause_when_sorting_by_a_different_field(): void
    {
        $query = Institution::query();

        $columnToCompare = 'column_name';

        $sort = 'foo';

        $direction = 'asc';

        $this->assertStringNotContainsString('order by', $query->toSql());

        $query = apply_filters('pressbooks_append_institution_to_query', $query, $columnToCompare, '', $sort, $direction);

        $this->assertStringNotContainsString('order by', $query->toSql());
    }
}
