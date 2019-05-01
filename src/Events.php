<?php

/**
 * The class provides a summary of the events fired by this module
 *
 * @package     Nails
 * @subpackage  module-common
 * @category    Events
 * @author      Nails Dev Team
 */

namespace Nails\Admin;

use Nails\Common\Events\Base;

class Events extends Base
{
    /**
     * Fired when admin starts to load.
     */
    const ADMIN_STARTUP = 'ADMIN:STARTUP';

    /**
     * Fired when admin is ready but before the controller is executed
     */
    const ADMIN_READY = 'ADMIN:READY';

    // --------------------------------------------------------------------------

    /**
     * Returns the namespace for events fired by this module
     *
     * @return stirng
     */
    public static function getEventNamespace(): string
    {
        return 'nails/module-admin';
    }
}
