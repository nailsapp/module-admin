<?php

/**
 * This class provides basic functionality for standard admin sections which are powered by the model.
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

//  @todo Handle models which are/are not able to be restored
//  @todo Populate fields

namespace Nails\Admin\Controller;

use Nails\Factory;
use Nails\Admin\Helper;
use Nails\Common\Exception\NailsException;

class DefaultController extends Base
{
    /**
     * The following constants are used to build the controller
     */
    const CONFIG_MODEL_NAME        = '';
    const CONFIG_MODEL_PROVIDER    = '';
    const CONFIG_TITLE_SINGLE      = '';
    const CONFIG_TITLE_PLURAL      = '';
    const CONFIG_SIDEBAR_GROUP     = '';
    const CONFIG_PERMISSION        = '';
    const CONFIG_BASE_URL          = '';
    const CONFIG_SORT_OPTIONS      = '';

    // --------------------------------------------------------------------------

    /**
     * DefaultController constructor.
     * @throws NailsException
     */
    public function __construct()
    {
        parent::__construct();

        //  Ensure required constants are set
        $aConstants = array(
            'CONFIG_MODEL_NAME',
            'CONFIG_MODEL_PROVIDER',
            'CONFIG_TITLE_SINGLE',
            'CONFIG_TITLE_PLURAL',
            'CONFIG_SIDEBAR_GROUP',
            'CONFIG_PERMISSION',
            'CONFIG_BASE_URL',
            'CONFIG_SORT_OPTIONS'
        );
        foreach ($aConstants as $sConstant) {
            if (empty(constant('static::' . $sConstant))) {
                throw new NailsException(
                    'The constant "static::' . $sConstant . '" must be set in ' . get_called_class()
                );
            }
        }

        //  And for the views
        $this->data['CONFIG_MODEL_NAME']     = static::CONFIG_MODEL_NAME;
        $this->data['CONFIG_MODEL_PROVIDER'] = static::CONFIG_MODEL_PROVIDER;
        $this->data['CONFIG_TITLE_SINGLE']   = static::CONFIG_TITLE_SINGLE;
        $this->data['CONFIG_TITLE_PLURAL']   = static::CONFIG_TITLE_PLURAL;
        $this->data['CONFIG_SIDEBAR_GROUP']  = static::CONFIG_SIDEBAR_GROUP;
        $this->data['CONFIG_PERMISSION']     = static::CONFIG_PERMISSION;
        $this->data['CONFIG_BASE_URL']       = static::CONFIG_BASE_URL;
        $this->data['CONFIG_SORT_OPTIONS']   = static::CONFIG_SORT_OPTIONS;
    }

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     * @throws NailsException
     */
    public static function announce()
    {
        $sSidebarGroup  = static::CONFIG_SIDEBAR_GROUP;
        $sPermissionStr = static::CONFIG_PERMISSION;
        $sTitlePlural   = static::CONFIG_TITLE_PLURAL;

        if (empty($sSidebarGroup)) {
            throw new NailsException('Child controller must define static::CONFIG_SIDEBAR_GROUP.');
        }

        if (empty($sPermissionStr)) {
            throw new NailsException('Child controller must define static::CONFIG_PERMISSION.');
        }

        if (empty($sSidebarGroup)) {
            throw new NailsException('Child controller must define static::CONFIG_TITLE_PLURAL.');
        }
        
        // --------------------------------------------------------------------------
        
        $navGroup = Factory::factory('Nav', 'nailsapp/module-admin');
        $navGroup->setLabel($sSidebarGroup);
        if (userHasPermission('admin:' . $sPermissionStr . ':browse')) {
            $navGroup->addAction('Manage ' . $sTitlePlural, 'index');
        }

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     * @return array
     */
    public static function permissions()
    {
        $aPermissions = parent::permissions();

        $aPermissions['browse']  = 'Can browse items';
        $aPermissions['create']  = 'Can create items';
        $aPermissions['edit']    = 'Can edit items';
        $aPermissions['delete']  = 'Can delete items';
        $aPermissions['restore'] = 'Can restore items';

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Browse all items
     * @return void
     */
    public function index()
    {
        if (!userHasPermission('admin:' . static::CONFIG_PERMISSION . ':browse')) {
            unauthorised();
        }

        $oInput     = Factory::service('Input');
        $oItemModel = Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );

        $sAlias     = $oItemModel->getTableAlias();
        $iPage      = $oInput->get('page')      ? $oInput->get('page')      : 0;
        $iPerPage   = $oInput->get('perPage')   ? $oInput->get('perPage')   : 50;
        $sSortOn    = $oInput->get('sortOn')    ? $oInput->get('sortOn')    : $sAlias . '.label';
        $sSortOrder = $oInput->get('sortOrder') ? $oInput->get('sortOrder') : 'asc';
        $sKeywords  = $oInput->get('keywords');

        $aSortCol     = array();
        $aSortConfig  = json_decode(static::CONFIG_SORT_OPTIONS) ?: array();
        foreach ($aSortConfig as $sColumn => $sLabel) {
            if (strpos($sColumn, '.') === false) {
                $aSortCol[$sAlias . '.' . $sColumn] = $sLabel;
            } else {
                $aSortCol[$sColumn] = $sLabel;
            }
        }

        $aData = array(
            'sort' => array(
                array($sSortOn, $sSortOrder)
            ),
            'keywords' => $sKeywords
        );

        // --------------------------------------------------------------------------

        $iTotalRows               = $oItemModel->countAll($aData);
        $this->data['items']      = $oItemModel->getAll($iPage, $iPerPage, $aData);
        $this->data['search']     = Helper::searchObject(true, $aSortCol, $sSortOn, $sSortOrder, $iPerPage, $sKeywords);
        $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);

        // --------------------------------------------------------------------------

        if (userHasPermission('admin:' . static::CONFIG_PERMISSION . ':create')) {
            Helper::addHeaderButton('admin/' . static::CONFIG_BASE_URL . '/create', 'Create');
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = static::CONFIG_TITLE_PLURAL . ' &rsaquo; Manage';
        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new item
     * @return void
     */
    public function create()
    {
        if (!userHasPermission('admin:' . static::CONFIG_PERMISSION . ':create')) {
            unauthorised();
        }

        $oDb        = Factory::service('Database');
        $oInput     = Factory::service('Input');
        $oItemModel = Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );

        if ($oInput->post()) {
            if ($this->runFormValidation()) {
                try {
                    $oDb->trans_begin();
                    if (!$oItemModel->create($this->getPostObject())) {
                        throw new NailsException('Failed to create item.' . $oItemModel->lastError());
                    }

                    $oDb->trans_commit();
                    $oSession = Factory::service('Session', 'nailsapp/module-auth');
                    $oSession->set_flashdata('success', 'Item created successfully.');
                    redirect('admin/' . static::CONFIG_BASE_URL);

                } catch (\Exception $e) {
                    $oDb->trans_rollback();
                    $this->data['error'] = $e->getMessage();
                }

            } else {
                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        //  View Data & Assets
        $this->loadEditViewData();

        $this->data['page']->title = static::CONFIG_TITLE_SINGLE . ' &rsaquo; Create';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit an existing item
     * @return void
     */
    public function edit()
    {
        if (!userHasPermission('admin:' . static::CONFIG_PERMISSION . ':edit')) {
            unauthorised();
        }

        $oDb        = Factory::service('Database');
        $oUri       = Factory::service('Uri');
        $oInput     = Factory::service('Input');
        $oItemModel = Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );
        $iItemId    = (int) $oUri->segment(5);
        $oItem      = $oItemModel->getById($iItemId);

        if (empty($oItem)) {
            show_404();
        }

        // --------------------------------------------------------------------------

        if ($oInput->post()) {
            if ($this->runFormValidation()) {
                try {
                    $oDb->trans_begin();
                    if (!$oItemModel->update($iItemId, $this->getPostObject())) {
                        throw new NailsException('Failed to update item.' . $oItemModel->lastError());
                    }

                    $oDb->trans_commit();
                    $oSession = Factory::service('Session', 'nailsapp/module-auth');
                    $oSession->set_flashdata('success', 'Item updated successfully.');
                    redirect('admin/' . static::CONFIG_BASE_URL);

                } catch (\Exception $e) {
                    $oDb->trans_rollback();
                    $this->data['error'] = $e->getMessage();
                }

            } else {
                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        //  View Data & Assets
        $this->loadEditViewData($oItem);

        $this->data['page']->title = static::CONFIG_TITLE_SINGLE . ' &rsaquo; Edit';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     * @return bool
     */
    private function runFormValidation()
    {
        $oFormValidation = Factory::service('FormValidation');

        $aRules = array(
            'label' => 'xss_clean|required'
        );

        $aRulesFormValidation = array();
        foreach ($aRules as $sKey => $sRules) {
            $aRulesFormValidation[] = array(
                'field' => $sKey,
                'label' => '',
                'rules' => $sRules
            );
        }

        $oFormValidation->set_rules($aRulesFormValidation);

        $oFormValidation->set_message('required', lang('fv_required'));

        return $oFormValidation->run();
    }

    // --------------------------------------------------------------------------

    /**
     * Load data for the edit/create view
     * @param  \stdClass $oItem The main item object
     * @return void
     */
    private function loadEditViewData($oItem = null)
    {
        $this->data['item'] = $oItem;
    }

    // --------------------------------------------------------------------------

    /**
     * Extract data from post variable
     * @return array
     */
    private function getPostObject()
    {
        $oInput = Factory::service('Input');
        return array(
            'label' => $oInput->post('label'),
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an item
     * @return void
     */
    public function delete()
    {
        if (!userHasPermission('admin:' . static::CONFIG_PERMISSION . ':delete')) {
            unauthorised();
        }

        $oDb        = Factory::service('Database');
        $oUri       = Factory::service('Uri');
        $oItemModel = Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );
        $iItemId    = (int) $oUri->segment(5);
        $oItem      = $oItemModel->getById($iItemId);

        if (empty($oItem)) {
            show_404();
        }

        try {
            if (!$oItemModel->delete($iItemId)) {
                throw new NailsException('Failed to delete item.' . $oItemModel->lastError());
            }

            $sRestoreLink = anchor('admin/' . static::CONFIG_BASE_URL . '/restore/' . $iItemId, 'Restore?');

            $oDb->trans_commit();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('success', 'Item deleted successfully. ' . $sRestoreLink);
            redirect('admin/' . static::CONFIG_BASE_URL);

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $this->data['error'] = $e->getMessage();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an item
     * @return void
     */
    public function restore()
    {
        if (!userHasPermission('admin:' . static::CONFIG_PERMISSION . ':restore')) {
            unauthorised();
        }

        $oUri       = Factory::service('Uri');
        $oDb        = Factory::service('Database');
        $oItemModel = Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );
        $iItemId    = (int) $oUri->segment(5);
        $oItem      = $oItemModel->getAll(
            null,
            null,
            array(
                'where' => array(
                    array(
                        'id', $iItemId
                    )
                )
            ),
            true
        );

        if (empty($oItem[0])) {
            show_404();
        }

        try {
            if (!$oItemModel->restore($iItemId)) {
                throw new NailsException('Failed to restore item.' . $oItemModel->lastError());
            }

            $oDb->trans_commit();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('success', 'Item restored successfully.');
            redirect('admin/' . static::CONFIG_BASE_URL);

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $this->data['error'] = $e->getMessage();
        }
    }
}
