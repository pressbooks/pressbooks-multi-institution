<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;
use WP_List_Table;

class InstitutionsUsersTable extends WP_List_Table
{
    protected int $paginationSize = 20;

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
            'username' => ['username', false],
            'name' => ['name', false],
            'email' => ['email', false],
            'institution' => ['institution', false],
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
        $users = $this->getUsers($_REQUEST);

        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, [], $sortable];

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
        $search = $request['s'] ?? '';
        $orderBy = $request['orderby'] ?? 'ID';
        $order = $request['order'] ?? 'ASC';

        $search = sanitize_text_field($search);

        $superAdmins = get_super_admins();

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
            ->whereNotIn('users.user_login', $superAdmins)
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
}
