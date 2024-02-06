<?php

namespace PressbooksMultiInstitution\Actions;

use Illuminate\Support\Str;
use PressbooksMultiInstitution\Models\Institution;

class AssignBookToInstitution
{
    public function handle(): bool
    {
        $bookId = get_current_blog_id();

        if (is_main_site($bookId)) {
            return false;
        }

        $user = wp_get_current_user();

        if (! $user) {
            return false;
        }

        $email = Str::of($user->user_email);

        $domain = (string) $email->after('@')->trim();

        /** @var Institution $institution */
        $institution = Institution::query()->whereRelation('users', 'user_id', $user->ID)->first();

        if (! $institution) {
            return false;
        }

        $institution->books()->create([
            'blog_id' => $bookId,
        ]);

        return true;
    }
}
