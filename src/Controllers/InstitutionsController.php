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
            'add_new_url' => network_admin_url('admin.php?page=pb_multi_institution&action=new'),
            'table' => $this->table,
            'result' => $result,
        ]);
    }

    protected function processBulkActions(): array
    {
        return [];
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
