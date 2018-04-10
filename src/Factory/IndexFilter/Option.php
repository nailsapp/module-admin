<?php

/**
 * Option for admin index filters
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Factory
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Factory\IndexFilter;

class Option
{
    /**
     * Stores an array of the getter/setters for the other properties
     * @var array
     */
    protected $aMethods = [];

    /**
     * The label to give the option
     * @var string
     */
    protected $sLabel;

    /**
     * The value to give the option
     * @var
     */
    protected $mValue;

    /**
     * Whether the item is selected or not
     * @var
     */
    protected $bSelected;

    // --------------------------------------------------------------------------

    /**
     * Base constructor.
     */
    public function __construct()
    {
        $aVars = get_object_vars($this);
        unset($aVars['aMethods']);
        unset($aVars['bSelected']);
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
     * Set whetehr the option is selected or not
     *
     * @param boolean $bSelected The selected state
     *
     * @return $this
     */
    public function setSelected($bSelected)
    {
        $this->bSelected = $bSelected;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns whether the option is selected or not
     *
     * @return bool
     */
    public function isSelected()
    {
        return (bool) $this->bSelected;
    }
}
