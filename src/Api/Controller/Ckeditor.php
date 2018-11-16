<?php

/**
 * Admin API end points: CKEditor
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Controller\BaseApi;
use Nails\Factory;

class Ckeditor extends BaseApi
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
     * Returns the URL of the config to use for CKEditor instances
     * @return \Nails\Api\Factory\ApiResponse
     */
    public function getConfigs()
    {
        return Factory::factory('ApiResponse', 'nails/module-api')
                      ->setData([
                          'basic'   => $this->findConfig('ckeditor.config.basic.min.js'),
                          'default' => $this->findConfig('ckeditor.config.default.min.js'),
                      ]);
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for an app version of the file
     *
     * @param string $sFile The file name to search for
     *
     * @return string
     */
    protected function findConfig($sFile)
    {
        //  @todo (Pablo - 2018-07-13) - The paths and URLs should probably be determined by the Asset service
        if (file_exists(NAILS_APP_PATH . 'assets/build/js/' . $sFile)) {
            return site_url('assets/build/js/' . $sFile);
        } elseif (file_exists(NAILS_APP_PATH . 'assets/js/' . $sFile)) {
            return site_url('assets/js/' . $sFile);
        } else {
            return NAILS_ASSETS_URL . 'js/' . $sFile;
        }
    }
}
