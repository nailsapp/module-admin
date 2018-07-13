<?php

namespace Nails\Admin\Controller;

use Nails\Api\Controller\Base;

abstract class BaseApi extends Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Determines whether a user is authenticated to access these methods
     *
     * @param string $sHttpMethod The HTTP Method being used
     * @param string $sMethod     The method being called
     *
     * @return bool
     */
    public static function isAuthenticated($sHttpMethod = '', $sMethod = '')
    {
        return parent::isAuthenticated($sHttpMethod, $sMethod) && isAdmin();
    }
}
