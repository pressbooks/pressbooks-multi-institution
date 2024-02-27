<?php

namespace PressbooksMultiInstitution\Support;

use PressbooksMultiInstitution\Models\InstitutionUser;

use function Pressbooks\Admin\NetworkManagers\is_restricted;

function get_institution_by_manager($user = null): int
{
    $currentUser = $user ?? wp_get_current_user();

    if (! is_restricted()) {
        return 0;
    }

    return InstitutionUser::query()->isManager($currentUser->ID)->value('institution_id') ?? 0;
}
