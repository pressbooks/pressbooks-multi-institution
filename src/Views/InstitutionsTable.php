<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;
use WP_List_Table;

class InstitutionsTable extends WP_List_Table
{
    protected int $paginationSize = 1000;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'institution',
            'plural' => 'instutitions', // Parent will create bulk nonce: "bulk-{$plural}"
            'ajax' => true,
        ]);
    }

    /**
     * This method is called when the parent class can't find a method
     * for a given column. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists. If it doesn't this one will be used.
     *
     * @see WP_List_Table::single_row_columns()
     *
     * @param object $item A singular item (one full row's worth of data)
     * @param string $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
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
        return wp_kses($item[$column_name], $allowed_tags);
    }

    /**
     * @param  array  $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td>
     */
    public function column_name(array $item): string
    {
        $edit_url = network_admin_url(
            sprintf('/admin.php?page=%s&action=%s&ID=%s', $_REQUEST['page'], 'edit', $item['ID'])
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
        $delete_url = esc_url(add_query_arg('_wpnonce', wp_create_nonce('bulk-platforms'), $delete_url));

        $onclick = 'onclick="if ( !confirm(\'' . esc_attr(__('Are you sure you want to delete this?', 'pressbooks')) . '\') ) { return false }"';

        $actions['trash'] = sprintf(
            '<a href="%s" class="submitdelete" aria-label="%s" ' . $onclick . '>%s</a>',
            $delete_url,
            /* translators: %s: post title */
            esc_attr(sprintf(__('Move &#8220;%s&#8221; to the Trash'), $item['name'])),
            _x('Trash', 'verb')
        );

        return sprintf(
            '<div class="row-title"><a href="%1$s" class="title">%2$s</a></div> %3$s',
            $edit_url,
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item): string
    {
        return sprintf('<input type="checkbox" name="ID[]" value="%s" />', $item['ID']);
    }

    /**
     * @return array
     */
    public function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'pressbooks-multi-institution'),
            'email_domains' => __('Email Domains', 'pressbooks-multi-institution'),
            'institutional_managers' => __('Institutional Managers', 'pressbooks-multi-institution'),
            'book_limit' => __('Books', 'pressbooks-multi-institution'),
            'user_limit' => __('Users', 'pressbooks-multi-institution'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'name' => ['name', false],
            'email_domains' => ['email_domains', false],
            'book_limit' => ['book_limit', false],
            'user_limit' => ['user_limit', false],
        ];
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function column_email_domains(array $item): string
    {
        return $item['email_domains'];
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function column_institutional_managers(array $item): string
    {
        return $item['institutional_managers'];
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function column_book_limit(array $item): string
    {
        return $item['book_limit'];
    }

    /**
     * @param array $item
     *
     * @return string
     */
    public function column_user_limit(array $item): string
    {
        return $item['user_limit'];
    }

    /**
     * @return array
     */
    public function get_bulk_actions(): array
    {
        return [
            'delete' => 'Delete',
            'activate' => 'Activate',
            'deactivate' => 'Deactivate',
        ];
    }

    /**
     * Prepares the list of items for displaying.
     */
    public function prepare_items(): void
    {
        // Retrieve the paginated data using Eloquent
        $institutions = Institution::query()->searchAndOrder($_REQUEST)->
        paginate($this->paginationSize, ['*'], 'paged', $this->get_pagenum());

        // Define Columns
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Total items count
        $total_items = $institutions->total();

        // Extract the data from the Eloquent paginator object
        $this->items = $institutions->map(function ($institution) {
            return [
                'ID' => $institution->id,
                'name' => $institution->name,
                'email_domains' => $institution->email_domains,
                'institutional_managers' => $institution->institutional_managers,
                'book_limit' => $institution->book_limit,
                'user_limit' => $institution->user_limit,
            ];
        })->toArray();

        $this->items[] = [
            'ID' => 'total_items', // this is a dummy ID, it won't be used for anything
            'name' => __('Total Items', 'pressbooks-multi-institution'),
            'email_domains' => $total_items,
            'institutional_managers' => '',
            'book_limit' => '',
            'user_limit' => '',
        ];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $this->paginationSize,
            'total_pages' => $institutions->lastPage(),
        ]);
    }
}