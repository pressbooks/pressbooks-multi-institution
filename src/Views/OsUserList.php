<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\InstitutionUser;

class OsUserList
{
    public function setupHooks(): void
    {
        add_filter('wpmu_users_columns', [$this, 'addColumn']);
        add_filter('manage_users_custom_column', [$this, 'displayInstitutionValue'], 10, 3);

        add_filter('manage_users-network_sortable_columns', [$this, 'makeColumnSortable']);

        add_filter('users_list_table_query_args', [$this, 'addQueryArgsSorting']);
        add_action('pre_user_query', [$this, 'modifyUserQuery']);
    }

    public function displayInstitutionValue(string $value, string $columnName, int $userId): string
    {
        if ($columnName !== 'institution') {
            return $value;
        }

        $institution = InstitutionUser::query()
            ->where('user_id', $userId)
            ->first()
            ?->institution
            ->name;
        return $institution ?? __('Unassigned', 'pressbooks-multi-institution');
    }

    public function addColumn(array $columns): array
    {
        return array_slice($columns, 0, 4, true) +
            ['institution' => __('Institution', 'pressbooks-multi-institution')] +
            array_slice($columns, 4, null, true);
    }

    public function makeColumnSortable(array $columns): array
    {
        $columns['institution'] = 'institution';
        return $columns;
    }

    public function addQueryArgsSorting(array $args): array
    {
        return $args;
    }

    public function modifyUserQuery(\WP_User_Query $query): void
    {
        global $pagenow;
        if (!is_admin() || $pagenow !== 'users.php' || empty($_GET['orderby']) || $_GET['orderby'] !== 'institution') {
            return;
        }

        global $wpdb;
        $query->query_from .= " LEFT JOIN {$wpdb->base_prefix}institutions_users AS iu ON {$wpdb->users}.ID = iu.user_id";
        $query->query_from .= " LEFT JOIN {$wpdb->base_prefix}institutions AS i ON iu.institution_id = i.id";

        $order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';

        $query->query_orderby = "ORDER BY i.name " . $order;
    }
}
