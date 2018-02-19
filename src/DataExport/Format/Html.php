<?php

namespace Nails\Admin\DataExport\Format;

use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces\DataExport\Format;
use Nails\Factory;

/**
 * Class Html
 * @package Nails\Admin\DataFormat
 */
class Html implements Format
{
    /**
     * Returns the format's label
     * @return string
     */
    public function getLabel()
    {
        return 'HTML';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription()
    {
        return 'Produces an HTML file with a table containing the data';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's file extension
     * @return string
     */
    public function getFileExtension()
    {
        return 'html';
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

        return (object) [
            'filename'  => $oData->filename,
            'extension' => 'html',
            'data'      => $oView->load(
                'admin/utilities/export/html',
                [
                    'label'  => $oData->label,
                    'fields' => $oData->fields,
                    'data'   => $oData->data,
                ],
                true
            ),
        ];
    }
}
