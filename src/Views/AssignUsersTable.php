<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Traits\OverridesBulkActions;
use WP_List_Table;

class AssignUsersTable extends WP_List_Table
{
    use OverridesBulkActions;

    protected int $paginationSize = 50;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'user',
            'plural' => 'users',
        ]);
    }

    public function column_default($item, $column_name): string
    {
        return $item[$column_name] ?? '';
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
            'username' => ['username', false],
            'name' => ['name', false],
            'email' => ['email', false],
            'institution' => ['institution', false],
        ];
    }

    public function column_username(array $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.user', [
            'editUrl' => admin_url('user-edit.php?user_id=' . $item['ID']),
            'username' => $item['username'],
        ]);
    }

    public function prepare_items(): void
    {
        $users = $this->getUsers($_REQUEST);

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->items = array_map(function ($user) {
            return [
                'ID' => $user->ID,
                'username' => $user->username,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'institution' => $user->institution ?? __('Unassigned', 'pressbooks-multi-institution'),
            ];
        }, $users->items());

        $this->set_pagination_args([
            'total_items' => $users->total(),
            'per_page' => $this->paginationSize,
            'total_pages' => $users->lastPage(),
        ]);
    }

    private function getUsers(array $request): object
    {
        $request = $this->validateRequest($request);

        $search = $request['s'] ?? '';
        $orderBy = $request['orderby'] ?? 'ID';
        $order = $request['order'] ?? 'ASC';
        $unassigned = $request['unassigned'] ?? '';

        $superAdmins = get_super_admins();

        $search = sanitize_text_field($search);

        return $this->getBaseQuery()
            ->when($unassigned, function ($query) {
                return $query->whereNull('institutions_users.user_id');
            })
            ->when($search, function ($query, $search) use ($superAdmins) {
                return $query
                    ->whereNotIn('users.user_login', $superAdmins)
                    ->where(function ($query) use ($search, $superAdmins) {
                        $query->where('users.user_login', 'like', "%$search%")
                            ->orWhere('users.user_email', 'like', "%$search%")
                            ->orWhere('institutions.name', 'like', "%$search%");
                    })
                    ->orWhereExists(function ($query) use ($search, $superAdmins) {
                        $query->select('meta_value')
                            ->from('usermeta')
                            ->whereColumn('usermeta.user_id', 'users.ID')
                            ->whereNotIn('users.user_login', $superAdmins)
                            ->where(function ($query) {
                                $query->where('meta_key', 'first_name')
                                ->orWhere('meta_key', 'last_name');
                            })
                            ->where('meta_value', 'like', "%$search%");
                    });
            })
            ->when($orderBy === 'name', function ($query) use ($order) {
                return $query->orderBy('first_name', $order)
                    ->orderBy('last_name', $order);
            }, function ($query) use ($orderBy, $order) {
                return $query->orderBy($orderBy, $order);
            })
            ->paginate($this->paginationSize, ['*'], 'page', $request['paged'] ?? 1);
    }

    public function getBaseQuery(): object
    {
        $db = app('db');

        return $db
            ->table('users')
            ->select('users.ID', 'users.user_login AS username', 'users.user_email AS email', 'institutions.name as institution')
            ->addSelect([
                'first_name' => $db
                    ->table('usermeta')
                    ->select('meta_value')
                    ->where('meta_key', 'first_name')
                    ->whereColumn('usermeta.user_id', 'users.ID'),
                'last_name' => $db
                    ->table('usermeta')
                    ->select('meta_value')
                    ->where('meta_key', 'last_name')
                    ->whereColumn('usermeta.user_id', 'users.ID'),
            ])
            ->leftJoin('institutions_users', 'users.ID', '=', 'institutions_users.user_id')
            ->leftJoin('institutions', 'institutions_users.institution_id', '=', 'institutions.id')
            ->whereNotIn('users.user_login', get_super_admins());
    }

    public function getTotalUsers(): int
    {
        return $this->getBaseQuery()->count();
    }

    public function getUnassignedUsersCount(): int
    {
        return $this->getBaseQuery()->whereNull('institutions_users.user_id')->count();
    }

    private function validateRequest(array $request): array
    {
        $request['orderby'] = sanitize_text_field($request['orderby'] ?? '');
        $request['order'] = sanitize_text_field($request['order'] ?? '');
        $request['s'] = sanitize_text_field($request['s'] ?? '');
        $request['paged'] = sanitize_text_field($request['paged'] ?? '');

        if (isset($request['ID'])) {
            $request['ID'] = array_map('intval', $request['ID']);
        }

        if (!in_array($request['orderby'], ['username', 'name', 'email', 'institution'])) {
            $request['orderby'] = 'username';
        }

        if (!in_array(strtolower($request['order']), ['asc', 'desc'])) {
            $request['order'] = 'asc';
        }

        return $request;
    }
}
