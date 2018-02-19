<?php

namespace Nails\Admin\DataExport\Format;

use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces\DataExport\Format;
use Nails\Factory;

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
        return 'Export as a JSON file';
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
        $oView = Factory::service('View');

        $aData = [];
        foreach ($oData->data as $aItem) {
            $aData[] = array_combine($oData->fields, $aItem);
        }

        return (object) [
            'filename'  => $oData->filename,
            'extension' => 'json',
            'data'      => $oView->load(
                'admin/utilities/export/json',
                [
                    'label'  => $oData->label,
                    'fields' => $oData->fields,
                    'data'   => $aData,
                ],
                true
            ),
        ];
    }
}
