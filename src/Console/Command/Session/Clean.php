<?php

namespace Nails\Admin\Console\Command\Session;

use Nails\Admin\Constants;
use Nails\Admin\Model\Export;
use Nails\Admin\Model\Session;
use Nails\Admin\Service\DataExport;
use Nails\Cdn\Service\Cdn;
use Nails\Common\Exception\NailsException;
use Nails\Common\Service\Database;
use Nails\Console\Command\Base;
use Nails\Console\Exception\ConsoleException;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Clean
 *
 * @package Nails\Admin\Console\Command\Session
 */
class Clean extends Base
{
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:session:clean')
            ->setDescription('Cleans old admin sessions');
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

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Session $oModel */
        $oModel = Factory::model('Session', Constants::MODULE_SLUG);

        // --------------------------------------------------------------------------

        try {

            $this->banner('Admin Session: Clean');

            $oDb->query(sprintf(
                'DELETE FROM `%s` WHERE heartbeat < DATE_SUB(NOW(), INTERVAL 1 HOUR)',
                $oModel->getTableName()
            ));

        } catch (ConsoleException $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        //  And we're done
        $oOutput->writeln('Complete!');

        return self::EXIT_CODE_SUCCESS;
    }
}
