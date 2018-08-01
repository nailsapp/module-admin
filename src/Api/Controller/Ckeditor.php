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
    /**
     * Returns the URL of the config to use for CKEditor instances
     * @return \Nails\Api\Factory\ApiResponse
     */
    public function getConfigs()
    {
        return Factory::factory('ApiResponse', 'nailsapp/module-api')
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
        if (file_exists(FCPATH . 'assets/build/js/' . $sFile)) {
            return site_url('assets/build/js/' . $sFile);
        } elseif (file_exists(FCPATH . 'assets/js/' . $sFile)) {
            return site_url('assets/js/' . $sFile);
        } else {
            return NAILS_ASSETS_URL . 'js/' . $sFile;
        }
    }
}