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
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Factory;

/**
 * Class Ckeditor
 *
 * @package Nails\Admin\Api\Controller
 */
class Ckeditor extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    /**
     * Returns the URL of the config to use for CKEditor instances
     *
     * @return \Nails\Api\Factory\ApiResponse
     */
    public function getConfigs()
    {
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData([
                'basic'   => $this->findConfig('ckeditor.config.basic.min.js'),
                'default' => $this->findConfig('ckeditor.config.default.min.js'),
            ]);

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for an app version of the file
     *
     * @param string $sFile The file name to search for
     *
     * @return string
     */
    protected function findConfig($sFile): string
    {
        //  @todo (Pablo - 2018-07-13) - The paths and URLs should probably be determined by the Asset service
        if (file_exists(NAILS_APP_PATH . 'assets/build/js/' . $sFile)) {
            return siteUrl('assets/build/js/' . $sFile);

        } elseif (file_exists(NAILS_APP_PATH . 'assets/js/' . $sFile)) {
            return siteUrl('assets/js/' . $sFile);

        } else {
            return NAILS_ASSETS_URL . 'js/' . $sFile;
        }
    }
}
