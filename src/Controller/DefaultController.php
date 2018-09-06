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
use Nails\Common\Exception\ValidationException;
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
        'created'     => 'Created',
        'modified'    => 'Modified',
        'modified_by' => 'Modified By',
    ];

    /**
     * Any additional header buttons to add to the page.
     */
    const CONFIG_INDEX_HEADER_BUTTONS = [];

    /**
     * Any additional buttons to add to each row on the page.
     * See static::$aConfigIndexRowButtons for details
     */
    const CONFIG_INDEX_ROW_BUTTONS = [];

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
     * Fields which should be marked as readonly when creating an item
     */
    const CONFIG_CREATE_READONLY_FIELDS = [];

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
     * Fields which should be marked as readonly when editing an item
     */
    const CONFIG_EDIT_READONLY_FIELDS = [];

    /**
     * Additional data to pass into the getAll call on the edit view
     */
    const CONFIG_EDIT_DATA = [];

    /**
     * Specify a specific order for fieldsets
     */
    const CONFIG_EDIT_FIELDSET_ORDER = [];

    /**
     * Additional data to pass into the getAll call on the sort view
     */
    const CONFIG_SORT_DATA = [];

    /**
     * When creating, this string is passed to supporting functions
     */
    const EDIT_MODE_CREATE = 'CREATE';

    /**
     * When editing, this string is passed to supporting functions
     */
    const EDIT_MODE_EDIT = 'EDIT';

    /**
     * Enable or disable the "Notes" feature
     */
    const EDIT_ENABLE_NOTES = true;

    /**
     * Message displayed to user when an item is successfully created
     */
    const CREATE_SUCCESS_MESSAGE = 'Item created successfully. %s';

    /**
     * Message displayed to user when an item fails to be created
     */
    const CREATE_ERROR_MESSAGE = 'Failed to create item.';

    /**
     * Message displayed to user when an item is successfully updated
     */
    const EDIT_SUCCESS_MESSAGE = 'Item updated successfully. %s';

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

    /**
     * Message displayed to user when an items are ordered successfully
     */
    const ORDER_SUCCESS_MESSAGE = 'Items ordered successfully.';

    /**
     * Message displayed to user when an item fails to be ordered
     */
    const ORDER_ERROR_MESSAGE = 'Failed to order items.';

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
        $oModel = $this->getModel();

        $this->aConfig['CAN_RESTORE'] = !$oModel->isDestructiveDelete();
        $this->aConfig['FIELDS']      = $oModel->describeFields();
        $this->data['CONFIG']         = $this->aConfig;
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
        $oModel  = static::getModel();
        $aConfig = [
            'MODEL_NAME'             => static::CONFIG_MODEL_NAME,
            'MODEL_PROVIDER'         => static::CONFIG_MODEL_PROVIDER,
            'MODEL_INSTANCE'         => $oModel,
            'CAN_CREATE'             => static::CONFIG_CAN_CREATE,
            'CAN_EDIT'               => static::CONFIG_CAN_EDIT,
            'CAN_VIEW'               => static::CONFIG_CAN_VIEW,
            'CAN_DELETE'             => static::CONFIG_CAN_DELETE,
            'PERMISSION'             => static::CONFIG_PERMISSION,
            'TITLE_SINGLE'           => static::CONFIG_TITLE_SINGLE,
            'TITLE_PLURAL'           => static::CONFIG_TITLE_PLURAL,
            'SIDEBAR_GROUP'          => static::CONFIG_SIDEBAR_GROUP,
            'SIDEBAR_ICON'           => static::CONFIG_SIDEBAR_ICON,
            'BASE_URL'               => static::CONFIG_BASE_URL,
            'SORT_OPTIONS'           => static::CONFIG_SORT_OPTIONS,
            'SORT_DIRECTION'         => static::CONFIG_SORT_DIRECTION,
            'INDEX_FIELDS'           => static::CONFIG_INDEX_FIELDS,
            'INDEX_HEADER_BUTTONS'   => static::CONFIG_INDEX_HEADER_BUTTONS,
            'INDEX_ROW_BUTTONS'      => array_merge(static::$aConfigIndexRowButtons, static::CONFIG_INDEX_ROW_BUTTONS),
            'INDEX_DATA'             => static::CONFIG_INDEX_DATA,
            'INDEX_BOOL_FIELDS'      => static::CONFIG_INDEX_BOOL_FIELDS,
            'INDEX_USER_FIELDS'      => static::CONFIG_INDEX_USER_FIELDS,
            'CREATE_READONLY_FIELDS' => static::CONFIG_CREATE_READONLY_FIELDS,
            'EDIT_READONLY_FIELDS'   => static::CONFIG_EDIT_READONLY_FIELDS,
            'EDIT_IGNORE_FIELDS'     => static::CONFIG_EDIT_IGNORE_FIELDS,
            'EDIT_DATA'              => static::CONFIG_EDIT_DATA,
            'SORT_DATA'              => static::CONFIG_SORT_DATA,
            'FIELDSET_ORDER'         => static::CONFIG_EDIT_FIELDSET_ORDER,
            'ENABLE_NOTES'           => static::EDIT_ENABLE_NOTES,
        ];

        //  Additional fields
        if (classUses($oModel, 'Nails\Common\Traits\Model\Sortable')) {
            $aConfig['SORT_OPTIONS']         = array_merge(['order' => 'Defined Order'], $aConfig['SORT_OPTIONS']);
            $aConfig['EDIT_IGNORE_FIELDS'][] = $oModel->getSortableColumn();
        }

        if (classUses($oModel, 'Nails\Common\Traits\Model\Nestable')) {
            $aConfig['EDIT_IGNORE_FIELDS'][] = $oModel->getBreadcrumbsColumn();
        }

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

    protected static function getModel()
    {
        return Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );
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

        $oInput = Factory::service('Input');
        $oModel = $this->getModel();

        $sAlias      = $oModel->getTableAlias();
        $aSortConfig = $this->aConfig['SORT_OPTIONS'];

        if (classUses($oModel, '\Nails\Common\Traits\Model\Nestable')) {
            $aSortConfig = array_merge(['breadcrumbs' => 'Hierarchy'], $aSortConfig);
        }

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

        $iTotalRows               = $oModel->countAll($aData);
        $this->data['items']      = $oModel->getAll($iPage, $iPerPage, $aData);
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

        if (static::CONFIG_CAN_EDIT) {
            $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':edit';
            $bIsSortable    = classUses($oModel, 'Nails\Common\Traits\Model\Sortable');
            if ($bIsSortable && (empty($this->aConfig['PERMISSION']) || userHasPermission($sPermissionStr))) {
                Helper::addHeaderButton($this->aConfig['BASE_URL'] . '/sort', 'Set Order');
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

        $oDb    = Factory::service('Database');
        $oInput = Factory::service('Input');
        $oModel = $this->getModel();

        // --------------------------------------------------------------------------

        //  View Data & Assets
        $this->loadEditViewData();

        // --------------------------------------------------------------------------

        if ($oInput->post()) {
            try {
                $this->runFormValidation();
                $oDb->trans_begin();
                $this->beforeCreateAndEdit(static::EDIT_MODE_CREATE);
                $this->beforeCreate();

                $oItem = $oModel->create($this->getPostObject(), true);
                if (!$oItem) {
                    throw new NailsException(static::CREATE_ERROR_MESSAGE . ' ' . $oModel->lastError());
                }

                $this->afterCreateAndEdit(static::EDIT_MODE_CREATE, $oItem);
                $this->afterCreate($oItem);
                $oDb->trans_commit();

                if (property_exists($oItem, 'url')) {
                    $sLink = anchor(
                        $oItem->url,
                        'View &nbsp;<span class="fa fa-external-link"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } else {
                    $sLink = '';
                }

                $oSession = Factory::service('Session', 'nailsapp/module-auth');
                $oSession->setFlashData('success', sprintf(static::CREATE_SUCCESS_MESSAGE, $sLink));

                redirect($this->aConfig['BASE_URL'] . '/edit/' . $oItem->id);

            } catch (\Exception $e) {
                $oDb->trans_rollback();
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

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

        $oDb    = Factory::service('Database');
        $oInput = Factory::service('Input');
        $oModel = $this->getModel();
        $oItem  = $this->getItem($this->aConfig['EDIT_DATA']);

        // --------------------------------------------------------------------------

        //  View Data & Assets
        $this->loadEditViewData($oItem);

        // --------------------------------------------------------------------------

        if ($oInput->post()) {
            try {

                $this->runFormValidation();
                $oDb->trans_begin();
                $this->beforeCreateAndEdit(static::EDIT_MODE_EDIT, $oItem);
                $this->beforeEdit($oItem);

                if (!$oModel->update($iItemId, $this->getPostObject())) {
                    throw new NailsException(static::EDIT_ERROR_MESSAGE . ' ' . $oModel->lastError());
                }

                $oNewItem = $oModel->getById($iItemId);
                $this->afterCreateAndEdit(static::EDIT_MODE_EDIT, $oNewItem, $oItem);
                $this->afterEdit($oNewItem, $oItem);
                $oDb->trans_commit();

                if (property_exists($oNewItem, 'url')) {
                    $sLink = anchor(
                        $oNewItem->url,
                        'View &nbsp;<span class="fa fa-external-link"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } else {
                    $sLink = '';
                }

                $oSession = Factory::service('Session', 'nailsapp/module-auth');
                $oSession->setFlashData('success', sprintf(static::EDIT_SUCCESS_MESSAGE, $sLink));
                redirect($this->aConfig['BASE_URL'] . '/edit/' . $iItemId);

            } catch (\Exception $e) {
                $oDb->trans_rollback();
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

        if (static::CONFIG_CAN_CREATE) {
            $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':create';
            if (empty($this->aConfig['PERMISSION']) || userHasPermission($sPermissionStr)) {
                Helper::addHeaderButton($this->aConfig['BASE_URL'] . '/create', 'Create');
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = $this->aConfig['TITLE_SINGLE'] . ' &rsaquo; Edit';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param string    $sMode Whether the action was CREATE or EDIT
     * @param \stdClass $oItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeCreateAndEdit($sMode, \stdClass $oItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param \stdClass $oItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeEdit(\stdClass $oItem = null)
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
     * @param \stdClass $oItem The item being deleted
     *
     * @return void
     */
    protected function beforeDelete(\stdClass $oItem)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param string    $sMode    Whether the action was CREATE or EDIT
     * @param \stdClass $oNewItem The new item, after it was edited
     * @param \stdClass $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterCreateAndEdit($sMode, \stdClass $oNewItem, \stdClass $oOldItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param \stdClass $oNewItem The new item, after it was edited
     * @param \stdClass $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterEdit(\stdClass $oNewItem, \stdClass $oOldItem = null)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is created
     *
     * @param \stdClass $oNewItem The new item
     *
     * @return void
     */
    protected function afterCreate(\stdClass $oNewItem)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is deleted
     *
     * @param \stdClass $oItem The deleted item
     *
     * @return void
     */
    protected function afterDelete(\stdClass $oItem)
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     *
     * @param array $aOverrides Any overrides for the fields; best to do this in the model's describeFields() method
     *
     * @throws ValidationException
     * @return void
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
        $aImplementedRules = arrayUniqueMulti($aImplementedRules);

        foreach ($aImplementedRules as $sRule) {
            $sMessage = lang('fv_' . $sRule);
            if ($sMessage) {
                $oFormValidation->set_message($sRule, $sMessage);
            }
        }

        if (!$oFormValidation->run()) {
            throw new ValidationException(lang('fv_there_were_errors'));
        }
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
        //  Extract the fields into fieldsets
        $aFieldSets = array_combine(
            $this->aConfig['FIELDSET_ORDER'],
            array_pad(
                [],
                count($this->aConfig['FIELDSET_ORDER']),
                []
            )
        );

        foreach ($this->aConfig['FIELDS'] as $oField) {

            if (in_array($oField->key, $this->aConfig['EDIT_IGNORE_FIELDS'])) {
                continue;
            }

            $sFieldSet = getFromArray('fieldset', (array) $oField, 'Details');

            if (!array_key_exists($sFieldSet, $aFieldSets)) {
                $aFieldSets[$sFieldSet] = [];
            }

            $aFieldSets[$sFieldSet][] = $oField;
        }

        $this->data['aFieldSets'] = array_filter($aFieldSets);
        $this->data['item']       = $oItem;
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

            //  Type casting
            switch ($oField->type) {
                case 'boolean':
                    $aOut[$oField->key] = (bool) $aOut[$oField->key];
                    break;
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

        $oDb    = Factory::service('Database');
        $oModel = $this->getModel();
        $oItem  = $this->getItem();

        if (empty($oItem)) {
            show_404();
        }

        try {

            $oDb->trans_begin();
            $this->beforeDelete($oItem);

            if (!$oModel->delete($oItem->id)) {
                throw new NailsException(static::DELETE_ERROR_MESSAGE . ' ' . $oModel->lastError());
            }

            $this->afterDelete($oItem);
            $oDb->trans_commit();

            if ($this->aConfig['CAN_RESTORE']) {
                $sRestoreLink = anchor($this->aConfig['BASE_URL'] . '/restore/' . $oItem->id, 'Restore?');
            } else {
                $sRestoreLink = '';
            }

            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->setFlashData('success', static::DELETE_SUCCESS_MESSAGE . ' ' . $sRestoreLink);
            redirect($this->aConfig['BASE_URL']);

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->setFlashData('error', static::DELETE_ERROR_MESSAGE . ' ' . $e->getMessage());
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

        $oUri    = Factory::service('Uri');
        $oDb     = Factory::service('Database');
        $oModel  = $this->getModel();
        $iItemId = (int) $oUri->segment(5);
        $aItems  = $oModel->getAll(
            null,
            null,
            [
                'where' => [
                    ['id', $iItemId],
                ],
            ],
            true
        );

        $oItem = reset($aItems);
        if (empty($oItem)) {
            show_404();
        }

        try {
            $oDb->trans_begin();
            if (!$oModel->restore($oItem->id)) {
                throw new NailsException(static::RESTORE_ERROR_MESSAGE . ' ' . $oModel->lastError());
            }

            $oDb->trans_commit();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->setFlashData('success', static::RESTORE_SUCCESS_MESSAGE);
            redirect($this->aConfig['BASE_URL']);

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $oSession = Factory::service('Session', 'nailsapp/module-auth');
            $oSession->setFlashData('error', static::RESTORE_ERROR_MESSAGE . ' ' . $e->getMessage());
            redirect($this->aConfig['BASE_URL']);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sort items into order
     * @return void
     */
    public function sort()
    {
        if (!static::CONFIG_CAN_EDIT) {
            show_404();
        }

        $sPermissionStr = 'admin:' . $this->aConfig['PERMISSION'] . ':edit';
        if (!empty($this->aConfig['PERMISSION']) && !userHasPermission($sPermissionStr)) {
            unauthorised();
        }

        $oModel = $this->getModel();
        $oInput = Factory::service('Input');
        if ($oInput->post()) {
            try {
                $aItems = array_values((array) $oInput->post('order'));
                foreach ($aItems as $iOrder => $iId) {
                    if (!$oModel->update($iId, ['order' => $iOrder])) {
                        throw new NailsException(static::ORDER_ERROR_MESSAGE . ' ' . $oModel->lastError());
                    }
                }
                $oSession = Factory::service('Session', 'nailsapp/module-auth');
                $oSession->setFlashData('success', static::ORDER_SUCCESS_MESSAGE);
                redirect($this->aConfig['BASE_URL'] . '/sort');
            } catch (\Exception $e) {
                $this->data['error'] = $e->getMessage();
            }
        }

        $aItems                    = $oModel->getAll($this->aConfig['SORT_DATA']);
        $this->data['items']       = $aItems;
        $this->data['page']->title = $this->aConfig['TITLE_PLURAL'] . ' &rsaquo; Sort';
        Helper::loadView('order');
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the item being requested, thorwing a 404 if it's not found
     *
     * @param array   $aData    Data to pass to the getById method
     * @param integer $iSegment The URL segment contianing the ID
     *
     * @return mixed
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function getItem($aData = [], $iSegment = 5)
    {
        $oUri    = Factory::service('Uri');
        $oModel  = $this->getModel();
        $iItemId = (int) $oUri->segment($iSegment);
        $oItem   = $oModel->getById($iItemId, $aData);

        if (empty($oItem)) {
            show_404();
        }

        return $oItem;
    }
}
