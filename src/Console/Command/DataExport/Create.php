<?php

namespace Nails\Admin\Console\Command\DataExport;

use Nails\Admin\Exception\Console\ControllerExistsException;
use Nails\Common\Exception\NailsException;
use Nails\Console\Command\BaseMaker;
use Nails\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends BaseMaker
{
    const RESOURCE_PATH = NAILS_PATH . 'module-admin/resources/console/';
    const EXPORT_PATH   = NAILS_APP_PATH . 'src/DataExport/Source/';

    // --------------------------------------------------------------------------

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('make:admin:dataexport')
            ->setDescription('Creates a new Admin DataExport Source')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Define the name of the data export'
            )
            ->addArgument(
                'description',
                InputArgument::OPTIONAL,
                'Define the description of the data export'
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
            //  Ensure the paths exist
            $this->createPath(self::EXPORT_PATH);
            //  Create the DataExport source
            $this->createSource();
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

    /**
     * Create the Model
     *
     * @return void
     * @throws \Exception
     */
    private function createSource(): void
    {
        $aFields               = $this->getArguments();
        $aFields['CLASS_NAME'] = str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z ]/', '', $aFields['NAME'])));
        $aFields['FILENAME']   = strtolower(url_title($aFields['NAME']));

        try {

            $this->oOutput->write('Creating DataExport Source <comment>' . $aFields['CLASS_NAME'] . '</comment>... ');

            //  Check for existing DataExport source
            $sPath = static::EXPORT_PATH . $aFields['CLASS_NAME'] . '.php';
            if (file_exists($sPath)) {
                throw new ControllerExistsException(
                    'DataExport Source "' . $aFields['CLASS_NAME'] . '" exists already at path "' . $sPath . '"'
                );
            }

            $this->createFile($sPath, $this->getResource('template/data_export_source.php', $aFields));
            $aCreated[] = $sPath;
            $this->oOutput->writeln('<info>done!</info>');

        } catch (\Exception $e) {
            $this->oOutput->writeln('<error>failed!</error>');
            throw new NailsException($e->getMessage());
        }
    }
}
