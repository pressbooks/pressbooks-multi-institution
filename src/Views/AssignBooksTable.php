<?php

namespace PressbooksMultiInstitution\Views;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use WP_List_Table;

use function Pressbooks\Image\default_cover_url;
use function Pressbooks\Sanitize\sanitize_string;

class AssignBooksTable extends WP_List_Table
{
    protected int $paginationSize = 15;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'assign-book',
            'plural' => 'assign-books', // Parent will create bulk nonce: "bulk-{$plural}"
        ]);
    }

    public function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox" />',
            'cover' => __('Cover', 'pressbooks-multi-institution'),
            'title' => __('Title', 'pressbooks-multi-institution'),
            'institution' => __('Institution', 'pressbooks-multi-institution'),
            'book_administrators' => __('Book Administrators', 'pressbooks-multi-institution'),
        ];
    }

    /**
     * This method is called when the parent class can't find a method
     * for a given column. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists. If it doesn't this one will be used.
     *
     * @param object $item A singular item (one full row's worth of data)
     * @param string $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
     * @see WP_List_Table::single_row_columns()
     *
     */
    public function column_default($item, $column_name): string
    {
        $allowed_tags = [
            'p' => [],
            'a' => [
                'href' => [],
                'title' => []
            ]
        ];

        return wp_kses($item->$column_name ?? null, $allowed_tags);
    }

    public function column_cb($book): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.checkbox', [
            'name' => 'id',
            'value' => $book->id,
        ]);
    }

    public function column_cover(object $book): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.cover', [
            'url' => $book->cover ?? default_cover_url(),
            'alt_text' => "{$book->title}'s cover",
        ]);
    }

    public function column_title(object $book): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.book-title', [
            'title' => $book->title,
            'url' => $book->url,
        ]);
    }

    public function column_book_administrators(object $book): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.book-admins', [
            'admins' => $book->admins,
        ]);
    }

    public function get_sortable_columns(): array
    {
        return [
            'title' => ['title', true],
            'institution' => ['institution', false],
        ];
    }

    public function get_bulk_actions(): array
    {
        return [];
    }

    public function prepare_items(): void
    {
        /** @var Manager $db */
        $db = app('db');

        $search = sanitize_text_field($_REQUEST['s'] ?? '');
        $order = sanitize_string($_REQUEST['orderby'] ?? 'title');
        $direction = in_array($_REQUEST['order'] ?? '', ['asc', 'desc']) ? $_REQUEST['order'] : 'asc';

        $books = $db
            ->table('blogs')
            ->select(
                'blogs.blog_id as id',
                'blogs.domain',
                'blogs.path',
                'institutions.name as institution'
            )
            ->addSelect([
                'title' => $db
                    ->table('blogmeta')
                    ->select('meta_value')
                    ->where('meta_key', 'pb_title')
                    ->whereColumn('blogmeta.blog_id', 'blogs.blog_id'),
                'url' => $db
                    ->table('blogmeta')
                    ->select('meta_value')
                    ->where('meta_key', 'pb_book_url')
                    ->whereColumn('blogmeta.blog_id', 'blogs.blog_id'),
                'cover' => $db
                    ->table('blogmeta')
                    ->select('meta_value')
                    ->where('meta_key', 'pb_cover_image')
                    ->whereColumn('blogmeta.blog_id', 'blogs.blog_id')
            ])
            ->leftJoin('institutions_blogs', 'institutions_blogs.blog_id', '=', 'blogs.blog_id')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->where('blogs.blog_id', '<>', get_main_site_id())
            ->where('blogs.archived', false)
            ->where('blogs.spam', false)
            ->where('blogs.deleted', false)
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('blogs.path', 'like', "%{$search}%")
                        ->orWhereExists(function (Builder $query) use ($search) {
                            $query
                                ->selectRaw(1)
                                ->from('blogmeta')
                                ->whereColumn('blogmeta.blog_id', 'blogs.blog_id')
                                ->where('meta_key', 'pb_title')
                                ->where('meta_value', 'like', "%{$search}%");
                        })
                        ->orWhereExists(function (Builder $query) use ($search) {
                            $query
                                ->selectRaw(1)
                                ->from('blogmeta')
                                ->whereColumn('blogmeta.blog_id', 'blogs.blog_id')
                                ->where('meta_key', 'pb_book_url')
                                ->where('meta_value', 'like', "%{$search}%");
                        })
                        ->orWhereExists(function (Builder $query) use ($search) {
                            global $wpdb;

                            $query->selectRaw(1)
                                ->from('users as u')
                                ->join('usermeta as um1', 'u.ID', '=', 'um1.user_id')
                                ->whereRaw("{$wpdb->base_prefix}um1.meta_key = concat('{$wpdb->base_prefix}', {$wpdb->blogs}.blog_id, '_capabilities')")
                                ->where('um1.meta_value', 'like', '%administrator%')
                                ->where(function (Builder $query) use ($search) {
                                    $query
                                        ->where('u.user_email', 'like', "%{$search}%")
                                        ->orWhere('u.user_login', 'like', "%{$search}%")
                                        ->orWhereExists(function (Builder $query) use ($search) {
                                            $query
                                                ->selectRaw(1)
                                                ->from('usermeta as um2')
                                                ->whereColumn('um1.user_id', 'um2.user_id')
                                                ->where('um2.meta_key', 'first_name')
                                                ->where('um2.meta_value', 'like', "%{$search}%");
                                        })
                                        ->orWhereExists(function (Builder $query) use ($search) {
                                            $query
                                                ->selectRaw(1)
                                                ->from('usermeta as um2')
                                                ->whereColumn('um1.user_id', 'um2.user_id')
                                                ->where('um2.meta_key', 'last_name')
                                                ->where('um2.meta_value', 'like', "%{$search}%");
                                        })
                                        ->orWhereExists(function (Builder $query) use ($search) {
                                            $query
                                                ->selectRaw(1)
                                                ->from('institutions')
                                                ->join('institutions_users', 'institutions.id', '=', 'institutions_users.institution_id')
                                                ->whereColumn('u.ID', 'institutions_users.user_id')
                                                ->where('institutions.name', 'like', "%{$search}%");
                                        });
                                });
                        });
                });
            })
            ->when($order, function (Builder $query, string $order) use ($direction) {
                if ($order === 'institution') {
                    $query->orderByRaw(
                        $direction === 'asc' ? 'institution IS NULL' : 'institution IS NOT NULL'
                    );
                }

                $query->orderBy($order, $direction);
            })
            ->paginate(
                perPage: $this->paginationSize,
                pageName: 'paged',
                page: $this->get_pagenum(),
            );

        $books->map(function (object $book) use ($db) {
            global $wpdb;

            switch_to_blog($book->id);

            $book->admins = $db
                ->table('users')
                ->select('users.user_login', 'users.user_email', 'institutions.name as institution')
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
                ->join('usermeta', 'users.ID', '=', 'usermeta.user_id')
                ->leftJoin('institutions_users', 'users.ID', '=', 'institutions_users.user_id')
                ->leftJoin('institutions', 'institutions_users.institution_id', 'institutions.id')
                ->where('usermeta.meta_key', "{$wpdb->base_prefix}{$book->id}_capabilities")
                ->where('usermeta.meta_value', 'like', '%administrator%')
                ->orderByRaw('institution IS NULL')
                ->orderBy('institution')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('user_login')
                ->get()
                ->map(function (object $admin) {
                    $admin->fullname = (string) Str::of($admin->first_name)
                        ->append(" {$admin->last_name}")
                        ->trim();

                    return $admin;
                });

            restore_current_blog();

            return $book;
        });

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->items = $books;

        $this->set_pagination_args([
            'total_items' => $books->total(),
            'per_page' => $this->paginationSize,
            'total_pages' => $books->lastPage(),
        ]);
    }

    //    public function single_row($item): void
    //    {
    //
    //        if (isset($item['unassigned']) || isset($item['totals'])) {
    //            echo app('Blade')->render('PressbooksMultiInstitution::institutions.rows.totals', $item);
    //        } else {
    //            parent::single_row($item);
    //        }
    //    }
}
