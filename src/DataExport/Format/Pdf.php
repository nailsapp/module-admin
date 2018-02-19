<?php

namespace Nails\Admin\DataExport\Format;

use Nails\Admin\DataExport\SourceResponse;
use Nails\Admin\Interfaces\DataExport\Format;
use Nails\Factory;

/**
 * Class Pdf
 * @package Nails\Auth\DataFormat
 */
class Pdf implements Format
{
    /**
     * Returns the format's label
     * @return string
     */
    public function getLabel()
    {
        return 'PDF';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription()
    {
        return 'Saves a PDF using the data from the HTML export option';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's file extension
     * @return string
     */
    public function getFileExtension()
    {
        return 'pdf';
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
        $oPdf = Factory::service('Pdf', 'nailsapp/module-pdf');
        $oPdf->setPaperSize('A4', 'landscape');
        $oPdf->loadView(
            'admin/utilities/export/html',
            [
                'label'  => $oData->label,
                'fields' => $oData->fields,
                'data'   => $oData->data,
            ]
        );

        try {
            $oPdf->render();
            $sData = $oPdf->output();
            $oPdf->reset();
        } catch (\Exception $e) {
            throw new \Exception(
                'Failed to render PDF. The following exception was raised: ' . $e->getMessage()
            );
        }

        return (object) [
            'filename'  => $oData->filename,
            'extension' => 'pdf',
            'data'      => $sData,
        ];
    }
}
