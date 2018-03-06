<?php

/**
 * Cron controller responsible for generating admin exports
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 */

namespace Nails\Cron\Admin;

use Nails\Cron\Controller\Base;
use Nails\Factory;

class Export extends Base
{
    public function index()
    {
        $this->writeLog('Generating exports');

        $oNow = Factory::factory('DateTime');
        setAppSetting('data-export-cron-last-run', 'nailsapp/module-admin', $oNow->format('Y-m-d H:i:s'));

        $oService  = Factory::service('DataExport', 'nailsapp/module-admin');
        $oModel    = Factory::model('Export', 'nailsapp/module-admin');
        $aRequests = $oModel->getAll(['where' => [['status', $oModel::STATUS_PENDING]]]);

        if (!empty($aRequests)) {

            $this->writeLog(count($aRequests) . ' requests');
            $this->writeLog('Marking as "RUNNING"');
            $oModel->setBatchStatus($aRequests, $oModel::STATUS_RUNNING);

            //  Group identical requests
            $aGroupedRequests = [];
            foreach ($aRequests as $oRequest) {
                $aHash = [$oRequest->source, $oRequest->format, $oRequest->options];
                $sHash = md5(json_encode($aHash));
                if (array_key_exists($sHash, $aGroupedRequests)) {
                    $aGroupedRequests[$sHash]->recipients[] = $oRequest->created_by;
                    $aGroupedRequests[$sHash]->ids[]        = $oRequest->id;
                } else {
                    $aGroupedRequests[$sHash] = (object) [
                        'source'     => $oRequest->source,
                        'format'     => $oRequest->format,
                        'options'    => json_decode($oRequest->options),
                        'recipients' => [$oRequest->created_by],
                        'ids'        => [$oRequest->id],
                    ];
                }
            }

            //  Process each request
            $oEmail = Factory::factory('EmailDataExport', 'nailsapp/module-admin');
            foreach ($aGroupedRequests as $oRequest) {
                try {
                    $this->writeLog('Starting ' . $oRequest->source . '->' . $oRequest->format);
                    $oModel->setBatchDownloadId(
                        $oRequest->ids,
                        $oService->export($oRequest->source, $oRequest->format)
                    );
                    $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_COMPLETE);
                    $this->writeLog('Completed ' . $oRequest->source . '->' . $oRequest->format);
                    $this->writeLog('Sending emails');

                    $oEmail
                        ->data([
                            'status' => $oModel::STATUS_COMPLETE,
                            'error'  => null,
                        ]);

                    foreach ($oRequest->recipients as $iRecipient) {
                        $oEmail->to($iRecipient)->send();
                    }

                } catch (\Exception $e) {
                    $this->writeLog('Exception: ' . $e->getMessage());
                    $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_FAILED, $e->getMessage());

                    $oEmail
                        ->data([
                            'status' => $oModel::STATUS_FAILED,
                            'error'  => $e->getMessage(),
                        ]);

                    foreach ($oRequest->recipients as $iRecipient) {
                        $oEmail->to($iRecipient)->send();
                    }
                }
            }

        } else {
            $this->writeLog('Nothing to do');
        }

        $this->writeLog('Complete');
    }
}
