<?php

/**
 * The DataExport Cron task
 *
 * @package  Nails\Admin
 * @category Task
 */

namespace Nails\Admin\Cron\Task;

use Nails\Cron\Task\Base;

/**
 * Class DataExport
 *
 * @package Nails\Admin\Cron\Task
 */
class DataExport extends Base
{
    /**
     * The task description
     *
     * @var string
     */
    const DESCRIPTION = 'Processes any pending data export requests';

    /**
     * The cron expression of when to run
     *
     * @var string
     */
    const CRON_EXPRESSION = '* * * * *';

    /**
     * The console command to execute
     *
     * @var string
     */
    const CONSOLE_COMMAND = 'admin:dataexport:process';
}
