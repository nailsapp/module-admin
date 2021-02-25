<?php

namespace Nails\Admin\Admin\Dashboard\Widget;

use Nails\Admin\Service;
use Nails\Admin\Interfaces;

/**
 * Class Welcome
 *
 * @package Nails\Admin\Admin\Dashboard\Base
 */
abstract class Base implements Interfaces\Dashboard\Widget
{
    /**
     * Defines the default size for the widget when it's added to the UI
     */
    const DEFAULT_SIZE = Service\Dashboard\Widget::SIZE_SMALL;

    /**
     * Whether to pad the body or not
     */
    const PAD_BODY = true;

    // --------------------------------------------------------------------------

    /** @var string|null */
    protected $sSize;

    /** @var array */
    protected $sConfig;

    // --------------------------------------------------------------------------

    /**
     * Base constructor.
     *
     * @param string|null $sSize   The size this widget is configured to be
     * @param array       $aConfig Any user specific configs
     */
    public function __construct(?string $sSize, array $aConfig)
    {
        $this->sSize   = $sSize ?? static::DEFAULT_SIZE;
        $this->aConfig = $aConfig;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the configured size of the widget
     *
     * @return string
     */
    public function getSize(): string
    {
        return $this->sSize;
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
    public function padBody(): bool
    {
        return static::PAD_BODY;
    }
}
