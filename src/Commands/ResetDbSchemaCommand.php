<?php

namespace PressbooksMultiInstitution\Commands;

use PressbooksMultiInstitution\Actions\PermissionsManager;
use PressbooksMultiInstitution\Database\Migration;

class ResetDbSchemaCommand
{
    /**
     * Reset DB Schema.
     */
    public function __invoke($args, $assoc_args): bool
    {
        try {
            PermissionsManager::revokeInstitutionalManagersPrivileges();
            Migration::rollback();
            Migration::migrate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
