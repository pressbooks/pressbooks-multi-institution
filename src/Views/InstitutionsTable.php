<?php

namespace PressbooksMultiInstitution\Views;

use Illuminate\Database\Eloquent\Relations\HasMany;
use PressbooksMultiInstitution\Models\Institution;
use WP_List_Table;

use function PressbooksMultiInstitution\Support\is_network_unlimited;

class InstitutionsTable extends WP_List_Table
{
    protected int $paginationSize = 50;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'institution',
            'plural' => 'institutions', // Parent will create bulk nonce: "bulk-{$plural}"
        ]);
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
            'div' => [],
            'a' => [
                'href' => [],
                'title' => []
            ],
            'span'
        ];
        return wp_kses($item[$column_name], $allowed_tags);
    }

    /**
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td>
     */
    public function column_name(array $item): string
    {
        $edit_url = network_admin_url(
            sprintf('/admin.php?page=%s&action=%s&ID=%s', 'pb_multi_institution_form', 'edit', $item['ID'])
        );

        $actions['edit'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $edit_url,
            /* translators: %s: post title */
            esc_attr(sprintf(__('Edit &#8220;%s&#8221;'), $item['name'])),
            __('Edit')
        );

        $delete_url = network_admin_url(
            sprintf('/admin.php?page=%s&action=%s&ID[]=%s', $_REQUEST['page'], 'delete', $item['ID'])
        );
        $delete_url = esc_url(add_query_arg('_wpnonce', wp_create_nonce('bulk-institutions'), $delete_url));

        $onclick = 'onclick="if ( !confirm(\'' . esc_attr(__('Are you sure you want to delete this?', 'pressbooks-multi-institution')) . '\') ) { return false }"';

        $actions['trash'] = sprintf(
            '<a href="%s" class="submitdelete" aria-label="%s" ' . $onclick . '>%s</a>',
            $delete_url,
            /* translators: %s: post title */
            esc_attr(sprintf(__('Move &#8220;%s&#8221; to the Trash'), $item['name'])),
            _x('Delete', 'verb')
        );

        return sprintf(
            '<div class="row-title"><a href="%1$s" class="title">%2$s</a></div> %3$s',
            $edit_url,
            $item['name'],
            $this->row_actions($actions)
        );
    }

    public function column_cb($item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.checkbox', [
            'name' => 'ID',
            'value' => $item['ID'],
            'label' => sprintf(__('Select %s'), $item['name']),
        ]);
    }

    public function get_columns(): array
    {
        return array_filter([
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'pressbooks-multi-institution'),
            'email_domains' => __('Email Domains', 'pressbooks-multi-institution'),
            'buy_in' => is_network_unlimited() ? null : __('Buy-in', 'pressbooks-multi-institution'),

            'institutional_managers' => __('Institutional Managers', 'pressbooks-multi-institution'),
            'book_limit' => __('Books', 'pressbooks-multi-institution'),
            'users' => __('Users', 'pressbooks-multi-institution'),
        ]);
    }

    public function get_sortable_columns(): array
    {
        return [
            'name' => ['name', false],
            'book_limit' => ['book_limit', false],
            'users' => ['users', false],
        ];
    }

    public function column_email_domains(array $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.email-domains', [
            'domains' => $item['email_domains'],
        ]);
    }

    public function column_buy_in(array $item): string
    {
        return $item['buy_in'] ?
            sprintf('<span aria-hidden="true">✅</span><span class="screen-reader-text">%s</span>', __('Yes', 'pressbooks-multi-institution')) :
            sprintf('<span aria-hidden="true">❌</span><span class="screen-reader-text">%s</span>', __('No', 'pressbooks-multi-institution'));
    }

    public function column_institutional_managers(array $item): string
    {
        return app('Blade')->render('PressbooksMultiInstitution::table.institutional-managers', [
            'managers' => $item['managers'],
        ]);
    }

    public function column_book_limit(array $item): string
    {
        return $item['book_limit'];
    }

    public function column_users(array $item): string
    {
        return $item['users'];
    }

    public function get_bulk_actions(): array
    {
        return [
            'delete' => __('Delete', 'pressbooks-multi-institution'),
        ];
    }

    public function prepare_items(): void
    {
        $unlimitedNetwork = is_network_unlimited();

        // Retrieve the paginated data using Eloquent
        $institutions = Institution::query()
            ->withCount('books', 'users')
            ->with([
                'domains',
                'managers' => function (HasMany $query) {
                    $query
                        ->join('users', 'institutions_users.user_id', '=', 'users.ID')
                        ->orderBy('users.display_name');
                },
            ])
            ->searchAndOrder($_REQUEST)
            ->paginate($this->paginationSize, ['*'], 'paged', $this->get_pagenum());

        // Define Columns
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Extract the data from the Eloquent paginator object
        $this->items = $institutions->map(function (Institution $institution) use ($unlimitedNetwork) {
            $bookLimit = $institution->books_count;

            if ($institution->book_limit === 0 && ! $unlimitedNetwork) {
                $bookLimit .= '/' . __('unlimited', 'pressbooks-multi-institution');
            }

            if ($institution->book_limit > 0 && ! $unlimitedNetwork) {
                $bookLimit .= "/{$institution->book_limit}";
            }

            return [
                'ID' => $institution->id,
                'name' => $institution->name,
                'email_domains' => $institution->domains,
                'buy_in' => $institution->buy_in,
                'managers' => $institution->managers,
                'book_limit' => $bookLimit,
                'users' => $institution->users_count,
            ];
        })->toArray();

        $this->set_pagination_args([
            'total_items' => $institutions->total(),
            'per_page' => $this->paginationSize,
            'total_pages' => $institutions->lastPage(),
        ]);
    }

    public function single_row($item): void
    {
        if (! isset($item['totals'])) {
            parent::single_row($item);

            return;
        }

        echo app('Blade')->render(
            'PressbooksMultiInstitution::institutions.rows.totals',
            [
                'item' => $item,
                'colspan' => is_network_unlimited() ? 4 : 5,
            ]
        );
    }
}
