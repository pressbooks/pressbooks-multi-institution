<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\InstitutionUser;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class OsUserList
{
    public function setupHooks(): void
    {
        add_filter('wpmu_users_columns', [$this, 'addColumn']);
        add_filter('manage_users_custom_column', [$this, 'displayInstitutionValue'], 10, 3);

        add_filter('manage_users-network_sortable_columns', [$this, 'makeColumnSortable']);

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

    public function modifyUserQuery(\WP_User_Query $query): void
    {
        global $pagenow;
        if (!is_admin() || $pagenow !== 'users.php') {
            return;
        }

        global $wpdb;
        $query->query_from .= " LEFT JOIN {$wpdb->base_prefix}institutions_users AS iu ON {$wpdb->users}.ID = iu.user_id";
        $query->query_from .= " LEFT JOIN {$wpdb->base_prefix}institutions AS i ON iu.institution_id = i.id";

        $institution = get_institution_by_manager();
        if ($institution !== 0) {
            $query->query_where .= $wpdb->prepare(" AND iu.institution_id = %d", $institution);
        }

        $order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';

        $query->query_orderby = "ORDER BY i.name " . $order;
    }
}
