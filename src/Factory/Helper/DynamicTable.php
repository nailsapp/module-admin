<?php

namespace Nails\Admin\Factory\Helper;

use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ViewNotFoundException;
use Nails\Common\Service\View;
use Nails\Factory;

/**
 * Class DynamicTable
 *
 * @package Nails\Admin\Factory\Helper
 */
class DynamicTable
{
    /** @var string */
    protected $sKey;

    /** @var array[] */
    protected $aFields;

    /** @var array */
    protected $aData;

    /** @var bool */
    protected $bIsSortable;

    // --------------------------------------------------------------------------

    /**
     * Sets the POST key
     *
     * @param string $sKey
     *
     * @return $this;
     */
    public function setKey(string $sKey): self
    {
        $this->sKey = $sKey;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the POST key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->sKey;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the fields array
     *
     * @param array $aFields
     *
     * @return $this;
     */
    public function setFields(array $aFields): self
    {
        $this->aFields = $aFields;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a single field
     *
     * @param string $sLabel
     * @param array  $aConfig
     *
     * @return $this
     */
    public function addField(string $sLabel, array $aConfig): self
    {
        $this->aFields[$sLabel] = $aConfig;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the fields array
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->aFields;
    }


    // --------------------------------------------------------------------------

    /**
     * Sets the initial load data
     *
     * @param array $aData
     *
     * @return $this;
     */
    public function setData(array $aData): self
    {
        $this->aData = $aData;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the initial load data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->aData;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets whether the table should be sortable
     *
     * @param bool $bIsSortable
     *
     * @return $this;
     */
    public function setIsSortable(bool $bIsSortable): self
    {
        $this->bIsSortable = $bIsSortable;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns whether the table is sortable
     *
     * @return bool
     */
    public function isIsSortable(): bool
    {
        return $this->bIsSortable;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the compiled table markup
     *
     * @return string
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public function render(): string
    {
        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView
            ->load(
                'admin/_components/dynamic-table',
                [
                    'sKey'        => $this->sKey,
                    'aFields'     => $this->aFields,
                    'aData'       => $this->aData,
                    'bIsSortable' => $this->bIsSortable,
                ],
                true
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Allows the object to be cast as a string to render
     *
     * @return string
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public function __toString()
    {
        return $this->render();
    }
}
