<?php

namespace Tests\Feature\Actions;

use Illuminate\Database\Capsule\Manager;
use PressbooksMultiInstitution\Bootstrap;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use ReflectionFunction;
use Tests\TestCase;

class AssignUserToInstitutionTest extends TestCase
{
    /**
     * @test
     */
    public function it_register_hook_action(): void
    {
        $this->assertTrue(
            has_action('user_register')
        );

        $this->assertHasCallbackAction('user_register', Bootstrap::class);
    }

    /**
     * @test
     */
    public function it_associates_users_with_institutions(): void
    {
        $id = $this->newUser([
            'user_email' => 'johndoe@fakedomain.edu',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        $institution->domains()->create([
            'domain' => 'fakedomain.edu',
        ]);

        $this->assertDatabaseEmpty('institutions_users');

        $this->assertTrue(
            (new AssignUserToInstitution)->handle($id)
        );

        $this->assertDatabaseCount('institutions_users', 1);

        $this->assertEquals(
            1,
            InstitutionUser::query()
            ->where('user_id', $id)
            ->where('institution_id', $institution->id)
            ->count()
        );
    }

    /**
     * @test
     */
    public function it_does_not_associate_when_email_domain_does_not_match(): void
    {
        $id = $this->newUser([
            'user_email' => 'johndoe@anotherfakedomain.edu',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        $institution->domains()->create([
            'domain' => 'fakedomain.edu',
        ]);

        $this->assertDatabaseEmpty('institutions_users');

        $this->assertFalse(
            (new AssignUserToInstitution)->handle($id)
        );

        $this->assertDatabaseEmpty('institutions_users');
    }

    /**
     * @test
     */
    public function it_does_not_associate_invalid_users(): void
    {
        $this->newUser([
            'user_email' => 'johndoe@fakedomain.edu',
        ]);

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        $institution->domains()->create([
            'domain' => 'fakedomain.edu',
        ]);

        $this->assertDatabaseEmpty('institutions_users');

        $this->assertFalse(
            (new AssignUserToInstitution)->handle(9999)
        );

        $this->assertDatabaseEmpty('institutions_users');
    }

    /**
     * @test
     */
    public function it_does_not_associate_when_there_are_no_institutions(): void
    {
        $id = $this->newUser([
            'user_email' => 'johndoe@fakedomain.edu',
        ]);

        $this->assertDatabaseEmpty('institutions_users');

        $this->assertFalse(
            (new AssignUserToInstitution)->handle($id)
        );

        $this->assertDatabaseEmpty('institutions_users');
    }

    protected function assertDatabaseCount(string $table, int $count): void
    {
        // TODO: we could extract this methods to a assertions trait

        /** @var Manager $db */
        $db = app('db');

        $this->assertEquals($count, $db->table($table)->count());
    }

    protected function assertDatabaseEmpty(string $table): void
    {
        // TODO: we could extract this methods to a assertions trait

        $this->assertDatabaseCount($table, 0);
    }

    protected function assertHasCallbackAction(string $hook, string $class): bool
    {
        // TODO: should we improve this method and have it in a trait for other methods to use?
        global $wp_filter;

        foreach ($wp_filter[$hook] ?? [] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function'])) {
                    continue;
                }

                $closure = new ReflectionFunction($callback['function']);

                if($closure->getClosureScopeClass()?->getName() === $class) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function newUser(array $properties = []): int
    {
        // TODO: we will probably make use of this in other places as well

        global $wpdb;

        $wpdb->query('START TRANSACTION');

        $wpdb->delete($wpdb->users, [
            'user_login' => $properties['user_login'] ?? 'johndoe',
        ]);

        $user = $this->factory()->user->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'user_login' => 'johndoe',
            'user_email' => 'johndoe@fakedomain.edu',
            ...$properties
        ]);

        $wpdb->query('COMMIT');

        return $user;
    }
}
