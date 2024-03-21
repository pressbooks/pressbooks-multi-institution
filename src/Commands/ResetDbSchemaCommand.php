<?php

namespace PressbooksMultiInstitution\Commands;

use PressbooksMultiInstitution\Actions\PermissionsManager;
use PressbooksMultiInstitution\Database\Migration;

class ResetDbSchemaCommand
{
    /**
     * Dump DB data generated.
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args)
    {
        try {
            PermissionsManager::revokeInstitutionalManagersPrivileges();
            Migration::rollback();
            Migration::migrate();
            echo "Database schema successfully reset. \n";
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . "/n";
        }
    }
}
