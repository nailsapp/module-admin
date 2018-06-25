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

use Nails\Api\Controller\Base;
use Nails\Api\Exception\ApiException;
use Nails\Factory;

class Users extends Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    public function __construct($oApiRouter)
    {
        parent::__construct($oApiRouter);
        if (!isAdmin()) {
            $oHttpCodes = Factory::service('HttpCodes');
            throw new ApiException(
                'You do not have permission to access this resource',
                $oHttpCodes::STATUS_UNAUTHORIZED
            );
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return array
     */
    public function getSearch()
    {
        $oInput     = Factory::service('Input');
        $oUserModel = Factory::model('User', 'nailsapp/module-auth');
        $oResults   = $oUserModel->search($oInput->get('term'), 1, 50);

        $oResponse = Factory::factory('ApiResponse', 'nailsapp/module-api');
        $oResponse->setData(array_map([$this, 'formatObject'], $oResults->data));
        return $oResponse;
    }

    // --------------------------------------------------------------------------

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
