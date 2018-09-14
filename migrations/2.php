<?php

/**
 * Migration:   2
 * Started:     08/12/2015
 * Finalised:   08/12/2015
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nails\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration2 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        /**
         * Convert admin changelog data into JSON strings rather than use serialize
         */

        $oResult = $this->query('SELECT id, changes FROM {{NAILS_DB_PREFIX}}admin_changelog');
        while ($oRow = $oResult->fetch(\PDO::FETCH_OBJ)) {

            $mOldValue = unserialize($oRow->changes);
            $sNewValue = json_encode($mOldValue);

            //  Update the record
            $sQuery = '
                UPDATE `{{NAILS_DB_PREFIX}}admin_changelog`
                SET
                    `changes` = :newValue
                WHERE
                    `id` = :id
            ';

            $oSth = $this->prepare($sQuery);

            $oSth->bindParam(':newValue', $sNewValue, \PDO::PARAM_STR);
            $oSth->bindParam(':id', $oRow->id, \PDO::PARAM_INT);

            $oSth->execute();
        }
    }
}
