<?php

namespace Nails\Admin\Interfaces\DataExport;

use Nails\Admin\DataExport\SourceResponse;

/**
 * Interface Format
 * @package Nails\Admin\Interfaces\DataExport
 */
interface Format
{
    /**
     * Returns the format's label
     * @return string
     */
    public function getLabel();

    // --------------------------------------------------------------------------

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription();

    // --------------------------------------------------------------------------

    /**
     * Returns the format's file extension
     * @return string
     */
    public function getFileExtension();

    // --------------------------------------------------------------------------

    /**
     * Takes a SourceResponse object and transforms it into the appropriate format.
     *
     * @param SourceResponse $oSourceResponse A SourceResponse object
     * @param resource       $rFile           The file resource to write to
     */
    public function execute($oSourceResponse, $rFile);
}
