<?php

namespace Nails\Admin\Console\Command\Controller;

use Nails\Admin\Exception\Console\ControllerExistsException;
use Nails\Console\Command\BaseMaker;
use Nails\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends BaseMaker
{
    const RESOURCE_PATH   = NAILS_PATH . 'module-admin/resources/console/';
    const CONTROLLER_PATH = FCPATH . 'application/modules/admin/controllers/';

    // --------------------------------------------------------------------------

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('make:controller:admin');
        $this->setDescription('Creates a new Admin controller');
        $this->addArgument(
            'modelName',
            InputArgument::OPTIONAL,
            'Define the name of the model on which to base the controller'
        );
        $this->addArgument(
            'modelProvider',
            InputArgument::OPTIONAL,
            'Define the provider of the model',
            'app'
        );
        $this->addOption(
            'skip-check',
            null,
            InputOption::VALUE_OPTIONAL,
            'Skip model check'
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Executes the app
     *
     * @param  InputInterface $oInput The Input Interface provided by Symfony
     * @param  OutputInterface $oOutput The Output Interface provided by Symfony
     * @return int
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::execute($oInput, $oOutput);

        // --------------------------------------------------------------------------

        try {
            //  Ensure the paths exist
            $this->createPath(self::CONTROLLER_PATH);
            //  Create the controller
            $this->createController();
        } catch (\Exception $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                $e->getMessage()
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
     * @throws \Exception
     * @return void
     */
    private function createController()
    {
        $aFields = $this->getArguments();

        try {

            //  Validate model exists by attempting to load it
            if (!stringToBoolean($this->oInput->getOption('skip-check'))) {
                Factory::model($aFields['MODEL_NAME'], $aFields['MODEL_PROVIDER']);
            }

            //  Check for existing controller
            $sPath  = static::CONTROLLER_PATH . strtolower($aFields['MODEL_NAME']) . '.php';
            if (file_exists($sPath)) {
                throw new ControllerExistsException(
                    'Controller "' . $aFields['MODEL_NAME'] . '" exists already at path "' . $sPath . '"'
                );
            }

            $this->createFile($sPath, $this->getResource('template/controller.php', $aFields));

        } catch (ControllerExistsException $e) {
            //  Do not clean up (delete existing controller)!
            throw new \Exception($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            //  Clean up
            if (!empty($sPath)) {
                @unlink($sPath);
            }
            throw new \Exception($e->getMessage());
        }
    }
}
