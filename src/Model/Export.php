<?php

/**
 * Export Model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 */

namespace Nails\Admin\Model;

use Nails\Common\Model\Base;
use Nails\Config;
use Nails\Factory;

class Export extends Base
{
    /**
     * The various statuses
     */
    const STATUS_PENDING  = 'PENDING';
    const STATUS_RUNNING  = 'RUNNING';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_FAILED   = 'FAILED';

    // --------------------------------------------------------------------------

    /**
     * Export constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = Config::get('NAILS_DB_PREFIX') . 'admin_export';
    }

    // --------------------------------------------------------------------------

    /**
     * Updates the status of multiple request items in a batch
     *
     * @param array  $aIds    The requests to update
     * @param string $sStatus The status to set
     * @param string $sError  Any error message to set
     */
    public function setBatchStatus(array $aIds, $sStatus, $sError = '')
    {
        if (is_object(reset($aIds))) {
            $aIds = arrayExtractProperty($aIds, 'id');
        }
        if (!empty($aIds)) {
            $oDb = Factory::service('Database');
            $oDb->set('status', $sStatus);
            $oDb->set('modified', 'NOW()', false);
            if ($sError) {
                $oDb->set('error', $sError);
            }
            $oDb->where_in('id', $aIds);
            $oDb->update($this->getTableName());
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Updates the download ID of multiple requests
     *
     * @param array   $aIds        The requests to update
     * @param integer $iDownloadId The download ID
     */
    public function setBatchDownloadId(array $aIds, $iDownloadId)
    {
        if (is_object(reset($aIds))) {
            $aIds = arrayExtractProperty($aIds, 'id');
        }
        if (!empty($aIds)) {
            $oDb = Factory::service('Database');
            $oDb->set('download_id', $iDownloadId);
            $oDb->set('modified', 'NOW()', false);
            $oDb->where_in('id', $aIds);
            $oDb->update($this->getTableName());
        }
    }
}
