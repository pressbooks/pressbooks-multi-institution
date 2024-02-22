<?php

use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;
use Tests\TestCase;

class Admin_InstitutionalManagerDashboardTest extends TestCase
{
    use utilsTrait;

    /**
     * @test
     * @group institutional-manager-dashboard
     */
    public function it_checks_instance(): void
    {
        $this->assertInstanceOf(InstitutionalManagerDashboard::class, InstitutionalManagerDashboard::init());
    }

    /**
     * @test
     * @group institutional-manager-dashboard
     */
    public function it_checks_hooks(): void
    {
        global $wp_filter;

        InstitutionalManagerDashboard::init()->hooks();

        $this->assertArrayHasKey('load-index.php', $wp_filter);
        $this->assertArrayHasKey('admin_menu', $wp_filter);
    }

    /**
     * @test
     * @group institutional-manager-dashboard
     */
    public function it_renders_home_page(): void
    {
        ob_start();
        InstitutionalManagerDashboard::init()->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to', $output);
        $this->assertStringContainsString('Institutional Usage', $output);
        $this->assertStringContainsString('Administer Institution', $output);
        $this->assertStringContainsString('Support resources', $output);
    }

    public function setupUserAndInstitution()
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
    }
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_redirects_to_the_expected_page(): void {
    // 	$dashboard = $this->getMockBuilder( InstitutionalManagerDashboard::class )
    // 		->onlyMethods( [ 'doRedirect' ] )
    // 		->getMock();
    //
    // 	$dashboard
    // 		->expects( $this->once() )
    // 		->method( 'doRedirect' )
    // 		->willReturn( true );
    //
    // 	set_current_screen( 'dashboard-network' );
    //
    // 	$this->assertSame( network_admin_url( 'index.php?page=pb_institutional_manager' ), $dashboard->getUrl() );
    // 	$this->assertTrue( $dashboard->redirect() );
    // }

    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_does_not_redirect_when_not_the_right_screen(): void {
    // 	$dashboard = $this->getMockBuilder( InstitutionalManagerDashboard::class )
    // 		->onlyMethods( [ 'doRedirect' ] )
    // 		->getMock();
    //
    // 	$dashboard
    // 		->expects( $this->never() )
    // 		->method( 'doRedirect' )
    // 		->willReturn( true );
    //
    // 	set_current_screen( 'dashboard' );
    //
    // 	$this->assertFalse(
    // 		InstitutionalManagerDashboard::init()->redirect()
    // 	);
    //
    // 	set_current_screen( 'dashboard-user' );
    //
    // 	$this->assertFalse(
    // 		InstitutionalManagerDashboard::init()->redirect()
    // 	);
    // }

    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_returns_false_if_env_not_defined(): void {
    //
    // 	putenv( 'PB_CHECKLIST_NETWORK_CREATION_MONTHS_AGO=' );
    // 	$this->assertFalse( (new InstitutionalManagerDashboard())->shouldDisplayChecklist() );
    // }
    //
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_shows_network_checklist_only_if_network_is_recently_created(): void {
    //
    // 	putenv( 'PB_CHECKLIST_NETWORK_CREATION_MONTHS_AGO="-6 month"' );
    // 	// Network created 5 months ago
    // 	update_blog_details(1, ['registered' => date('Y-m-d H:i:s', strtotime('-5 month'))]);
    //
    // 	ob_start();
    // 	InstitutionalManagerDashboard::init()->render();
    // 	$output = ob_get_clean();
    //
    // 	$this->assertStringContainsString( 'Ready to Launch Checklist', $output );
    // }
    //
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_does_not_display_network_checklist_if_is_old_network(): void {
    //
    // 	putenv( 'PB_CHECKLIST_NETWORK_CREATION_MONTHS_AGO="-6 month"' );
    // 	// Network created 10 months ago
    // 	update_blog_details(1, ['registered' => date('Y-m-d H:i:s', strtotime('-10 month'))]);
    //
    // 	ob_start();
    // 	InstitutionalManagerDashboard::init()->render();
    // 	$output = ob_get_clean();
    //
    // 	$this->assertStringNotContainsString( 'Ready to Launch Checklist', $output );
    // }
    //
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_should_display_network_checklist_according_to_constraints(): void {
    // 	putenv( 'PB_CHECKLIST_NETWORK_CREATION_MONTHS_AGO="-6 month"' );
    // 	update_blog_details(1, ['registered' => date('Y-m-d H:i:s', strtotime('-4 month'))]);
    // 	$this->assertTrue( (new InstitutionalManagerDashboard())->shouldDisplayChecklist() );
    //
    // 	update_blog_details(1, ['registered' => date('Y-m-d H:i:s', strtotime('-10month'))]);
    // 	$this->assertFalse( (new InstitutionalManagerDashboard())->shouldDisplayChecklist() );
    // }
    //
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_returns_network_checklist_items_for_os_users(): void {
    // 	$networkDashboard = new InstitutionalManagerDashboard();
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	$this->assertCount( 4, $items );
    // 	$this->assertEquals( $items[2]['link'], network_admin_url( 'settings.php' ) );
    // }
    //
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_returns_network_checklist_items_with_sso(): void {
    // 	$networkDashboard = new InstitutionalManagerDashboard();
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	add_filter('pre_option_active_plugins', function ($value) {
    // 		if (false === $value) {
    // 			$value = [];
    // 		}
    // 		$value[] = 'pressbooks-saml-sso/pressbooks-saml-sso.php';
    // 		return $value;
    // 	});
    //
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	$this->assertCount( 5, $items );
    //
    // 	$ssoItem = $items[3];
    // 	$this->assertStringContainsString( 'pb_saml_admin', $ssoItem['link'] );
    //
    // 	add_filter('pre_option_active_plugins', function ($value) {
    // 		if (false === $value) {
    // 			$value = [];
    // 		}
    // 		unset($value[0]); // remove saml for testing because only one sso plugin can be active at a time
    // 		$value[] = 'pressbooks-cas-sso/pressbooks-cas-sso.php';
    // 		return $value;
    // 	});
    //
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	$this->assertCount( 5, $items );
    //
    // 	$ssoItem = $items[3];
    // 	$this->assertStringContainsString( 'pb_cas_admin', $ssoItem['link'] );
    // }
    //
    // /**
    //  * @test
    //  * @group institutional-manager-dashboard
    //  */
    // public function it_returns_network_checklist_items_with_extra_help_links(): void {
    //
    // 	putenv( 'PB_CHECKLIST_BOOKING_URL=https://calendly.com/fancypb-support' );
    //
    // 	$networkDashboard = new InstitutionalManagerDashboard();
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	add_filter('pre_option_active_plugins', function ($value) {
    // 		if (false === $value) {
    // 			$value = [];
    // 		}
    // 		$value[] = 'pressbooks-network-analytics/pressbooks-network-analytics.php';
    // 		return $value;
    // 	});
    //
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	$this->assertCount( 6, $items );
    // 	$settingsItem = $items[2];
    // 	$this->assertStringContainsString( 'settings.php?page=pb_network_analytics_options', $settingsItem['link'] );
    //
    // 	$bookingItem = $items[5];
    // 	$this->assertStringContainsString( 'https://calendly.com/fancypb-support', $bookingItem['link'] );
    //
    // 	putenv( 'PB_CHECKLIST_ONBOARDING_SURVEY=https://surveysparrow.com/test-survey' );
    // 	$items = $networkDashboard->getNetworkChecklist();
    // 	$this->assertCount( 7, $items );
    // 	$surveyItem = $items[6];
    // 	$this->assertStringContainsString( 'https://surveysparrow.com/test-survey', $surveyItem['link'] );
    // }
}
