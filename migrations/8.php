<?php

/**
 * Migration:   8
 * Started:     07/11/2019
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nails\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration8 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query('
            CREATE TABLE `{{NAILS_DB_PREFIX}}admin_dashboard_widget` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `slug` varchar(255) NOT NULL DEFAULT \'\',
            `x` int unsigned NOT NULL,
            `y` int unsigned NOT NULL,
            `w` int unsigned NOT NULL,
            `h` int unsigned NOT NULL,
            `config` json DEFAULT NULL,
            `created` datetime NOT NULL,
            `created_by` int unsigned DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            `modified_by` int unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `created_by` (`created_by`),
            KEY `modified_by` (`modified_by`),
            CONSTRAINT `{{NAILS_DB_PREFIX}}admin_dashboard_widget_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
            CONSTRAINT `{{NAILS_DB_PREFIX}}admin_dashboard_widget_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
    }
}
