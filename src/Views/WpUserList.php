<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;

use WP_User_Query;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class WpUserList
{
    public function init(): void
    {
        add_filter('wpmu_users_columns', [$this, 'manageTableColumns']);
        add_filter('manage_users_custom_column', [$this, 'displayCustomColumns'], 10, 3);

        add_filter('manage_users-network_sortable_columns', [$this, 'makeInstitutionColumnSortable']);

        add_action('pre_user_query', [$this, 'modifyUserQuery']);

        add_filter('views_users-network', [$this, 'removeSuperAdminFilter']);
    }

    public function displayCustomColumns(string $value, string $columnName, int $userId): string
    {
        return match ($columnName) {
            'institution' => InstitutionUser::query()
                ->where('user_id', $userId)
                ->first()
                ?->institution
                ->name ?? __('Unassigned', 'pressbooks-multi-institution'),
            'books' => $this->getBooksColumnValue($userId),
            default => $value,
        };
    }

    private function getBooksColumnValue(int $userId): string
    {
        $blogs = get_blogs_of_user($userId);

        unset($blogs[get_main_site_id()]);

        return app('Blade')->render('PressbooksMultiInstitution::table.wp-users.books-column', [
            'books' => $blogs,
        ]);
    }

    public function manageTableColumns(array $columns): array
    {
        unset($columns['blogs']);

        return array_slice($columns, 0, 4, true) +
            ['institution' => __('Institution', 'pressbooks-multi-institution')] +
            array_slice($columns, 4, null, true) +
            ['books' => __('Books', 'pressbooks-multi-institution')];
    }

    public function makeInstitutionColumnSortable(array $columns): array
    {
        $columns['institution'] = 'institution';
        return $columns;
    }

    public function modifyUserQuery(WP_User_Query $query): void
    {
        global $pagenow;
        if (! is_super_admin() || ! is_main_site() || $pagenow !== 'users.php') {
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

    public function removeSuperAdminFilter(array $views): array
    {
        $institution = get_institution_by_manager();

        if($institution === 0) {
            return $views;
        }

        unset($views['super']);

        $totalUsers = Institution::find($institution)->users()->count();
        $views['all'] = "<a href='#' class='current' aria-current='page'> " .
            __('All', 'pressbooks-multi-institution') . " <span class='count'>({$totalUsers})</span></a>";

        return $views;
    }
}
