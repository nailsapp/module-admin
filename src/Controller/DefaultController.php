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

//  @todo - Ensure search still works
//  @todo - Ensure sorting stillworks
//  @todo - Render fields properly

namespace Nails\Admin\Controller;

use Nails\Admin\Helper;
use Nails\Common\Exception\NailsException;
use Nails\Factory;

class DefaultController extends Base
{
    /**
     * The following constants are used to build the controller
     */

    /**
     * The model to use for this admin view (and which module provides it)
     */
    const CONFIG_MODEL_NAME     = '';
    const CONFIG_MODEL_PROVIDER = '';

    /**
     * The permission string to use when checking permissions; if not provided then no permissions required
     */
    const CONFIG_PERMISSION = '';

    /**
     * The singular and plural name of the item being managed; defaults to the model name
     */
    const CONFIG_TITLE_SINGLE = '';
    const CONFIG_TITLE_PLURAL = '';

    /**
     * Where to display this controller in the admin sidebar; defaults to CONFIG_TITLE_PLURAL
     */
    const CONFIG_SIDEBAR_GROUP = '';

    /**
     * the base URL for this controller
     */
    const CONFIG_BASE_URL = '';

    /**
     * The sorting options to give the user on the index view
     */
    const CONFIG_SORT_OPTIONS = [
        'label'    => 'Label',
        'created'  => 'Created',
        'modified' => 'Modified',
    ];

    /**
     * The fields to show on the index view.
     */
    const CONFIG_INDEX_FIELDS = [
        'label'    => 'Label',
        'modified' => 'Modified',
    ];

    /**
     * Additional data to pass into the getAll call on the index view
     */
    const CONFIG_INDEX_DATA = [];

    /**
     * The fields to ignore on the create/edit view
     */
    const CONFIG_EDIT_IGNORE_FIELDS = [
        'id',
        'slug',
        'is_deleted',
        'created',
        'created_by',
        'modified',
        'modified_by',
    ];

    /**
     * Additional data to pass into the getAll call on the edit view
     */
    const CONFIG_EDIT_DATA = [];

    // --------------------------------------------------------------------------

    /**
     * Contains the configs for this controller
     * @var array
     */
    protected $aConfig;

    // --------------------------------------------------------------------------

    /**
     * DefaultController constructor.
     * @throws NailsException
     */
    public function __construct()
    {
        parent::__construct();

        $this->aConfig = static::getConfig();

        // --------------------------------------------------------------------------

        //  Model specific fields
        $oItemModel = Factory::model(
            $this->aConfig['MODEL_NAME'],
            $this->aConfig['MODEL_PROVIDER']
        );

        $this->aConfig['CAN_RESTORE'] = !$oItemModel->isDestructiveDelete();
        $this->aConfig['FIELDS']      = $oItemModel->describeFields();

        $this->data['CONFIG'] = $this->aConfig;
    }

    // --------------------------------------------------------------------------

    public static function getConfig()
    {
        //  Ensure required constants are set
        $aConstants = [
            'CONFIG_MODEL_NAME',
            'CONFIG_MODEL_PROVIDER',
        ];
        foreach ($aConstants as $sConstant) {
            if (empty(constant('static::' . $sConstant))) {
                throw new NailsException(
                    'The constant "static::' . $sConstant . '" must be set in ' . get_called_class()
                );
            }
        }

        //  Build the config array
        $aConfig                   = [];
        $aConfig['MODEL_NAME']     = static::CONFIG_MODEL_NAME;
        $aConfig['MODEL_PROVIDER'] = static::CONFIG_MODEL_PROVIDER;

        //  Define remaining "constants"
        $aConfig['PERMISSION']         = static::CONFIG_PERMISSION;
        $aConfig['TITLE_SINGLE']       = static::CONFIG_TITLE_SINGLE;
        $aConfig['TITLE_PLURAL']       = static::CONFIG_TITLE_PLURAL;
        $aConfig['SIDEBAR_GROUP']      = static::CONFIG_SIDEBAR_GROUP;
        $aConfig['BASE_URL']           = static::CONFIG_BASE_URL;
        $aConfig['SORT_OPTIONS']       = static::CONFIG_SORT_OPTIONS;
        $aConfig['INDEX_FIELDS']       = static::CONFIG_INDEX_FIELDS;
        $aConfig['INDEX_DATA']         = static::CONFIG_INDEX_DATA;
        $aConfig['EDIT_IGNORE_FIELDS'] = static::CONFIG_EDIT_IGNORE_FIELDS;
        $aConfig['EDIT_DATA']          = static::CONFIG_EDIT_DATA;

        //  Set defaults where appropriate
        if (empty($aConfig['TITLE_SINGLE'])) {
            $aConfig['TITLE_SINGLE'] = preg_replace('/([a-z])([A-Z])/', '$1 $2', static::CONFIG_MODEL_NAME);
            $aConfig['TITLE_SINGLE'] = strtolower($aConfig['TITLE_SINGLE']);
            $aConfig['TITLE_SINGLE'] = ucwords($aConfig['TITLE_SINGLE']);
        }

        if (empty($aConfig['TITLE_PLURAL'])) {
            Factory::helper('inflector');
            $aConfig['TITLE_PLURAL'] = pluralise(2, $aConfig['TITLE_SINGLE']);
        }

        if (empty($aConfig['SIDEBAR_GROUP'])) {
            $aConfig['SIDEBAR_GROUP'] = $aConfig['TITLE_PLURAL'];
        }

        if (empty($aConfig['BASE_URL'])) {
            $aBits               = explode('\\', get_called_class());
            $aConfig['BASE_URL'] = 'admin/' . $aBits[count($aBits) - 2] . '/' . $aBits[count($aBits) - 1];
            $aConfig['BASE_URL'] = strtolower($aConfig['BASE_URL']);
        }

        return $aConfig;
    }

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     * @throws NailsException
     */
    public static function announce()
    {
        $aConfig = static::getConfig();

        // --------------------------------------------------------------------------

        $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
        $oNavGroup->setLabel($aConfig['SIDEBAR_GROUP']);

        if (empty($aConfig['PERMISSION']) || userHasPermission('admin:' . $aConfig['PERMISSION'] . ':browse')) {
            $oNavGroup->addAction('Manage ' . $aConfig['TITLE_PLURAL'], 'index');
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     * @return array
     */
    public static function permissions()
    {
        $aPermissions = parent::permissions();
        $aConfig      = static::getConfig();

        if (!empty($aConfig['PERMISSION'])) {
            $aPermissions['browse']  = 'Can browse items';
            $aPermissions['create']  = 'Can create items';
            $aPermissions['edit']    = 'Can edit items';
            $aPermissions['delete']  = 'Can delete items';
            $aPermissions['restore'] = 'Can restore items';
        }

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Browse all items
     * @return void
     */
    public function index()
    {
        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':browse';
        if (!empty($this->aConfig['PERMISSION']) && !userHasPermission($sPermissionStr)) {
            unauthorised();
        }

        $oInput     = Factory::service('Input');
        $oItemModel = Factory::model(
            $this->aConfig['MODEL_NAME'],
            $this->aConfig['MODEL_PROVIDER']
        );

        $sAlias      = $oItemModel->getTableAlias();
        $aSortConfig = $this->aConfig['SORT_OPTIONS'];

        //  Get the first key (i.e the default sort)
        reset($aSortConfig);
        $sFirstKey = key($aSortConfig);

        //  Prepare the sort options so they have the appropriate table alias
        $aSortCol = [];
        foreach ($aSortConfig as $sColumn => $sLabel) {
            if (strpos($sColumn, '.') === false) {
                $aSortCol[$sAlias . '.' . $sColumn] = $sLabel;
            } else {
                $aSortCol[$sColumn] = $sLabel;
            }
        }

        //  Other parameters
        $iPage      = $oInput->get('page') ? $oInput->get('page') : 0;
        $iPerPage   = $oInput->get('perPage') ? $oInput->get('perPage') : 50;
        $sSortOn    = $oInput->get('sortOn') ? $oInput->get('sortOn') : $sAlias . '.' . $sFirstKey;
        $sSortOrder = $oInput->get('sortOrder') ? $oInput->get('sortOrder') : 'asc';
        $sKeywords  = $oInput->get('keywords');

        $aData = [
                'sort'     => [
                    [$sSortOn, $sSortOrder],
                ],
                'keywords' => $sKeywords,
            ] + $this->aConfig['INDEX_DATA'];

        // --------------------------------------------------------------------------

        $iTotalRows               = $oItemModel->countAll($aData);
        $this->data['items']      = $oItemModel->getAll($iPage, $iPerPage, $aData);
        $this->data['search']     = Helper::searchObject(true, $aSortCol, $sSortOn, $sSortOrder, $iPerPage, $sKeywords);
        $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);

        // --------------------------------------------------------------------------

        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':create';
        if (empty($this->aConfig['PERMISSION']) || userHasPermission($sPermissionStr)) {
            Helper::addHeaderButton($this->aConfig['BASE_URL'] . '/create', 'Create');
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = $this->aConfig['TITLE_PLURAL'] . ' &rsaquo; Manage';
        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new item
     * @return void
     */
    public function create()
    {
        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':create';
        if (!empty($this->aConfig['PERMISSION']) && !userHasPermission($sPermissionStr)) {
            unauthorised();
        }

        $oDb        = Factory::service('Database');
        $oInput     = Factory::service('Input');
        $oItemModel = Factory::model(
            $this->aConfig['MODEL_NAME'],
            $this->aConfig['MODEL_PROVIDER']
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
                    redirect($this->aConfig['BASE_URL']);

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

        $this->data['page']->title = $this->aConfig['TITLE_SINGLE'] . ' &rsaquo; Create';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit an existing item
     * @return void
     */
    public function edit()
    {
        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':edit';
        if (!empty($this->aConfig['PERMISSION']) && !userHasPermission($sPermissionStr)) {
            unauthorised();
        }

        $oDb        = Factory::service('Database');
        $oUri       = Factory::service('Uri');
        $oInput     = Factory::service('Input');
        $oItemModel = Factory::model(
            $this->aConfig['MODEL_NAME'],
            $this->aConfig['MODEL_PROVIDER']
        );
        $iItemId    = (int) $oUri->segment(5);
        $oItem      = $oItemModel->getById($iItemId, $this->aConfig['EDIT_DATA']);

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
                    redirect($this->aConfig['BASE_URL']);

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

        $this->data['page']->title = $this->aConfig['TITLE_SINGLE'] . ' &rsaquo; Edit';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     * @return bool
     */
    private function runFormValidation()
    {
        $oFormValidation      = Factory::service('FormValidation');
        $aRulesFormValidation = [];
        foreach ($this->aConfig['FIELDS'] as $oField) {
            $aRulesFormValidation[] = [
                'field' => $oField->key,
                'label' => $oField->label,
                'rules' => $oField->validation,
            ];
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
        $aOut   = [];

        foreach ($this->aConfig['FIELDS'] as $oField) {
            if (in_array($oField->key, $this->aConfig['EDIT_IGNORE_FIELDS'])) {
                continue;
            }
            $aOut[$oField->key] = $oInput->post($oField->key);

            if ($oField->allow_null && empty($aOut[$oField->key])) {
                $aOut[$oField->key] = null;
            }
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an item
     * @return void
     */
    public function delete()
    {
        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':delete';
        if (!empty($this->aConfig['PERMISSION']) && !userHasPermission($sPermissionStr)) {
            unauthorised();
        }

        $oDb        = Factory::service('Database');
        $oUri       = Factory::service('Uri');
        $oItemModel = Factory::model(
            $this->aConfig['MODEL_NAME'],
            $this->aConfig['MODEL_PROVIDER']
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

            if ($this->aConfig['CAN_RESTORE']) {
                $sRestoreLink = anchor($this->aConfig['BASE_URL'] . '/restore/' . $iItemId, 'Restore?');
            } else {
                $sRestoreLink = '';
            }

            $oDb->trans_commit();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('success', 'Item deleted successfully. ' . $sRestoreLink);
            redirect($this->aConfig['BASE_URL']);

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
        if (!$this->aConfig['CAN_RESTORE']) {
            show_404();
        }

        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':restore';
        if (!empty($this->aConfig['PERMISSION']) && !userHasPermission($sPermissionStr)) {
            unauthorised();
        }

        $oUri       = Factory::service('Uri');
        $oDb        = Factory::service('Database');
        $oItemModel = Factory::model(
            $this->aConfig['MODEL_NAME'],
            $this->aConfig['MODEL_PROVIDER']
        );
        $iItemId    = (int) $oUri->segment(5);
        $oItem      = $oItemModel->getAll(
            null,
            null,
            [
                'where' => [
                    [
                        'id', $iItemId,
                    ],
                ],
            ],
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
            redirect($this->aConfig['BASE_URL']);

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $this->data['error'] = $e->getMessage();
        }
    }
}
