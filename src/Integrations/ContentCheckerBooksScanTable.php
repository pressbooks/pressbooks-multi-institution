<?php

namespace PressbooksMultiInstitution\Integrations;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class ContentCheckerBooksScanTable
{
    public function register(): void
    {
        add_filter('pressbooks_content_checker_book_scan_table_columns', [$this, 'addInstitutionColumn'], 10, 1);
        add_filter('pressbooks_content_checker_book_scan_table_sortable_columns', [$this, 'addInstitutionSortableColumn'], 10, 1);
        add_filter('pressbooks_content_checker_book_scan_table_orderby_map', [$this, 'addInstitutionOrderBySpec'], 10, 1);
        add_filter('pressbooks_content_checker_book_scan_table_query', [$this, 'augmentBooksScanQuery'], 10, 1);
    }

    public function addInstitutionColumn(array $columns): array
    {
        $injected = ['institution' => __('Institution', 'pressbooks-multi-institution')];
        
        $prev_columns = array_slice($columns, 0, array_search('blog_title', array_keys($columns)) + 1, true);
        $next_columns = array_slice($columns, array_search('blog_title', array_keys($columns)) + 1, null, true);
        
        return array_merge($prev_columns, $injected, $next_columns);
    }

    public function addInstitutionSortableColumn(array $columns): array
    {
        $columns['institution'] = ['institution', false];
        return $columns;
    }

    public function addInstitutionOrderBySpec(array $map): array
    {
        global $wpdb;

        $map['institution'] = [
            'expression' => 'MAX(' . $wpdb->prefix . 'institutions.name)',
        ];

        return $map;
    }

    public function augmentBooksScanQuery($query)
    {
        global $wpdb;
        $institutionId = get_institution_by_manager();

        return $query
            ->leftJoin('institutions_blogs', 'institutions_blogs.blog_id', '=', 'pressbooks_content_checker_reports.blog_id')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->selectRaw('MAX(' . $wpdb->prefix . 'institutions.name) AS institution')
            ->when($institutionId !== 0, function ($q) use ($institutionId) {
                $q->where('institutions.id', '=', $institutionId);
            });
    }
}
