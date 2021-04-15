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
     * Whether to pad the body or not
     */
    const PAD_BODY = true;

    /** @var array */
    protected $aConfig;

    // --------------------------------------------------------------------------

    /**
     * Base constructor.
     *
     * @param array|null $aConfig Any user specific configs
     */
    public function __construct(array $aConfig = null)
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
    public function padBody(): bool
    {
        return static::PAD_BODY;
    }
}
