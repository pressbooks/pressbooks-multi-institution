<?php

namespace PressbooksMultiInstitution\Actions;

use Illuminate\Support\Str;
use PressbooksMultiInstitution\Models\Institution;
use WP_User;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class AssignUserToInstitution
{
    public function handle(int $userId): bool
    {
        $user = get_userdata($userId);

        if (! $user) {
            return false;
        }

        $institution = get_institution_by_manager();

        return $institution === 0 ?
            $this->assignByUserDomain($user) : $this->assignUserByInstitution($user, $institution);
    }

    private function assignByUserDomain(WP_User $user): bool
    {
        $email = Str::of($user->user_email);

        $domain = (string) $email->after('@')->trim();

        /** @var Institution $institution */
        $institution = Institution::query()->whereRelation('domains', 'domain', $domain)->first();

        if (! $institution) {
            return false;
        }

        $institution->users()->create([
            'user_id' => $user->ID,
        ]);

        return true;
    }

    private function assignUserByInstitution(WP_User $user, int $institution): bool
    {
        $institution = Institution::find($institution);

        $institution->users()->create([
            'user_id' => $user->ID,
        ]);

        return true;
    }
}
