<?php

namespace Tests\Feature\Views;

use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Views\AssignBooksTable;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

class AssignBooksTableTest extends TestCase
{
    use CreatesModels;

    private AssignBooksTable $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = app(AssignBooksTable::class);
    }

    /**
     * @test
     */
    public function it_renders_default_value(): void
    {
        $result = $this->table->column_default($this->fakeInstitution(), 'title');

        $this->assertEquals('Fake Institution', $result);
    }

    /**
     * @test
     */
    public function it_renders_the_checkbox_column(): void
    {
        $expected = <<<HTML
<input type="checkbox" name="id[]" value="42" />

HTML;

        $result = $this->table->column_cb($this->fakeInstitution());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_the_title_column(): void
    {
        $expected = <<<HTML
<p>
    <span style="display: block">Fake Institution</span>

    <a href="https://fakeinstitution.edu">https://fakeinstitution.edu</a>
</p>

HTML;

        $result = $this->table->column_title($this->fakeInstitution());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_book_admins_column(): void
    {
        $expected = <<<HTML
<div style="margin-bottom: .5rem">
    <p style="margin-bottom: .125rem">
        <strong>John Doe</strong>
        <a href="mailto:johndoe@example.com">johndoe@example.com</a>
    </p>

    <span>Fake Institution</span>
</div>

HTML;

        $result = $this->table->column_book_administrators($this->fakeInstitution());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_handles_empty_book_admins_list(): void
    {
        $result = $this->table->column_book_administrators($this->fakeInstitution([
            'admins' => []
        ]));

        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function it_handles_multiple_book_admins(): void
    {
        $expected = <<<HTML
<div style="margin-bottom: .5rem">
    <p style="margin-bottom: .125rem">
        <strong>John Doe</strong>
        <a href="mailto:johndoe@example.com">johndoe@example.com</a>
    </p>

    <span>Fake Institution</span>
</div>
<div style="margin-bottom: .5rem">
    <p style="margin-bottom: .125rem">
        <strong>Jane Doe</strong>
        <a href="mailto:janedoe@example.com">janedoe@example.com</a>
    </p>

    <span>Another Fake Institution</span>
</div>

HTML;

        $result = $this->table->column_book_administrators($this->fakeInstitution([
            'admins' => [
                (object) [
                    'fullname' => 'John Doe',
                    'institution' => 'Fake Institution',
                    'user_email' => 'johndoe@example.com',
                    'user_login' => 'johndoe',
                ],
                (object) [
                    'fullname' => 'Jane Doe',
                    'institution' => 'Another Fake Institution',
                    'user_email' => 'janedoe@example.com',
                    'user_login' => 'janedoe',
                ]
            ],
        ]));

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_changes_the_bulk_actions(): void
    {
        Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        Institution::query()->create([
            'name' => 'Another Fake Institution',
        ]);

        $this->assertEquals([
            0 => 'Unassigned',
            2 => 'Another Fake Institution',
            1 => 'Fake Institution',
        ], $this->table->get_bulk_actions());
    }

    /**
     * @test
     */
    public function it_displays_information_sorted_by_book_name_by_default(): void
    {
        $this->createBooksAndInstitutions();

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        tap($books->first(), function (object $book) {
            $this->assertEquals('Another Fake Book', $book->title);
            $this->assertNull($book->institution);
            $this->assertEquals('http://example.org/anotherfakepath', $book->url);
        });

        tap($books->last(), function (object $book) {
            $this->assertEquals('Fake Book', $book->title);
            $this->assertEquals('http://example.org/fakepath', $book->url);
            $this->assertEquals('Fake Institution', $book->institution);
        });
    }

    /**
     * @test
     */
    public function it_displays_information_with_search_query(): void
    {
        $this->createBooksAndInstitutions();

        $_REQUEST['s'] = 'another';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(1, $books);

        tap($books->first(), function (object $book) {
            $this->assertEquals('Another Fake Book', $book->title);
            $this->assertNull($book->institution);
            $this->assertEquals('http://example.org/anotherfakepath', $book->url);
        });

        $_REQUEST['s'] = 'institution';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(1, $books);

        tap($books->first(), function (object $book) {
            $this->assertEquals('Fake Book', $book->title);
            $this->assertEquals('Fake Institution', $book->institution);
            $this->assertEquals('http://example.org/fakepath', $book->url);
        });
    }

    /**
     * @test
     */
    public function it_displays_information_sorted_by_name_in_ascending_order(): void
    {
        $this->createBooksAndInstitutions();

        $_REQUEST['orderby'] = 'name';
        $_REQUEST['order'] = 'asc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Another Fake Book', $books->first()->title);
        $this->assertEquals('Fake Book', $books->last()->title);
    }

    /**
     * @test
     */
    public function it_displays_information_sorted_by_name_in_descending_order(): void
    {
        $this->createBooksAndInstitutions();

        $_REQUEST['orderby'] = 'name';
        $_REQUEST['order'] = 'desc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Fake Book', $books->first()->title);
        $this->assertEquals('Another Fake Book', $books->last()->title);

        $_REQUEST['orderby'] = 'institution';
        $_REQUEST['order'] = 'asc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Fake Book', $books->first()->title);
        $this->assertEquals('Another Fake Book', $books->last()->title);

        $_REQUEST['orderby'] = 'institution';
        $_REQUEST['order'] = 'desc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Another Fake Book', $books->first()->title);
        $this->assertEquals('Fake Book', $books->last()->title);
    }

    /**
     * @test
     */
    public function it_displays_information_sorted_by_institution_in_ascending_order(): void
    {
        $this->createBooksAndInstitutions();

        $_REQUEST['orderby'] = 'institution';
        $_REQUEST['order'] = 'asc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Fake Book', $books->first()->title);
        $this->assertEquals('Another Fake Book', $books->last()->title);
    }

    /**
     * @test
     */
    public function it_displays_information_sorted_by_institution_in_descending_order(): void
    {
        $this->createBooksAndInstitutions();

        $_REQUEST['orderby'] = 'institution';
        $_REQUEST['order'] = 'desc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Another Fake Book', $books->first()->title);
        $this->assertEquals('Fake Book', $books->last()->title);
    }

    protected function fakeInstitution(array $attributes = []): object
    {
        return (object) [
            'id' => $attributes['id'] ?? 42,
            'title' => $attributes['title'] ?? 'Fake Institution',
            'url' => $attributes['url'] ?? 'https://fakeinstitution.edu',
            'admins' => $attributes['admins'] ?? array_map(fn (array $admin) => (object) $admin, [
                [
                    'fullname' => 'John Doe',
                    'institution' => 'Fake Institution',
                    'user_email' => 'johndoe@example.com',
                    'user_login' => 'johndoe',
                ]
            ]),
        ];
    }

    protected function createBooksAndInstitutions(): void
    {
        $bookId = $this->newBook([
            'title' => 'Fake Book',
            'path' => 'fakepath',
        ]);

        $this->newBook([
            'title' => 'Another Fake Book',
            'path' => 'anotherfakepath',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        InstitutionBook::query()->create([
            'blog_id' => $bookId,
            'institution_id' => $institution->id,
        ]);
    }
}
