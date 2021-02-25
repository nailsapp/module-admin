<?php

namespace Nails\Admin\Interfaces\Dashboard;

use Nails\Admin\DataExport\SourceResponse;

/**
 * Interface Widget
 *
 * @package Nails\Admin\Interfaces\Dashboard
 */
interface Widget
{
    /**
     * Widget constructor.
     *
     * @param string $sSize   The size the widget is configured to be
     * @param array  $aConfig Any user specific configs
     */
    public function __construct(string $sSize, array $aConfig);

    // --------------------------------------------------------------------------

    /**
     * Renders the title of the widget
     *
     * @return string
     */
    public function getTitle(): string;

    // --------------------------------------------------------------------------

    /**
     * Renders the body of the widget
     *
     * @return string
     */
    public function getBody(): string;

    // --------------------------------------------------------------------------

    /**
     * Whether to pad the body or not
     *
     * @return bool
     */
    public function padBody(): bool;

    // --------------------------------------------------------------------------

    /**
     * Renders the config section of the widget
     *
     * @return string
     */
    public function getConfig(): string;

    // --------------------------------------------------------------------------

    /**
     * Gets the configured size of the widget
     *
     * @return string
     */
    public function getSize(): string;
}
