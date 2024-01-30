<?php

namespace PressbooksMultiInstitution\Controllers;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Views\InstitutionsTable;

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
        return $this->renderView('institutions.form', [
            'result' => null,
            'institution' => Institution::query()->make(),
            'back_url' => network_admin_url('/admin.php?page=pb_multi_institution'),
        ]);
    }
}
