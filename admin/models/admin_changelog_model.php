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

class NAILS_Admin_changelog_model extends NAILS_Model
{
    protected $changes;
    protected $batchSave;

    // --------------------------------------------------------------------------

    /**
     * Constructs the model
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        //  Define data structure
        $this->_table        = NAILS_DB_PREFIX . 'admin_changelog';
        $this->_table_prefix = 'acl';

        // --------------------------------------------------------------------------

        //  Set defaults
        $this->changes   = array();
        $this->batchSave = true;

        // --------------------------------------------------------------------------

        /**
         * Add a hook for after the controller is done so we can process the changes
         * and save to the DB.
         */

        $hook             = array();
        $hook['classref'] = &$this;
        $hook['method']   = 'save';
        $hook['params']   = '';

        if ($GLOBALS['EXT']->add_hook('post_system', $hook) == false) {

            $this->batchSave = false;
            log_message('error', 'Admin_changelog_model could not set the post_controller hook to save items in batches.');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new changelog item
     * @param string  $verb     The verb, e.g "created"
     * @param string  $article
     * @param string  $item
     * @param string  $itemId   The item's ID (e.g the blog post's ID)
     * @param string  $title    The title of the item (e.g the blog post's title)
     * @param string  $url      The url to the item (e.g the blog post's URL)
     * @param string  $field
     * @param mixed   $oldValue The old value
     * @param mixed   $newValue The new value
     * @param boolean $strict   whether or not to compare $oldValue and $newValue strictly
     */
    public function add($verb, $article, $item, $itemId, $title, $url = null, $field = null, $oldValue = null, $newValue = null, $strict = true)
    {
        /**
         * if the old_value and the new_value are the same then why are you logging
         * a change!? Lazy [read: efficient] dev.
         */

        if (!is_null($field)) {

            if (!is_string($newValue)) {

                $newValue = print_r($newValue, true);
            }

            if (!is_string($oldValue)) {

                $oldValue = print_r($oldValue, true);
            }

            $newValue = trim($newValue);
            $oldValue = trim($oldValue);

            if ($strict) {

                if ($newValue === $oldValue) {

                    return false;
                }

            } else {

                if ($newValue == $oldValue) {

                    return false;
                }
            }
        }

        // --------------------------------------------------------------------------

        /**
         * Define the key for this change; keys should be common across identical
         * items so we can group changes of the same item together.
         */

        $key = md5(activeUser('id') . '|' . $verb . '|' . $article . '|' . $item . '|' . $itemId . '|' . $title . '|' . $url);

        if (empty($this->changes[$key])) {

            $this->changes[$key]            = array();
            $this->changes[$key]['user_id'] = activeUser('id') ? activeUser('id') : null;
            $this->changes[$key]['verb']    = $verb;
            $this->changes[$key]['article'] = $article;
            $this->changes[$key]['item']    = $item;
            $this->changes[$key]['item_id'] = $itemId;
            $this->changes[$key]['title']   = $title;
            $this->changes[$key]['url']     = $url;
            $this->changes[$key]['changes'] = array();
        }

        // --------------------------------------------------------------------------

        /**
         * Generate a subkey, so that multiple calls to the same field overwrite
         * each other
         */

        if ($field) {

            $subkey = md5($field);

            $this->changes[$key]['changes'][$subkey]            = new \stdClass();
            $this->changes[$key]['changes'][$subkey]->field     = $field;
            $this->changes[$key]['changes'][$subkey]->old_value = $oldValue;
            $this->changes[$key]['changes'][$subkey]->new_value = $newValue;
        }

        // --------------------------------------------------------------------------

        //  If we're not saving  in batches then save now
        if (!$this->batchSave) {

            $this->save();
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Save the changelog items
     * @return void
     */
    public function save()
    {
        //  Process all the items and save to the DB, then clean up
        if ($this->changes) {

            $this->changes = array_values($this->changes);

            for ($i = 0; $i < count($this->changes); $i++) {

                $this->changes[$i]['changes']     = array_values($this->changes[$i]['changes']);
                $this->changes[$i]['changes']     = serialize($this->changes[$i]['changes']);
                $this->changes[$i]['created']     = date('Y-m-d H:i:s');
                $this->changes[$i]['created_by']  = activeUser('id');
                $this->changes[$i]['modified']    = date('Y-m-d H:i:s');
                $this->changes[$i]['modified_by'] = activeUser('id');
            }

            $this->db->insert_batch($this->_table, $this->changes);
        }

        $this->clear();
    }

    // --------------------------------------------------------------------------

    /**
     * Clear all recorded changes
     * @return void
     */
    public function clear()
    {
        $this->changes = array();
    }

    /**
     * Fetches all objects, optionally paginated.
     * @param int    $page    The page number of the results, if null then no pagination
     * @param int    $perPage How many items per page of paginated results
     * @param mixed  $data    Any data to pass to _getcount_common()
     * @param string $_caller Internal flag to pass to _getcount_common(), contains the calling method
     * @return array
     **/
    public function get_all($page = null, $perPage = null, $data = array(), $_caller = 'GET_ALL')
    {
        $this->db->select($this->_table_prefix . '.*, u.first_name, u.last_name, u.gender, u.profile_img, ue.email');
        return parent::get_all($page, $perPage, $data, false, $_caller);
    }

    // --------------------------------------------------------------------------

    /**
     * Applies common conditionals
     * @param string $data    Data passed from the calling method
     * @param string $_caller The name of the calling method
     * @return void
     **/
    protected function _getcount_common($data = null, $caller = null)
    {
        //  Join user tables
        $this->db->join(NAILS_DB_PREFIX . 'user u', 'u.id = ' . $this->_table_prefix . '.user_id', 'LEFT');
        $this->db->join(NAILS_DB_PREFIX . 'user_email ue', 'ue.user_id = ' . $this->_table_prefix . '.user_id AND ue.is_primary = 1', 'LEFT');

        //  Searching?
        if (!empty($data['keywords'])) {

            if (empty($data['or_like'])) {

                $data['or_like'] = array();
            }

            $toSlug = strtolower(str_replace(' ', '_', $data['keywords']));

            $data['or_like'][] = array(
                'column' => $this->_table_prefix . '.type',
                'value'  => $toSlug
            );
            $data['or_like'][] = array(
                'column' => 'ue.email',
                'value'  => $data['keywords']
            );
        }

        parent::_getcount_common($data);
    }

    // --------------------------------------------------------------------------

    /**
     * Format a changelog object
     * @param  stdClass &$obj The object to format
     * @return void
     */
    protected function _format_object(&$obj)
    {
        parent::_format_object($obj);

        if (!empty($obj->item_id)) {

            $obj->item_id = (int) $obj->item_id;
        }

        $obj->changes = @unserialize($obj->changes);

        $obj->user              = new \stdClass();
        $obj->user->id          = $obj->user_id;
        $obj->user->first_name  = $obj->first_name;
        $obj->user->last_name   = $obj->last_name;
        $obj->user->gender      = $obj->gender;
        $obj->user->profile_img = $obj->profile_img;
        $obj->user->email       = $obj->email;

        unset($obj->user_id);
        unset($obj->first_name);
        unset($obj->last_name);
        unset($obj->gender);
        unset($obj->profile_img);
        unset($obj->email);
    }
}

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' MODELS
 *
 * The following block of code makes it simple to extend one of the core Nails
 * models. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_ADMIN_CHANGELOG_MODEL')) {

    class Admin_changelog_model extends NAILS_Admin_changelog_model
    {
    }
}
