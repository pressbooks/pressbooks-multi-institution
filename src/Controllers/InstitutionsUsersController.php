<?php

namespace PressbooksMultiInstitution\Controllers;

use Illuminate\Support\Arr;
use PressbooksMultiInstitution\Models\Institution;
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

		$actions = Institution::query()->get()->pluck('name')->toArray();

		if (!in_array($action, $actions)) {
			return [];
		}

		check_admin_referer('bulk-institutions');

		$items = $_REQUEST['ID'] ?? [];

		if (!$items) {
			return [];
		}

		$base = InstitutionsUsersTable::query()->whereIn('id', $items);

		$institution = Institution::query()->where('name', $action)->first();

		$base->institution_id = $institution->id;
		$base->save();

		return [
			'success' => true,
			'message' => __('Action completed.', 'pressbooks-multi-institution'),
		];
	}
}
