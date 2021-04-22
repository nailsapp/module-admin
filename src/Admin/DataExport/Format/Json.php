<?php

namespace Nails\Admin\Admin\DataExport\Format;

use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces\DataExport\Format;

/**
 * Class Json
 * @package Nails\Auth\DataFormat
 */
class Json implements Format
{
    /**
     * Returns the format's label
     * @return string
     */
    public function getLabel()
    {
        return 'JSON';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription()
    {
        return 'Export as a JSON file, useful for transporting to other systems';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's file extension
     * @return string
     */
    public function getFileExtension()
    {
        return 'json';
    }

    // --------------------------------------------------------------------------

    /**
     * Takes a SourceResponse object and transforms it into the appropriate format.
     *
     * @param SourceResponse $oSourceResponse A SourceResponse object
     * @param resource       $rFile           The file resource to write to
     */
    public function execute($oSourceResponse, $rFile)
    {
        fwrite($rFile, '[');

        //  Write the data to the file
        while ($oRow = $oSourceResponse->getNextItem()) {
            fwrite($rFile, trim(json_encode($oRow) . ','));
        }

        $aStats = fstat($rFile);
        $aStats = array_slice($aStats, 13);
        if ($aStats['size'] > 1) {
            ftruncate($rFile, $aStats['size'] - 1);
            fseek($rFile, $aStats['size'] - 1);
        }

        fwrite($rFile, ']');
    }
}
