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

    public function index(): string
    {
        $result = $this->processBulkActions();

        $this->table->prepare_items();

        return $this->renderView('assignusers.index', [
            'page' => 'pb_multi_institutions_users',
            'list_url' => network_admin_url('admin.php?page=pb_multi_institutions_users'),
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

        if (!$action) {
            return [];
        }

        $items = $_REQUEST['ID'] ?? [];

        if (!$items) {
            return [];
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
            'message' => __('User/s assigned.', 'pressbooks-multi-institution'),
        ];
    }
}
