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
        $aSources    = $oDataExport->getAllSources();
        $aFormats    = $oDataExport->getAllFormats();

        // --------------------------------------------------------------------------

        $oInput = Factory::service('Input');
        if ($oInput->post()) {

            try {

                $oFormValidation = Factory::service('FormValidation');
                $oFormValidation->set_rules('source', '', 'required');
                $oFormValidation->set_rules('format', '', 'required');
                $oFormValidation->set_message('required', lang('fv_required'));

                if (!$oFormValidation->run()) {
                    throw new \Exception(lang('fv_there_were_errors'));
                }

                //  Validate source
                $oSelectedSource = $oDataExport->getSourceBySlug($oInput->post('source'));
                if (empty($oSelectedSource)) {
                    throw new \Exception('Invalid data source');
                }

                //  Validate format
                $oSelectedFormat = $oDataExport->getFormatBySlug($oInput->post('format'));
                if (empty($oSelectedFormat)) {
                    throw new \Exception('Invalid data format');
                }

                //  Prepare options
                $aOptions = [];
                foreach ($oSelectedSource->options as $aOption) {
                    $sKey            = getFromArray('key', $aOption);
                    $aOptions[$sKey] = getFromArray($sKey, (array) $oInput->post('options'));
                }

                $oDataExportModel = Factory::model('Export', 'nailsapp/module-admin');
                $aData            = [
                    'source'  => $oSelectedSource->slug,
                    'options' => json_encode($aOptions),
                    'format'  => $oSelectedFormat->slug,
                ];
                if (!$oDataExportModel->create($aData)) {
                    throw new \Exception('Failed to schedule export.');
                }

                $oSession = Factory::service('Session', 'nailsapp/module-auth');
                $oSession->setFlashData('success', 'Export scheduled');
                redirect('admin/admin/utilities/export');

            } catch (\Exception $e) {
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

        $oModel  = Factory::model('Export', 'nailsapp/module-admin');
        $aRecent = $oModel->getAll([
            'where' => [['created_by', activeUser('id')]],
            'sort'  => [['created', 'desc']],
            'limit' => 10,
        ]);

        //  Pretty source labels, format labels, and options
        $aRecent = array_map(function ($oItem) use ($aSources, $aFormats) {

            //  Sources
            foreach ($aSources as $oSource) {
                if ($oSource->slug === $oItem->source) {
                    $oItem->source = $oSource->label;
                }
            }

            if (empty($oItem->source)) {
                $oItem->source = 'Unknown';
            }

            //  Formats
            foreach ($aFormats as $oFormat) {
                if ($oFormat->slug === $oItem->format) {
                    $oItem->format = $oFormat->label;
                }
            }

            if (empty($oItem->format)) {
                $oItem->format = 'Unknown';
            }

            //  Options
            $oOptions = json_decode($oItem->options);
            if ($oOptions) {
                $oItem->options = '<pre>' . json_encode($oOptions, JSON_PRETTY_PRINT) . '</pre>';
            } else {
                $oItem->options = '';
            }

            return $oItem;
        }, $aRecent);

        // --------------------------------------------------------------------------

        //  Cron running?
        $sLastRun   = appSetting('data-export-cron-last-run', 'nailsapp/module-admin');
        $bIsRunning = false;
        if ($sLastRun) {
            $oNow       = Factory::factory('DateTime');
            $oLastRun   = new \DateTime($sLastRun);
            $iDiff      = $oNow->getTimestamp() - $oLastRun->getTimestamp();
            $bIsRunning = $iDiff <= 300;
        }
        if (!$bIsRunning) {
            $this->data['warning'] = '<strong>The data export cron job is not running</strong>';
            $this->data['warning'] .= '<br>The cron job has not been executed within the past 5 minutes.';
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['page']->title = 'Export Data';
        $this->data['aSources']    = $aSources;
        $this->data['aFormats']    = $aFormats;
        $this->data['aRecent']     = $aRecent;

        // --------------------------------------------------------------------------

        //  Load assets
        $oAsset = Factory::service('Asset');
        $oAsset->load('nails.admin.export.min.js', 'NAILS');

        //  Load views
        Helper::loadView('export/index');
    }
}
