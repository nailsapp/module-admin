<?php

namespace Nails\Admin\Console\Command\DataExport;

use Nails\Console\Command\Base;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Base
{
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:dataexport')
            ->setDescription('Processes pending data exports');
    }

    // --------------------------------------------------------------------------

    /**
     * Executes the app
     *
     * @param InputInterface  $oInput  The Input Interface provided by Symfony
     * @param OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @return int
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput): int
    {
        parent::execute($oInput, $oOutput);

        // --------------------------------------------------------------------------

        try {

            $this->banner('Nails Admin Data Export');
            $this->process();

        } catch (\Exception $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        //  Cleaning up
        $oOutput->writeln('');
        $oOutput->writeln('<comment>Cleaning up...</comment>');

        // --------------------------------------------------------------------------

        //  And we're done
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');

        return self::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    protected function process()
    {
        $this->oOutput->writeln('Generating exports');

        $oNow = Factory::factory('DateTime');
        setAppSetting('data-export-cron-last-run', 'nails/module-admin', $oNow->format('Y-m-d H:i:s'));

        $oService  = Factory::service('DataExport', 'nails/module-admin');
        $oModel    = Factory::model('Export', 'nails/module-admin');
        $aRequests = $oModel->getAll(['where' => [['status', $oModel::STATUS_PENDING]]]);

        if (!empty($aRequests)) {

            Factory::helper('Inflector');
            $this->oOutput->writeln('Processing ' . count($aRequests) . ' ' . pluralise(count($aRequests), 'request'));
            $this->oOutput->writeln('Marking as <info>RUNNING</info>');
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

                    $this->oOutput->writeln(
                        'Starting <info>' . $oRequest->source . '->' . $oRequest->format . '</info> (<info>' . json_encode($oRequest->options) . '</info>)'
                    );
                    $oModel->setBatchDownloadId(
                        $oRequest->ids,
                        $oService->export($oRequest->source, $oRequest->format, $oRequest->options)
                    );
                    $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_COMPLETE);
                    $this->oOutput->writeln('Completed <info>' . $oRequest->source . '->' . $oRequest->format . '</info>');
                    $this->oOutput->writeln('Sending emails');

                    $oEmail
                        ->data([
                            'status' => $oModel::STATUS_COMPLETE,
                            'error'  => null,
                        ]);

                    foreach ($oRequest->recipients as $iRecipient) {
                        $oEmail->to($iRecipient)->send();
                    }

                } catch (\Exception $e) {
                    $this->executionFailed($e, $oRequest, $oModel, $oEmail);
                }
            }

        } else {
            $this->oOutput->writeln('Nothing to do');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Marks a request as failed, recording why it failed and informs the recipients
     *
     * @param \Exception                            $oException The exception which was thrown
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

        $this->oOutput->writeln('<error>' . get_class($oException) . ': ' . $oException->getMessage() . '</error>');
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
