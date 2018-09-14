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

namespace Nails\Database\Migration\Nails\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration1 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}user_meta_admin` (
                `user_id` int(11) unsigned NOT NULL,
                `nav_state` text,
                PRIMARY KEY (`user_id`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}user_meta_admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` CHANGE `title` `label` VARCHAR(150)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD `duration` INT(11)  UNSIGNED  NOT NULL  AFTER `vimeo_id`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD `created` DATETIME  NOT NULL  AFTER `duration`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD `created_by` INT(11)  UNSIGNED  NULL  DEFAULT NULL  AFTER `created`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD `modified` DATETIME  NOT NULL  AFTER `created_by`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD `modified_by` INT(11)  UNSIGNED  NULL  DEFAULT NULL  AFTER `modified`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}admin_help_video` ADD FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}admin_changelog` (`id`) ON DELETE SET NULL;");
    }
}
