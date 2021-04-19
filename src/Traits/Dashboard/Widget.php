<?php

namespace Nails\Admin\Traits\Dashboard;

use Nails\Admin\Service;
use Nails\Admin\Interfaces;

/**
 * Trait Widget
 *
 * @package Nails\Admin\Traits\Dashboard
 */
trait Widget
{
    /** @var array */
    protected $aConfig = [];

    // --------------------------------------------------------------------------

    /**
     * Base constructor.
     *
     * @param array $aConfig Any user specific configs
     */
    public function __construct(array $aConfig = [])
    {
        $this->aConfig = $aConfig;
    }

    // --------------------------------------------------------------------------

    /**
     * Renders the config section of the widget
     *
     * @return string
     */
    public function getConfig(): string
    {
        return '';
    }

    // --------------------------------------------------------------------------

    /**
     * Whether to pad the body or not
     *
     * @return bool
     */
    public function isPadded(): bool
    {
        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether the widget is configurable
     *
     * @return bool
     */
    public function isConfigurable(): bool
    {
        return false;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the widget's image
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return null;
    }
}
