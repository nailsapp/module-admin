<?php

/**
 * This class is used for building navGroups in admin
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Helper
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin;

class Nav
{
    protected $label;
    protected $icon;
    protected $actions;

    // --------------------------------------------------------------------------

    /**
     * Construct the navGroup
     * @param string $label The label to give the navGroup
     * @param string $icon  The icon to give the navGroup
     */
    public function __construct($label = '', $icon = '')
    {
        $this->setLabel($label);
        $this->setIcon($icon);
        $this->actions = array();
    }

    // --------------------------------------------------------------------------

    /**
     * Set the navGroup's label
     * @return $this
     */
    public function setLabel($label = '')
    {
        $this->label = $label;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's icon
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the navGroup's icon
     * @return string
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's actions
     * @return string
     */
    public function getActions()
    {
        return $this->actions;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new action to the navGroup. An action is menu item essentially.
     * @param string $label  The label to give the action
     * @param string $url    The url this action applies to
     * @param array  $alerts An array of alerts to have along side this action
     * @param mixed  $order  An optional order index, used to push menu items up and down the group
     */
    public function addAction($label, $url = 'index', $alerts = array(), $order = null)
    {
        $this->actions[$url]         = new \stdClass();
        $this->actions[$url]->label  = $label;
        $this->actions[$url]->alerts = $alerts;

        if (is_null($order)) {

            $this->actions[$url]->order = count($this->actions);

        } else {

            $this->actions[$url]->order = $order;
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Removes a action
     * @param  string $url The URL/key of the action to remove
     * @return Object      $this, for chaining
     */
    public function removeAction($url)
    {
        unset($this->actions[$url]);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Creates an alert object
     * @param  mixed  $value    The value of the alert, i.e., what's shown to the user
     * @param  string $severity The severty of the alert
     * @param  string $label    The label to give the alert, shown on hover
     * @return stdClass
     */
    public static function alertObject($value, $severity = 'info', $label = '')
    {
        $temp           = new \stdClass();
        $temp->value    = $value;
        $temp->severity = $severity;
        $temp->label    = $label;

        return $temp;
    }
}
