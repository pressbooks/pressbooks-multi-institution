<?php

namespace PressbooksMultiInstitution\Controllers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PressbooksMultiInstitution\Models\EmailDomain;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Views\InstitutionsTable;
use PressbooksMultiInstitution\Views\InstitutionsTotals;
use PressbooksMultiInstitution\Support\ConvertEmptyStringsToNull;
use stdClass;
use WP_User;

use function Pressbooks\Admin\NetworkManagers\is_restricted;

class InstitutionsController extends BaseController
{
    private InstitutionsTable $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = new InstitutionsTable;
    }

    public function index(): string
    {
        $result = $this->processBulkActions();

        $this->table->prepare_items();

        return $this->renderView('institutions.index', [
            'page' => 'pb_multi_institutions',
            'list_url' => network_admin_url('admin.php?page=pb_multi_institutions'),
            'add_new_url' => network_admin_url('admin.php?page=pb_multi_institution_form&action=new'),
            'table' => $this->table,
            'totals' => (new InstitutionsTotals(app('db')))->getTotals(),
            'result' => $result,
            'params' => [
                'searchQuery' => $_REQUEST['s'] ?? '',
                'orderBy' => $_REQUEST['orderby'] ?? 'ID',
                'order' => $_REQUEST['order'] ?? 'ASC',
            ]
        ]);
    }

    public function form(): string
    {
        $isSuperAdmin = is_super_admin() && ! is_restricted();

        $result = $this->save($isSuperAdmin);

        $institution = $this->fetchInstitution();

        $users = $this->fetchUsers($institution);

        return $this->renderView('institutions.form', [
            'back_url' => network_admin_url('admin.php?page=pb_multi_institutions'),
            'isSuperAdmin' =>  $isSuperAdmin,
            'institution' => $institution,
            'old' => $result['success'] ? [] : $_POST,
            'result' => $result,
            'users' => $users,
        ]);
    }

    public function processBulkActions(): array
    {
        $action = $this->table->current_action();

        $actions = ['delete'];

        if (!in_array($action, $actions)) {
            return [];
        }

        check_admin_referer('bulk-institutions');

        $items = $_REQUEST['ID'] ?? [];

        if (!$items) {
            return [];
        }

        $institutionalManagerIds = InstitutionUser::query()
            ->whereIn('institution_id', $items)
            ->managers()->pluck('user_id')->toArray();

        match($action) {
            'delete' => Institution::query()->whereIn('id', $items)->delete(),
            default => null,
        };

        apply_filters('pb_institutional_after_delete', [], $institutionalManagerIds);

        return [
            'success' => true,
            'message' => __('Action completed.', 'pressbooks-multi-institution'),
        ];
    }

    public function save(bool $isSuperAdmin): array
    {
        if (! $_POST) {
            return [
                'success' => true
            ];
        }

        check_admin_referer('pb_multi_institution_form');

        $data = Arr::only($this->sanitize($_POST), [
            'name', 'domains', 'managers', 'allow_institutional_managers', 'book_limit', 'buy_in'
        ]);

        $id = $_POST['ID'] ?? null;

        $errors = $this->validate($data, $id);

        if ($errors) {
            return [
                'success' => false,
                'message' => __('The form is invalid.', 'pressbooks-multi-institution'),
                'errors' => $errors,
            ];
        }

        $domains = array_filter($data['domains'] ?? []);
        $managers = array_slice(array_filter($data['managers'] ?? []), 0, 3);
        $data = Arr::except($data, [
            'domains',
            'managers',
            ...$isSuperAdmin ? [] : [
                'allow_institutional_managers',
                'book_limit',
                'buy_in',
            ],
        ]);

        if ($id) {
            /** @var Institution $institution */
            $institution = Institution::query()->find($id);

            $institution->update($data);
        } else {
            /** @var Institution $institution */
            $institution = Institution::query()->create($data);
        }

        $institution->updateDomains(
            array_map(fn (string $domain) => ['domain' => Str::of($domain)->lower()], $domains)
        );

        if ($institution->allowsInstitutionalManagers() || $isSuperAdmin) {
            $managers = array_map(fn (string $id) => (int) $id, $managers);

            InstitutionUser::query()
                ->notManagers()
                ->whereIn('user_id', $managers)
                ->where('institution_id', '<>', $institution->id)
                ->delete();

            $institution->syncManagers($managers);
        }

        return [
            'success' => true,
            'message' => $institution->wasRecentlyCreated
                ? __('Institution has been added.', 'pressbooks-multi-institution')
                : __('Institution has been updated.', 'pressbooks-multi-institution'),
        ];
    }

    protected function sanitize(array $data): array
    {
        $keys = [
            'name' => '',
            'domains' => [],
            'managers' => [],
            'allow_institutional_managers' => false,
            'book_limit' => null,
            'buy_in' => false,
        ];

        foreach ($keys as $key => $default) {
            $data[$key] ??= $default;
        }

        return (new ConvertEmptyStringsToNull)->handle($data);
    }

    protected function validate(array $data, ?int $id): array
    {
        $errors = [];

        if (is_null($data['name'])) {
            $errors['name'][] = __('The name field is required.', 'pressbooks-multi-institution');
        }

        if ($this->nameExists($data['name'], $id)) {
            $errors['name'][] = $this->renderView('partials.errors.duplicate-name', [
                'name' => $data['name']
            ]);
        }

        if (! is_numeric($data['book_limit']) && ! is_null($data['book_limit'])) {
            $errors['book_limit'][] = __('The book limit field should be numeric.', 'pressbooks-multi-institution');
        }

        if ($domainErrors = $this->checkForInvalidDomains($data['domains'] ?? [])) {
            $errors['domains'] = $domainErrors;
        }

        if ($domainErrors = $this->checkForDuplicateDomains($data['domains'] ?? [], $id)) {
            $errors['domains'] = [
                ...$errors['domains'] ?? [],
                ...$domainErrors
            ];
        }

        if ($managerErrors = $this->checkForDuplicateManagers($data['managers'] ?? [], $id)) {
            $errors['managers'] = $managerErrors;
        }

        return $errors;
    }

    protected function nameExists(?string $name, ?int $id): bool
    {
        return Institution::query()
            ->where('name', $name)
            ->when($id, fn (EloquentBuilder $query) => $query->where('id', '<>', $id))
            ->exists();
    }

    protected function fetchInstitution(): Institution
    {
        /** @var Institution|null $institution */
        $institution = Institution::query()->with('domains', 'managers')->find($_GET['ID'] ?? null);

        if (! $institution) {
            /** @var Institution $institution */
            $institution = Institution::query()->make();

            return $institution
                ->setRelation('domains', collect())
                ->setRelation('managers', collect());
        }

        return $institution;
    }

    protected function fetchUsers(?Institution $institution): array
    {
        $usersToSkip = app('db')
            ->table('users')
            ->whereIn('ID', function (Builder $query) use ($institution) {
                $query
                    ->select('ID')
                    ->from('users')
                    ->whereIn('user_login', get_super_admins())
                    ->when(
                        $institution,
                        fn (Builder $query) => $query->whereNotIn('ID', $institution->managers->pluck('user_id'))
                    );
            })
            ->pluck('ID')
            ->toArray();

        $users = get_users([
            'blog_id' => 0, // all users from the network
            'fields' => ['ID', 'display_name', 'user_email'],
            'orderby' => [
                'display_name',
                'email',
                'name',
            ],
            'exclude' => $usersToSkip
        ]);

        return array_map(fn (stdClass $value) => new WP_User($value), $users);
    }

    protected function checkForInvalidDomains(array $domains): array
    {
        $domains = array_filter($domains);

        if (! $domains) {
            return [];
        }

        $domains = array_map(function (string $domain) {
            $pattern = '/^(?:(?:[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]|[a-zA-Z0-9])+\.)+[a-zA-Z]{2,}$/';

            if (preg_match($pattern, $domain) === 1) {
                return false;
            }

            return $domain;
        }, $domains);

        $invalidDomains = array_filter($domains);

        if (! $invalidDomains) {
            return [];
        }

        return [
            $this->renderView('partials.errors.invalid-domains', [
                'domains' => $invalidDomains
            ])
        ];
    }

    protected function checkForDuplicateDomains(array $domains, ?int $id): array
    {
        $domains = array_filter($domains);

        if (! $domains) {
            return [];
        }

        /** @var Collection<EmailDomain> $duplicates */
        $duplicates = EmailDomain::query()
            ->with('institution:id,name')
            ->whereIn('domain', $domains)
            ->when($id, fn (EloquentBuilder $query) => $query->where('institution_id', '<>', $id))
            ->get();

        return $duplicates->map(fn (EmailDomain $duplicate) => $this->renderView('partials.errors.duplicate-domain', [
            'domain' => "<strong class='red'>{$duplicate->domain}</strong>",
            'institution' => "<strong class='red'>{$duplicate->institution->name}</strong>",
        ]))->toArray();
    }

    protected function checkForDuplicateManagers(array $managers, ?int $id): array
    {
        $managers = array_filter($managers);

        if (! $managers) {
            return [];
        }

        /** @var Collection $duplicates */
        $duplicates = Institution::query()
            ->select('institutions.name as institution')
            ->addSelect([
                'user' => app('db')
                    ->table('users')
                    ->select('user_login')
                    ->whereColumn('user_id', 'users.ID')
            ])
            ->join('institutions_users', 'institutions.id', '=', 'institutions_users.institution_id')
            ->whereIn('institutions_users.user_id', $managers)
            ->where('institutions_users.manager', true)
            ->when($id, fn (EloquentBuilder $query) => $query->where('institutions.id', '<>', $id))
            ->get();

        return $duplicates->map(fn (object $duplicate) => $this->renderView('partials.errors.duplicate-manager', [
            'user' => "<strong class='red'>{$duplicate->user}</strong>",
            'institution' => "<strong class='red'>{$duplicate->institution}</strong>",
        ]))->toArray();
    }
}
