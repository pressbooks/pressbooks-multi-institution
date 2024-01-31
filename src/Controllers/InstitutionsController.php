<?php

namespace PressbooksMultiInstitution\Controllers;

use Illuminate\Support\Collection;
use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Views\InstitutionsTable;
use PressbooksMultiInstitution\Support\ConvertEmptyStringsToNull;

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
        $action = $_GET['action'] ?? 'list';

        return match($action) {
            'new', 'edit' => $this->form(),
            default => $this->list(),
        };
    }

    protected function list(): string
    {
        $result = $this->processBulkActions();

        $this->table->prepare_items();

        return $this->renderView('institutions.index', [
            'page' => 'pb_multi_institution',
            'list_url' => network_admin_url('admin.php?page=pb_multi_institution'),
            'add_new_url' => network_admin_url('admin.php?page=pb_multi_institution&action=new'),
            'table' => $this->table,
            'result' => $result,
            'params' => [
                'searchQuery' => $_REQUEST['s'] ?? '',
                'orderBy' => $_REQUEST['orderby'] ?? 'ID',
                'order' => $_REQUEST['order'] ?? 'ASC',
            ]
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

    protected function form(): string
    {
        $result = $this->save();

        return $this->renderView('institutions.form', [
            'result' => $result,
            'institution' => $this->fetchInstitution(),
            'users' => get_users([
                'blog_id' => 0,
                'orderby' => [
                    'display_name',
                    'email',
                    'name'
                ]
            ]),
            'back_url' => network_admin_url('/admin.php?page=pb_multi_institution'),
        ]);
    }

    protected function save(): array
    {
        if (! $_POST) {
            return [];
        }

        check_admin_referer('pb_multi_institution');

        $data = collect($this->sanitize($_POST))->only(
            ['name', 'domains', 'managers', 'book_limit', 'user_limit']
        );

        $errors = $this->validate($data);

        if ($errors) {
            return [
                'success' => false,
                'message' => __('The form is invalid.', 'pressbooks-multi-institution'),
                'errors' => $errors,
            ];
        }

        $id = $_POST['ID'] ?? null;

        if ($id) {
            $institution = Institution::query()->find($id);

            $institution->update(
                $data->except('domains', 'managers')->all()
            );
        } else {
            /** @var Institution $institution */
            $institution = Institution::query()->create(
                $data->except('domains', 'managers')->all(),
            );
        }

        $domains = array_filter($data['domains'] ?? []);
        $managers = array_filter($data['managers'] ?? []);

        $institution
            ->updateDomains(
                array_map(fn (string $domain) => ['domain' => $domain], $domains)
            )
            ->updateManagers($managers);


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

    protected function validate(Collection $data): array
    {
        $errors = [];

        if (is_null($data['name'])) {
            $errors['name'] = __('The name field is required.', 'pressbooks-multi-institution');
        }

        if (! is_array($data['domains']) && ! is_null($data['domains'])) {
            $errors['domains'] = __('The domains field should be an array.', 'pressbooks-multi-institution');
        }

        if (! is_array($data['managers']) && ! is_null($data['managers'])) {
            $errors['managers'] = __('The managers field should be an array.', 'pressbooks-multi-institution');
        }

        if (! is_numeric($data['book_limit']) && ! is_null($data['book_limit'])) {
            $errors['book_limit'] = __('The book limit field should be numeric.', 'pressbooks-multi-institution');
        }

        if (! is_numeric($data['user_limit']) && ! is_null($data['user_limit'])) {
            $errors['user_limit'] = __('The user limit field should be numeric.', 'pressbooks-multi-institution');
        }

        return $errors;
    }

    protected function fetchInstitution(): Institution
    {
        /** @var Institution|null $institution */
        $institution = Institution::query()->with('domains')->find($_GET['ID'] ?? null);

        if (! $institution) {
            /** @var Institution $institution */
            $institution = Institution::query()->make();

            return $institution
                ->setRelation('domains', [])
                ->setRelation('managers', []);
        }

        global $wpdb;

        // TODO: update this when we have eloquent on user model
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->base_prefix}institutions_users WHERE institution_id = %d",
                $institution->id
            )
        );

        return $institution->setRelation('managers', array_map(fn (string $id) => (int) $id, $ids));
    }
}
