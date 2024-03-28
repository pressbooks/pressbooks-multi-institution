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
     * @group pressbooks-multi-institution
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
