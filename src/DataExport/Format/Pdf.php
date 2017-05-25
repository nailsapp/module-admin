<?php

namespace Nails\Admin\DataExport\Format;

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
     * Takes the supplied data and transforms it into the appropriate format
     *
     * @param \stdClass $oData The Data to transform
     *
     * @throws \Exception
     * @return \stdClass
     */
    public function execute($oData)
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
