<?php

/**
 * Migration:   6
 * Started:     23/08/2018
 * Finalised:   23/08/2018
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration6 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("RENAME TABLE `{{NAILS_DB_PREFIX}}admin_notes` TO `{{NAILS_DB_PREFIX}}admin_note`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_note` ADD `is_deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `message`;");
    }
}
