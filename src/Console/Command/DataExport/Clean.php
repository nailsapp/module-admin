<?php

namespace Nails\Admin\Console\Command\DataExport;

use Nails\Admin\Model\Export;
use Nails\Admin\Service\DataExport;
use Nails\Cdn\Service\Cdn;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Service\Database;
use Nails\Config;
use Nails\Console\Command\Base;
use Nails\Console\Exception\ConsoleException;
use Nails\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Clean
 *
 * @package Nails\Admin\Console\Command\DataExport
 */
class Clean extends Base
{
    /**
     * How long the expiring URL should be valid for
     *
     * @var int
     */
    const EXPORT_TTL = 3600;

    // --------------------------------------------------------------------------

    /**
     * The DataExport service
     *
     * @var DataExport
     */
    protected $oExportService;

    // --------------------------------------------------------------------------

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:dataexport:clean')
            ->setDescription('Cleans old data exports according to data retention rules');
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

            $this->banner('Data Export Clean');

            $iRetention = Config::get('ADMIN_DATA_EXPORT_RETENTION', 60);
            if ($iRetention) {

                $oOutput->writeln('Retention policy: <info>' . $iRetention . ' minutes</info>');

                /** @var Database $oDb */
                $oDb = Factory::service('Database');
                /** @var \DateTime $oNow */
                $oNow = Factory::factory('DateTime');
                /** @var Export $oModel */
                $oModel = Factory::model('Export', 'nails/module-admin');
                /** @var Cdn $oCdn */
                $oCdn = Factory::service('Cdn', \Nails\Cdn\Constants::MODULE_SLUG);

                $oNow->sub(new \DateInterval('PT' . $iRetention . 'M'));
                $oOutput->writeln('Cleaning items older than <comment>' . $oNow->format('Y-m-d H:i:s') . '</comment>');

                $aToClean = $oModel->getAll([
                    'where' => [
                        ['modified <', $oNow->format('Y-m-d H:i:s')],
                    ],
                ]);

                if (!empty($aToClean)) {

                    $oOutput->writeln('Cleaning <info>' . count($aToClean) . '</info> items');
                    foreach ($aToClean as $oExport) {
                        try {

                            $oDb->trans_begin();
                            $oOutput->write('Cleaning export <info>#' . $oExport->id . '</info>... ');

                            $oModel->delete($oExport->id);

                            if (!empty($oExport->download_id)) {
                                if (!$oCdn->objectDestroy($oExport->download_id)) {
                                    throw new NailsException(
                                        'Failed to delete object. ' . $oCdn->lastError()
                                    );
                                }
                            }

                            $oDb->trans_commit();
                            $oOutput->writeln('<info>done</info>');

                        } catch (\Exception $e) {
                            $oDb->trans_rollback();
                            $oOutput->writeln('<error>' . $e->getMessage() . '</error>');
                        }
                    }

                } else {
                    $oOutput->writeln('Nothing to clean');
                }

            } else {
                $oOutput->writeln('Data Export cleanup disabled');
            }

        } catch (ConsoleException $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        //  And we're done
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');

        return self::EXIT_CODE_SUCCESS;
    }
}
