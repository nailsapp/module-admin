<?php

namespace Nails\Admin\Console\Command\DataExport;

use Nails\Admin\Service\DataExport;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Console\Command\Base;
use Nails\Console\Exception\ConsoleException;
use Nails\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Run
 *
 * @package Nails\Admin\Console\Command\DataExport
 */
class Run extends Base
{
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
            ->setName('admin:dataexport:run')
            ->setDescription('Generates a new report')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'The source report\'s slug, see <info>admin:dataexport:list</info>.'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The format to export as, see <info>admin:dataexport:list</info>.'
            )
            ->addOption(
                'opt',
                'o',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Options to pass to the report'
            );
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

            /** @var DataExport $oExportService */
            $this->oExportService = Factory::service('DataExport', 'nails/module-admin');

            $this->banner('Nails Admin Data Export: Run');
            $this->process();

        } catch (\Exception $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        return self::EXIT_CODE_SUCCESS;
    }

    // -------------------------

    /**
     * Processes the requested export
     *
     * @throws ConsoleException
     * @throws FactoryException
     * @throws NailsException
     */
    protected function process()
    {
        /** @var DataExport $oExportService */
        $oExportService = Factory::service('DataExport', 'nails/module-admin');
        $sSlug          = $this->oInput->getArgument('source');

        $oSource = $oExportService->getSourceBySlug($sSlug);
        if (empty($oSource)) {
            throw new ConsoleException('"' . $sSlug . '" is not a valid data export source.');
        }

        $aUserOptions = [];
        foreach ($this->oInput->getOption('opt') as $sOption) {
            preg_match('/([^=]+)=(.*)/', $sOption, $aMatches);
            if (!empty($aMatches)) {
                $aUserOptions[$aMatches[1]] = $aMatches[2];
            }
        }

        $aOptions = [];
        foreach ($oSource->options as $aOption) {
            $aOptions[$aOption['key']] = getFromArray($aOption['key'], $aUserOptions);;
        }

        $aOptions = array_filter($aOptions);

        if (empty($aOptions)) {
            $this->oOutput->writeln('Beginning report generation...');
        } else {
            $this->oOutput->writeln('Beginning report generation using the following options:');
            $this->keyValueList($aOptions);
        }

        $iResult = $oExportService->export(
            $oSource->slug,
            $this->oInput->getOption('format') ?? $this->oExportService::DEFAULT_FORMAT,
            $aOptions
        );

        $this->oOutput->writeln('done!');
        $this->oOutput->writeln('Download from: <info>' . cdnServe($iResult, true) . '</info>');
        $this->oOutput->writeln('');
    }
}
