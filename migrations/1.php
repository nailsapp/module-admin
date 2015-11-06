<?php

/**
 * Migration:   1
 * Started:     06/11/2015
 * Finalised:   06/11/2015
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration_1 extends Base {

    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("
            CREATE TABLE `nails_user_meta_admin` (
                `user_id` int(11) unsigned NOT NULL,
                `nav_state` text,
                PRIMARY KEY (`user_id`),
                CONSTRAINT `nails_user_meta_admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `nails_user` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}
