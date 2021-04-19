<?php

namespace Nails\Admin\Model\Dashboard;

use Nails\Admin\Constants;
use Nails\Common\Model\Base;
use Nails\Config;

class Widget extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_dashboard_widget';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'DashboardWidget';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;
}
