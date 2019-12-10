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

use Nails\Admin\Factory\IndexFilter;
use Nails\Admin\Factory\IndexFilter\Option;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Admin\Model\ChangeLog;
use Nails\Auth;
use Nails\Auth\Service\Session;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Factory\Model\Field;
use Nails\Common\Helper\ArrayHelper;
use Nails\Common\Resource;
use Nails\Common\Service\Database;
use Nails\Common\Service\FormValidation;
use Nails\Common\Service\Input;
use Nails\Common\Service\Locale;
use Nails\Common\Service\Uri;
use Nails\Common\Traits\Model\Copyable;
use Nails\Common\Traits\Model\Localised;
use Nails\Common\Traits\Model\Nestable;
use Nails\Common\Traits\Model\Sortable;
use Nails\Factory;

/**
 * Class DefaultController
 *
 * @package Nails\Admin\Controller
 */
abstract class DefaultController extends Base
{
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
     * The format for the sidebar link
     */
    const CONFIG_SIDEBAR_FORMAT = 'Manage %s';

    /**
     * the base URL for this controller
     */
    const CONFIG_BASE_URL = '';

    /**
     * The sorting options to give the user on the index view
     */
    const CONFIG_SORT_OPTIONS = [
        'Label'    => 'label',
        'Created'  => 'created',
        'Modified' => 'modified',
    ];

    /**
     * The default sorting order
     */
    const CONFIG_SORT_DIRECTION = 'asc';

    /**
     * The fields to show on the index view;
     *
     * This array forms the basis for the $this->$aConfig['INDEX_FIELDS'] config.
     * You are free to manipulate this after the constructor has been called.
     *
     * If you wish to create any dynamic fields then the element value can be a
     * closure/callable which is passed $oItem as the one and only argument.
     */
    const CONFIG_INDEX_FIELDS = [
        'Label'       => 'label',
        'Created'     => 'created',
        'Modified'    => 'modified',
        'Modified By' => 'modified_by',
    ];

    /**
     * Any additional header buttons to add to the index page.
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
     * Specify whether the controller supports item deletion
     */
    const CONFIG_CAN_DELETE = true;

    /**
     * Specify whether the controller supports item restoration
     */
    const CONFIG_CAN_RESTORE = true;

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
     * The fields on the index view which should be run through number_format
     */
    const CONFIG_INDEX_NUMERIC_FIELDS = [
        'id',
    ];

    /**
     * The fields on the index view which should be centered
     */
    const CONFIG_INDEX_CENTERED_FIELDS = [
        'id',
    ];

    /**
     * The ID to give the index page
     */
    const CONFIG_INDEX_PAGE_ID = '';

    /**
     * Fields which should be marked as readonly when creating an item
     */
    const CONFIG_CREATE_READONLY_FIELDS = [];

    /**
     * The fields to ignore on the create view
     */
    const CONFIG_CREATE_IGNORE_FIELDS = [
        'id',
        'slug',
        'token',
        'is_deleted',
        'created',
        'created_by',
        'modified',
        'modified_by',
    ];

    /**
     * The fields to ignore on the edit view
     */
    const CONFIG_EDIT_IGNORE_FIELDS = self::CONFIG_CREATE_IGNORE_FIELDS;

    /**
     * Fields which should be marked as readonly when editing an item
     */
    const CONFIG_EDIT_READONLY_FIELDS = [];

    /**
     * Additional data to pass into the getAll call on the edit view
     */
    const CONFIG_EDIT_DATA = [];

    /**
     * Any additional header buttons to add to the edit page.
     */
    const CONFIG_EDIT_HEADER_BUTTONS = [];

    /**
     * Specify a specific order for fieldsets
     */
    const CONFIG_EDIT_FIELDSET_ORDER = [];

    /**
     * The ID to give the edit page
     */
    const CONFIG_EDIT_PAGE_ID = '';

    /**
     * Additional data to pass into the getAll call on the sort view
     */
    const CONFIG_SORT_DATA = [];

    /**
     * Which column to use for the label when sorting
     */
    const CONFIG_SORT_LABEL = 'label';

    /**
     * Any additional columns to add to the sort view
     */
    const CONFIG_SORT_COLUMNS = [];

    /**
     * When creating, this string is passed to supporting functions
     */
    const EDIT_MODE_CREATE = 'CREATE';

    /**
     * When editing, this string is passed to supporting functions
     */
    const EDIT_MODE_EDIT = 'EDIT';

    /**
     * When deleting, this string is passed to supporting functions
     */
    const EDIT_MODE_DELETE = 'DELETE';

    /**
     * When restoring, this string is passed to supporting functions
     */
    const EDIT_MODE_RESTORE = 'RESTORE';

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
    const RESTORE_SUCCESS_MESSAGE = 'Item restored successfully.';

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

    /**
     * Message displayed to user when an item is successfully copied
     */
    const COPY_SUCCESS_MESSAGE = 'Item copied successfully.';

    /**
     * Whether to record updates in the admin change log
     */
    const CHANGELOG_ENABLED = true;

    /**
     * The name to use when creating changelog items, defaults to the resource class name
     */
    const CHANGELOG_ENTITY_NAME = null;

    /**
     * An array of fields to ignore when processing change log updates
     */
    const CHANGELOG_FIELDS_IGNORE = [
        'id',
        'is_deleted',
        'created',
        'created_by',
        'modified',
        'modified_by',
    ];

    /**
     * An array of fields to redact/mask when processing changelog updates
     */
    const CHANGELOG_FIELDS_REDACT = [
        'password',
    ];

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
     *
     * @var array
     */
    protected $aConfig;

    // --------------------------------------------------------------------------

    /**
     * The ChangeLog model instance
     *
     * @var ChangeLog
     */
    protected $oChangeLogModel;

    // --------------------------------------------------------------------------

    /**
     * DefaultController constructor.
     *
     * @throws NailsException
     */
    public function __construct()
    {
        parent::__construct();
        $this->getConfig();
        $this->oChangeLogModel = Factory::model('ChangeLog', 'nails/module-admin');
    }

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     *
     * @return array|Nav
     * @throws NailsException
     */
    public static function announce()
    {
        /** @var Nav $oNavGroup */
        $oNavGroup = Factory::factory('Nav', 'nails/module-admin');
        $oNavGroup
            ->setLabel(static::getSidebarGroup())
            ->setIcon(static::CONFIG_SIDEBAR_ICON);

        if (static::userCan('browse')) {
            $oNavGroup
                ->addAction(
                    sprintf(static::CONFIG_SIDEBAR_FORMAT, static::getTitlePlural()),
                    'index'
                );
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     *
     * @return array
     */
    public static function permissions(): array
    {
        $aPermissions = parent::permissions();

        if (!empty(static::CONFIG_PERMISSION)) {
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
     *
     * @return void
     */
    public function index(): void
    {
        if (!static::userCan('browse')) {
            unauthorised();
        }

        /** @var Input $oInput */
        $oInput  = Factory::service('Input');
        $oModel  = $this->getModel();
        $aConfig = $this->getConfig();

        $sAlias      = $oModel->getTableAlias();
        $aSortConfig = $aConfig['SORT_OPTIONS'];

        if (classUses($oModel, Nestable::class)) {
            $aSortConfig = array_merge(['Hierarchy' => 'order'], $aSortConfig);
        }

        //  Get the first key (i.e the default sort)
        $sFirstKey = reset($aSortConfig);

        //  Prepare the sort options so they have the appropriate table alias
        $aSortCol = [];
        foreach ($aSortConfig as $sLabel => $sColumn) {
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
        $sSortOrder = $oInput->get('sortOrder') ? $oInput->get('sortOrder') : $aConfig['SORT_DIRECTION'];
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
            ] + $aConfig['INDEX_DATA'];

        // --------------------------------------------------------------------------

        if (classUses($oModel, Localised::class)) {
            $aData['NO_LOCALISE_FILTER'] = true;
        }

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

        static::addHeaderButtons($aConfig['INDEX_HEADER_BUTTONS']);

        // --------------------------------------------------------------------------

        $this->data['page']->title = $aConfig['TITLE_PLURAL'] . ' &rsaquo; Manage';
        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new item
     *
     * @return void
     */
    public function create(): void
    {
        $aConfig = $this->getConfig();
        if (!$aConfig['CAN_CREATE']) {
            show404();
        } elseif (!static::userCan('create')) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        $oModel = $this->getModel();

        // --------------------------------------------------------------------------

        //  View Data & Assets
        $this->loadEditViewData();

        // --------------------------------------------------------------------------

        if ($oInput->post()) {
            try {

                $this->runFormValidation(static::EDIT_MODE_CREATE);
                $oDb->trans_begin();
                $this->beforeCreateAndEdit(static::EDIT_MODE_CREATE);
                $this->beforeCreate();

                if (classUses($oModel, Localised::class)) {

                    /** @var Locale $oLocale */
                    $oLocale = Factory::service('Locale');
                    /** @var \Nails\Common\Factory\Locale $oItemLocale */
                    $oItemLocale = Factory::factory('Locale');
                    $oLocale->setFromString($oItemLocale, $oInput->post('locale'));

                    $oItem = $oModel->create($this->getPostObject(), true, $oItemLocale);

                } else {
                    $oItem = $oModel->create($this->getPostObject(), true);
                }

                if (!$oItem) {
                    throw new NailsException(static::CREATE_ERROR_MESSAGE . ' ' . $oModel->lastError());
                }

                $this->afterCreateAndEdit(static::EDIT_MODE_CREATE, $oItem);
                $this->afterCreate($oItem);
                $this->addToChangeLog(static::EDIT_MODE_CREATE, $oItem);
                $oDb->trans_commit();

                if (property_exists($oItem, 'url')) {
                    $sLink = anchor(
                        $oItem->url,
                        'View &nbsp;<span class="fa fa-external-link-alt"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } else {
                    $sLink = '';
                }

                /** @var Auth\Service\Session $oSession */
                $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);
                $oSession->setFlashData('success', sprintf(static::CREATE_SUCCESS_MESSAGE, $sLink));

                if ($aConfig['CAN_EDIT'] && static::userCan('edit')) {
                    if (classUses($oModel, Localised::class)) {
                        redirect($aConfig['BASE_URL'] . '/edit/' . $oItem->id . '/' . $oItem->locale);
                    } else {
                        redirect($aConfig['BASE_URL'] . '/edit/' . $oItem->id);
                    }
                } else {
                    $this->returnToIndex();
                }

            } catch (\Exception $e) {
                $oDb->trans_rollback();
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

        /** @var Uri $oUri */
        $oUri           = Factory::service('Uri');
        $iExistingId    = (int) $oUri->segment(5) ?: null;
        $sDesiredLocale = $oUri->segment(6) ?: null;
        if (classUses($oModel, Localised::class) && !$iExistingId) {

            /** @var Locale $oLocale */
            $oLocale = Factory::service('Locale');
            foreach ($aConfig['FIELDS'] as $oField) {
                if ($oField->key === 'locale') {
                    $oField->info .= ' New items must be created in ' . $oLocale->getDefautLocale()->getDisplayLanguage();
                    break;
                }
            }

        } elseif (classUses($oModel, Localised::class)) {

            //  Validate the ID, and only allow creation of new locales
            $aExisting = $oModel->getAll([
                'NO_LOCALISE_FILTER' => true,
                'where'              => [
                    ['id', $iExistingId],
                ],
            ]);

            if (empty($aExisting)) {
                show404();
            }

            //  Test if new locales can be created, filter out existing locales from the options list
            $aExistingLocales = arrayExtractProperty($aExisting, 'locale');
            foreach ($aConfig['FIELDS'] as $oField) {
                if ($oField->key === 'locale') {

                    $aDiff = array_diff(
                        array_keys($oField->options),
                        array_values($aExistingLocales)
                    );

                    if (empty($aDiff)) {
                        /** @var Auth\Service\Session $oSession */
                        $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);
                        $oSession->setFlashData('error', 'No more variations of this item can be created.');
                        $this->returnToIndex();
                    }

                    $oField->options = array_intersect_key(
                        $oField->options,
                        array_flip($aDiff)
                    );

                    $oField->default = $sDesiredLocale;

                    unset($this->aConfig['CREATE_READONLY_FIELDS'][array_search('locale', $this->aConfig['CREATE_READONLY_FIELDS'])]);
                    break;
                }
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = $aConfig['TITLE_SINGLE'] . ' &rsaquo; Create';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit an existing item
     *
     * @return void
     */
    public function edit(): void
    {
        $aConfig = $this->getConfig();
        if (!$aConfig['CAN_EDIT']) {
            show404();
        } elseif (!static::userCan('edit')) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $oModel = $this->getModel();
        $oItem  = $this->getItem($aConfig['EDIT_DATA']);

        // --------------------------------------------------------------------------

        //  View Data & Assets
        $this->loadEditViewData($oItem);

        // --------------------------------------------------------------------------

        if ($oInput->post()) {
            try {

                $this->runFormValidation(static::EDIT_MODE_EDIT);
                $oDb->trans_begin();
                $this->beforeCreateAndEdit(static::EDIT_MODE_EDIT, $oItem);
                $this->beforeEdit($oItem);

                if (classUses($oModel, Localised::class)) {
                    $bResult = $oModel->update($oItem->id, $this->getPostObject(), $oItem->locale);
                } else {
                    $bResult = $oModel->update($oItem->id, $this->getPostObject());
                }

                if (!$bResult) {
                    throw new NailsException(static::EDIT_ERROR_MESSAGE . ' ' . $oModel->lastError());
                }

                if (classUses($oModel, Localised::class)) {
                    $oNewItem = $this->getItem(
                        array_merge(
                            $aConfig['EDIT_DATA'],
                            ['USE_LOCALE' => $oInput->post('locale')]
                        )
                    );
                } else {
                    $oNewItem = $this->getItem($aConfig['EDIT_DATA']);
                }
                $this->afterCreateAndEdit(static::EDIT_MODE_EDIT, $oNewItem, $oItem);
                $this->afterEdit($oNewItem, $oItem);
                $this->addToChangeLog(static::EDIT_MODE_EDIT, $oNewItem, $oItem);
                $oDb->trans_commit();

                if (property_exists($oNewItem, 'url')) {
                    $sLink = anchor(
                        $oNewItem->url,
                        'View &nbsp;<span class="fa fa-external-link-alt"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } else {
                    $sLink = '';
                }

                /** @var Auth\Service\Session $oSession */
                $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);
                $oSession->setFlashData('success', sprintf(static::EDIT_SUCCESS_MESSAGE, $sLink));

                if (classUses($oModel, Localised::class)) {
                    redirect($aConfig['BASE_URL'] . '/edit/' . $oItem->id . '/' . $oItem->locale);
                } else {
                    redirect($aConfig['BASE_URL'] . '/edit/' . $oItem->id);
                }

            } catch (\Exception $e) {
                $oDb->trans_rollback();
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

        static::addHeaderButtons($aConfig['EDIT_HEADER_BUTTONS']);

        // --------------------------------------------------------------------------

        $this->data['page']->title = $aConfig['TITLE_SINGLE'] . ' &rsaquo; Edit';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an item
     *
     * @return void
     */
    public function delete(): void
    {
        $aConfig = $this->getConfig();
        if (!$aConfig['CAN_DELETE']) {
            show404();
        } elseif (!static::userCan('delete')) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Auth\Service\Session $oSession */
        $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);

        $oModel = $this->getModel();
        $oItem  = $this->getItem();

        if (empty($oItem)) {
            show404();
        }

        try {

            $oDb->trans_begin();
            $this->beforeDelete($oItem);

            if (classUses($oModel, Localised::class)) {
                $oModel->delete($oItem->id, $oItem->locale);
            } elseif (!$oModel->delete($oItem->id)) {
                throw new NailsException(static::DELETE_ERROR_MESSAGE . ' ' . $oModel->lastError());
            }

            $this->afterDelete($oItem);
            $this->addToChangeLog(static::EDIT_MODE_DELETE, $oItem);
            $oDb->trans_commit();

            if ($aConfig['CAN_RESTORE'] && static::userCan('restore')) {
                if (classUses($oModel, Localised::class)) {
                    $sRestoreLink = anchor(
                        $aConfig['BASE_URL'] . '/restore/' . $oItem->id . '/' . $oItem->locale,
                        'Restore?'
                    );
                } else {
                    $sRestoreLink = anchor(
                        $aConfig['BASE_URL'] . '/restore/' . $oItem->id,
                        'Restore?'
                    );
                }
            } else {
                $sRestoreLink = '';
            }

            $oSession->setFlashData('success', static::DELETE_SUCCESS_MESSAGE . ' ' . $sRestoreLink);
            $this->returnToIndex();

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $oSession->setFlashData('error', static::DELETE_ERROR_MESSAGE . ' ' . $e->getMessage());
            $this->returnToIndex();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Restore an item
     *
     * @return void
     */
    public function restore(): void
    {
        $aConfig = $this->getConfig();
        if (!$aConfig['CAN_RESTORE']) {
            show404();
        } elseif (!static::userCan('restore')) {
            unauthorised();
        }

        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Auth\Service\Session $oSession */
        $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);

        $oModel = $this->getModel();
        $oItem  = $this->getItem([], null, true);

        try {

            $oDb->trans_begin();
            if (classUses($oModel, Localised::class)) {
                $bResult = $oModel->restore($oItem->id, $oItem->locale);
            } else {
                $bResult = $oModel->restore($oItem->id);
            }

            if (!$bResult) {
                throw new NailsException(static::RESTORE_ERROR_MESSAGE . ' ' . $oModel->lastError());
            }

            $this->addToChangeLog(static::EDIT_MODE_RESTORE, $oItem);
            $oDb->trans_commit();
            $oSession->setFlashData('success', static::RESTORE_SUCCESS_MESSAGE);
            $this->returnToIndex();

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $oSession->setFlashData('error', static::RESTORE_ERROR_MESSAGE . ' ' . $e->getMessage());
            $this->returnToIndex();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sort items into order
     *
     * @return void
     */
    public function sort(): void
    {
        $aConfig = $this->getConfig();
        if (!$aConfig['CAN_EDIT']) {
            show404();
        } elseif (!static::userCan('edit')) {
            unauthorised();
        }

        $oModel = $this->getModel();

        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var Database $oDb */
        $oDb = Factory::service('Database');

        if ($oInput->post()) {
            try {

                $oDb->trans_begin();
                $aItems = array_values((array) $oInput->post('order'));
                foreach ($aItems as $iOrder => $iId) {

                    if (classUses($oModel, Localised::class)) {

                        $aItems = $oModel->getAll([
                            'NO_LOCALISE_FILTER' => true,
                            'where'              => [
                                ['id', $iId],
                            ],
                        ]);

                        foreach ($aItems as $oItem) {
                            if (!$oModel->update($iId, ['order' => $iOrder], $oItem->locale)) {
                                throw new NailsException(
                                    static::ORDER_ERROR_MESSAGE . ' ' . $oModel->lastError()
                                );
                            }
                        }

                    } elseif (!$oModel->update($iId, ['order' => $iOrder])) {
                        throw new NailsException(
                            static::ORDER_ERROR_MESSAGE . ' ' . $oModel->lastError()
                        );
                    }
                }

                //  @todo (Pablo - 2019-10-30) - Add changelog support here

                $oDb->trans_commit();

                /** @var Auth\Service\Session $oSession */
                $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);
                $oSession->setFlashData('success', static::ORDER_SUCCESS_MESSAGE);

                redirect($aConfig['BASE_URL'] . '/sort');

            } catch (\Exception $e) {
                $oDb->trans_rollback();
                $this->data['error'] = $e->getMessage();
            }
        }

        $aItems                    = $oModel->getAll($aConfig['SORT_DATA']);
        $this->data['items']       = $aItems;
        $this->data['page']->title = $aConfig['TITLE_PLURAL'] . ' &rsaquo; Sort';
        Helper::loadView('order');
    }

    // --------------------------------------------------------------------------

    /**
     * Duplicate an item
     *
     * @throws FactoryException
     */
    public function copy()
    {
        if (!static::userCan('create') || !static::userCan('edit')) {
            unauthorised();
        }

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();
        $oItem   = $this->getItem();

        if (empty($oItem)) {
            show404();
        }

        /** @var Session $oSession */
        $oSession = Factory::service('Session', Auth\Constants::MODULE_SLUG);

        try {

            $iNewId = $oModel->copy($oItem->id);
            if (empty($iNewId)) {
                throw new \Exception($oModel->lastError());
            }

            //  @todo (Pablo - 2019-12-10) - Add support for classes which implement Localised trait

            $oSession->setFlashData('success', static::COPY_SUCCESS_MESSAGE);
            redirect($aConfig['BASE_URL'] . '/edit/' . $iNewId);

        } catch (\Exception $e) {
            $oSession->setFlashData('error', 'Failed to copy item. ' . $e->getMessage());
            $this->returnToIndex();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the controllers config
     *
     * @return array
     * @throws NailsException
     * @throws FactoryException
     */
    protected function &getConfig(): array
    {
        if (!empty($this->aConfig)) {
            return $this->aConfig;
        }

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
            'CAN_RESTORE'            => static::CONFIG_CAN_RESTORE && !$oModel->isDestructiveDelete(),
            'PERMISSION'             => static::CONFIG_PERMISSION,
            'TITLE_SINGLE'           => static::getTitleSingle(),
            'TITLE_PLURAL'           => static::getTitlePlural(),
            'SIDEBAR_GROUP'          => static::getSidebarGroup(),
            'SIDEBAR_ICON'           => static::CONFIG_SIDEBAR_ICON,
            'BASE_URL'               => static::getBaseUrl(),
            'SORT_OPTIONS'           => static::CONFIG_SORT_OPTIONS,
            'SORT_DIRECTION'         => static::CONFIG_SORT_DIRECTION,
            'INDEX_FIELDS'           => static::CONFIG_INDEX_FIELDS,
            'INDEX_HEADER_BUTTONS'   => static::CONFIG_INDEX_HEADER_BUTTONS,
            'INDEX_ROW_BUTTONS'      => array_merge(static::$aConfigIndexRowButtons, static::CONFIG_INDEX_ROW_BUTTONS),
            'INDEX_DATA'             => static::CONFIG_INDEX_DATA,
            'INDEX_BOOL_FIELDS'      => static::CONFIG_INDEX_BOOL_FIELDS,
            'INDEX_USER_FIELDS'      => static::CONFIG_INDEX_USER_FIELDS,
            'INDEX_NUMERIC_FIELDS'   => static::CONFIG_INDEX_NUMERIC_FIELDS,
            'INDEX_CENTERED_FIELDS'  => static::CONFIG_INDEX_CENTERED_FIELDS,
            'INDEX_PAGE_ID'          => static::CONFIG_INDEX_PAGE_ID,
            'CREATE_READONLY_FIELDS' => static::CONFIG_CREATE_READONLY_FIELDS,
            'CREATE_IGNORE_FIELDS'   => static::CONFIG_CREATE_IGNORE_FIELDS,
            'EDIT_HEADER_BUTTONS'    => static::CONFIG_EDIT_HEADER_BUTTONS,
            'EDIT_READONLY_FIELDS'   => static::CONFIG_EDIT_READONLY_FIELDS,
            'EDIT_IGNORE_FIELDS'     => static::CONFIG_EDIT_IGNORE_FIELDS,
            'EDIT_DATA'              => static::CONFIG_EDIT_DATA,
            'EDIT_PAGE_ID'           => static::CONFIG_EDIT_PAGE_ID,
            'SORT_DATA'              => static::CONFIG_SORT_DATA,
            'SORT_LABEL'             => static::CONFIG_SORT_LABEL,
            'SORT_COLUMNS'           => static::CONFIG_SORT_COLUMNS,
            'FIELDSET_ORDER'         => static::CONFIG_EDIT_FIELDSET_ORDER,
            'ENABLE_NOTES'           => static::EDIT_ENABLE_NOTES,
            'FIELDS'                 => $oModel->describeFields(),
        ];

        if (classUses($oModel, Sortable::class)) {
            $aConfig['SORT_OPTIONS']           = array_merge(['Defined Order' => 'order'], $aConfig['SORT_OPTIONS']);
            $aConfig['CREATE_IGNORE_FIELDS'][] = $oModel->getSortableColumn();
            $aConfig['EDIT_IGNORE_FIELDS'][]   = $oModel->getSortableColumn();
        }

        if (classUses($oModel, Nestable::class)) {
            $aConfig['CREATE_IGNORE_FIELDS'][] = $oModel->getBreadcrumbsColumn();
            $aConfig['CREATE_IGNORE_FIELDS'][] = $oModel->getOrderColumn();
            $aConfig['EDIT_IGNORE_FIELDS'][]   = $oModel->getBreadcrumbsColumn();
            $aConfig['EDIT_IGNORE_FIELDS'][]   = $oModel->getOrderColumn();
        }

        if (classUses($oModel, Localised::class)) {
            $aConfig['CREATE_READONLY_FIELDS'][] = 'locale';
            $aConfig['EDIT_READONLY_FIELDS'][]   = 'locale';
            $aConfig['INDEX_FIELDS']             = array_merge(
                [
                    'Locale' => function ($oRow) {
                        $sFlag = $oRow->locale->getFlagEmoji();
                        return $sFlag ? '<span rel="tipsy" title="' . $oRow->locale->getDisplayLanguage() . '">' . $sFlag . '</span>' : $oRow->locale;
                    },
                ],
                $aConfig['INDEX_FIELDS']
            );
        }

        // --------------------------------------------------------------------------

        if ($aConfig['CAN_CREATE'] && static::userCan('create') && classUses($oModel, Localised::class)) {
            $oItem = $this->getItem([], null, false, false);
            if (!empty($oItem)) {
                $aVersions = [];
                foreach ($oItem->missing_locales as $oLocale) {
                    $aVersions['Create ' . $oLocale->getDisplayLanguage()] = $aConfig['BASE_URL'] . '/create/' . $oItem->id . '/' . $oLocale;
                }
                $aConfig['EDIT_HEADER_BUTTONS'][] = [
                    $aVersions,
                    'Create Version',
                    'btn btn-warning',
                ];
            }
        }

        if ($aConfig['CAN_CREATE'] && static::userCan('create')) {
            $aConfig['INDEX_HEADER_BUTTONS'][] = [
                $aConfig['BASE_URL'] . '/create',
                'Create',
            ];
            $aConfig['EDIT_HEADER_BUTTONS'][]  = [
                $aConfig['BASE_URL'] . '/create',
                'Create',
            ];
        }

        if ($aConfig['CAN_EDIT'] && static::userCan('edit') && classUses($oModel, Sortable::class)) {
            $aConfig['INDEX_HEADER_BUTTONS'][] = [
                $aConfig['BASE_URL'] . '/sort',
                'Set Order',
            ];
        }

        // --------------------------------------------------------------------------

        $bIsLocalised = classUses(static::getModel(), Localised::class);
        $bIsCopyable  = classUses(static::getModel(), Copyable::class);

        /** @var Locale $oLocale */
        $oLocale = Factory::service('Locale');

        $aConfig['INDEX_ROW_BUTTONS'] = array_merge(
            $aConfig['INDEX_ROW_BUTTONS'],
            array_filter([
                [
                    'url'     => '{{url}}',
                    'label'   => lang('action_view'),
                    'class'   => 'btn-default',
                    'attr'    => 'target="_blank"',
                    'enabled' => function ($oItem) {
                        return static::isViewButtonEnabled($oItem);
                    },
                ],

                //  Non-localised buttons
                !$bIsLocalised ? [
                    'url'     => 'edit/{{id}}',
                    'label'   => lang('action_edit'),
                    'class'   => 'btn-primary',
                    'enabled' => function ($oItem) {
                        return static::isEditButtonEnabled($oItem);
                    },
                ] : null,
                !$bIsLocalised ? [
                    'url'     => 'delete/{{id}}',
                    'label'   => lang('action_delete'),
                    'class'   => 'btn-danger confirm',
                    'enabled' => function ($oItem) {
                        return static::isDeleteButtonEnabled($oItem);
                    },
                ] : null,

                //  Localised buttons
                $bIsLocalised ? [
                    'url'     => 'edit/{{id}}/{{locale}}',
                    'label'   => lang('action_edit'),
                    'class'   => 'btn-primary',
                    'enabled' => function ($oItem) {
                        return static::isEditButtonEnabled($oItem);
                    },
                ] : null,
                $bIsLocalised ? [
                    'url'   => function ($oItem) {
                        $aOut = [];
                        foreach ($oItem->missing_locales as $oLocale) {
                            $aOut['Add ' . $oLocale->getDisplayLanguage()] = 'create/{{id}}/' . $oLocale;
                        }
                        return $aOut;
                    },
                    'label' => 'Create Version',
                    'class' => function ($oItem) {
                        if (!static::localisedItemCanBeCreated($oItem)) {
                            return 'btn-warning btn-disabled';
                        }
                        return 'btn-warning';
                    },
                    'attr'  => function ($oItem) {
                        if (!static::localisedItemCanBeCreated($oItem)) {
                            //  @todo (Pablo - 2019-04-17) - Explicitly state why
                            return 'rel="tipsy" title="A new version cannot be created; you may not have permission, ' .
                                'or all supported locales exist already"';
                        }
                        return '';
                    },
                ] : null,
                $bIsLocalised ? [
                    'url'     => 'delete/{{id}}/{{locale}}',
                    'label'   => lang('action_delete'),
                    'class'   => function ($oItem) {
                        if (!static::localisedItemCanBeDeleted($oItem)) {
                            return 'btn-danger btn-disabled';
                        }
                        return 'btn-danger confirm';
                    },
                    'attr'    => function ($oItem) {
                        if (!static::localisedItemCanBeDeleted($oItem)) {
                            //  @todo (Pablo - 2019-04-17) - Explicitly state why
                            return 'rel="tipsy" title="This item cannot be deleted; you may not have permission, ' .
                                'or other locales may exist which need to be deleted first"';
                        }
                        return '';
                    },
                    'enabled' => function ($oItem) use ($oLocale) {
                        return static::isDeleteButtonEnabled($oItem);
                    },
                ] : null,

                $bIsCopyable ? [
                    'url'     => 'copy/{{id}}',
                    'label'   => 'Copy',
                    'class'   => 'btn-default',
                    'enabled' => function ($oItem) {
                        return static::userCan('edit') && static::userCan('create');
                    },
                ] : null,
            ])
        );

        // --------------------------------------------------------------------------

        $this->aConfig        =& $aConfig;
        $this->data['CONFIG'] =& $this->aConfig;
        return $this->aConfig;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether a user has permission to create a localised item
     *
     * @param Resource $oItem The item to test
     *
     * @return bool
     */
    protected static function localisedItemCanBeCreated(Resource $oItem)
    {
        //  New versions can only be created if the user has permissions and there is a remaining supported locale
        if (static::CONFIG_CAN_CREATE && static::userCan('create')) {
            return !empty($oItem->missing_locales);
        }
        return false;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether a localised item can be delted
     *
     * @param Resource $oItem The item to test
     *
     * @return bool
     * @throws FactoryException
     */
    protected static function localisedItemCanBeDeleted(Resource $oItem)
    {
        /** @var Locale $oLocale */
        $oLocale        = Factory::service('Locale');
        $sDefaultLocale = (string) $oLocale->getDefautLocale();
        $sItemLocale    = (string) $oItem->locale;
        if ($sDefaultLocale !== $sItemLocale) {
            return true;
        } elseif ($sDefaultLocale === $sItemLocale && count($oItem->available_locales) === 1) {
            return true;
        } else {
            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the string to use as the "single" title
     *
     * @return string
     */
    protected static function getTitleSingle(): string
    {
        if (!empty(static::CONFIG_TITLE_SINGLE)) {
            return static::CONFIG_TITLE_SINGLE;
        }

        $sTitle = preg_replace('/([a-z])([A-Z])/', '$1 $2', static::CONFIG_MODEL_NAME);
        $sTitle = strtolower($sTitle);
        $sTitle = ucwords($sTitle);

        return $sTitle;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the title to use as the "plural" title
     *
     * @return string
     * @throws FactoryException
     */
    protected static function getTitlePlural(): string
    {
        Factory::helper('inflector');
        return static::CONFIG_TITLE_PLURAL ?: pluralise(2, static::getTitleSingle());
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the value to use for the sidebar group
     *
     * @return string
     * @throws FactoryException
     */
    protected static function getSidebarGroup(): string
    {
        return static::CONFIG_SIDEBAR_GROUP ?: static::getTitlePlural();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the base URL
     *
     * @return string
     */
    protected static function getBaseUrl(): string
    {
        if (!empty(static::CONFIG_BASE_URL)) {
            return static::CONFIG_BASE_URL;
        }

        $aBits   = explode('\\', get_called_class());
        $sModule = strtolower($aBits[count($aBits) - 2]);
        $sClass  = lcfirst($aBits[count($aBits) - 1]);
        return 'admin/' . $sModule . '/' . $sClass;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether a user has permission to perform an action
     *
     * @param string $sPermission The permission to check
     *
     * @return bool
     */
    protected static function userCan(string $sPermission): bool
    {
        return empty(static::CONFIG_PERMISSION) || userHasPermission('admin:' . static::CONFIG_PERMISSION . ':' . $sPermission);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the model instance
     *
     * @return \Nails\Common\Model\Base
     * @throws FactoryException
     */
    protected static function getModel(): \Nails\Common\Model\Base
    {
        return Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Any checkbox style filters to include on the index page
     *
     * @return array
     */
    protected function indexCheckboxFilters(): array
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Any dropdown style filters to include on the index page
     *
     * @return array
     */
    protected function indexDropdownFilters(): array
    {
        /** @var IndexFilter $aFilters */
        $aFilters = [];
        if (classUses(static::getModel(), Localised::class)) {

            /** @var Locale $oLocale */
            $oLocale = Factory::service('Locale');

            /** @var Option[] $aOptions */
            $aOptions   = [];
            $aOptions[] = Factory::factory('IndexFilterOption', 'nails/module-admin')
                ->setLabel('All Locales');

            foreach ($oLocale->getSupportedLocales() as $oSupportedLocale) {
                $aOptions[] = Factory::factory('IndexFilterOption', 'nails/module-admin')
                    ->setLabel($oSupportedLocale->getDisplayLanguage())
                    ->setValue($oSupportedLocale->getLanguage() . '_' . $oSupportedLocale->getRegion());
            }

            $aFilters[] = Factory::factory('IndexFilter', 'nails/module-admin')
                ->setLabel('Locale')
                ->setColumn('CONCAT(`language`, \'_\', `region`)')
                ->addOptions($aOptions);
        }

        return $aFilters;
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param string   $sMode Whether the action was CREATE or EDIT
     * @param Resource $oItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeCreateAndEdit($sMode, Resource $oItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param Resource $oItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeEdit(Resource $oItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is created
     *
     * @return void
     */
    protected function beforeCreate(): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is deleted
     *
     * @param Resource $oItem The item being deleted
     *
     * @return void
     */
    protected function beforeDelete(Resource $oItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param string   $sMode    Whether the action was CREATE or EDIT
     * @param Resource $oNewItem The new item, after it was edited
     * @param Resource $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterCreateAndEdit($sMode, Resource $oNewItem, Resource $oOldItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param Resource $oNewItem The new item, after it was edited
     * @param Resource $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterEdit(Resource $oNewItem, Resource $oOldItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is created
     *
     * @param Resource $oNewItem The new item
     *
     * @return void
     */
    protected function afterCreate(Resource $oNewItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is deleted
     *
     * @param Resource $oItem The deleted item
     *
     * @return void
     */
    protected function afterDelete(Resource $oItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     *
     * @param string $sMode      The mode in which the validation is being run
     * @param array  $aOverrides Any overrides for the fields; best to do this in the model's describeFields() method
     *
     * @return void
     * @throws ValidationException
     */
    protected function runFormValidation(string $sMode, array $aOverrides = []): void
    {
        $aConfig = $this->getConfig();
        $oModel  = static::getModel();
        /** @var FormValidation $oFormValidation */
        $oFormValidation = Factory::service('FormValidation');

        $aRulesFormValidation = [];
        $aImplementedRules    = [];

        foreach ($aConfig['FIELDS'] as &$oField) {

            if ($sMode === static::EDIT_MODE_CREATE && classUses($oModel, Localised::class)) {
                if ($oField->key == 'locale') {

                    /** @var Locale $oLocale */
                    $oLocale = Factory::service('Locale');
                    /** @var Uri $oUri */
                    $oUri = Factory::service('Uri');
                    if (empty($oUri->segment(5))) {
                        $oField->validation[] = 'is[' . $oLocale->getDefautLocale() . ']';
                    }
                }
            }

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

        if (!$oFormValidation->run($this)) {
            throw new ValidationException(lang('fv_there_were_errors'));
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load data for the edit/create view
     *
     * @param Resource $oItem The main item object
     *
     * @throws FactoryException
     * @throws NailsException
     */
    protected function loadEditViewData(Resource $oItem = null): void
    {
        $aConfig = $this->getConfig();
        $aFields = $aConfig['FIELDS'];
        foreach ($aFields as $oField) {
            $this->loadEditViewDataSetDefaultValue($oField, $oItem);
            $this->loadEditViewDataSetRequired($oField);
            $this->loadEditViewDataSetReadOnly($oField, $oItem);
        }

        $this->data['aFieldSets'] = $this->loadEditViewDataSetFieldsets($aFields);
        $this->data['item']       = $oItem;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the "default" property for a field in the edit view
     *
     * @param Field         $oField The field being set
     * @param Resource|null $oItem  The item being edited
     */
    protected function loadEditViewDataSetDefaultValue(Field &$oField, Resource $oItem = null)
    {
        if ($oField->default instanceof \Closure) {

            $oField->default = call_user_func($oField->default, $oItem);

        } elseif (!is_null($oItem) && property_exists($oItem, $oField->key)) {

            if ($oItem->{$oField->key} instanceof Resource\ExpandableField) {
                $oField->default = $oItem->{$oField->key}->data;
            } else {
                $oField->default = $oItem->{$oField->key};
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the "required" property for a field in the edit view
     *
     * @param Field $oField The field being set
     */
    protected function loadEditViewDataSetRequired(Field &$oField)
    {
        if (!property_exists($oField, 'required')) {
            $oField->required = in_array('required', $oField->validation);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the "required" property for a field in the edit view
     *
     * @param Field         $oField The field being set
     * @param Resource|null $oItem  The item being edited
     *
     * @throws FactoryException
     * @throws NailsException
     */
    protected function loadEditViewDataSetReadOnly(Field &$oField, Resource $oItem = null)
    {
        $aConfig = $this->getConfig();
        if (!is_null($oItem)) {
            $oField->readonly = in_array($oField->key, $aConfig['EDIT_READONLY_FIELDS']);
        } else {
            $oField->readonly = in_array($oField->key, $aConfig['CREATE_READONLY_FIELDS']);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Organises fields into their fieldsets
     *
     * @param Field[] $aFields The fields being organised
     *
     * @return Field[]
     * @throws FactoryException
     * @throws NailsException
     */
    protected function loadEditViewDataSetFieldsets(array $aFields): array
    {
        //  Extract the fields into fieldsets
        $aConfig    = $this->getConfig();
        $aFieldSets = array_combine(
            $aConfig['FIELDSET_ORDER'],
            array_pad(
                [],
                count($aConfig['FIELDSET_ORDER']),
                []
            )
        );

        //  Organsie fields into the fieldsets
        foreach ($aFields as $oField) {

            if (empty($oItem)) {
                if (in_array($oField->key, $aConfig['CREATE_IGNORE_FIELDS'])) {
                    continue;
                }
            } else {
                if (in_array($oField->key, $aConfig['EDIT_IGNORE_FIELDS'])) {
                    continue;
                }
            }

            $sFieldSet = $oField->fieldset;

            if (!array_key_exists($sFieldSet, $aFieldSets)) {
                $aFieldSets[$sFieldSet] = [];
            }

            $aFieldSets[$sFieldSet][] = $oField;
        }

        return array_filter($aFieldSets);
    }

    // --------------------------------------------------------------------------

    /**
     * Extract data from post variable
     *
     * @return array
     */
    protected function getPostObject(): array
    {
        $aConfig = $this->getConfig();
        $oModel  = static::getModel();

        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');

        $aOut = [];

        foreach ($aConfig['FIELDS'] as $oField) {
            if (in_array($oField->key, $aConfig['EDIT_IGNORE_FIELDS'])) {
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

        if (classUses($oModel, Localised::class)) {
            $iExistingId = $oUri->segment(5);
            if ($oUri->segment(5)) {
                $aOut['id'] = $oUri->segment(5);
            }
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the item being requested, thorwing a 404 if it's not found
     *
     * @param array   $aData    Data to pass to the getById method
     * @param integer $iSegment The URL segment contianing the ID
     *
     * @return mixed
     * @throws FactoryException
     */
    protected function getItem(array $aData = [], int $iSegment = null, bool $bIncludeDeleted = false, bool $b404 = true)
    {
        $iSegment = $iSegment ?? 5;

        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');

        $oModel  = $this->getModel();
        $iItemId = (int) $oUri->segment($iSegment);

        if (classUses($oModel, Localised::class) && $oUri->segment($iSegment + 1)) {
            $aData['USE_LOCALE'] = $oUri->segment($iSegment + 1);
        }

        if (!array_key_exists('where', $aData)) {
            $aData['where'] = [];
        }

        $aData['where'][] = [$oModel->getTableAlias() . '.' . $oModel->getColumn('id'), $iItemId];

        $aItems = $oModel->getAll(
            null,
            null,
            $aData,
            $bIncludeDeleted
        );

        $oItem = reset($aItems);

        if ($b404 && empty($oItem)) {
            show404();
        }

        return $oItem;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "View" row button is enabled
     *
     * @param Resource $oItem The row item
     *
     * @return bool
     */
    protected static function isViewButtonEnabled($oItem): bool
    {
        return static::CONFIG_CAN_VIEW && property_exists($oItem, 'url');
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Edit" row button is enabled
     *
     * @param Resource $oItem The row item
     *
     * @return bool
     */
    protected static function isEditButtonEnabled($oItem): bool
    {
        return static::CONFIG_CAN_EDIT && static::userCan('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Delete" row button is enabled
     *
     * @param Resource $oItem The row item
     *
     * @return bool
     */
    protected static function isDeleteButtonEnabled($oItem): bool
    {
        return static::CONFIG_CAN_DELETE && static::userCan('delete');
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the user to the index pagel if a referrer is available then go there instead
     * This is useful for returning the user to a filtered view
     *
     * @throws FactoryException
     */
    protected function returnToIndex(): void
    {
        /** @var Input $oInput */
        $oInput    = Factory::service('Input');
        $sReferrer = $oInput->server('HTTP_REFERER');

        if (!empty($sReferrer)) {
            redirect($sReferrer);
        } else {
            redirect($this->getConfig()['BASE_URL']);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds buttons to the header area
     *
     * @param array $aButtons the buttons to add
     */
    protected function addHeaderButtons(array $aButtons): void
    {
        foreach ($aButtons as $aButton) {

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
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an appropriate action to the ChangeLog
     *
     * @param string               $sMode    The update mode
     * @param Resource\Entity      $oItem    The current version of the item
     * @param Resource\Entity|null $oOldItem the old version of the item
     *
     * @throws FactoryException
     * @throws ModelException
     */
    protected function addToChangeLog(
        string $sMode,
        Resource\Entity $oItem,
        Resource\Entity $oOldItem = null
    ): void {

        if (static::CHANGELOG_ENABLED) {
            switch ($sMode) {
                case static::EDIT_MODE_CREATE:
                    $this->addToChangeLogCreate($oItem);
                    break;
                case static::EDIT_MODE_EDIT:
                    $this->addToChangeLogEdit($oItem, $oOldItem);
                    break;
                case static::EDIT_MODE_DELETE:
                    $this->addToChangeLogDelete($oItem);
                    break;
                case static::EDIT_MODE_RESTORE:
                    $this->addToChangeLogRestore($oItem);
                    break;
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a "Create" item to the ChangeLog
     *
     * @param Resource\Entity $oItem The item which was created
     *
     * @throws FactoryException
     * @throws ModelException
     */
    protected function addToChangeLogCreate(Resource\Entity $oItem): void
    {
        $this
            ->oChangeLogModel
            ->add(
                'created',
                'a',
                static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                $oItem->id,
                $oItem->label ?? 'Item #' . $oItem->id,
                $this->aConfig['BASE_URL'] . '/edit/' . $oItem->id
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an "Update" item to the ChangeLog
     *
     * @param Resource\Entity      $oItem    The new item
     * @param Resource\Entity|null $oOldItem The old item
     */
    protected function addToChangeLogEdit(Resource\Entity $oItem, Resource\Entity $oOldItem = null): void
    {
        $aNew = $this->changeLogFlattenObject($oItem);
        $aOld = $this->changeLogFlattenObject($oOldItem);

        $aSameKeys    = array_keys(array_intersect_key($aNew, $aOld));
        $aAddedKeys   = array_keys(array_diff_key($aNew, $aOld));
        $aRemovedKeys = array_keys(array_diff_key($aOld, $aNew));

        $aChangeData = [];
        foreach ($aSameKeys as $sKey) {
            $aChangeData[$sKey] = [
                getFromArray($sKey, $aOld),
                getFromArray($sKey, $aNew),
            ];
        }
        foreach ($aAddedKeys as $sKey) {
            $aChangeData[$sKey] = [
                getFromArray($sKey, $aOld),
                getFromArray($sKey, $aNew),
            ];
        }
        foreach ($aRemovedKeys as $sKey) {
            $aChangeData[$sKey] = [
                getFromArray($sKey, $aOld),
                getFromArray($sKey, $aNew),
            ];
        }

        foreach ($aChangeData as $sKey => $aValues) {

            [$sOldValue, $sNewValue] = $aValues;
            $bForce = false;
            if (in_array($sKey, static::CHANGELOG_FIELDS_REDACT)) {
                $bForce    = $sOldValue !== $sNewValue;
                $sOldValue = '[REDACTED]';
                $sNewValue = '[REDACTED]';
            }

            $this
                ->oChangeLogModel
                ->add(
                    'updated',
                    'a',
                    static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                    $oItem->id,
                    $oItem->label ?? 'Item #' . $oItem->id,
                    $this->aConfig['BASE_URL'] . '/edit/' . $oItem->id,
                    $sKey,
                    $sOldValue,
                    $sNewValue,
                    false,
                    $bForce
                );
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a "Delete" item to the ChangeLog
     *
     * @param Resource\Entity $oItem The deleted item
     */
    protected function addToChangeLogDelete(Resource\Entity $oItem): void
    {
        $this
            ->oChangeLogModel
            ->add(
                'deleted',
                'a',
                static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                $oItem->id,
                $oItem->label ?? 'Item #' . $oItem->id
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a "Restore" item to the ChangeLog
     *
     * @param Resource\Entity $oItem The restored item
     */
    protected function addToChangeLogRestore(Resource\Entity $oItem): void
    {
        $this
            ->oChangeLogModel
            ->add(
                'restored',
                'a',
                static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                $oItem->id,
                $oItem->label ?? 'Item #' . $oItem->id,
                $this->aConfig['BASE_URL'] . '/edit/' . $oItem->id
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Flattens the object suitable for the change log
     *
     * @param mixed        $mItem   The item to flattem
     * @param string       $sPrefix The prefix to give the key
     * @param null|integer $iDepth  The depth of the array
     *
     * @return array
     */
    protected function changeLogFlattenObject($mItem, string $sPrefix = '', $iDepth = null): array
    {
        $sPrefix = $sPrefix ? $sPrefix . '.' : '';
        $aOut    = [];

        foreach ($mItem as $sKey => $mValue) {

            if (in_array($sKey, static::CHANGELOG_FIELDS_IGNORE)) {
                continue;
            }

            if ($mValue instanceof Resource\ExpandableField) {

                foreach ($mValue->data as $iIndex => $mArrayValue) {
                    $aOut = array_merge(
                        $aOut,
                        $this->changeLogFlattenObject($mValue->data, $sPrefix . $sKey, $iIndex)
                    );
                }

            } elseif (is_object($mValue)) {

                $aOut = array_merge($aOut, $this->changeLogFlattenObject($mValue, $sPrefix . $sKey));

            } elseif (is_array($mValue)) {

                foreach ($mValue as $iIndex => $mArrayValue) {
                    $aOut = array_merge(
                        $aOut,
                        $this->changeLogFlattenObject($mValue, $sPrefix . $sKey, $iIndex)
                    );
                }

            } else {
                $aOut[$sPrefix . ($iDepth !== null ? $iDepth : '') . $sKey] = (string) $mValue;
            }
        }

        return $aOut;
    }
}
