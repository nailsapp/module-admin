<?php

namespace Nails\Admin\Interfaces\DataExport;

/**
 * Interface Source
 * @package Nails\Admin\Interfaces\DataExport
 */
interface Source
{
    /**
     * Returns the source's label
     * @return string
     */
    public function getLabel();

    // --------------------------------------------------------------------------

    /**
     * Returns the source's filename
     * @return string
     */
    public function getFileName();

    // --------------------------------------------------------------------------

    /**
     * Returns the source's description
     * @return string
     */
    public function getDescription();

    // --------------------------------------------------------------------------

    /**
     * Returns an array of additional options for the export
     * @return array
     */
    public function getOptions();

    // --------------------------------------------------------------------------

    /**
     * Provides an opportunity for the source to decide whether it is available or not to the user
     * @return bool
     */
    public function isEnabled();

    // --------------------------------------------------------------------------

    /**
     * Performs the export; must return a \Nails\Admin\DataExport\SourceResponse object,
     * or an array of these objects.
     *
     * @param array $aOptions The options, in key/value form
     *
     * @return \Nails\Admin\DataExport\SourceResponse|array
     */
    public function execute($aOptions = []);
}
