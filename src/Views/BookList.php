<?php

namespace PressbooksMultiInstitution\Views;

use Pressbooks\DataCollector\Book;
use PressbooksMultiInstitution\Models\Institution;

use function Pressbooks\Utility\format_bytes;
use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class BookList extends BaseInstitutionList
{
    public function init(): void
    {
        add_filter('pb_network_analytics_book_list_custom_texts', [$this, 'getCustomTexts']);
        add_filter('pb_network_analytics_book_list_columns', [$this, 'addColumns']);
        add_filter('pb_network_analytics_book_list_select_clause', [$this, 'appendAdditionalColumnsToQuery']);
        add_filter('pb_network_analytics_book_list_where_clause', [$this, 'appendAdditionalWhereClausesToQuery']);
        add_filter('pb_network_analytics_book_list_filter', [$this, 'addFilters']);
        add_filter('pb_network_analytics_book_list_fields', [$this, 'filterInstitutionListItems']);
    }

    public function addColumns(array $columns): array
    {
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

    public function filterInstitutionListItems($bookList): array
    {
        return array_map(function (\stdClass $item) {
            unset($item->institution_id);
            $item->institution = $item->institution ?? __('Unassigned', 'pressbooks-multi-institution');
            return $item;
        }, $bookList);
    }
}
