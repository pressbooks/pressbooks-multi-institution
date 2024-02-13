<?php

namespace Tests\Feature\Controllers;

use PressbooksMultiInstitution\Controllers\AssignBooksController;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionBook;
use Tests\TestCase;
use Tests\Traits\Assertions;
use Tests\Traits\CreatesModels;

class AssignBooksControllerTest extends TestCase
{
    use Assertions;
    use CreatesModels;

    /**
     * @test
     */
    public function it_renders_index_page_with_parameters(): void
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

        $result = (new AssignBooksController)->index();

        $this->assertStringContainsString('<span style="display: block">Another Fake Book</span>', $result);
        $this->assertStringContainsString('<span style="display: block">Fake Book</span>', $result);
        $this->assertStringContainsString('<p>Fake Institution</p>', $result);
        $this->assertStringContainsString('<p>Unassigned</p>', $result);

        $_REQUEST['s'] = 'another fake book';

        $result = app(AssignBooksController::class)->index();

        $this->assertStringContainsString('<span style="display: block">Another Fake Book</span>', $result);
        $this->assertStringNotContainsString('<span style="display: block">Fake Book</span>', $result);
        $this->assertStringContainsString('<p>Unassigned</p>', $result);
        $this->assertStringNotContainsString('<p>Fake Institution</p>', $result);
    }

    /**
     * @test
     */
    public function it_unassigns_a_single_book(): void
    {
        $firstBookId = $this->newBook([
            'title' => 'Fake Book',
            'path' => 'fakepath',
        ]);

        $secondBookId = $this->newBook([
            'title' => 'Another Fake Book',
            'path' => 'anotherfakepath',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        InstitutionBook::query()->create([
            'blog_id' => $firstBookId,
            'institution_id' => $institution->id,
        ]);

        InstitutionBook::query()->create([
            'blog_id' => $secondBookId,
            'institution_id' => $institution->id,
        ]);

        $this->assertDatabaseCount('institutions_blogs', 2);

        $this->assertTrue(
            InstitutionBook::query()
                ->where('blog_id', $firstBookId)
                ->where('institution_id', $institution->id)
                ->exists()
        );

        $_REQUEST['id'] = [$firstBookId];
        $_REQUEST['action'] = 0;
        $_REQUEST['_wpnonce'] = wp_create_nonce('bulk-assign-books');

        app(AssignBooksController::class)->index();

        $this->assertDatabaseCount('institutions_blogs', 1);

        $this->assertFalse(
            InstitutionBook::query()
                ->where('blog_id', $firstBookId)
                ->where('institution_id', $institution->id)
                ->exists()
        );
    }

    /**
     * @test
     */
    public function it_unassigns_multiple_books(): void
    {
        $firstBookId = $this->newBook([
            'title' => 'Fake Book',
            'path' => 'fakepath',
        ]);

        $secondBookId = $this->newBook([
            'title' => 'Another Fake Book',
            'path' => 'anotherfakepath',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        InstitutionBook::query()->create([
            'blog_id' => $firstBookId,
            'institution_id' => $institution->id,
        ]);

        InstitutionBook::query()->create([
            'blog_id' => $secondBookId,
            'institution_id' => $institution->id,
        ]);

        $this->assertDatabaseCount('institutions_blogs', 2);

        $this->assertEquals(
            2,
            InstitutionBook::query()
            ->whereIn('blog_id', [$firstBookId, $secondBookId])
            ->where('institution_id', $institution->id)
            ->count()
        );

        $_REQUEST['id'] = [$firstBookId, $secondBookId];
        $_REQUEST['action'] = 0;
        $_REQUEST['_wpnonce'] = wp_create_nonce('bulk-assign-books');

        app(AssignBooksController::class)->index();

        $this->assertDatabaseEmpty('institutions_blogs');

        $this->assertEquals(
            0,
            InstitutionBook::query()
                ->whereIn('blog_id', [$firstBookId, $secondBookId])
                ->where('institution_id', $institution->id)
                ->count()
        );
    }

    /**
     * @test
     */
    public function it_assigns_a_single_book(): void
    {
        $firstBookId = $this->newBook([
            'title' => 'Fake Book',
            'path' => 'fakepath',
        ]);

        $secondBookId = $this->newBook([
            'title' => 'Another Fake Book',
            'path' => 'anotherfakepath',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        $this->assertDatabaseEmpty('institutions_blogs');

        $_REQUEST['id'] = [$firstBookId];
        $_REQUEST['action'] = $institution->id;
        $_REQUEST['_wpnonce'] = wp_create_nonce('bulk-assign-books');

        app(AssignBooksController::class)->index();

        $this->assertDatabaseCount('institutions_blogs', 1);

        $this->assertTrue(
            InstitutionBook::query()
                ->where('blog_id', $firstBookId)
                ->where('institution_id', $institution->id)
                ->exists()
        );
    }

    /**
     * @test
     */
    public function it_assigns_multiple_books(): void
    {
        $firstBookId = $this->newBook([
            'title' => 'Fake Book',
            'path' => 'fakepath',
        ]);

        $secondBookId = $this->newBook([
            'title' => 'Another Fake Book',
            'path' => 'anotherfakepath',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        $this->assertDatabaseEmpty('institutions_blogs');

        $_REQUEST['id'] = [$firstBookId, $secondBookId];
        $_REQUEST['action'] = $institution->id;
        $_REQUEST['_wpnonce'] = wp_create_nonce('bulk-assign-books');

        app(AssignBooksController::class)->index();

        $this->assertDatabaseCount('institutions_blogs', 2);

        $this->assertEquals(
            2,
            InstitutionBook::query()
                ->whereIn('blog_id', [$firstBookId, $secondBookId])
                ->where('institution_id', $institution->id)
                ->count()
        );
    }
}
