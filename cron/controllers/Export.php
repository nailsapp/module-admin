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

use Nails\Admin\Exception\DataExport\FailureException;
use Nails\Cron\Controller\Base;
use Nails\Factory;

class Export extends Base
{
    public function index()
    {
        $this->writeLog('Generating exports');

        $oNow = Factory::factory('DateTime');
        setAppSetting('data-export-cron-last-run', 'nails/module-admin', $oNow->format('Y-m-d H:i:s'));

        $oService  = Factory::service('DataExport', 'nails/module-admin');
        $oModel    = Factory::model('Export', 'nails/module-admin');
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
                        'options'    => json_decode($oRequest->options, JSON_OBJECT_AS_ARRAY),
                        'recipients' => [$oRequest->created_by],
                        'ids'        => [$oRequest->id],
                    ];
                }
            }

            //  Process each request
            $oEmail = Factory::factory('EmailDataExport', 'nails/module-admin');
            foreach ($aGroupedRequests as $oRequest) {
                try {

                    $this->writeLog(
                        'Starting ' . $oRequest->source . '->' . $oRequest->format . ' (' . json_encode($oRequest->options) . ')'
                    );
                    $oModel->setBatchDownloadId(
                        $oRequest->ids,
                        $oService->export($oRequest->source, $oRequest->format, $oRequest->options)
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

                } catch (FailureException $e) {
                    $this->executionFailed($e, $oRequest, $oModel, $oEmail);
                } catch (\Exception $e) {
                    $this->executionFailed($e, $oRequest, $oModel, $oEmail);
                    //  Let unexpected exceptions bubble up
                    throw $e;
                }
            }

        } else {
            $this->writeLog('Nothing to do');
        }

        $this->writeLog('Complete');
    }

    // --------------------------------------------------------------------------

    /**
     * Marks a request as failed, recording why it failed and informs the recipients
     *
     * @param \Exception                            $oException The exception whichw as thrown
     * @param \stdClass                             $oRequest   The current request
     * @param \Nails\Admin\Model\Export             $oModel     The data export model
     * @param \Nails\Admin\Factory\Email\DataExport $oEmail     The email object
     */
    protected function executionFailed(
        \Exception $oException,
        \stdClass $oRequest,
        \Nails\Admin\Model\Export $oModel,
        \Nails\Admin\Factory\Email\DataExport $oEmail
    ) {
        $this->writeLog('Exception: ' . $oException->getMessage());
        $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_FAILED, $oException->getMessage());

        $oEmail
            ->data([
                'status' => $oModel::STATUS_FAILED,
                'error'  => $oException->getMessage(),
            ]);

        foreach ($oRequest->recipients as $iRecipient) {
            $oEmail->to($iRecipient)->send();
        }
    }
}
