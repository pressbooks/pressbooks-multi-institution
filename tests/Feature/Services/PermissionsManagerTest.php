<?php

namespace Tests\Feature\Services;

use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;
use PressbooksMultiInstitution\Actions\TableViews;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Services\PermissionsManager;
use PressbooksMultiInstitution\Views\UserList;
use Tests\TestCase;
use Tests\Traits\CreatesModels;
use Tests\Traits\Utils;

/**
 * @group permissions-manager
 */
class PermissionsManagerTest extends TestCase
{
    use CreatesModels;
    use Utils;
    private string $redirect_url = '';

    public function setUp(): void
    {
        parent::setUp();
        // Override the redirect function to capture the URL and not actually redirect
        add_filter('wp_redirect', [$this, 'captureRedirect'], 10, 2);
    }

    public function captureRedirect($location, $status): bool
    {
        $this->redirect_url = $location;
        return false;
    }

    private function setSuperAdminUser(): int
    {
        $superAdminUserId = $this->newUser([
            'user_login' => 'superadmin',
            'user_email' => 'superadmin@test.com',
        ]);

        grant_super_admin($superAdminUserId);
        wp_set_current_user($superAdminUserId);

        return $superAdminUserId;
    }

    /**
     * @test
     */
    public function it_adds_institutions_filter_to_users_list_for_super_admins(): void
    {
        $this->setSuperAdminUser();
        $this->createInstitutionsUsers(2, 10);

        $institutions = Institution::query()->get();

        $_GET['page'] = 'pb_network_analytics_userlist';

        $tableViews = new TableViews;
        $tableViews->init();

        $data = $tableViews->addInstitutionsFilterTab([])[0];

        $this->assertArrayHasKey('tab', $data);
        $this->assertArrayHasKey('content', $data);

        // asssert that tab content template is rendered with regex
        $this->assertMatchesRegularExpression(
            '/<a href="#institutions-tab">/',
            $data['tab']
        );

        $this->assertMatchesRegularExpression(
            '/<div id="institutions-tab" class="table-controls">/',
            $data['content']
        );
        $this->assertMatchesRegularExpression(
            '/<input\\s+[^>]*?name="institution\\[\\]"\\s+[^>]*?type="checkbox"\\s+[^>]*?value="0"\\s*\\/?>\\s*Unassigned/',
            $data['content']
        );

        foreach ($institutions as $institution) {
            $this->assertMatchesRegularExpression(
                '/<input\\s+[^>]*?name="institution\\[\\]"\\s+[^>]*?type="checkbox"\\s+[^>]*?value="' . $institution->id . '"\\s*\\/?>\\s*' . $institution->name . '/',
                $data['content']
            );
        }


        $regularUserId = $this->newUser([
            'user_login' => 'regularuser',
            'user_email' => 'test@regular.com',
        ]);
        wp_set_current_user($regularUserId);

        $_GET['page'] = 'pb_network_analytics_booklist';

        $this->assertEmpty($tableViews->addInstitutionsFilterTab([]));
        InstitutionBook::query()->delete();
    }

    /**
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
        $permissionsManager->syncRestrictedUsers([
            $userId
        ], []);

        $permissionsManager->setupFilters();

        wp_set_current_user(1);
        $this->assertFalse(apply_filters('pb_institution', false));

        wp_set_current_user($userId);
        $permissionsManager->setupFilters();

        $this->assertNotFalse(apply_filters('pb_institution', false));
        $this->assertTrue(has_filter('pb_institutional_users'));
    }

    /**
     * Sets up a restricted institutional manager without persisting site options,
     * so no test state leaks into other tests.
     */
    private function setUpInstitutionalManager(): int
    {
        $institution = $this->createInstitution();
        $managerId = $this->newUser();

        $institution->users()->create([
            'user_id' => $managerId,
            'manager' => true,
        ]);

        $login = get_user_by('ID', $managerId)->user_login;

        add_filter('pre_site_option_site_admins', fn () => [$login]);
        add_filter('pre_site_option_pressbooks_network_managers', fn () => [$managerId]);

        wp_set_current_user($managerId);

        (new PermissionsManager)->setupFilters();

        return $managerId;
    }

    /**
     * @test
     */
    public function it_allows_admin_post_requests_on_the_main_site_for_institutional_managers(): void
    {
        $this->setUpInstitutionalManager();

        set_current_screen('wp-admin/admin-post.php');

        global $pagenow;
        $pagenow = 'admin-post.php';
        $_GET['action'] = 'pb_gdocs_callback';

        $deniedWith = false;

        ob_start();
        try {
            do_action('admin_init');
        } catch (\WPDieException $e) {
            $deniedWith = $e->getMessage();
        } finally {
            ob_end_clean();
        }

        $this->assertFalse(
            $deniedWith,
            "Institutional managers must be able to reach admin-post.php on the main site, but access was denied with: {$deniedWith}"
        );
    }

    /**
     * @test
     */
    public function it_restricts_admin_post_requests_for_books_outside_the_institution(): void
    {
        $this->setUpInstitutionalManager();

        $this->runWithoutFilter('pb_new_blog', fn () => $this->newBook());

        set_current_screen('wp-admin/admin-post.php');

        global $pagenow;
        $pagenow = 'admin-post.php';
        $_GET['action'] = 'pb_gdocs_callback';

        ob_start();
        try {
            do_action('admin_init');
            $this->fail('Access to admin-post.php on a book outside the institution should be denied.');
        } catch (\WPDieException $e) {
            $this->assertStringContainsString('not allowed to access this page', $e->getMessage());
        } finally {
            restore_current_blog();
            ob_end_clean();
        }
    }

    /**
     * @test
     */
    public function it_redirects_super_admins_if_tries_to_reach_institutional_manager_dashboard(): void
    {
        $institutionalManagerDashboard = new InstitutionalManagerDashboard;
        $institutionalManagerDashboard->hooks();

        $userId = $this->newUser();
        wp_set_current_user($userId);
        grant_super_admin($userId);

        set_current_screen('wp-admin/index.php?page=pb_institutional_manager');
        $_GET['page'] = 'pb_institutional_manager';

        ob_start();
        do_action('admin_init');
        ob_get_clean();

        $this->assertStringContainsString('index.php?page=pb_network_page', $this->redirect_url);
    }

    /**
     * @test
     */
    public function it_adds_institution_column_to_users_list_before_email_column(): void
    {
        $columns = app(UserList::class)->addColumns([
            [
                'title' => 'Bulk action',
                'field' => '_bulkAction',
                'formatter' => 'rowSelection',
                'titleFormatter' => 'rowSelection',
                'align' => 'center',
                'headerSort' => false,
                'cellClick' => true,
            ],
            [
                'title' => 'id',
                'field' => 'id',
                'visible' => false,
            ],
            [
                'title' => 'Username',
                'field' => 'username',
                'formatter' => 'html',
            ],
            [
                'title' => 'Name',
                'field' => 'name',
            ],
            [
                'title' => 'Email',
                'field' => 'email',
            ],
            [
                'title' => 'Registered',
                'field' => 'registered',
                'formatter' => 'datetime',
                'formatterParams' => [
                    'inputFormat' => 'YYYY-MM-DD hh:mm:ss',
                    'outputFormat' => 'YYYY-MM-DD',
                    'invalidPlaceholder' => 'N/A',
                ],
            ],
        ]);

        $this->assertEquals($columns[4], [
            'title' => 'Email',
            'field' => 'email',
        ]);

        $this->assertEquals($columns[5], [
            'title' => 'Institution',
            'field' => 'institution',
        ]);
    }
}
