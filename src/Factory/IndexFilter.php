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

use Nails\Admin\Factory\IndexFilter\Option;
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
     * @param string|Option $sLabel      The label to give the option, or an IndexFilterOption object
     * @param mixed         $mValue      The value to give the option
     * @param bool          $bIsSelected Whether the item is selected
     * @param bool          $bIsQuery    If true, treat the value as the entire query
     *
     * @return $this
     */
    public function addOption($sLabel, $mValue = null, $bIsSelected = false, $bIsQuery = null)
    {
        if ($sLabel instanceof Option) {
            $this->aOptions[] = $sLabel;
        } else {
            $this->aOptions[] = Factory::factory('IndexFilterOption', 'nailsapp/module-admin')
                                       ->setLabel($sLabel)
                                       ->setValue($mValue)
                                       ->setIsSelected($bIsSelected)
                                       ->setIsQuery($bIsQuery);
        }

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
            if ($aOption instanceof Option) {
                $this->aOptions[] = $aOption;
            } else {
                $sLabel      = getFromArray('label', $aOption, getFromArray(0, $aOption));
                $mValue      = getFromArray('value', $aOption, getFromArray(1, $aOption));
                $bIsSelected = (bool) getFromArray('selected', $aOption, getFromArray(2, $aOption));
                $bIsQuery    = (bool) getFromArray('query', $aOption, getFromArray(3, $aOption));
                $this->addOption($sLabel, $mValue, $bIsSelected, $bIsQuery);
            }
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
