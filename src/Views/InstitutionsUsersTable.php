<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use WP_List_Table;
use WP_User_Query;

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
        return $item[$column_name];
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
            'name' => ['name', false],
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
        $users = $this->getUsers();

        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, [], $sortable];

        $this->items = array_map(function ($user) {
            return [
                'ID' => $user['ID'],
                'username' => $user['user_login'],
                'name' => $user['name'],
                'email' => $user['user_email'],
                'institution' => $user['institution'],
            ];
        }, $users['users']);

        $this->set_pagination_args([
            'total_items' => $users['total'],
            'per_page' => $this->paginationSize,
            'total_pages' => ceil($users['total'] / $this->paginationSize),
        ]);
    }

    private function getUsers(): array
    {
        $args = $this->getWPUserArgs($_REQUEST);

        add_action('pre_user_query', [$this, 'modifyUserQuery']);

        $wpUsers = new WP_User_Query($args);

        $user_ids = array_map(function ($user) {
            return $user->ID;
        }, $wpUsers->results);

        $institutionsUsers = InstitutionUser::query()
            ->join('institutions', 'institutions_users.institution_id', '=', 'institutions.id')
            ->whereIn('user_id', $user_ids)
            ->select('institutions_users.user_id', 'institutions.name')
            ->get();

        $users = [];

        foreach ($wpUsers->results as $wpUser) {
            $institutionsUser = $institutionsUsers->firstWhere('user_id', $wpUser->ID);

            $usermeta = get_user_meta($wpUser->ID);

            $users[] = [
                'ID' => $wpUser->ID,
                'user_login' => $wpUser->user_login,
                'name' => $usermeta['first_name'][0] . ' ' . $usermeta['last_name'][0],
                'user_email' => $wpUser->user_email,
                'institution' => $institutionsUser?->name ?? '',
            ];
        }

        return [
            'total' => $wpUsers->total_users,
            'users' => $users,
        ];
    }

    private function getWPUserArgs(array $request): array
    {
        $args = [
            'fields' => 'all',
            'number' => $this->paginationSize,
            'orderby' => 'ID',
            'count_total' => true,
            'offset' => ($this->get_pagenum() - 1) * $this->paginationSize,
            'meta_query' => [
                'relation' => 'AND',
                'query_first_name' => [
                    'key' => 'first_name',
                ],
                'query_last_name' => [
                    'key' => 'last_name',
                ],
            ],
        ];

        $searchTerm = $request['s'] ?? '';

        if ($searchTerm) {
            $searchTerm = sanitize_text_field($searchTerm);
            $args['search'] = '*' . $searchTerm . '*';
            $args['meta_query'] = [
                'relation' => 'OR',
                'query_first_name' => [
                    'key' => 'first_name',
                    'value' => $searchTerm,
                    'compare' => 'LIKE',
                ],
                'query_last_name' => [
                    'key' => 'last_name',
                    'value' => $searchTerm,
                    'compare' => 'LIKE',
                ],
            ];
        }

        $orderby = $request['orderby'] ?? '';

        if ($orderby && in_array($orderby, ['user_login', 'name', 'user_email', 'institutions.name'])) {
            $order = $request['order'] ?? 'ASC';

            if ($orderby === 'name') {
                $args['orderby'] = [
                    'query_first_name' => $order,
                    'query_last_name' => $order,
                ];
            } else {
                $args['orderby'] = [$orderby => $order];
                $args['order'] = $order;
            }
        }

        return $args;
    }

    public function modifyUserQuery(WP_User_Query $query): void
    {
        $search = $query->query_vars['search'] ?? '';
        if (! empty($search)) {
            $query->query_where = str_replace(
                ') AND (user_login LIKE',
                ') OR (user_login LIKE',
                $query->query_where
            );
        }

        $orderby = $query->query_vars['orderby'] ?? '';
        if ($orderby && is_array($orderby) && key($orderby) === 'institutions.name') {
            global $wpdb;

            $prefix = $wpdb->base_prefix;

            $order = $query->query_vars['order'] ?? 'ASC';

            $query->query_from .= " LEFT JOIN {$prefix}institutions_users AS iu ON {$prefix}users.ID = iu.user_id "
                . "LEFT JOIN {$prefix}institutions AS ins ON iu.institution_id = ins.id";

            if ($order === 'ASC') {
                $query->query_orderby = "ORDER BY CASE WHEN ins.name IS NULL THEN 1 ELSE ins.name END, user_login ASC";
            } else {
                $query->query_orderby = "ORDER BY CASE WHEN ins.name IS NULL THEN 1 ELSE ins.name END DESC, user_login DESC";
            }
        }
    }
}
