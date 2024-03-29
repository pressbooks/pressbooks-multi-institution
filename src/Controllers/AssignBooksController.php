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

        $filters = [
            'order' => 'asc',
            'orderby' => 'title',
            'paged' => 1,
            's' => '',
        ];

        $this->table->prepare_items();

        return $this->renderView('assign.index', [
            'title' => __('Assign Books', 'pressbooks-multi-institution'),
            'list_url' => network_admin_url('admin.php?page=pb_assign_books'),
            'page' => 'pb_assign_books',
            'all_count' => $this->table->getTotalBooksCount(),
            'unassigned_count' => $this->table->getUnassignedBooksCount(),
            'params' => collect($filters)
                ->flatMap(fn (string $filter, string $key) => [$key => $_REQUEST[$key] ?? $filter])
                ->toArray(),
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

        check_admin_referer('bulk-assign-books');

        if ($action == 0) {
            InstitutionBook::query()->whereIn('blog_id', $ids)->delete();

            return [
                'success' => true,
                'message' => _n('Book updated.', 'Books updated.', count($ids), 'pressbooks-multi-institution'),
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
            'message' => _n('Book updated.', 'Books updated.', count($ids), 'pressbooks-multi-institution'),
        ];
    }
}
