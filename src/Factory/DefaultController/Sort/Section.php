<?php

namespace Nails\Admin\Factory\DefaultController\Sort;

use Nails\Common\Resource\Entity;

/**
 * Class Section
 *
 * @package Nails\Admin\Factory\DefaultController\Sort
 */
class Section
{
    /** @var string */
    protected $sLabel = 'Items';

    /** @var Entity[] */
    protected $aItems = [];

    // --------------------------------------------------------------------------

    /**
     * Section constructor.
     *
     * @param string   $sLabel
     * @param Entity[] $aItems
     */
    public function __construct(string $sLabel = '', array $aItems = [])
    {
        $this->sLabel = $sLabel;
        $this->aItems = $aItems;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the section's label
     *
     * @param string $sLabel The label to set
     */
    public function setLabel(string $sLabel): self
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the section's label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the section's items
     *
     * @param Entity[] $aItems The items to set
     */
    public function setItems(array $aItems): self
    {
        $this->aItems = $aItems;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a single item to the section
     *
     * @param Entity $oItem The item to add
     *
     * @return $this
     */
    public function addItem(Entity $oItem): self
    {
        $this->aItems[] = $oItem;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the section's items
     *
     * @return Entity[]
     */
    public function getItems(): array
    {
        return $this->aItems;
    }
}
