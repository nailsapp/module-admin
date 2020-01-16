<?php

namespace Nails\Admin\Console\Command\DataExport;

use Nails\Admin\Resource\DataExport\Format;
use Nails\Admin\Resource\DataExport\Source;
use Nails\Admin\Service\DataExport;
use Nails\Console\Command\Base;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListReports
 *
 * @package Nails\Admin\Console\Command\DataExport
 */
class ListReports extends Base
{
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:dataexport:list')
            ->setDescription('List available data reports');
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

            $this->banner('Nails Admin Data Export: List');

            /** @var DataExport $oExportService */
            $oExportService = Factory::service('DataExport', 'nails/module-admin');

            $this
                ->listSources($oExportService)
                ->listFormats($oExportService);

        } catch (\Exception $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        return self::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    /**
     * Lists all sources
     *
     * @param DataExport $oExportService The DataExport service
     *
     * @return $this
     */
    protected function listSources(DataExport $oExportService): ListReports
    {
        $this->oOutput->writeln('Data Sources');
        $this->oOutput->writeln('------------');

        /** @var Source $oSource */
        foreach ($oExportService->getAllSources() as $oSource) {
            $this->oOutput->writeln('<info>' . $oSource->label . ' [' . $oSource->slug . ']</info>');
            $this->oOutput->writeln($oSource->description);

            $aKeyValues = [];
            foreach ($oSource->options as $aOption) {

                $sKey   = '@option <info>--opt="' . $aOption['key'] . '=<comment>{value}</comment>"</info>';
                $sLabel = $aOption['label'] . (!empty($aOption['default']) ? ' <comment>(default: ' . $aOption['default'] . ')</comment>' : '');

                $aKeyValues[$sKey] = $sLabel;
            }
            $this->keyValueList($aKeyValues, false, false, '');
            $this->oOutput->writeln('');
        }

        $this->oOutput->writeln('');
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Lists all formats
     *
     * @param DataExport $oExportService The DataExport service
     *
     * @return $this
     */
    protected function listFormats(DataExport $oExportService): ListReports
    {
        $this->oOutput->writeln('Data Formats');
        $this->oOutput->writeln('------------');

        /** @var Format $oFormat */
        foreach ($oExportService->getAllFormats() as $oFormat) {
            $this->oOutput->writeln('<info>' . $oFormat->label . ' [' . $oFormat->slug . ']</info>');
            $this->oOutput->writeln($oFormat->description);
            $this->oOutput->writeln('');
        }

        $this->oOutput->writeln('');
        return $this;
    }
}
