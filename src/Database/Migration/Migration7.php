<?php

/**
 * Migration:   7
 * Started:     07/11/2019
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Database\Migration;

use Nails\Common\Console\Migrate\Base;

class Migration7 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_changelog` CHANGE `changes` `changes` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_changelog` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
    }
}
