<?php

namespace Nails\Admin\DataExport\Format;

use Nails\Admin\Interfaces\DataExport\Format;
use Nails\Factory;

/**
 * Class Csv
 * @package Nails\Auth\DataFormat
 */
class Csv implements Format
{
    /**
     * Returns the format's label
     * @return string
     */
    public function getLabel()
    {
        return 'CSV';
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription()
    {
        return 'Easily imports to many software packages, including Microsoft Excel.';
    }

    // --------------------------------------------------------------------------

    /**
     * Takes the supplied data and transforms it into the appropriate format
     *
     * @param \stdClass $oData The Data to transform
     *
     * @return \stdClass
     */
    public function execute($oData)
    {
        $oView = Factory::service('View');

        return (object) [
            'filename'  => $oData->filename,
            'extension' => 'csv',
            'data'      => $oView->load(
                'admin/utilities/export/csv',
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
