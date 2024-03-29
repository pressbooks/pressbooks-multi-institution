<?php

namespace Tests\Feature\Services;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Services\InstitutionStatsService;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group institution-stats-service
 */
class InstitutionStatsServiceTest extends TestCase
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

        $service = new InstitutionStatsService;

        $service->setupHooks();

        $this->assertTrue(has_filter('pb_network_analytics_stats_title'));
        $this->assertTrue(has_filter('pb_network_analytics_stats_download_filename'));
        $this->assertTrue(has_filter('pb_network_analytics_stats_blogmeta_query'));
        $this->assertTrue(has_filter('pb_network_analytics_stats_user_query'));
        $this->assertTrue(has_filter('pb_network_analytics_stats_download_column'));
    }

    /** @test */
    public function it_gets_stats_title(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new InstitutionStatsService;
        $this->assertEquals($this->institution->name . ' Stats', $service->getStatsTitle('Network Stats'));
    }


    /** @test */
    public function it_return_query_params_for_blogmeta(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new InstitutionStatsService;
        $queryData = $service->addInstitutionToBlogmetaQuery([], 'blogmeta');

        global $wpdb;

        $this->assertEquals("i.name AS Institution", $queryData['column']);
        $this->assertEquals(" LEFT OUTER JOIN {$wpdb->base_prefix}institutions_blogs AS ib ON ib.blog_id = blogmeta.blog_id LEFT OUTER JOIN {$wpdb->base_prefix}institutions AS i ON i.id = ib.institution_id", $queryData['join']);
        $this->assertEquals("i.id = {$this->institution->id}", $queryData['conditions']);

    }

    /** @test */
    public function it_does_not_add_download_conditions_for_network_managers(): void
    {
        wp_set_current_user($this->newNetworkManager());

        $service = new InstitutionStatsService;
        $queryData = $service->addInstitutionToBlogmetaQuery([], 'blogmeta');

        $this->assertEmpty($queryData['conditions']);
    }

    /** @test */
    public function it_replaces_downloads_filename(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new InstitutionStatsService;
        $this->assertEquals($this->institution->name . ' file.', $service->replaceDownloadsFilename('Network file.'));
    }

    /** @test */
    public function it_returns_query_params_for_users_query(): void
    {
        wp_set_current_user($this->newInstitutionalManager($this->institution));

        $service = new InstitutionStatsService;
        $queryData = $service->addInstitutionToUserQuery([], 'users');

        global $wpdb;
        $this->assertEquals("i.name AS Institution", $queryData['column']);
        $this->assertEquals(" LEFT OUTER JOIN {$wpdb->base_prefix}institutions_users AS iu ON iu.user_id = users.ID LEFT OUTER JOIN {$wpdb->base_prefix}institutions AS i ON i.id = iu.institution_id", $queryData['join']);
    }

    /** @test */
    public function it_does_not_add_download_conditions_for_network_managers_in_user_query(): void
    {
        wp_set_current_user($this->newNetworkManager());

        $service = new InstitutionStatsService;
        $queryData = $service->addInstitutionToUserQuery([], 'users');

        $this->assertEmpty($queryData['conditions']);
    }

    /** @test */
    public function it_returns_column_alias(): void
    {
        $service = new InstitutionStatsService;
        $this->assertEquals('Institution', $service->getColumnAlias());
    }

}
