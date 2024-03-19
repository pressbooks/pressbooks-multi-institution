<?php

namespace PressbooksMultiInstitution\Commands;

use PressbooksMultiInstitution\Database\Migration;
use WP_CLI;
use WP_CLI_Command;

use function PressbooksMultiInstitution\Support\revoke_institutional_manager_privileges;

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
            revoke_institutional_manager_privileges();
            Migration::rollback();
            Migration::migrate();
            WP_CLI::success('Database schema reset successfully.');
        } catch (\Exception $e) {
            WP_CLI::error('Error dumping data: ' . $e->getMessage());
        }
    }
}
