<?php

namespace PressbooksMultiInstitution\Controllers;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Views\InstitutionsUsersTable;

class InstitutionsUsersController extends BaseController
{
    private InstitutionsUsersTable $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = new InstitutionsUsersTable;
    }

    public function assign(): string
    {
        $result = $this->processBulkActions();

        $filters = [
            's' => '',
            'orderby' => 'username',
            'order' => 'asc',
            'paged' => 1,
        ];

        $this->table->prepare_items();

        return $this->renderView('users.assign', [
            'page' => 'pb_multi_institutions_users',
            'list_url' => network_admin_url('admin.php?page=pb_multi_institutions_users'),
            'table' => $this->table,
            'result' => $result,
            'params' => collect($filters)
                ->flatMap(fn (string $filter, string $key) => [$key => $_REQUEST[$key] ?? $filter])
                ->filter()
                ->toArray(),
        ]);
    }

    protected function processBulkActions(): array
    {
        $action = $this->table->current_action();

        if ($action === false) {
            return [];
        }

        $items = $_REQUEST['ID'] ?? [];

        if (!$items) {
            return [];
        }

        $successMsg = _n('User updated.', 'Users updated.', count($items), 'pressbooks-multi-institution');

        if ($action === '0') {
            InstitutionUser::query()->whereIn('user_id', $items)->delete();
            return [
                'success' => true,
                'message' => $successMsg,
            ];
        }

        $institution = Institution::find($action);
        if (!$institution) {
            return [
                'success' => false,
                'message' => __('Institution not found.', 'pressbooks-multi-institution'),
            ];
        }

        foreach ($items as $user_id) {
            InstitutionUser::updateOrCreate(
                ['user_id' => $user_id],
                ['institution_id' => $institution->id]
            );
        }

        return [
            'success' => true,
            'message' => $successMsg,
        ];
    }
}
