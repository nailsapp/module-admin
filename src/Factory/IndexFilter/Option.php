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

use Nails\Common\Exception\NailsException;

/**
 * Class Option
 *
 * @package Nails\Admin\Factory\IndexFilter
 *
 * @method self setLabel(string $sLabel)
 * @method string getLabel()
 * @method self setValue(mixed $sValue)
 * @method mixed getValue()
 * @method self setIsSelected(bool $sIsSelected)
 * @method bool getIsSelected()
 * @method self setIsQuery(bool $sIsQuery)
 * @method bool getIsQuery()
 */
class Option
{
    /**
     * Stores an array of the getter/setters for the other properties
     *
     * @var array
     */
    protected $aMethods = [];

    /**
     * Stores an array of the getter/setters for bool properties
     *
     * @var array
     */
    protected $aBoolMethods = [];

    /**
     * The label to give the option
     *
     * @var string
     */
    protected $sLabel;

    /**
     * The value to give the option
     *
     * @var mixed
     */
    protected $mValue;

    /**
     * Whether the item is selected or not
     *
     * @var bool
     */
    protected $bIsSelected;

    /**
     * If true, treat the value as the entire query
     *
     * @var bool
     */
    protected $bIsQuery;

    // --------------------------------------------------------------------------

    /**
     * Base constructor.
     */
    public function __construct()
    {
        $aVars = get_object_vars($this);
        unset($aVars['aMethods']);
        unset($aVars['aBoolMethods']);
        $aVars = array_keys($aVars);

        foreach ($aVars as $sVar) {
            $sNormalised = substr($sVar, 1);
            if (preg_match('/^Is[A-Z]/', $sNormalised)) {
                $this->aBoolMethods['set' . $sNormalised]  = $sVar;
                $this->aBoolMethods[lcfirst($sNormalised)] = $sVar;
            } else {
                $this->aMethods['set' . $sNormalised] = $sVar;
                $this->aMethods['get' . $sNormalised] = $sVar;
            }
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
            return $this->handleMethodCall($this->aMethods, $sMethod, reset($aArguments));
        } elseif (array_key_exists($sMethod, $this->aBoolMethods)) {
            return $this->handleMethodCall($this->aBoolMethods, $sMethod, (bool) reset($aArguments));
        } else {
            throw new NailsException('Call to undefined method ' . get_called_class() . '::' . $sMethod . '()');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Executes the appropriate "method" behaviour
     *
     * @param array  $aMethods The array of methods to check against
     * @param string $sMethod  The method being called
     * @param mixed  $mValue   The value to assign when setting
     *
     * @return $this|mixed
     */
    private function handleMethodCall($aMethods, $sMethod, $mValue)
    {
        if (substr($sMethod, 0, 3) !== 'set') {
            return $this->{$aMethods[$sMethod]};
        }

        $this->{$aMethods[$sMethod]} = $mValue;
        return $this;
    }
}
