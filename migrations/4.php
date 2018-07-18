<?php

/**
 * Migration:   4
 * Started:     18/07/2018
 * Finalised:   18/07/2018
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration4 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export` CHANGE `error` `error` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;");
    }
}
