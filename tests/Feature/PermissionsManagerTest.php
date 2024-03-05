<?php

namespace Tests\Feature;

use PressbooksMultiInstitution\Actions\PermissionsManager;
use PressbooksMultiInstitution\Models\Institution;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

class PermissionsManagerTest extends TestCase
{
    use CreatesModels;
    /**
     * @group pressbooks-multi-institution
     * @test
     */
    public function it_test_institutional_managers_hooks(): void
    {

        $userId = $this->newUser();

        $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        $this->assertFalse(has_filter('pb_institution'));

        /** @var Institution $institution */
        $institution = Institution::query()->create([
            'name' => 'Fake Institution',
        ]);

        // Associate user with institution
        $institution->users()->create([
            'user_id' => $userId,
            'manager' => true,
        ]);

        $permissionsManager = new PermissionsManager;
        $permissionsManager->afterSaveInstitution([
            $userId
        ], []);

        $permissionsManager->setupInstitutionalFilters();

        wp_set_current_user(1);
        $this->assertFalse(apply_filters('pb_institution', false));

        wp_set_current_user($userId);
        $permissionsManager->setupInstitutionalFilters();

        $this->assertNotFalse(apply_filters('pb_institution', false));
        $this->assertTrue(has_filter('pb_institutional_users'));
    }
}
