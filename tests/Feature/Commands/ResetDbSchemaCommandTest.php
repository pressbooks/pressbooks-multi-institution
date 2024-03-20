<?php

namespace Tests\Feature\Commands;

use PressbooksMultiInstitution\Commands\ResetDbSchemaCommand;
use PressbooksMultiInstitution\Models\Institution;
use Tests\TestCase;
use Tests\Traits\CreatesModels;
use Tests\Traits\Assertions;

use function Pressbooks\Admin\NetworkManagers\_restricted_users;

/**
 * @group reset-db-schema-command
 */
class ResetDbSchemaCommandTest extends TestCase
{
    use Assertions;
    use CreatesModels;

    public function setUp(): void
    {
        parent::setUp();
        $this->createInstitutionsUsers(2, 10);
    }

    /**
     * @test
     */
    public function it_resets_db_schema(): void
    {
        $this->assertDatabaseCount('institutions', 2);
        $this->assertDatabaseCount('institutions_users', 10);

        $resetCommand = new ResetDbSchemaCommand;
        ob_start();
        $resetCommand->__invoke([], []);
        $output = ob_get_clean();

        $this->assertStringContainsString('Database schema reset successfully.', $output);

        $this->assertDatabaseCount('institutions', 0);
        $this->assertDatabaseCount('institutions_users', 0);
    }

    /**
     * @test
     */
    public function it_revokes_super_admin_privileges(): void
    {
        $institution = Institution::query()->first();

        $userManager = $this->assignAnInstitutionalManager($institution);

        $resetCommand = new ResetDbSchemaCommand;
        $resetCommand->__invoke([], []);

        $this->assertNotContains($userManager->user_id, _restricted_users());

        $this->assertFalse(is_super_admin($userManager->user_id));
    }
}
