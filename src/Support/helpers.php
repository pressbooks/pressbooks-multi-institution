<?php

namespace PressbooksMultiInstitution\Support;

use PressbooksMultiInstitution\Models\InstitutionUser;

use function Pressbooks\Admin\NetworkManagers\is_restricted;

function get_institution_by_manager($user = null): int
{
    $user = ($user instanceof \WP_User && $user->ID !== 0) ? $user : wp_get_current_user();
    return is_restricted() ? InstitutionUser::query()->isManager($user->ID)->first()?->institution_id ?? 0 : 0;
}
