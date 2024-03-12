<?php

namespace Tests\Feature\Views;

use PressbooksMultiInstitution\Views\AssignUsersTable;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group institutions-users-table
 */
class AssignUsersTableTest extends TestCase
{
    use CreatesModels;

    private AssignUsersTable $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = app(AssignUsersTable::class);
    }

    /**
     * @test
     */
    public function it_should_return_default_column(): void
    {
        $item = ['name' => 'John Doe'];
        $this->assertEquals('John Doe', $this->table->column_default($item, 'name'));
    }

    /**
     * @test
     */
    public function it_should_return_name_column(): void
    {
        $item = ['name' => 'John Doe'];
        $this->assertEquals('<div class="row-title">John Doe</div>', $this->table->column_name($item));
    }

    /**
     * @test
     */
    public function it_should_return_cb_column(): void
    {
        $item = ['ID' => 1];
        $this->assertEquals('<input type="checkbox" name="ID[]" value="1" />', $this->table->column_cb($item));
    }

    /**
     * @test
     */
    public function it_should_return_columns(): void
    {
        $expected = [
            'cb' => '<input type="checkbox" />',
            'username' => __('Username', 'pressbooks-multi-institution'),
            'name' => __('Name', 'pressbooks-multi-institution'),
            'email' => __('Email', 'pressbooks-multi-institution'),
            'institution' => __('Institution', 'pressbooks-multi-institution'),
        ];
        $this->assertEquals($expected, $this->table->get_columns());
    }

    /**
     * @test
     */
    public function it_should_return_sortable_columns(): void
    {
        $expected = [
            'username' => ['username', false],
            'name' => ['name', false],
            'email' => ['email', false],
            'institution' => ['institution', false],
        ];
        $this->assertEquals($expected, $this->table->get_sortable_columns());
    }

    /**
     * @test
     */
    public function it_should_return_bulk_actions(): void
    {
        $this->createInstitution(['name' => 'Institution 1']);
        $this->createInstitution(['name' => 'Institution 2']);

        $this->assertEquals([
            0 => __('Unassigned', 'pressbooks-multi-institution'),
            1 => 'Institution 1',
            2 => 'Institution 2',
        ], $this->table->get_bulk_actions());
    }

    /**
     * @test
     */
    public function it_should_prepare_items(): void
    {
        $this->createInstitutionsUsers(2, 5);

        $this->table->prepare_items();

        $this->assertCount(5, $this->table->items);

        $this->assertContains('johndoe1', array_column($this->table->items, 'username'));
        $this->assertContains('j4@fake.test', array_column($this->table->items, 'email'));
    }

    /**
     * @test
     */
    public function it_should_prepare_items_with_search_query(): void
    {
        $this->createInstitutionsUsers(2, 5);

        $_REQUEST['s'] = 'johndoe1';
        $this->table->prepare_items();

        $this->assertCount(1, $this->table->items);
        $this->assertEquals('johndoe1', $this->table->items[0]['username']);

        $_REQUEST['s'] = 'Doe3';
        $this->table->prepare_items();

        $this->assertCount(1, $this->table->items);
        $this->assertEquals('John3 Doe3', $this->table->items[0]['name']);

        // search by email
        $_REQUEST['s'] = 'j4@fake';
        $this->table->prepare_items();

        $this->assertCount(1, $this->table->items);
        $this->assertEquals('j4@fake.test', $this->table->items[0]['email']);
    }

    /**
     * @test
     */
    public function it_should_prepare_items_with_order_by(): void
    {
        $this->createInstitutionsUsers(2, 5);

        $_REQUEST['orderby'] = 'username';
        $_REQUEST['order'] = 'asc';
        $this->table->prepare_items();

        $this->assertEquals('johndoe0', $this->table->items[0]['username']);
        $this->assertEquals('johndoe4', $this->table->items[4]['username']);

        $_REQUEST['orderby'] = 'username';
        $_REQUEST['order'] = 'desc';
        $this->table->prepare_items();

        $this->assertEquals('johndoe4', $this->table->items[0]['username']);
        $this->assertEquals('johndoe0', $this->table->items[4]['username']);

        $_REQUEST['orderby'] = 'name';
        $_REQUEST['order'] = 'asc';
        $this->table->prepare_items();

        $this->assertEquals('johndoe0', $this->table->items[0]['username']);
        $this->assertEquals('John4 Doe4', $this->table->items[4]['name']);

        $this->newUser([
            'user_login' => "testuser",
            'user_email' => "test@fake.test",
        ]);

        $_REQUEST['orderby'] = 'institution';
        $_REQUEST['order'] = 'asc';
        $this->table->prepare_items();

        $this->assertEquals('Unassigned', $this->table->items[0]['institution']);
    }
}
