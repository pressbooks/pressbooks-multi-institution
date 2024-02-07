<?php

namespace PressbooksMultiInstitution\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PressbooksMultiInstitution\Models\EmailDomain;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Views\InstitutionsTable;
use PressbooksMultiInstitution\Support\ConvertEmptyStringsToNull;

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
        $canUpdateLimits = is_super_admin() && ! is_restricted();

        $result = $this->save($canUpdateLimits);

        return $this->renderView('institutions.form', [
            'back_url' => network_admin_url('admin.php?page=pb_multi_institutions'),
            'canUpdateLimits' =>  $canUpdateLimits,
            'institution' => $this->fetchInstitution(),
            'old' => $result['success'] ? [] : $_POST,
            'result' => $result,
            'users' => get_users([
                'blog_id' => 0,
                'orderby' => [
                    'display_name',
                    'email',
                    'name'
                ],
                'login__not_in' => get_super_admins()
            ]),
        ]);
    }

    protected function processBulkActions(): array
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

        $base = Institution::query()->whereIn('id', $items);

        match($action) {
            'delete' => $base->delete(),
            default => null,
        };

        return [
            'success' => true,
            'message' => __('Action completed.', 'pressbooks-multi-institution'),
        ];


    }

    protected function save(bool $canUpdateLimits): array
    {
        if (! $_POST) {
            return [
                'success' => true
            ];
        }

        check_admin_referer('pb_multi_institution_form');

        $data = Arr::only($this->sanitize($_POST), [
            'name', 'domains', 'managers', 'book_limit', 'user_limit'
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
        $managers = array_filter($data['managers'] ?? []);
        $data = Arr::except($data, [
            'domains',
            'managers',
            ...$canUpdateLimits ? [] : [
                'book_limit',
                'user_limit',
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

        $institution
            ->updateDomains(
                array_map(fn (string $domain) => ['domain' => $domain], $domains)
            )
            ->syncManagers(
                array_map(fn (string $id) => (int) $id, $managers),
            );


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
            'name',
            'domains',
            'managers',
            'book_limit',
            'user_limit',
        ];

        foreach ($keys as $key) {
            $data[$key] ??= '';
        }

        return (new ConvertEmptyStringsToNull)->handle($data);
    }

    protected function validate(array $data, ?int $id): array
    {
        $errors = [];

        if (is_null($data['name'])) {
            $errors['name'][] = __('The name field is required.', 'pressbooks-multi-institution');
        }

        if (! is_numeric($data['book_limit']) && ! is_null($data['book_limit'])) {
            $errors['book_limit'][] = __('The book limit field should be numeric.', 'pressbooks-multi-institution');
        }

        if (! is_numeric($data['user_limit']) && ! is_null($data['user_limit'])) {
            $errors['user_limit'][] = __('The user limit field should be numeric.', 'pressbooks-multi-institution');
        }

        if ($domainErrors = $this->checkForDuplicateDomains($data['domains'] ?? [], $id)) {
            $errors['domains'] = $domainErrors;
        }

        if ($managerErrors = $this->checkForDuplicateManagers($data['managers'] ?? [], $id)) {
            $errors['managers'] = $managerErrors;
        }

        return $errors;
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
            ->when($id, fn (Builder $query) => $query->where('institution_id', '<>', $id))
            ->get();

        return $duplicates->map(function (EmailDomain $duplicate) {
            $message = __(
                'Email domain %s is already in use with %s. Please use a different address.',
                'pressbooks-multi-institution',
            );

            return sprintf($message, "<strong>{$duplicate->domain}</strong>", "<strong>{$duplicate->institution->name}</strong>");
        })->toArray();
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
            ->when($id, fn (Builder $query) => $query->where('institutions.id', '<>', $id))
            ->get();

        return $duplicates->map(function (object $duplicate) {
            $message = __(
                "%s is already assigned as an institutional manager for %s. They cannot be assigned to manage two institutions at the same time.",
                'pressbooks-multi-institution'
            );

            return sprintf($message, "<strong>{$duplicate->user}</strong>", "<strong>{$duplicate->institution}</strong>");
        })->toArray();
    }
}
