<?php

/**
 * Admin changelog model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Common\Model\Base;
use Nails\Factory;

class ChangeLog extends Base
{
    /**
     * The chnages which are to be saved
     *
     * @var array
     */
    protected $aChanges;

    /**
     * Whether to save as a batch
     *
     * @var bool
     */
    protected $bBatchSave;

    // --------------------------------------------------------------------------

    /**
     * ChangeLog constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->table      = NAILS_DB_PREFIX . 'admin_changelog';
        $this->aChanges   = [];
        $this->bBatchSave = true;

        // --------------------------------------------------------------------------

        /**
         * Add a hook for after the controller is done so we can process the changes
         * and save to the DB.
         */
        $aHook = [
            'classref' => &$this,
            'method'   => 'save',
            'params'   => [],
        ];

        if (get_instance()->hooks->addHook('post_system', $aHook) === false) {
            $this->bBatchSave = false;
            Factory::service('Logger')
                ->line(get_class() . ' could not set the post_system hook to save items in batches.');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new changelog item
     *
     * @param string  $sVerb     The verb, e.g "created"
     * @param string  $sArticle
     * @param string  $sItem
     * @param integer $iItemId   The item's ID (e.g the blog post's ID)
     * @param string  $sTitle    The title of the item (e.g the blog post's title)
     * @param string  $sUrl      The url to the item (e.g the blog post's URL)
     * @param string  $sField
     * @param mixed   $mOldValue The old value
     * @param mixed   $mNewValue The new value
     * @param boolean $bStrict   whether or not to compare $mOldValue and $mNewValue strictly
     *
     * @return bool
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\ModelException
     */
    public function add(
        $sVerb,
        $sArticle,
        $sItem,
        $iItemId,
        $sTitle,
        $sUrl = null,
        $sField = null,
        $mOldValue = null,
        $mNewValue = null,
        $bStrict = true
    ) {
        /**
         * if the old_value and the new_value are the same then why are you logging
         * a change!? Lazy [read: efficient] dev.
         */

        if (!is_null($sField)) {

            if (!is_string($mNewValue)) {
                $mNewValue = print_r($mNewValue, true);
            }

            if (!is_string($mOldValue)) {
                $mOldValue = print_r($mOldValue, true);
            }

            $mNewValue = trim($mNewValue);
            $mOldValue = trim($mOldValue);

            if ($bStrict && $mNewValue === $mOldValue) {
                return false;
            } elseif ($mNewValue == $mOldValue) {
                return false;
            }
        }

        // --------------------------------------------------------------------------

        /**
         * Define the key for this change; keys should be common across identical
         * items so we can group changes of the same item together.
         */
        $key = md5(activeUser('id') . '|' . $sVerb . '|' . $sArticle . '|' . $sItem . '|' . $iItemId . '|' . $sTitle . '|' . $sUrl);

        if (empty($this->aChanges[$key])) {
            $this->aChanges[$key] = [
                'user_id' => activeUser('id') ? activeUser('id') : null,
                'verb'    => $sVerb,
                'article' => $sArticle,
                'item'    => $sItem,
                'item_id' => $iItemId,
                'title'   => $sTitle,
                'url'     => $sUrl,
                'changes' => [],
            ];
        }

        // --------------------------------------------------------------------------

        /**
         * Generate a subkey, so that multiple calls to the same field overwrite
         * each other
         */
        if ($sField) {
            $this->aChanges[$key]['changes'][md5($sField)] = (object) [
                'field'     => $sField,
                'old_value' => $mOldValue,
                'new_value' => $mNewValue,
            ];
        }

        // --------------------------------------------------------------------------

        //  If we're not saving  in batches then save now
        if (!$this->bBatchSave) {
            $this->save();
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Save the changelog items
     *
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\ModelException
     */
    public function save()
    {
        //  Process all the items and save to the DB, then clean up
        if ($this->aChanges) {

            $this->aChanges = array_values($this->aChanges);
            $oDate          = Factory::factory('DateTime');

            for ($i = 0; $i < count($this->aChanges); $i++) {
                $this->aChanges[$i]['changes']     = array_values($this->aChanges[$i]['changes']);
                $this->aChanges[$i]['changes']     = json_encode($this->aChanges[$i]['changes']);
                $this->aChanges[$i]['created']     = $oDate->format('Y-m-d H:i:s');
                $this->aChanges[$i]['created_by']  = activeUser('id');
                $this->aChanges[$i]['modified']    = $oDate->format('Y-m-d H:i:s');
                $this->aChanges[$i]['modified_by'] = activeUser('id');
            }

            $oDb = Factory::service('Database');
            $oDb->insert_batch($this->getTableName(), $this->aChanges);
        }

        $this->clear();
    }

    // --------------------------------------------------------------------------

    /**
     * Clear all recorded changes
     */
    public function clear()
    {
        $this->aChanges = [];
    }

    // --------------------------------------------------------------------------

    /**
     * Fetches all objects, optionally paginated.
     *
     * @param integer $iPage           The page number of the results, if null then no pagination
     * @param integer $iPerPage        How many items per page of paginated results
     * @param array   $aData           Any data to pass to getCountCommon()
     * @param bool    $bIncludeDeleted Whetehr to include deleted items
     *
     * @return array
     **/
    public function getAll($iPage = null, $iPerPage = null, array $aData = [], $bIncludeDeleted = false)
    {
        //  If the first value is an array then treat as if called with getAll(null, null, $aData);
        //  @todo (Pablo - 2017-06-29) - Refactor how this join works (use expandable field)
        if (is_array($iPage)) {
            $aData = $iPage;
            $iPage = null;
        }

        if (empty($aData['select'])) {
            $aData['select'] = [
                $this->tableAlias . '.*',
                'u.first_name',
                'u.last_name',
                'u.gender',
                'u.profile_img',
                'ue.email',
            ];
        }

        return parent::getAll($iPage, $iPerPage, $aData, $bIncludeDeleted);
    }

    // --------------------------------------------------------------------------

    /**
     * Applies common conditionals
     *
     * @param array $aData Data passed from the calling method
     *
     * @throws \Nails\Common\Exception\FactoryException
     **/
    protected function getCountCommon(array $aData = [])
    {
        //  Join user tables
        $oDb = Factory::service('Database');
        $oDb->join(NAILS_DB_PREFIX . 'user u', 'u.id = ' . $this->tableAlias . '.user_id', 'LEFT');
        $oDb->join(NAILS_DB_PREFIX . 'user_email ue', 'ue.user_id = ' . $this->tableAlias . '.user_id AND ue.is_primary = 1', 'LEFT');

        //  Searching?
        if (!empty($aData['keywords'])) {

            if (empty($aData['or_like'])) {
                $aData['or_like'] = [];
            }

            $toSlug = strtolower(str_replace(' ', '_', $aData['keywords']));

            $aData['or_like'][] = [
                'column' => $this->tableAlias . '.type',
                'value'  => $toSlug,
            ];
            $aData['or_like'][] = [
                'column' => 'ue.email',
                'value'  => $aData['keywords'],
            ];
        }

        parent::getCountCommon($aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Formats a single object
     *
     * The getAll() method iterates over each returned item with this method so as to
     * correctly format the output. Use this to cast integers and booleans and/or organise data into objects.
     *
     * @param  object $oObj      A reference to the object being formatted.
     * @param  array  $aData     The same data array which is passed to _getcount_common, for reference if needed
     * @param  array  $aIntegers Fields which should be cast as integers if numerical and not null
     * @param  array  $aBools    Fields which should be cast as booleans if not null
     * @param  array  $aFloats   Fields which should be cast as floats if not null
     */
    protected function formatObject(
        &$oObj,
        array $aData = [],
        array $aIntegers = [],
        array $aBools = [],
        array $aFloats = []
    ) {

        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);

        if (!empty($oObj->item_id)) {
            $oObj->item_id = (int) $oObj->item_id;
        }

        $oObj->changes = @json_decode($oObj->changes);
        $oObj->user    = (object) [
            'id'          => $oObj->user_id,
            'first_name'  => isset($oObj->first_name) ? $oObj->first_name : '',
            'last_name'   => isset($oObj->last_name) ? $oObj->last_name : '',
            'gender'      => isset($oObj->gender) ? $oObj->gender : '',
            'profile_img' => isset($oObj->profile_img) ? $oObj->profile_img : '',
            'email'       => isset($oObj->email) ? $oObj->email : '',
        ];

        unset($oObj->user_id);
        unset($oObj->first_name);
        unset($oObj->last_name);
        unset($oObj->gender);
        unset($oObj->profile_img);
        unset($oObj->email);
    }
}
