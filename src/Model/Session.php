<?php

namespace Nails\Admin\Model;

use Nails\Admin\Constants;
use Nails\Common\Exception\ModelException;
use Nails\Common\Model\Base;
use Nails\Config;

class Session extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_session';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'Session';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;

    // --------------------------------------------------------------------------

    /**
     * Session constructor.
     *
     * @throws ModelException
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->hasOne('user', 'User', \Nails\Auth\Constants::MODULE_SLUG);
    }
}
