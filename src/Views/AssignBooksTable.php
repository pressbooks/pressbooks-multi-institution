<?php

namespace PressbooksMultiInstitution\Views;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use PressbooksMultiInstitution\Traits\OverridesBulkActions;
use WP_List_Table;

use function Pressbooks\Image\default_cover_url;
use function Pressbooks\Sanitize\sanitize_string;

class AssignBooksTable extends WP_List_Table
{
    use OverridesBulkActions;

    protected int $paginationSize = 50;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'assign-book',
            'plural' => 'assign-books',
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
     * @param object $item
     * @return string
     */
    public function column_cb($item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.checkbox', [
            'name' => 'id',
            'value' => $item->id,
        ]);
    }

    public function column_cover(object $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.cover', [
            'url' => empty($item->cover) ? default_cover_url() : $item->cover,
            'alt_text' => "{$item->title}'s cover",
        ]);
    }

    public function column_title(object $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.book-title', [
            'title' => $item->title,
            'url' => "$item->url/wp-admin",
        ]);
    }

    public function column_institution(object $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.institution', [
            'institution' => $item->institution,
        ]);
    }

    public function column_book_administrators(object $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.book-admins', [
            'admins' => $item->admins,
        ]);
    }

    public function get_sortable_columns(): array
    {
        return [
            'title' => ['title', true],
            'institution' => ['institution', false],
        ];
    }

    public function prepare_items(): void
    {
        $books = $this->getBooks($_REQUEST);

        $books->map($this->getBookAdmins(...));

        $this->_column_headers = [
            $this->get_columns(), [], $this->get_sortable_columns()
        ];

        $this->items = $books;

        $this->set_pagination_args([
            'total_items' => $books->total(),
            'per_page' => $this->paginationSize,
            'total_pages' => $books->lastPage(),
        ]);
    }

    /**
     * Queries the paginated list of books in the network matching the search parameters.
     *
     * @param  array  $request
     * @return LengthAwarePaginator
     */
    protected function getBooks(array $request): LengthAwarePaginator
    {
        $search = sanitize_text_field($request['s'] ?? '');
        $order = sanitize_string($request['orderby'] ?? 'title');
        $direction = in_array($request['order'] ?? '', ['asc', 'desc']) ? $request['order'] : 'asc';
        $unassigned = $request['unassigned'] ?? '';

        return $this->getBaseQuery()
            ->when($unassigned, function (Builder $query) {
                $query->whereNull('institutions_blogs.blog_id');
            })
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('blogs.path', 'like', "%{$search}%")
                        ->orWhere('institutions.name', 'like', "%{$search}%")
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
                                                ->join(
                                                    'institutions_users',
                                                    'institutions.id',
                                                    '=',
                                                    'institutions_users.institution_id'
                                                )
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
                        $direction === 'asc' ? 'institution IS NOT NULL' : 'institution IS NULL'
                    );
                }

                $query->orderBy($order, $direction);
            })
            ->paginate(
                perPage: $this->paginationSize,
                pageName: 'paged',
                page: $this->get_pagenum(),
            );
    }

    public function getBaseQuery(): object
    {
        /** @var Manager $db */
        $db = app('db');

        return $db
            ->table('blogs')
            ->select(
                'blogs.blog_id as id',
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
            ->where('blogs.deleted', false);
    }

    public function getTotalBooksCount(): int
    {
        return $this->getBaseQuery()->count();
    }

    public function getUnassignedBooksCount(): int
    {
        return $this->getBaseQuery()->whereNull('institutions_blogs.blog_id')->count();
    }

    /**
     * Queries the book administrators for a given book.
     *
     * @param  object  $book
     * @return object
     */
    protected function getBookAdmins(object $book): object
    {
        global $wpdb;

        /** @var Manager $db */
        $db = app('db');

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
    }
}
