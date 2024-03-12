<?php

namespace Tests\Feature\Support;

use Tests\TestCase;

use Tests\Traits\CreatesModels;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;
use function PressbooksMultiInstitution\Support\is_network_unlimited;

class HelpersTest extends TestCase
{
    use CreatesModels;

    /**
     * @test
     */
    public function it_determines_institutional_manager_institution(): void
    {
        $institution = $this->createInstitution();

        $id = $this->newInstitutionalManager($institution);

        wp_set_current_user($id);

        $this->assertEquals(
            $institution->id,
            get_institution_by_manager()
        );
    }

    /**
     * @test
     */
    public function it_returns_zero_network_managers(): void
    {
        $this->createInstitution();

        wp_set_current_user(
            $this->newNetworkManager()
        );

        $this->assertEquals(0, get_institution_by_manager());
    }

    /**
     * @test
     */
    public function it_returns_zero_for_super_admins(): void
    {
        $this->createInstitution();

        wp_set_current_user(
            $this->newSuperAdmin()
        );

        $this->assertEquals(0, get_institution_by_manager());
    }

    /**
     * @test
     */
    public function it_determines_if_network_is_unlimited(): void
    {
        // Not set
        $this->assertFalse(is_network_unlimited());

        // Limited to 100 books
        update_option('pb_plan_settings_book_limit', 100);

        $this->assertFalse(is_network_unlimited());

        // Unlimited
        update_option('pb_plan_settings_book_limit', 0);

        $this->assertTrue(is_network_unlimited());

        // Not set
        delete_option('pb_plan_settings_book_limit');

        $this->assertFalse(is_network_unlimited());
    }
}
