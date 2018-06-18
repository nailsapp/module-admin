<?php

/**
 * Index filter for admin index views
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Factory
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Factory;

use Nails\Factory;

class IndexFilter
{
    /**
     * Stores an array of the getter/setters for the other properties
     * @var array
     */
    protected $aMethods = [];

    /**
     * The label to give the filter
     * @var string
     */
    protected $sLabel;

    /**
     * The column the filter acts on
     * @var string
     */
    protected $sColumn;

    /**
     * An array of options to present to the user
     * @var array
     */
    protected $aOptions = [];

    // --------------------------------------------------------------------------

    /**
     * Base constructor.
     */
    public function __construct()
    {
        $aVars = get_object_vars($this);
        unset($aVars['aMethods']);
        unset($aVars['aOptions']);
        $aVars = array_keys($aVars);

        foreach ($aVars as $sVar) {
            $sNormalised                          = substr($sVar, 1);
            $this->aMethods['set' . $sNormalised] = $sVar;
            $this->aMethods['get' . $sNormalised] = $sVar;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Mimics setters and getters for class properties
     *
     * @param string $sMethod    The method being called
     * @param array  $aArguments Any passed arguments
     *
     * @return $this
     * @throws \Exception
     */
    public function __call($sMethod, $aArguments)
    {
        if (array_key_exists($sMethod, $this->aMethods)) {
            if (substr($sMethod, 0, 3) === 'set') {
                $this->{$this->aMethods[$sMethod]} = reset($aArguments);
                return $this;
            } else {
                return $this->{$this->aMethods[$sMethod]};
            }
        } else {
            throw new \Exception('Call to undefined method ' . get_called_class() . '::' . $sMethod . '()');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new option
     *
     * @param string $sLabel    the label to give the option
     * @param mixed  $mValue    The value to give the option
     * @param bool   $bSelected Whether the item is selected
     *
     * @return $this
     */
    public function addOption($sLabel, $mValue, $bSelected = false)
    {
        $this->aOptions[] = Factory::factory('IndexFilterOption', 'nailsapp/module-admin')
                                   ->setLabel($sLabel)
                                   ->setValue($mValue)
                                   ->setSelected($bSelected);
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Add multiple options
     *
     * @param array $aOptions An array of options to add
     *
     * @return $this
     */
    public function addOptions($aOptions)
    {
        foreach ($aOptions as $aOption) {
            $sLabel    = getFromArray('label', $aOption, getFromArray(0, $aOption));
            $mValue    = getFromArray('value', $aOption, getFromArray(1, $aOption));
            $bSelected = getFromArray('selected', $aOption, getFromArray(2, $aOption));
            $this->addOption($sLabel, $mValue, $bSelected);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->aOptions;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific option
     *
     * @param integer $iOptionIndex the option index to return
     *
     * @return mixed|null
     */
    public function getOption($iOptionIndex)
    {
        return array_key_exists($iOptionIndex, $this->aOptions) ? $this->aOptions[$iOptionIndex] : null;
    }
}
