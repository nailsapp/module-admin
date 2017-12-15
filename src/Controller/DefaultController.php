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

namespace Nails\Admin\Controller;

use Nails\Admin\Helper;
use Nails\Common\Exception\NailsException;
use Nails\Factory;

abstract class DefaultController extends Base
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
     * What icon to display in the nav
     */
    const CONFIG_SIDEBAR_ICON = '';

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
     * The default sorting order
     */
    const CONFIG_SORT_DIRECTION = 'asc';

    /**
     * The fields to show on the index view
     */
    const CONFIG_INDEX_FIELDS = [
        'label'       => 'Label',
        'modified'    => 'Modified',
        'modified_by' => 'Modified By',
    ];

    /**
     * Any additional header buttons to add to the page.
     * See static::$aConfigIndexRowButtons for details
     */
    const CONFIG_INDEX_HEADER_BUTTONS = [];

    /**
     * Specify whether the controller supports item creation
     */
    const CONFIG_CAN_CREATE = true;

    /**
     * Specify whether the controller supports item editing
     */
    const CONFIG_CAN_EDIT = true;

    /**
     * Specify whether the controller supports linking to the item
     */
    const CONFIG_CAN_VIEW = true;

    /**
     * Specify whether the controller supports item deletion (and restoration if possible)
     */
    const CONFIG_CAN_DELETE = true;

    /**
     * Additional data to pass into the getAll call on the index view
     */
    const CONFIG_INDEX_DATA = [];

    /**
     * The fields on the index view which should be rendered as user cells
     */
    const CONFIG_INDEX_USER_FIELDS = [
        'created_by',
        'modified_by',
        'user_id',
    ];

    /**
     * The fields on the index view which should be rendered as boolean cells
     */
    const CONFIG_INDEX_BOOL_FIELDS = [
        'is_active',
        'is_published',
        'is_deleted',
    ];

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

    /**
     * When creating, this string is passed to supporting functions
     */
    const EDIT_MODE_CREATE = 'CREATE';

    /**
     * When editing, this string is passed to supporting functions
     */
    const EDIT_MODE_EDIT = 'EDIT';

    /**
     * Message displayed to user when an item is successfully created
     */
    const CREATE_SUCCESS_MESSAGE = 'Item created successfully.';

    /**
     * Message displayed to user when an item fails to be created
     */
    const CREATE_ERROR_MESSAGE = 'Failed to create item.';

    /**
     * Message displayed to user when an item is successfully updated
     */
    const EDIT_SUCCESS_MESSAGE = 'Item updated successfully.';

    /**
     * Message displayed to user when an item fails to be created
     */
    const EDIT_ERROR_MESSAGE = 'Failed to update item.';

    /**
     * Message displayed to user when an item is successfully deleted
     */
    const DELETE_SUCCESS_MESSAGE = 'Item deleted successfully.';

    /**
     * Message displayed to user when an item fails to be deleted
     */
    const DELETE_ERROR_MESSAGE = 'Failed to delete item.';

    /**
     * Message displayed to user when an item is successfully restored
     */
    const RESTORE_SUCCESS_MESSAGE = 'Item restore successfully.';

    /**
     * Message displayed to user when an item fails to be restored
     */
    const RESTORE_ERROR_MESSAGE = 'Failed to restore item.';

    // --------------------------------------------------------------------------

    /**
     * Any additional buttons to add for each row item (will sit between "View" if available and "Edit").
     * Takes the following format:
     *
     *   [
     *       // The button's URL, row items can be substituted in using double curly syntax.
     *       // Will be prefixed with $CONFIG['BASE_URL'] (which is the URL to the controller)
     *       'url'        => 'edit/{{id}}',
     *
     *       // The button's value/label
     *       'label'      => 'Edit',
     *
     *       // Additional classes to add to the button
     *       'class'      => 'btn-primary',
     *
     *       // Additional attributes to add to the button
     *       'attr'       => '',
     *
     *       // If required, a permission string to check in order to render the button;
     *       // will be appended to `admin:$CONFIG['PERMISSION']:`
     *       'permission' => 'edit',
     *
     *       // An expression to determine if the button can be rendered
     *       'enabled'   => function($oItem) { return true; },
     *   ],
     */
    protected static $aConfigIndexRowButtons = [];

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
        $aRequiredConstants = [
            'CONFIG_MODEL_NAME',
            'CONFIG_MODEL_PROVIDER',
        ];
        foreach ($aRequiredConstants as $sConstant) {
            if (empty(constant('static::' . $sConstant))) {
                throw new NailsException(
                    'The constant "static::' . $sConstant . '" must be set in ' . get_called_class()
                );
            }
        }

        //  Build the config array
        $aConfig = [
            'MODEL_NAME'           => static::CONFIG_MODEL_NAME,
            'MODEL_PROVIDER'       => static::CONFIG_MODEL_PROVIDER,
            'CAN_CREATE'           => static::CONFIG_CAN_CREATE,
            'CAN_EDIT'             => static::CONFIG_CAN_EDIT,
            'CAN_VIEW'             => static::CONFIG_CAN_VIEW,
            'CAN_DELETE'           => static::CONFIG_CAN_DELETE,
            'PERMISSION'           => static::CONFIG_PERMISSION,
            'TITLE_SINGLE'         => static::CONFIG_TITLE_SINGLE,
            'TITLE_PLURAL'         => static::CONFIG_TITLE_PLURAL,
            'SIDEBAR_GROUP'        => static::CONFIG_SIDEBAR_GROUP,
            'SIDEBAR_ICON'         => static::CONFIG_SIDEBAR_ICON,
            'BASE_URL'             => static::CONFIG_BASE_URL,
            'SORT_OPTIONS'         => static::CONFIG_SORT_OPTIONS,
            'SORT_DIRECTION'       => static::CONFIG_SORT_DIRECTION,
            'INDEX_FIELDS'         => static::CONFIG_INDEX_FIELDS,
            'INDEX_HEADER_BUTTONS' => static::CONFIG_INDEX_HEADER_BUTTONS,
            'INDEX_ROW_BUTTONS'    => array_merge(static::$aConfigIndexRowButtons, static::CONFIG_INDEX_HEADER_BUTTONS),
            'INDEX_DATA'           => static::CONFIG_INDEX_DATA,
            'INDEX_BOOL_FIELDS'    => static::CONFIG_INDEX_BOOL_FIELDS,
            'INDEX_USER_FIELDS'    => static::CONFIG_INDEX_USER_FIELDS,
            'EDIT_IGNORE_FIELDS'   => static::CONFIG_EDIT_IGNORE_FIELDS,
            'EDIT_DATA'            => static::CONFIG_EDIT_DATA,
        ];

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
            $aBits   = explode('\\', get_called_class());
            $sModule = strtolower($aBits[count($aBits) - 2]);
            $sClass  = lcfirst($aBits[count($aBits) - 1]);

            $aConfig['BASE_URL'] = 'admin/' . $sModule . '/' . $sClass;
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
        $oNavGroup->setIcon($aConfig['SIDEBAR_ICON']);

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
        $sSortOrder = $oInput->get('sortOrder') ? $oInput->get('sortOrder') : $this->aConfig['SORT_DIRECTION'];
        $sKeywords  = $oInput->get('keywords');
        $aCbFilters = $this->indexCheckboxFilters();
        $aDdFilters = $this->indexDropdownFilters();

        $aData = [
                'cbFilters' => $aCbFilters,
                'ddFilters' => $aDdFilters,
                'keywords'  => $sKeywords,
                'sort'      => [
                    [$sSortOn, $sSortOrder],
                ],
            ] + $this->aConfig['INDEX_DATA'];

        // --------------------------------------------------------------------------

        $iTotalRows               = $oItemModel->countAll($aData);
        $this->data['items']      = $oItemModel->getAll($iPage, $iPerPage, $aData);
        $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);
        $this->data['search']     = Helper::searchObject(
            true,
            $aSortCol,
            $sSortOn,
            $sSortOrder,
            $iPerPage,
            $sKeywords,
            $aCbFilters,
            $aDdFilters
        );

        // --------------------------------------------------------------------------

        if (static::CONFIG_CAN_CREATE) {
            $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':create';
            if (empty($this->aConfig['PERMISSION']) || userHasPermission($sPermissionStr)) {
                Helper::addHeaderButton($this->aConfig['BASE_URL'] . '/create', 'Create');
            }
        }

        // --------------------------------------------------------------------------

        foreach ($this->aConfig['INDEX_HEADER_BUTTONS'] as $aButton) {

            $sUrl          = getFromArray(0, $aButton);
            $sLabel        = getFromArray(1, $aButton);
            $sContext      = getFromArray(2, $aButton);
            $sConfirmTitle = getFromArray(3, $aButton);
            $sConfirmBody  = getFromArray(4, $aButton);

            Helper::addHeaderButton(
                $sUrl,
                $sLabel,
                $sContext,
                $sConfirmTitle,
                $sConfirmBody
            );
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = $this->aConfig['TITLE_PLURAL'] . ' &rsaquo; Manage';
        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Any checkbox style filters to include on the index page
     * @return array
     */
    protected function indexCheckboxFilters()
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Any dropdown style filters to include on the index page
     * @return array
     */
    protected function indexDropdownFilters()
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new item
     * @return void
     */
    public function create()
    {
        if (!static::CONFIG_CAN_CREATE) {
            show_404();
        }

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

                    $this->beforeCreateAndEdit(static::EDIT_MODE_CREATE);
                    $this->beforeCreate();

                    $iItemId = $oItemModel->create($this->getPostObject());
                    if (!$iItemId) {
                        throw new NailsException(static::CREATE_ERROR_MESSAGE . ' ' . $oItemModel->lastError());
                    }

                    $this->afterCreateAndEdit(static::EDIT_MODE_CREATE, $iItemId);
                    $this->afterCreate($iItemId);
                    $oDb->trans_commit();
                    $oSession = Factory::service('Session', 'nailsapp/module-auth');
                    $oSession->set_flashdata('success', static::CREATE_SUCCESS_MESSAGE);
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
        if (!static::CONFIG_CAN_EDIT) {
            show_404();
        }

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

                    $this->beforeCreateAndEdit(static::EDIT_MODE_EDIT, $iItemId, $oItem);
                    $this->beforeEdit($iItemId);

                    if (!$oItemModel->update($iItemId, $this->getPostObject())) {
                        throw new NailsException(static::EDIT_ERROR_MESSAGE . ' ' . $oItemModel->lastError());
                    }

                    $this->afterCreateAndEdit(static::EDIT_MODE_EDIT, $iItemId, $oItem);
                    $this->afterEdit($iItemId, $oItem);
                    $oDb->trans_commit();
                    $oSession = Factory::service('Session', 'nailsapp/module-auth');
                    $oSession->set_flashdata('success', static::EDIT_SUCCESS_MESSAGE);
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
     * Executed before an item is edited
     *
     * @param string    $sMode    Whether the action was CREATE or EDIT
     * @param int       $iItemId  The item's ID
     * @param \stdClass $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeCreateAndEdit($sMode, $iItemId = null, $oOldItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param int       $iItemId  The item's ID
     * @param \stdClass $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeEdit($iItemId, $oOldItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is created
     *
     * @return void
     */
    protected function beforeCreate()
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is deleted
     *
     * @param int $iItemId The item's ID
     *
     * @return void
     */
    protected function beforeDelete($iItemId)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param string    $sMode    Whether the action was CREATE or EDIT
     * @param int       $iItemId  The item's ID
     * @param \stdClass $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterCreateAndEdit($sMode, $iItemId, $oOldItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param int       $iItemId  The item's ID
     * @param \stdClass $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterEdit($iItemId, $oOldItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is created
     *
     * @param int $iItemId The item's ID
     *
     * @return void
     */
    protected function afterCreate($iItemId)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is deleted
     *
     * @param int $iItemId The item's ID
     *
     * @return void
     */
    protected function afterDelete($iItemId)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     *
     * @param array $aOverrides Any overrides for the fields; best to do this in the model's describeFields() method
     *
     * @return mixed
     */
    protected function runFormValidation($aOverrides = [])
    {
        $oFormValidation      = Factory::service('FormValidation');
        $aRulesFormValidation = [];
        $aImplementedRules    = [];

        foreach ($this->aConfig['FIELDS'] as $oField) {

            if (array_key_exists($oField->key, $aOverrides)) {
                $sRules            = implode('|', $aOverrides[$oField->key]);
                $aImplementedRules = array_merge($aImplementedRules, $aOverrides[$oField->key]);
            } else {
                $sRules            = implode('|', $oField->validation);
                $aImplementedRules = array_merge($aImplementedRules, $oField->validation);
            }

            $aRulesFormValidation[] = [
                'field' => $oField->key,
                'label' => $oField->label,
                'rules' => $sRules,
            ];
        }

        $oFormValidation->set_rules($aRulesFormValidation);

        //  Load up friendly versions of the form validation rules if they exist
        $aImplementedRules = array_map(
            function ($sRule) {
                return preg_replace('/\[.*\]/', '', $sRule);
            },
            $aImplementedRules
        );
        $aImplementedRules = array_unique_multi($aImplementedRules);

        foreach ($aImplementedRules as $sRule) {
            $sMessage = lang('fv_' . $sRule);
            if ($sMessage) {
                $oFormValidation->set_message($sRule, $sMessage);
            }
        }

        return $oFormValidation->run();
    }

    // --------------------------------------------------------------------------

    /**
     * Load data for the edit/create view
     *
     * @param  \stdClass $oItem The main item object
     *
     * @return void
     */
    protected function loadEditViewData($oItem = null)
    {
        $this->data['item'] = $oItem;
    }

    // --------------------------------------------------------------------------

    /**
     * Extract data from post variable
     * @return array
     */
    protected function getPostObject()
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
        if (!static::CONFIG_CAN_DELETE) {
            show_404();
        }

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

            $this->beforeDelete($iItemId);

            if (!$oItemModel->delete($iItemId)) {
                throw new NailsException(static::DELETE_ERROR_MESSAGE . ' ' . $oItemModel->lastError());
            }

            $this->afterDelete($iItemId);

            if ($this->aConfig['CAN_RESTORE']) {
                $sRestoreLink = anchor($this->aConfig['BASE_URL'] . '/restore/' . $iItemId, 'Restore?');
            } else {
                $sRestoreLink = '';
            }

            $oDb->trans_commit();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('success', static::DELETE_SUCCESS_MESSAGE . ' ' . $sRestoreLink);
            redirect($this->aConfig['BASE_URL']);

        } catch (\Exception $e) {
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('error', static::DELETE_ERROR_MESSAGE . ' ' . $e->getMessage());
            redirect($this->aConfig['BASE_URL']);
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
                    ['id', $iItemId],
                ],
            ],
            true
        );

        if (empty($oItem[0])) {
            show_404();
        }

        try {
            if (!$oItemModel->restore($iItemId)) {
                throw new NailsException(static::RESTORE_ERROR_MESSAGE . ' ' . $oItemModel->lastError());
            }

            $oDb->trans_commit();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('success', static::RESTORE_SUCCESS_MESSAGE);
            redirect($this->aConfig['BASE_URL']);

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->set_flashdata('error', static::RESTORE_ERROR_MESSAGE . ' ' . $e->getMessage());
            redirect($this->aConfig['BASE_URL']);
        }
    }
}
