<?php

namespace Nails\Admin\Interfaces\DataExport;

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

    /**
     * Returns the format's description
     * @return string
     */
    public function getDescription();

    /**
     * Takes the supplied data and transforms it into the appropriate format.
     * This method should return data in the following format:
     *
     * {
     *     'filename':  'The filename of the file, minus the extension',
     *     'extension': 'The extension of the file',
     *     'data':      'The data to save to the file',
     *     'headers': [
     *          'Any additional headers to send'
     *      ]
     * }
     *
     * @param \stdClass $oData The Data to transform
     *
     * @return \stdClass
     */
    public function execute($oData);
}
