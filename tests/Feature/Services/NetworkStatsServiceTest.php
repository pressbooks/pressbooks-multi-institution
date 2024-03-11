<?php

namespace Tests\Feature\Services;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Services\NetworkStatsService;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group network-stats-service
 */
class NetworkStatsServiceTest extends TestCase
{
    use CreatesModels;

    private Institution $institution;

    public function setUp(): void
    {
        parent::setUp();

        $this->institution = $this->createInstitution();
    }

    /** @test */
    public function it_adds_all_hooks_for_institutional_manager(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new NetworkStatsService;
        $service->setupHooks();

        $this->assertTrue(has_filter('pressbooks_network_analytics_stats_blogmeta_conditions'));
        $this->assertTrue(has_filter('pressbooks_network_analytics_usersovertime_query_conditions'));
        $this->assertTrue(has_filter('pb_network_analytics_stats_title'));
        $this->assertTrue(has_filter('pb_network_analytics_downloads_additional_columns'));
        $this->assertTrue(has_filter('pb_network_analytics_stats_download_filename'));
    }

    /** @test */
    public function it_does_not_add_download_conditions_and_title_if_not_manager(): void
    {
        wp_set_current_user($this->newSuperAdmin());

        $service = new NetworkStatsService;
        $service->setupHooks();

        $this->assertFalse(has_filter('pb_network_analytics_stats_download_conditions'));
        $this->assertFalse(has_filter('pb_network_analytics_stats_title'));
    }

    /** @test */
    public function it_gets_stats_title(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new NetworkStatsService;

        $this->assertEquals($this->institution->name . ' Stats', $service->getStatsTitle());
    }


    /** @test */
    public function it_adds_download_conditions(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new NetworkStatsService;

        $blogIds = $this->addBooksToInstitution(5);

        $this->assertEquals("blogmeta.blog_id IN ({$blogIds->join(', ')})", $service->addBlogMetaCondition('', 'blogmeta'));
    }

    /** @test */
    public function it_does_not_add_download_conditions_if_no_books(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new NetworkStatsService;

        $this->assertEmpty($service->addBlogMetaCondition('', 'blogmeta'));
    }

    /** @test */
    public function it_adds_download_sub_query(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $subQuery = (new NetworkStatsService)->getInstitutionColumn('', 'blogmeta');

        $this->assertStringContainsString('SELECT name', $subQuery);
        $this->assertStringContainsString('AS Institution', $subQuery);
    }

    /** @test */
    public function it_adds_users_overtime_query_condintions(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new NetworkStatsService;

        $userIds = $this->addUsersToInstitution(5, $this->institution)->prepend(get_current_user_id());

        $this->assertEquals("ID IN ({$userIds->join(', ')})", $service->addUsersOverTimeConditions());
    }

    public function tearDown(): void
    {
        parent::tearDown();

        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->users}");
        $wpdb->query("DELETE FROM {$wpdb->blogs}");
        delete_network_option(null, 'pressbooks_network_managers');
    }

}
