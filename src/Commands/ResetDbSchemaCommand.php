<?php

namespace PressbooksMultiInstitution\Commands;

use PressbooksMultiInstitution\Database\Migration;
use PressbooksMultiInstitution\Models\InstitutionUser;

class ResetDbSchemaCommand extends WP_CLI_Command
{
    /**
     * Dump DB data generated.
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args)
    {
        try {
            $this->revokeSuperAdminPrivilegesForInstitutionalManagers();
            Migration::rollback();
            Migration::migrate();
        } catch (\Exception $e) {
            WP_CLI::error('Error dumping data: ' . $e->getMessage());
        }
    }

    private function revokeSuperAdminPrivilegesForInstitutionalManagers(): void
    {
        InstitutionUser::query()->managers()->each(function ($institutionUser) {
            revoke_super_admin($institutionUser->user_id);
        });
    }
}
