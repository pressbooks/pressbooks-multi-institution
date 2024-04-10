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
    public function it_renders_the_checkbox_column(): void
    {
        $expected = <<<HTML
<input type="checkbox" name="id[]" value="42" aria-label="Select Fake Book" />

HTML;

        $result = $this->table->column_cb($this->fakeBook());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_the_default_cover_column(): void
    {
        $expected = <<<HTML
<img src="http://example.org/wp-content/plugins/pressbooks/assets/dist/images/default-book-cover.jpg" alt="Fake Book&#039;s cover" style="height: 3rem" />

HTML;

        $result = $this->table->column_cover(
            $this->fakeBook(['cover' => ''])
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_the_book_cover_column(): void
    {
        $expected = <<<HTML
<img src="http://example.org/wp-content/pressbooks/assets/fakebook-cover.jpg" alt="Fake Book&#039;s cover" style="height: 3rem" />

HTML;

        $result = $this->table->column_cover($this->fakeBook());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_the_title_column(): void
    {
        $expected = <<<HTML
<span style="display: block">Fake Book</span>
<a href="https://fakeinstitution.edu/wp-admin">https://fakeinstitution.edu/wp-admin</a>

HTML;

        $result = $this->table->column_title($this->fakeBook());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_institution_column(): void
    {
        $expected = <<<HTML
<p>Fake Institution</p>

HTML;

        $result = $this->table->column_institution($this->fakeBook());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_renders_book_admins_column(): void
    {
        $expected = <<<HTML
<div style="margin-bottom: .5rem">
		<strong>John Doe</strong>
		<a href="mailto:johndoe@example.com">johndoe@example.com</a>
		<br />
		<span>Fake Institution</span>
	</div>

HTML;

        $result = $this->table->column_book_administrators($this->fakeBook());

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_handles_empty_book_admins_list(): void
    {
        $result = $this->table->column_book_administrators($this->fakeBook([
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
		<strong>John Doe</strong>
		<a href="mailto:johndoe@example.com">johndoe@example.com</a>
		<br />
		<span>Fake Institution</span>
	</div>
	<div style="margin-bottom: .5rem">
		<strong>Jane Doe</strong>
		<a href="mailto:janedoe@example.com">janedoe@example.com</a>
		<br />
		<span>Another Fake Institution</span>
	</div>

HTML;

        $result = $this->table->column_book_administrators($this->fakeBook([
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

        $this->assertEquals('Another Fake Book', $books->first()->title);
        $this->assertEquals('Fake Book', $books->last()->title);

        $_REQUEST['orderby'] = 'institution';
        $_REQUEST['order'] = 'desc';

        $this->table->prepare_items();

        $books = $this->table->items;

        $this->assertCount(2, $books);

        $this->assertEquals('Fake Book', $books->first()->title);
        $this->assertEquals('Another Fake Book', $books->last()->title);
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

        $this->assertEquals('Another Fake Book', $books->first()->title);
        $this->assertEquals('Fake Book', $books->last()->title);
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

        $this->assertEquals('Fake Book', $books->first()->title);
        $this->assertEquals('Another Fake Book', $books->last()->title);
    }

    protected function fakeBook(array $attributes = []): object
    {
        return (object) [
            'id' => $attributes['id'] ?? 42,
            'title' => $attributes['title'] ?? 'Fake Book',
            'institution' => $attributes['institution'] ?? 'Fake Institution',
            'url' => $attributes['url'] ?? 'https://fakeinstitution.edu',
            'cover' => $attributes['cover'] ?? 'http://example.org/wp-content/pressbooks/assets/fakebook-cover.jpg',
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
