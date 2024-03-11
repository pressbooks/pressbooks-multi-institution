<?php

namespace PressbooksMultiInstitution\Support;

use PressbooksMultiInstitution\Models\InstitutionUser;

use function Pressbooks\Admin\NetworkManagers\is_restricted;

function get_institution_by_manager(): int
{
    $user = wp_get_current_user();

    if (! is_restricted()) {
        return 0;
    }

    return InstitutionUser::query()->isManager($user->ID)->value('institution_id') ?? 0;
}

function is_network_unlimited(): bool
{
    $bookLimit = get_option('pb_plan_settings_book_limit', null);

    if (is_null($bookLimit)) {
        return false;
    }

    $bookLimit = (int) $bookLimit;

    return $bookLimit === 0;
}
