<?php

/**
 * The DataExport Process Cron task
 *
 * @package  Nails\Admin
 * @category Task
 */

namespace Nails\Admin\Cron\Task\DataExport;

use Nails\Cron\Task\Base;

/**
 * Class Process
 *
 * @package Nails\Admin\Cron\Task
 */
class Process extends Base
{
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
