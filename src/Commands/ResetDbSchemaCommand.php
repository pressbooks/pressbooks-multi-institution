<?php

namespace PressbooksMultiInstitution\Commands;

use PressbooksMultiInstitution\Database\Migration;
use PressbooksMultiInstitution\Services\PermissionsManager;

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
        } catch (\Exception) {
            return false;
        }
    }
}
