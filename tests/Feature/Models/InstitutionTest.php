<?php

namespace Tests\Feature\Models;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group institution-model
 */
class InstitutionTest extends TestCase
{
    use CreatesModels;

    /**
     * @test
     */
    public function users_counts_every_pivot_row_regardless_of_account_status(): void
    {
        $institution = $this->createInstitution();

        $activeUserId = $this->newUser(['user_login' => 'active-user', 'user_email' => 'active@fake.test']);
        $deletedUserId = $this->newUser(['user_login' => 'deleted-user', 'user_email' => 'deleted@fake.test']);
        $spamUserId = $this->newUser(['user_login' => 'spam-user', 'user_email' => 'spam@fake.test']);

        $this->markUserAs($deletedUserId, ['deleted' => 1]);
        $this->markUserAs($spamUserId, ['spam' => 1]);

        InstitutionUser::query()->create(['user_id' => $activeUserId, 'institution_id' => $institution->id]);
        InstitutionUser::query()->create(['user_id' => $deletedUserId, 'institution_id' => $institution->id]);
        InstitutionUser::query()->create(['user_id' => $spamUserId, 'institution_id' => $institution->id]);

        $this->assertEquals(3, $institution->users()->count());
    }

    /**
     * @test
     */
    public function active_users_excludes_deleted_and_spam_accounts(): void
    {
        $institution = $this->createInstitution();

        $activeUserId = $this->newUser(['user_login' => 'active-user', 'user_email' => 'active@fake.test']);
        $deletedUserId = $this->newUser(['user_login' => 'deleted-user', 'user_email' => 'deleted@fake.test']);
        $spamUserId = $this->newUser(['user_login' => 'spam-user', 'user_email' => 'spam@fake.test']);

        $this->markUserAs($deletedUserId, ['deleted' => 1]);
        $this->markUserAs($spamUserId, ['spam' => 1]);

        InstitutionUser::query()->create(['user_id' => $activeUserId, 'institution_id' => $institution->id]);
        InstitutionUser::query()->create(['user_id' => $deletedUserId, 'institution_id' => $institution->id]);
        InstitutionUser::query()->create(['user_id' => $spamUserId, 'institution_id' => $institution->id]);

        $this->assertEquals(1, $institution->activeUsers()->count());

        $withCount = Institution::query()->withCount('activeUsers')->find($institution->id);
        $this->assertEquals(1, $withCount->active_users_count);
    }
}
