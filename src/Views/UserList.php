<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class UserList extends BaseInstitutionList
{
    public function init(): void
    {
        add_filter('pb_network_analytics_user_list_custom_texts', [$this, 'getCustomTexts']);
        add_filter('pb_network_analytics_user_list_columns', [$this, 'addColumns']);
        add_filter('pb_network_analytics_user_list_select_clause', [$this, 'appendAdditionalColumnsToQuery']);
        add_filter('pb_network_analytics_user_list_where_clause', [$this, 'appendAdditionalWhereClausesToQuery']);
        add_filter('pb_network_analytics_user_list_filter', [$this, 'addFilters']);
        add_filter('pb_network_analytics_user_list_fields', [$this, 'filterInstitutionListItems']);
    }
    public function getCustomTexts(array $texts): array
    {
        $institutionId = get_institution_by_manager();
        if (! is_super_admin() || $institutionId === 0) {
            return $texts;
        }

        $institution = Institution::query()
            ->where('id', $institutionId)
            ->withCount('users')
            ->first();

        return [
            'title' => sprintf(__("%s's User List", 'pressbooks-multi-institution'), $institution->name),
            'count' => sprintf(
                _n(
                    'There is %s user assigned to %s.',
                    'There are %s users assigned to %s.',
                    $institution->users_count,
                    'pressbooks-multi-institution'
                ),
                $institution->users_count,
                $institution->name
            ),
        ];
    }

    public function addColumns(array $columns): array
    {
        array_splice($columns, 5, 0, [
            [
                'title' => __('Institution', 'pressbooks-multi-institution'),
                'field' => 'institution',
            ]
        ]);
        return $columns;
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
            ->join('institutions_users', 'institutions.id', '=', 'institutions_users.institution_id')
            ->whereRaw("{$prefix}institutions_users.user_id = us.id")->limit(1);

        $nameSubQuery = $this->db
            ->table('institutions')
            ->select('institutions.name')
            ->join('institutions_blogs', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->join('institutions_users', 'institutions.id', '=', 'institutions_users.institution_id')
            ->whereRaw("{$prefix}institutions_users.user_id = us.id")->limit(1);

        return "({$idSubQuery->toSql()}) as institution_id, ({$nameSubQuery->toSql()}) as institution";
    }

}
