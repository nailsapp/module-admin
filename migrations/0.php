<?php

/**
 * Migration:   0
 * Started:     09/01/2015
 * Finalised:   09/01/2015
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration_0 extends Base {

    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}admin_changelog` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(11) unsigned DEFAULT NULL,
                `verb` varchar(25) DEFAULT NULL,
                `article` varchar(3) DEFAULT NULL,
                `item` varchar(75) DEFAULT NULL,
                `item_id` int(11) unsigned DEFAULT NULL,
                `title` varchar(100) DEFAULT NULL,
                `url` varchar(255) DEFAULT NULL,
                `changes` text, `created` datetime NOT NULL,
                `created_by` int(10) unsigned DEFAULT NULL,
                `modified` datetime NOT NULL,
                `modified_by` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`), KEY `user_id` (`user_id`), KEY `created_by` (`created_by`), KEY `modified_by` (`modified_by`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_changelog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_changelog_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_changelog_ibfk_3` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}admin_help_video` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(150) NOT NULL DEFAULT '',
                `description` text NOT NULL,
                `vimeo_id` varchar(10) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}
