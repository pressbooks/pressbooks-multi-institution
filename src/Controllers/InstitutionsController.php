<?php

namespace PressbooksMultiInstitution\Controllers;

use PressbooksMultiInstitution\Models\Institution;

class InstitutionsController extends BaseController
{
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
        return $this->renderView('institutions.index', [
            'add_new_url' => network_admin_url('admin.php?page=pb_multi_institution&action=new'),
        ]);
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
