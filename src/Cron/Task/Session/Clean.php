<?php

/**
 * The Session Clean Cron task
 *
 * @package  Nails\Admin
 * @category Task
 */

namespace Nails\Admin\Cron\Task\Session;

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
    const CRON_EXPRESSION = '*/5 * * * *';

    /**
     * The console command to execute
     *
     * @var string
     */
    const CONSOLE_COMMAND = 'admin:session:clean';
}
