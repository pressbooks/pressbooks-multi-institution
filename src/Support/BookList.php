<?php

namespace PressbooksMultiInstitution\Support;

use Illuminate\Database\Capsule\Manager;
use Pressbooks\DataCollector\Book;
use PressbooksMultiInstitution\Models\Institution;

use function Pressbooks\Utility\format_bytes;

class BookList
{
    public function __construct(protected readonly Manager $db)
    {
    }

    public function init(): void
    {
        add_filter('pb_network_analytics_book_list_custom_texts', [$this, 'getCustomTexts']);
        add_filter('pb_network_analytics_book_list_columns', [$this, 'addColumns']);
        add_filter('pb_network_analytics_book_list_select_clause', [$this, 'appendAdditionalColumnsToQuery']);
        add_filter('pb_network_analytics_book_list_where_clause', [$this, 'appendAdditionalWhereClausesToQuery']);
        //        add_filter('pb_network_analytics_filter_tabs', [$this, 'addFilterTabs']);
        add_filter('pb_network_analytics_book_list_filter', [$this, 'addFilters']);
    }

    public function addFilterTabs(array $filters): array
    {
        if (! is_super_admin()) {
            return $filters;
        }

        if (get_institution_by_manager() > 0) {
            return $filters;
        }

        return [
            ...$filters,
            [
                'tab' => app('Blade')->render('PressbooksMultiInstitution::partials.filters.institutions.tab'),
                'content' => app('Blade')->render('PressbooksMultiInstitution::partials.filters.institutions.content', [
                    'institutions' => Institution::query()->orderBy('name')->get(),
                ])
            ]
        ];
    }

    public function addFilters(array $filters): array
    {
        if (! is_super_admin()) {
            return $filters;
        }

        if (get_institution_by_manager() > 0) {
            return $filters;
        }

        return [
            ...$filters,
            [
                'field' => 'institution',
                'name' => 'institution[]',
                'counterId' => 'institutions-tab-counter',
            ]
        ];
    }

    public function addColumns(array $columns): array
    {
        // TODO: I don't like this approach
        array_splice($columns, 7, 0, [
            [
                'title' => __('Institution', 'pressbooks-multi-institution'),
                'field' => 'institution',
            ]
        ]);

        return $columns;
    }

    public function getCustomTexts(array $texts): array
    {
        if (! is_super_admin()) {
            return $texts;
        }

        $institutionId = get_institution_by_manager();

        if ($institutionId === 0) {
            return $texts;
        }

        /** @var Institution $institution */
        $institution = Institution::query()
            ->where('id', $institutionId)
            ->with('books:blog_id,institution_id')
            ->withCount('books')
            ->first();

        $storage = $this->db
            ->table('blogmeta')
            ->where('meta_key', Book::STORAGE_SIZE)
            ->whereIn('blog_id', $institution->books->pluck('blog_id'))
            ->sum('meta_value');

        return [
            ...$texts,
            'title' => sprintf(
                str_ends_with($institution->name, 's')
                    ? __('%s\' Book List', 'pressbooks-multi-institution')
                    : __('%s\'s Book List', 'pressbooks-multi-institution'),
                $institution->name
            ),
            'count' => sprintf(
                __('There are %d books owned by %s. They use %s of storage.', 'pressbooks-multi-institution'),
                $institution->books_count,
                $institution->name,
                format_bytes($storage)
            ),
        ];
    }

    public function appendAdditionalColumnsToQuery(): string
    {
        $prefix = $this->db
            ->getDatabaseManager()
            ->getTablePrefix();

        $idSubQuery = $this->db
            ->table('institutions')
            ->select('institutions.id')
            ->join('institutions_blogs', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->whereRaw("{$prefix}institutions_blogs.blog_id = b.blog_id");

        $nameSubQuery = $this->db
            ->table('institutions')
            ->select('institutions.name')
            ->join('institutions_blogs', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->whereRaw("{$prefix}institutions_blogs.blog_id = b.blog_id");

        return "({$idSubQuery->toSql()}) as institution_id, ({$nameSubQuery->toSql()}) as institution";
    }

    public function appendAdditionalWhereClausesToQuery(string $where): string
    {
        global $wpdb;

        $institutionId = get_institution_by_manager();

        if (is_super_admin() && $institutionId > 0) {
            return "{$where} AND (institution_id = $institutionId)";
        }

        $institutionIds = array_map(fn (string $value) => (int) $value, $_GET['institution'] ?? []);

        if (! $institutionIds) {
            return $where;
        }

        $ids = array_filter($institutionIds, fn (int $value) => $value > 0);

        $unassigned = count($institutionIds) > count($ids);

        $whereIn = null;
        $whereNull = null;

        if ($ids) {
            $placeholder = implode(', ', array_fill(0, count($ids), '%d'));

            $whereIn = $wpdb->prepare("institution_id IN ({$placeholder})", $ids);
        }

        if ($unassigned) {
            $whereNull = 'institution_id IS NULL';
        }

        if ($whereIn && $whereNull) {
            return "{$where} AND ({$whereIn} OR {$whereNull})";
        }

        if ($whereIn) {
            return "{$where} AND ({$whereIn})";
        }

        if ($whereNull) {
            return "{$where} AND ({$whereNull})";
        }

        return $where;
    }
}
