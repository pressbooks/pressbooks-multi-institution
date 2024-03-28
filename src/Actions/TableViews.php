<?php

namespace PressbooksMultiInstitution\Actions;

use PressbooksMultiInstitution\Models\Institution;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class TableViews
{
    public function init(): void
    {
        add_filter('pb_network_analytics_filter_tabs', [$this, 'addInstitutionsFilterTab']);
    }
    public function addInstitutionsFilterTab(array $filters): array
    {
        $currentPage = $_GET['page'] ?? false;

        if (! $currentPage || ! is_super_admin() || get_institution_by_manager() > 0) {
            return $filters;
        }

        $associatedEntity = $currentPage === 'pb_network_analytics_booklist' ? 'books' : 'users';

        return [
            ...$filters,
            [
                'tab' => app('Blade')->render('PressbooksMultiInstitution::partials.filters.institutions.tab'),
                'content' => app('Blade')->render('PressbooksMultiInstitution::partials.filters.institutions.content', [
                    'institutions' => Institution::query()->whereHas($associatedEntity)->orderBy('name')->get(),
                ])
            ]
        ];
    }
}
