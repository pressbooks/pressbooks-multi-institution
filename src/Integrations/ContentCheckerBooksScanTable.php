<?php

namespace PressbooksMultiInstitution\Integrations;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class ContentCheckerBooksScanTable
{
    public function register(): void
    {
        add_filter('pressbooks_content_checker_book_scan_table_columns', [$this, 'addInstitutionColumn'], 10, 2);
        add_filter('pressbooks_content_checker_book_scan_table_sortable_columns', [$this, 'addInstitutionSortableColumn'], 10, 2);
        add_filter('pressbooks_content_checker_book_scan_table_orderby_map', [$this, 'addInstitutionOrderbySpec'], 10, 2);
        add_filter('pressbooks_content_checker_book_scan_table_query', [$this, 'augmentBooksScanQuery'], 10, 3);
    }

    public function addInstitutionColumn(array $columns, object $table): array
    {
        $injected = ['institution' => __('Institution', 'pressbooks-multi-institution')];

        if (array_key_exists('blog_title', $columns)) {
            $result = [];
            foreach ($columns as $key => $label) {
                $result[$key] = $label;
                if ($key === 'blog_title') {
                    foreach ($injected as $k => $v) {
                        $result[$k] = $v;
                    }
                }
            }
            return $result;
        }

        return $columns + $injected;
    }

    public function addInstitutionSortableColumn(array $columns, object $table): array
    {
        $columns['institution'] = ['institution', false];
        return $columns;
    }

    public function addInstitutionOrderbySpec(array $map, object $table): array
    {
        global $wpdb;

        $map['institution'] = [
            'expression' => 'MAX(' . $wpdb->prefix . 'institutions.name)',
        ];

        return $map;
    }

    public function augmentBooksScanQuery($query, array $request, object $table)
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
