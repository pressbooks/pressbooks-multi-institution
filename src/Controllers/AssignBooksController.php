<?php

namespace PressbooksMultiInstitution\Controllers;

use PressbooksMultiInstitution\Models\InstitutionBook;
use PressbooksMultiInstitution\Models\Institution;
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
        $result = $this->processBulkActions($_REQUEST['id'] ?? []);

        $this->table->prepare_items();

        return $this->renderView('books.assign', [
            'list_url' => network_admin_url('admin.php?page=pb_multi_institution_assign_book'),
            'page' => 'pb_multi_institution_assign_book',
            'params' => [
                'searchQuery' => $_REQUEST['s'] ?? '',
                'orderBy' => $_REQUEST['orderby'] ?? 'title',
                'order' => $_REQUEST['order'] ?? 'asc',
            ],
            'result' => $result,
            'table' => $this->table,
        ]);
    }

    protected function processBulkActions(array $ids): array
    {
        $action = $this->table->current_action();

        if ($action === false) {
            return [];
        }

        if (! $ids) {
            return [];
        }

        if ($action === '0') {
            InstitutionBook::query()->whereIn('blog_id', $ids)->delete();

            return [
                'success' => true,
                'message' => __('Books have been unassigned.', 'pressbooks-multi-institution'),
            ];
        }

        $institution = Institution::query()->find($action);

        if (! $institution) {
            return [
                'success' => false,
                'message' => __('Institution not found.', 'pressbooks-multi-institution'),
            ];
        }

        foreach ($ids as $id) {
            InstitutionBook::query()->updateOrCreate(
                ['blog_id' => $id],
                ['institution_id' => $institution->id]
            );
        }

        return [
            'success' => true,
            'message' => __('Books have been assigned.', 'pressbooks-multi-institution'),
        ];
    }
}
