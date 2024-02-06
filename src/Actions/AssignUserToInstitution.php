<?php

namespace PressbooksMultiInstitution\Actions;

use Illuminate\Support\Str;
use PressbooksMultiInstitution\Models\Institution;

class AssignUserToInstitution
{
    public function handle(int $userId): bool
    {
        $user = get_userdata($userId);

        if (! $user) {
            return false;
        }

        $email = Str::of($user->user_email);

        $domain = (string) $email->after('@')->trim();

        /** @var Institution $institution */
        $institution = Institution::query()->whereRelation('domains', 'domain', $domain)->first();

        if (! $institution) {
            return false;
        }

        $institution->users()->create([
            'user_id' => $userId,
        ]);

        return true;
    }
}
