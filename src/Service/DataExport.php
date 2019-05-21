<?php

/**
 * This service handles exporting data
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Library
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Service;

use Nails\Common\Exception\NailsException;
use Nails\Components;
use Nails\Factory;
use Nails\Admin\DataExport\SourceResponse;

/**
 * Class DataExport
 *
 * @package Nails\Admin\Service
 */
class DataExport
{
    protected $aSources = [];
    protected $aFormats = [];
    protected $aCacheFiles = [];

    // --------------------------------------------------------------------------

    /**
     * DataExport constructor.
     */
    public function __construct()
    {
        $this->aSources = [];
        $this->aFormats = [];

        foreach (Components::available() as $oComponent) {

            $sPath             = $oComponent->path;
            $sNamespace        = $oComponent->namespace;
            $aComponentSources = array_filter((array) directoryMap($sPath . 'src/DataExport/Source'));
            $aComponentFormats = array_filter((array) directoryMap($sPath . 'src/DataExport/Format'));

            foreach ($aComponentSources as $sSource) {

                $sClass    = $sNamespace . 'DataExport\\Source\\' . basename($sSource, '.php');
                $oInstance = new $sClass();

                if ($oInstance->isEnabled()) {
                    $this->aSources[] = (object) [
                        'slug'        => $oComponent->slug . '::' . basename($sSource, '.php'),
                        'label'       => $oInstance->getLabel(),
                        'description' => $oInstance->getDescription(),
                        'options'     => $oInstance->getOptions(),
                        'instance'    => $oInstance,
                    ];
                }
            }

            foreach ($aComponentFormats as $sFormat) {

                $sClass    = $sNamespace . 'DataExport\\Format\\' . basename($sFormat, '.php');
                $oInstance = new $sClass();

                $this->aFormats[] = (object) [
                    'slug'        => $oComponent->slug . '::' . basename($sFormat, '.php'),
                    'label'       => $oInstance->getLabel(),
                    'description' => $oInstance->getDescription(),
                    'instance'    => $oInstance,
                ];
            }
        }

        arraySortMulti($this->aSources, 'label');
        arraySortMulti($this->aFormats, 'label');

        $this->aSources = array_values($this->aSources);
        $this->aFormats = array_values($this->aFormats);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns all the available sources
     *
     * @return array
     */
    public function getAllSources()
    {
        return $this->aSources;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific source by its slug
     *
     * @param $sSlug
     *
     * @return \stdClass|null
     */
    public function getSourceBySlug($sSlug)
    {
        foreach ($this->aSources as $oSource) {
            if ($sSlug === $oSource->slug) {
                return $oSource;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns all the available formats
     *
     * @return array
     */
    public function getAllFormats()
    {
        return $this->aFormats;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a specific format by its slug
     *
     * @param $sSlug
     *
     * @return \stdClass|null
     */
    public function getFormatBySlug($sSlug)
    {
        foreach ($this->aFormats as $oFormat) {
            if ($sSlug === $oFormat->slug) {
                return $oFormat;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Executes a DateExport source then passes to a DataExport format. Once complete
     * the resulting file is uploaded to the CDN and the object's ID returned.
     *
     * @param string $sSourceSlug The slug of the source to use
     * @param string $sFormatSlug The slug of the format to use
     * @param array  $aOptions    Additional options to pass to the source
     *
     * @return integer
     * @throws NailsException
     */
    public function export($sSourceSlug, $sFormatSlug, $aOptions = [])
    {
        $oSource = $this->getSourceBySlug($sSourceSlug);
        if (empty($oSource)) {
            throw new NailsException('Invalid data source "' . $sSourceSlug . '"');
        }

        $oFormat = $this->getFormatBySlug($sFormatSlug);
        if (empty($oFormat)) {
            throw new NailsException('Invalid data format "' . $sFormatSlug . '"');
        }

        $oSourceResponse = $oSource->instance->execute($aOptions);
        if (!is_array($oSourceResponse)) {
            $aSourceResponses = [$oSourceResponse];
        } else {
            $aSourceResponses = $oSourceResponse;
        }

        //  Create temporary working directory
        $sTempDir = CACHE_PATH . 'data-export-' . md5(microtime(true)) . mt_rand() . '/';
        mkdir($sTempDir);

        //  Process each file
        $aFiles = [];
        try {

            foreach ($aSourceResponses as $oSourceResponse) {

                if (!($oSourceResponse instanceof SourceResponse)) {
                    throw new NailsException('Source must return an instance of SourceResponse');
                }

                //  Create a new file
                $sFile    = $sTempDir . $oSourceResponse->getFilename() . '.' . $oFormat->instance->getFileExtension();
                $aFiles[] = $sFile;
                $rFile    = fopen($sFile, 'w+');
                //  Write to the file
                $oSourceResponse->reset();
                $oFormat->instance->execute($oSourceResponse, $rFile);
                //  Close the file
                fclose($rFile);
            }

            //  Compress if > 1
            if (count($aFiles) > 1) {
                $sArchiveFile = $sTempDir . 'export.zip';
                $oZip         = Factory::service('Zip');
                foreach ($aFiles as $sFile) {
                    $oZip->read_file($sFile);
                }
                $oZip->archive($sArchiveFile);
                $aFiles[] = $sArchiveFile;
            }
            $sFile = end($aFiles);

            //  Save to CDN
            $oCdn    = Factory::service('Cdn', 'nails/module-cdn');
            $oObject = $oCdn->objectCreate($sFile, 'data-export');

            if (empty($oObject)) {
                throw new NailsException('Failed to upload exported file. ' . $oCdn->lastError());
            }
        } finally {
            //  Tidy up
            foreach ($aFiles as $sFile) {
                if (file_exists($sFile)) {
                    unlink($sFile);
                }
            }
            rmdir($sTempDir);
        }

        return $oObject->id;
    }

    // --------------------------------------------------------------------------

    /**
     * Cleans up any generated cache files
     */
    public function __destruct()
    {
        foreach ($this->aCacheFiles as $sCacheFile) {
            @unlink($sCacheFile);
        }
    }
}
