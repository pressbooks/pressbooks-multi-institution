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

function get_institution_books(): array
{
    $institution = get_institution_by_manager();

    if ($institution === 0) {
        return [];
    }

    return InstitutionUser::query()->byInstitution($institution)->pluck('user_id')->toArray();
}

function get_allowed_pages(): array
{
    return [
        'admin.php' => ['pb_network_analytics_booklist', 'pb_network_analytics_userlist', 'pb_network_analytics_admin', 'pb_cloner'],
        'sites.php' => ['confirm', 'delete', 'pb_network_analytics_booklist', 'pb_network_analytics_userlist', 'pb_network_analytics_admin', 'pb_cloner'],
        'index.php' => ['', 'book_dashboard', 'pb_institutional_manager', 'pb_home_page', 'pb_catalog'],
        'tools.php',
        'users.php',
        'admin-ajax.php',
        'options-general.php',
        'profile.php' => [''],
        'post-new.php',
        'edit.php',
        'edit-tags.php',
        'upload.php',
        'post.php',
        'themes.php',
        'plugins.php',
        'media-new.php',
        'users.php',
        'export-personal-data.php',
        'erase-personal-data.php',
        'options-privacy.php'
    ];
}

function get_allowed_book_pages(): array
{
    return [
        'site-info.php',
        'site-settings.php',
        'site-themes.php',
        'site-users.php',
    ];
}
