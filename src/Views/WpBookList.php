<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;

use WP_Site_Query;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class WpBookList
{
    public function init(): void
    {
        add_filter('wpmu_blogs_columns', [$this, 'addInstitutionColumn']);
        add_action('manage_sites_custom_column', [$this, 'renderInstitutionColumn'], 10, 2);
        add_filter('manage_sites-network_sortable_columns', [$this, 'addInstitutionAsSortableColumn']);

        // Modify WP_Site_Query clauses
        add_filter('sites_clauses', function (array $clauses): array {
            if (get_current_screen()?->id !== 'sites-network') {
                return $clauses;
            }

            if (! is_main_site()) {
                return $clauses;
            }

            if (! is_super_admin()) {
                return $clauses;
            }

            // TODO: find a better way other than clearing the cache
            wp_cache_flush_group('site-queries');

            global $wpdb;

            $clauses['join'] .= " LEFT JOIN {$wpdb->base_prefix}institutions_blogs ON {$wpdb->blogs}.blog_id = {$wpdb->base_prefix}institutions_blogs.blog_id";
            $clauses['join'] .= " LEFT JOIN {$wpdb->base_prefix}institutions ON {$wpdb->base_prefix}institutions_blogs.institution_id = {$wpdb->base_prefix}institutions.id";

            $clauses['fields'] .= ", {$wpdb->base_prefix}institutions.name AS institution";

            $institutionId = get_institution_by_manager();

            if ($institutionId > 0) {
                $clauses['where'] .= $wpdb->prepare(" AND {$wpdb->base_prefix}institutions.id = %d", $institutionId);
            }

            $orderBy = $_REQUEST['orderby'] ?? null;
            $order = $_REQUEST['order'] ?? 'asc';

            if ($orderBy === 'institution') {
                $clauses['orderby'] = $order === 'asc'
                    ? "{$wpdb->base_prefix}institutions.name ASC"
                    : "{$wpdb->base_prefix}institutions.name DESC";
            }

            return $clauses;
        });

        //        add_filter('sites_pre_query', function (array|null $site_data, WP_Site_Query $query): array|null {
        //            dump($site_data);
        //
        //            dump($query);
        //
        //            return $site_data;
        //        }, 10, 2);
    }

    public function addInstitutionColumn(array $columns): array
    {
        return [
            ... array_splice($columns, 0, 4),
            'institution' => __('Institution', 'pressbooks-multi-institution'),
            ...$columns,
        ];
    }

    public function renderInstitutionColumn(string $columnId, int $blogId): void
    {
        if ($columnId !== 'institution') {
            return;
        }

        if (is_main_site($blogId)) {
            return;
        }

        /** @var Institution|null $institution */
        $institution = Institution::query()
            ->whereHas('books', fn ($query) => $query->where('blog_id', $blogId))
            ->first();

        echo $institution->name ?? __('Unassigned', 'pressbooks-multi-institution');
    }

    public function addInstitutionAsSortableColumn(array $columns): array
    {
        return [
            ...$columns,
            'institution' => 'institution',
        ];
    }
}
