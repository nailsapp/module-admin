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

    /**
     * Returns the source's filename
     * @return string
     */
    public function getFileName();

    /**
     * Returns the source's description
     * @return string
     */
    public function getDescription();

    /**
     * Provides an opportunity for the source to decide whether it is available or not to the user
     * @return bool
     */
    public function isEnabled();

    /**
     * Performs the export; must return an object with the following fields (if multiple files
     * must be produced and zipped together, then this should return an array of these objects):
     *
     * {
     *     'label':    'A label to give the data set',
     *     'filename': 'The filename of the file',
     *     'fields': [
     *         'ID', 'The', 'Column', 'Names', 'or Labels'
     *     ],
     *     'data': [
     *         [1, 'the', 'data', 'to', 'export'],
     *         [2, 'the', 'data', 'to', 'export'],
     *         [3, 'the', 'data', 'to', 'export']
     *     ],
     * }
     *
     * @param array $aData Any data to pass to the source
     *
     * @return \stdClass|bool
     */
    public function execute($aData = []);
}
