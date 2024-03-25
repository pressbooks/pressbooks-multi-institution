<?php

namespace PressbooksMultiInstitution\Views;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;

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
        //This probably would be removed
        add_filter('pb_network_analytics_userslist', [$this, 'addInstitutionFieldToUsersTable']);
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

    public function addInstitutionFieldToUsersTable(array $users): array
    {
        $institutionUsers = InstitutionUser::query()->with('institution')->get();

        return array_map(function ($user) use ($institutionUsers) {
            $institutionUser = $institutionUsers->where('user_id', $user->id)->first();
            $properties = get_object_vars($user);
            $propertiesBeforeEmail = array_slice($properties, 0, 3, true);
            $propertiesAfterEmail = array_slice($properties, 3, null, true);
            $properties = array_merge(
                $propertiesBeforeEmail,
                ['institution' => $institutionUser?->institution->name ?? __('Unassigned', 'pressbooks-multi-institution')],
                $propertiesAfterEmail
            );

            return (object) $properties;
        }, $users);
    }
}
