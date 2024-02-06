<?php

namespace Tests\Feature\Actions;

use PressbooksMultiInstitution\Bootstrap;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use Tests\TestCase;
use Tests\Traits\Assertions;
use Tests\Traits\CreatesModels;

class AssignUserToInstitutionTest extends TestCase
{
    use Assertions;
    use CreatesModels;

    /**
     * @test
     */
    public function it_registers_hook_action(): void
    {
        $this->assertTrue(
            $this->assertHasCallbackAction('user_register', Bootstrap::class)
        );
    }

    /**
     * @test
     */
    public function it_handles_callback_action_properly(): void
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

        // Trigger hook to validate the method is executed
        do_action('user_register', $id);

        $this->assertDatabaseCount('institutions_users', 1);

        $this->assertTrue(
            InstitutionUser::query()
                ->where('user_id', $id)
                ->where('institution_id', $institution->id)
                ->exists()
        );
    }

    /**
     * @test
     */
    public function it_assigns_users_with_institutions(): void
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

        $this->assertTrue(
            InstitutionUser::query()
                ->where('user_id', $id)
                ->where('institution_id', $institution->id)
                ->exists()
        );
    }

    /**
     * @test
     */
    public function it_does_not_assign_when_email_domain_does_not_match(): void
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
    public function it_does_not_assign_invalid_users(): void
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
    public function it_does_not_assign_when_there_are_no_institutions(): void
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
}
