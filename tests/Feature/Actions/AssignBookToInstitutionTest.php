<?php

namespace Tests\Feature\Actions;

use PressbooksMultiInstitution\Actions\AssignBookToInstitution;
use PressbooksMultiInstitution\Bootstrap;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\Institution;
use Tests\TestCase;
use Tests\Traits\Assertions;
use Tests\Traits\CreatesModels;
use WP_Hook;

class AssignBookToInstitutionTest extends TestCase
{
    use Assertions;
    use CreatesModels;

    /**
     * @test
     */
    public function it_registers_hook_action(): void
    {
        $this->assertHasCallbackAction('pb_new_blog', Bootstrap::class);
    }

    /**
     * @test
     */
    public function it_handles_callback_action_properly(): void
    {
        $userId = $this->newUser();

        // Set as the logged-in user
        wp_set_current_user($userId);

        // Creates a new book without triggering 'pb_new_blog' hook
        $bookId = $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        // Associate user with institution
        $institution->users()->create([
            'user_id' => $userId,
        ]);

        $this->assertDatabaseEmpty('institutions_blogs');

        // Trigger hook to validate the method is executed
        do_action('pb_new_blog');

        $this->assertDatabaseCount('institutions_blogs', 1);

        $this->assertTrue(
            InstitutionBook::query()
                ->where('blog_id', $bookId)
                ->where('institution_id', $institution->id)
                ->exists()
        );
    }

    /**
     * @test
     */
    public function it_assigns_books_with_institutions(): void
    {
        $userId = $this->newUser();

        // Set as the logged-in user
        wp_set_current_user($userId);

        // Creates a new book without triggering 'pb_new_blog' hook
        $bookId = $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        // Associate user with institution
        $institution->users()->create([
            'user_id' => $userId,
        ]);

        $this->assertDatabaseEmpty('institutions_blogs');

        $this->assertTrue(
            (new AssignBookToInstitution)->handle()
        );

        $this->assertDatabaseCount('institutions_blogs', 1);

        $this->assertTrue(
            InstitutionBook::query()
                ->where('blog_id', $bookId)
                ->where('institution_id', $institution->id)
                ->exists()
        );
    }

    /**
     * @test
     */
    public function it_does_not_assign_when_user_is_not_assigned_to_institution(): void
    {
        $userId = $this->newUser();

        // Set as the logged-in user
        wp_set_current_user($userId);

        // Creates a new book without triggering 'pb_new_blog' hook
        $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        /** @var Institution $institution */
        Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        $this->assertDatabaseEmpty('institutions_blogs');

        $this->assertFalse(
            (new AssignBookToInstitution)->handle()
        );

        $this->assertDatabaseEmpty('institutions_blogs');
    }

    /**
     * @test
     */
    public function it_does_not_assign_the_main_site(): void
    {
        $userId = $this->newUser();

        // Set as the logged-in user
        wp_set_current_user($userId);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        // Associate user with institution
        $institution->users()->create([
            'user_id' => $userId,
        ]);

        $this->assertDatabaseEmpty('institutions_blogs');

        $this->assertFalse(
            (new AssignBookToInstitution)->handle()
        );

        $this->assertDatabaseEmpty('institutions_blogs');
    }

    /**
     * @test
     */
    public function it_does_not_assign_when_there_are_no_institutions(): void
    {
        $userId = $this->newUser([
            'user_email' => 'johndoe@anotherfakedomain.edu',
        ]);

        // Set as the logged-in user
        wp_set_current_user($userId);

        // Creates a new book without triggering 'pb_new_blog' hook
        $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        $this->assertDatabaseEmpty('institutions_blogs');

        $this->assertFalse(
            (new AssignBookToInstitution)->handle()
        );

        $this->assertDatabaseEmpty('institutions_blogs');
    }

    /**
     * This method runs a callback without triggering a given filter.
     * It returns the expected value from the callback.
     *
     * TODO: this method could be useful elsewhere, might be worth to extract it.
     *
     * @param  string  $hook The hook name to be skipped
     * @param  callable  $callback The method that should run
     * @return mixed The value from the callback method
     */
    protected function runWithoutFilter(string $hook, callable $callback): mixed
    {
        global $wp_filter;

        $handler = $wp_filter[$hook] ?? null;

        if ($handler instanceof WP_Hook) {
            unset($wp_filter[$hook]);
        }

        $response = call_user_func($callback);

        if ($handler instanceof WP_Hook) {
            $wp_filter[$hook] = $handler;
        }

        return $response;
    }
}
