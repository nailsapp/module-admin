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
     * @param array $aConfig Any user specific configs
     */
    public function __construct(array $aConfig = []);

    // --------------------------------------------------------------------------

    /**
     * Returns the widget's title
     *
     * @return string
     */
    public function getTitle(): string;

    // --------------------------------------------------------------------------

    /**
     * Returns the widget's description
     *
     * @return string
     */
    public function getDescription(): string;

    // --------------------------------------------------------------------------

    /**
     * Returns the widget's image
     *
     * @return string|null
     */
    public function getImage(): ?string;

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
    public function isPadded(): bool;

    // --------------------------------------------------------------------------

    /**
     * Whether to the widget is configurable
     *
     * @return bool
     */
    public function isConfigurable(): bool;

    // --------------------------------------------------------------------------

    /**
     * Renders the config section of the widget
     *
     * @return string
     */
    public function getConfig(): string;
}
