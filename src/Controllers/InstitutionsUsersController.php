<?php

namespace PressbooksMultiInstitution\Controllers;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Views\InstitutionsUsersTable;

class InstitutionsUsersController extends BaseController
{
    private InstitutionsUsersTable $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = new InstitutionsUsersTable;
    }

    public function assign(): string
    {
        $this->mergeRefererQueryParams();

        $result = $this->processBulkActions();

        $this->table->prepare_items();

        return $this->renderView('users.assign', [
            'page' => 'pb_multi_institutions_users',
            'list_url' => network_admin_url('admin.php?page=pb_multi_institutions_users'),
            'table' => $this->table,
            'result' => $result,
            'params' => [
                'searchQuery' => $_REQUEST['s'] ?? '',
                'orderBy' => $_REQUEST['orderby'] ?? 'ID',
                'order' => $_REQUEST['order'] ?? 'ASC',
            ]
        ]);
    }

    private function mergeRefererQueryParams(): void
    {
        $referer = wp_get_referer();

        if ($referer && str_contains($referer, 'page=pb_multi_institutions_users')) {
            parse_str(parse_url($referer)['query'], $queryParams);
            $_REQUEST = array_merge($_REQUEST, $queryParams);
            unset($_REQUEST['_wp_http_referer']);
        }
    }

    protected function processBulkActions(): array
    {
        $action = $this->table->current_action();

        if ($action === false) {
            return [];
        }

        $items = $_REQUEST['ID'] ?? [];

        if (!$items) {
            return [];
        }

        if ($action === '0') {
            InstitutionUser::query()->whereIn('user_id', $items)->delete();
            return [
                'success' => true,
                'message' => __('User/s unassigned.', 'pressbooks-multi-institution'),
            ];
        }

        $institution = Institution::find($action);
        if (!$institution) {
            return [
                'success' => false,
                'message' => __('Institution not found.', 'pressbooks-multi-institution'),
            ];
        }

        foreach ($items as $user_id) {
            InstitutionUser::updateOrCreate(
                ['user_id' => $user_id],
                ['institution_id' => $institution->id]
            );
        }

        return [
            'success' => true,
            'message' => __('User/s assigned.', 'pressbooks-multi-institution'),
        ];
    }
}
