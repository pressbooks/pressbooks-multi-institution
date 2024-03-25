<?php

namespace PressbooksMultiInstitution\Support;

class Events
{
    /**
     * This method is called after an institution is saved, and it updates the restricted users list
     *
     * @param array<int, int> $newManagers
     * @param array<int, int> $revokedManagers
     */
    public function afterSaveInstitution(array $newManagers, array $revokedManagers): void
    {
        $restricted = _restricted_users();

        // Grant super admin privileges to new institution managers and add them to the restricted users list
        foreach ($newManagers as $id) {
            $restricted[] = $id;

            grant_super_admin($id);
        }

        $restricted = array_diff(array_unique($restricted), $revokedManagers);

        // Remove institution managers from the restricted users list and revoke their super admin privileges
        foreach ($revokedManagers as $id) {
            revoke_super_admin($id);
        }

        // Update the restricted users list
        update_site_option('pressbooks_network_managers', $restricted);
    }
}
