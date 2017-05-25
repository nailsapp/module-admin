<?php

/**
 * This class renders Admin Utilities
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;
use Nails\Factory;

/**
 * Class Utilities
 * @package Nails\Admin\Admin
 */
class Utilities extends Base
{
    protected $aExportSources;
    protected $aExportFormats;

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     * @return \stdClass
     */
    public static function announce()
    {
        $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
        $oNavGroup->setLabel('Utilities');
        $oNavGroup->setIcon('fa-sliders');

        if (userHasPermission('admin:admin:utilities:rewriteRoutes')) {
            $oNavGroup->addAction('Rewrite Routes', 'rewrite_routes');
        }

        if (userHasPermission('admin:admin:utilities:export')) {
            $oNavGroup->addAction('Export Data', 'export');
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        $aPermissions                  = parent::permissions();
        $aPermissions['rewriteRoutes'] = 'Can Rewrite Routes';
        $aPermissions['export']        = 'Can Export Data';

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Rewrite the app's routes
     * @return void
     */
    public function rewrite_routes()
    {
        if (!userHasPermission('admin:admin:utilities:rewriteRoutes')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oInput = Factory::service('Input');
        if ($oInput->post('go')) {
            $oRoutesModel = Factory::model('Routes');
            if ($oRoutesModel->update()) {
                $this->data['success'] = 'Routes rewritten successfully.';
            } else {
                $this->data['error'] = 'There was a problem writing the routes. ';
                $this->data['error'] .= $oRoutesModel->lastError();
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('rewriteRoutes');
    }

    // --------------------------------------------------------------------------

    /**
     * Export data
     * @return void
     */
    public function export()
    {
        if (!userHasPermission('admin:admin:utilities:export')) {
            unauthorised();
        }

        $oDataExport = Factory::service('DataExport', 'nailsapp/module-admin');
        $aSources         = $oDataExport->getAllSources();
        $aFormats         = $oDataExport->getAllFormats();

        // --------------------------------------------------------------------------

        $oInput = Factory::service('Input');
        if ($oInput->post()) {

            try {

                $oFormValidation = Factory::service('FormValidation');
                $oFormValidation->set_rules('source', '', 'xss_clean|required');
                $oFormValidation->set_rules('format', '', 'xss_clean|required');
                $oFormValidation->set_message('required', lang('fv_required'));

                if (!$oFormValidation->run()) {
                    throw new \Exception(lang('fv_there_were_errors'));
                }

                $oDataExport->export(
                    $oInput->post('source'),
                    $oInput->post('format'),
                    [],
                    true
                );

                //  Kill the script so that the output class doesn't set additional headers
                die();

            } catch (\Exception $e) {
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['page']->title = 'Export Data';
        $this->data['sources']     = $aSources;
        $this->data['formats']     = $aFormats;

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('export/index');
    }
}
