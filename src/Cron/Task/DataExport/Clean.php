<?php

/**
 * The DataExport Clean Cron task
 *
 * @package  Nails\Admin
 * @category Task
 */

namespace Nails\Admin\Cron\Task\DataExport;

use Nails\Cron\Task\Base;

/**
 * Class Clean
 *
 * @package Nails\Admin\Cron\Task
 */
class Clean extends Base
{
    /**
     * The cron expression of when to run
     *
     * @var string
     */
    const CRON_EXPRESSION = '*/15 * * * *';

    /**
     * The console command to execute
     *
     * @var string
     */
    const CONSOLE_COMMAND = 'admin:dataexport:clean';
}
