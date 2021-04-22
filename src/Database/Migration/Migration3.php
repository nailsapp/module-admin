<?php

/**
 * Migration:   3
 * Started:     19/02/2018
 * Finalised:   19/02/2018
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Database\Migration;

use Nails\Common\Console\Migrate\Base;

class Migration3 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}admin_export` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `source` varchar(150) DEFAULT NULL,
                `options` text,
                `format` varchar(150) DEFAULT NULL,
                `status` enum('PENDING','RUNNING','COMPLETE','FAILED') NOT NULL DEFAULT 'PENDING',
                `error` varchar(150) DEFAULT NULL,
                `download_id` int(11) unsigned DEFAULT NULL,
                `created` datetime NOT NULL,
                `created_by` int(11) unsigned DEFAULT NULL,
                `modified` datetime NOT NULL,
                `modified_by` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `created_by` (`created_by`),
                KEY `modified_by` (`modified_by`),
                KEY `download_id` (`download_id`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_export_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_export_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_export_ibfk_3` FOREIGN KEY (`download_id`) REFERENCES `{{NAILS_DB_PREFIX}}cdn_object` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}
