<?php

namespace PressbooksMultiInstitution\Controllers;

use PressbooksMultiInstitution\Views\AssignBooksTable;

class AssignBooksController extends BaseController
{
    private readonly AssignBooksTable $table;

    public function __construct()
    {
        parent::__construct();

        $this->table = app(AssignBooksTable::class);
    }

    public function index(): string
    {
        $this->table->prepare_items();

        return $this->renderView('assign-books.index', [
            'list_url' => network_admin_url('admin.php?page=pb_multi_institution_assign_book'),
            'page' => 'pb_multi_institution_assign_book',
            'table' => $this->table,
        ]);
    }
}
