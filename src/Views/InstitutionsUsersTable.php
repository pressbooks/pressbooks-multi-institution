<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use WP_List_Table;

class InstitutionsUsersTable extends WP_List_Table
{
	protected int $paginationSize = 1000;

	public function __construct()
	{
		parent::__construct([
			'singular' => 'user',
			'plural' => 'users',
		]);
	}

	public function column_default($item, $column_name): string
	{
		$allowed_tags = [
			'p' => [],
			'a' => [
				'href' => [],
				'title' => []
			]
		];
		return wp_kses($item[$column_name], $allowed_tags);
	}

	public function column_name(array $item): string
	{
		return sprintf('<div class="row-title">%s</div>', $item['name']);
	}

	public function column_cb($item): string
	{
		return sprintf('<input type="checkbox" name="ID[]" value="%s" />', $item['ID']);
	}

	public function get_columns(): array
	{
		return [
			'cb' => '<input type="checkbox" />',
			'username' => __('Username', 'pressbooks-multi-institution'),
			'name' => __('Name', 'pressbooks-multi-institution'),
			'email' => __('Email', 'pressbooks-multi-institution'),
			'institution' => __('Institution', 'pressbooks-multi-institution'),
		];
	}

	public function get_sortable_columns(): array
	{
		return [
			'username' => ['user_login', false],
			'name' => ['display_name', false],
			'email' => ['user_email', false],
			'institution' => ['institutions.name', false],
		];
	}

	public function get_bulk_actions(): array
	{
		$institutions = Institution::query()->get()->toArray();
		return array_reduce($institutions, function ($carry, $institution) {
			$carry[$institution['id']] = $institution['name'];
			return $carry;
		}, []);
	}

	public function prepare_items(): void
	{
		$institutionsUsers = $this->getInstitutionsUsers();

		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [$columns, $hidden, $sortable];

		$total_items = $institutionsUsers->total();
		$this->items = $institutionsUsers->map(function ($institutionUser) {
			return [
				'ID' => $institutionUser->id,
				'username' => $institutionUser->user_login,
				'name' => $institutionUser->display_name,
				'email' => $institutionUser->user_email,
				'institution' => $institutionUser->name,
			];
		})->toArray();

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page' => $this->paginationSize,
			'total_pages' => $institutionsUsers->lastPage(),
		]);
	}

	private function getInstitutionsUsers(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
	{
		$searchTerm = $_REQUEST['s'] ?? '';

		$institutionsUsers = InstitutionUser::query()
			->join('users', 'institutions_users.user_id', '=', 'users.ID')
			->join('institutions', 'institutions_users.institution_id', '=', 'institutions.id')
			->where('users.user_login', 'like', "%{$searchTerm}%")
			->orWhere('users.user_email', 'like', "%{$searchTerm}%");

		$allowedOrderBy = ['user_login', 'display_name', 'user_email', 'institutions.name'];

		if (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], $allowedOrderBy)) {
			$institutionsUsers->orderBy($_REQUEST['orderby'], $_REQUEST['order'] ?? 'asc');
		}

		return $institutionsUsers->paginate($this->paginationSize, ['*'], 'paged', $this->get_pagenum());
	}
}
