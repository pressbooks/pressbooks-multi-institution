<?php

namespace PressbooksMultiInstitution;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Kucrut\Vite;
use Pressbooks\Container;
use PressbooksMultiInstitution\Actions\AssignBookToInstitution;
use PressbooksMultiInstitution\Actions\AssignUserToInstitution;
use PressbooksMultiInstitution\Actions\InstitutionalManagerDashboard;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Services\InstitutionStatsService;
use PressbooksMultiInstitution\Services\MenuManager;
use PressbooksMultiInstitution\Services\PermissionsManager;
use PressbooksMultiInstitution\Views\BookList;
use PressbooksMultiInstitution\Views\UserList;

/**
 * Class Bootstrap
 * @package PressbooksMultiInstitution
 *
 */
final class Bootstrap
{
    private static ?Bootstrap $instance = null;

    public static function run(): void
    {
        if (!self::$instance) {
            self::$instance = new self;

            self::$instance->setUp();
        }
    }

    public function setUp(): void
    {
        $this->registerActions();
        $this->registerBlade();
        $this->enqueueScripts();
        $this->loadTranslations();

        Container::getInstance()->singleton(BookList::class, fn () => new BookList(app('db')));
        Container::getInstance()->singleton(UserList::class, fn () => new UserList(app('db')));

        $this->registerInstitutionHooks();
    }

    private function registerActions(): void
    {
        add_action('user_register', fn (int $id) => app(AssignUserToInstitution::class)->handle($id));
        add_action('pb_new_blog', fn () => app(AssignBookToInstitution::class)->handle());
        add_action('network_admin_menu', fn () => app(MenuManager::class)->handleMenus(), 1000);
        add_action('admin_menu', fn () => app(MenuManager::class)->handleMenus(), 1000);
        add_filter('custom_menu_order', '__return_true');
        add_action('menu_order', fn ($menu) => app(MenuManager::class)->handleItems($menu), 999);
        add_action('init', fn () => app(PermissionsManager::class)->setupFilters());
        add_action('pb_institutional_after_save', [PermissionsManager::class, 'syncRestrictedUsers'], 10, 2);
        add_action('pb_institutional_after_delete', [PermissionsManager::class, 'syncRestrictedUsers'], 10, 2);
        add_action(
            'pb_institutional_filters_created',
            fn ($institution, $institutionalManagers, $institutionalUsers) => app(PermissionsManager::class)->handlePagesPermissions($institution, $institutionalManagers, $institutionalUsers),
            10,
            3
        );
        add_action('init', [InstitutionalManagerDashboard::class, 'init']);
        add_action('init', fn () => app(InstitutionStatsService::class)->setupHooks());
    }

    private function registerBlade(): void
    {
        app('Blade')->addNamespace(
            'PressbooksMultiInstitution',
            dirname(__DIR__) . '/resources/views'
        );
    }

    private function registerInstitutionHooks(): void
    {
        add_filter(
            hook_name: 'pressbooks_get_institution_id',
            callback: fn (mixed $default, int $userId) => InstitutionUser::query()->where('user_id', $userId)->value('institution_id') ?? 0,
            accepted_args: 2
        );

        add_filter(
            hook_name: 'pressbooks_get_institution_by_id',
            callback: function (mixed $default, string|int $id) {
                $institution = Institution::query()->find($id);

                return $institution?->id ?? $default;
            },
            accepted_args: 2
        );

        add_filter(
            hook_name: 'pressbooks_get_institution_dropdown',
            callback: function (array $default) {
                return Institution::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->prepend(__('Unassigned', 'pressbooks-multi-institution'), 0)
                    ->toArray();
            },
        );

        add_filter(
            hook_name: 'pressbooks_search_by_institution',
            callback: function (Builder|EloquentBuilder $query, string $search, string $columnToCompare): Builder|EloquentBuilder {
                $query->orWhereExists(function (Builder|EloquentBuilder $query) use ($search, $columnToCompare) {
                    $query
                        ->selectRaw(1)
                        ->from('institutions')
                        ->whereColumn('id', '=', $columnToCompare)
                        ->where('name', 'like', "%{$search}%");
                });

                return $query;
            },
            accepted_args: 3
        );

        add_filter(
            hook_name: 'pressbooks_append_institution_to_query',
            callback: function (Builder|EloquentBuilder $query, string $columnToCompare, string $order = '', string $direction = ''): Builder|EloquentBuilder {
                $query
                    ->addSelect([
                        'institution' => Institution::query()
                            ->select('name')
                            ->whereColumn('id', '=', $columnToCompare)
                    ])
                    ->when($order === 'institution', function (Builder|EloquentBuilder $query) use ($direction) {
                        $query
                            ->orderByRaw($direction === 'asc' ? 'institution IS NOT NULL' : 'institution IS NULL')
                            ->orderBy('institution', $direction);
                    });

                return $query;
            },
            accepted_args: 4
        );
    }

    private function enqueueScripts(): void
    {
        add_action('admin_enqueue_scripts', function ($page) {
            $context = [
                'institutions_page_pb_multi_institutions' => [
                    'formSelector' => '#pressbooks-multi-institution-admin',
                    'confirmationMessage' => __('Are you sure you want to delete the selected institutions?', 'pressbooks-multi-institution'),
                ],
            ];

            Vite\enqueue_asset(
                WP_PLUGIN_DIR . '/pressbooks-multi-institution/dist',
                'resources/assets/js/pressbooks-multi-institution.js',
                ['handle' => 'pressbooks-multi-institution']
            );

            Vite\enqueue_asset(
                WP_PLUGIN_DIR . '/pressbooks-multi-institution/dist',
                'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
                ['handle' => 'pressbooks-multi-select'],
            );

            wp_localize_script('pressbooks-multi-institution', 'context', $context[$page] ?? []);
        });
    }

    /**
     * Load the plugin translations
     * @return void
     */
    private function loadTranslations(): void
    {
        add_action('init', function () {
            load_plugin_textdomain('pressbooks-multi-institution', false, 'pressbooks-multi-institution/languages/');
        });
    }
}
