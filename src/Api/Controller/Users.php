<?php

/**
 * Admin API end points: Users
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 *
 */

//  @todo (Pablo - 2018-06-25) - Deprecate this in favour of the endpoint supplied by module-auth

namespace Nails\Admin\Api\Controller;

use Nails\Factory;

class Users extends BaseApi
{
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Determines whether the user is authenticated or not
     *
     * @param string $sHttpMethod The HTTP Method protocol being used
     * @param string $sMethod     The controller method being executed
     *
     * @return bool
     */
    public static function isAuthenticated($sHttpMethod = '', $sMethod = '')
    {
        return parent::isAuthenticated($sHttpMethod, $sMethod) && isAdmin();
    }

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return \Nails\Api\Factory\ApiResponse
     */
    public function getSearch()
    {
        $oInput     = Factory::service('Input');
        $oUserModel = Factory::model('User', 'nails/module-auth');
        $oResults   = $oUserModel->search($oInput->get('term'), 1, 50);

        $oResponse = Factory::factory('ApiResponse', 'nails/module-api');
        $oResponse->setData(array_map([$this, 'formatObject'], $oResults->data));
        return $oResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * Formats search objects
     *
     * @param stdClass $oObj The object to format
     *
     * @return \stdClass
     */
    protected function formatObject($oObj)
    {
        return (object) [
            'id'          => $oObj->id,
            'email'       => $oObj->email,
            'first_name'  => $oObj->first_name,
            'last_name'   => $oObj->last_name,
            'gender'      => $oObj->gender,
            'profile_img' => cdnAvatar($oObj->id),
        ];
    }
}
