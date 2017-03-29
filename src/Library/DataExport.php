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

namespace Nails\Admin\Library;

use Nails\Factory;

/**
 * Class DataExport
 * @package Nails\Admin\Library
 */
class DataExport
{
    protected $aSources    = [];
    protected $aFormats    = [];
    protected $aCacheFiles = [];

    // --------------------------------------------------------------------------

    /**
     * DataExport constructor.
     */
    public function __construct()
    {
        $this->aSources = [];
        $this->aFormats = [];
        $aComponents    = array_merge(
            [
                (object) [
                    'slug'      => 'app',
                    'namespace' => 'App\\',
                    'path'      => FCPATH,
                ],
            ],
            _NAILS_GET_COMPONENTS()
        );

        foreach ($aComponents as $oComponent) {

            $sPath             = $oComponent->path;
            $sNamespace        = $oComponent->namespace;
            $aComponentSources = array_filter((array) directory_map($sPath . 'src/DataExport/Source'));
            $aComponentFormats = array_filter((array) directory_map($sPath . 'src/DataExport/Format'));

            foreach ($aComponentSources as $sSource) {

                require_once $sPath . 'src/DataExport/Source/' . $sSource;

                $sClass    = $sNamespace . 'DataExport\\Source\\' . basename($sSource, '.php');
                $oInstance = new $sClass();

                if ($oInstance->isEnabled()) {
                    $this->aSources[] = (object) [
                        'slug'        => $oComponent->slug . '::' . basename($sSource, '.php'),
                        'label'       => $oInstance->getLabel(),
                        'description' => $oInstance->getDescription(),
                        'instance'    => $oInstance,
                    ];
                }
            }

            foreach ($aComponentFormats as $sFormat) {

                require_once $sPath . 'src/DataExport/Format/' . $sFormat;

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

        array_sort_multi($this->aSources, 'label');
        array_sort_multi($this->aFormats, 'label');

        $this->aSources = array_values($this->aSources);
        $this->aFormats = array_values($this->aFormats);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns all the available sources
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
     * Executes a source and passes to a format. If $bOutputToBrowser is true then
     * the result will be sent to the browser as a download, if not details about
     * the generated file will be returned. The cache file will be available until
     * the model destructs.
     *
     * @param string $sSourceSlug The slug of the source to use
     * @param string $sFormatSlug The slug of the format to use
     * @param bool $bOutputToBrowser Whether to send the output to the browser or not
     *
     * @return object
     * @throws \Exception
     */
    public function export($sSourceSlug, $sFormatSlug, $bOutputToBrowser = true)
    {
        $oSource = $this->getSourceBySlug($sSourceSlug);
        if (empty($oSource)) {
            throw new \Exception('Invalid data source');
        }

        $oFormat = $this->getFormatBySlug($sFormatSlug);
        if (empty($oFormat)) {
            throw new \Exception('Invalid data format');
        }

        $mData = $oSource->instance->execute();

        //  Save the export to disk
        $sCacheFile = DEPLOY_CACHE_DIR . 'data-export-' . md5(microtime(true)) . mt_rand();
        if (is_array($mData)) {

            $sCacheFile = $sCacheFile . '.zip';
            $oZip       = Factory::service('Zip');
            foreach ($mData as $oData) {
                $oFile = $oFormat->instance->execute($oData);
                $oZip->add_data($oFile->filename . '.' . $oFile->extension, $oFile->data);
            }
            $oDate = Factory::factory('DateTime');
            $oZip->archive($sCacheFile);
            $sFileName = $oSource->instance->getFileName() . '-' . $oDate->format('Y-m-d_H-i-s') . '.zip';

        } else {

            $oResult    = $oFormat->instance->execute($mData);
            $sCacheFile = $sCacheFile . '.' . $oResult->extension;
            $oNow       = Factory::factory('DateTime');
            $sFileName  = $oResult->filename . '-' . $oNow->format('Y-m-d_H-i-s') . '.' . $oResult->extension;

            if (!($rFh = @fopen($sCacheFile, FOPEN_WRITE_CREATE_DESTRUCTIVE))) {
                throw new \Exception('Failed to write file to disk');
            }

            flock($rFh, LOCK_EX);
            fwrite($rFh, $oResult->data);
            flock($rFh, LOCK_UN);
            fclose($rFh);
        }

        if ($bOutputToBrowser) {
            $aHeaders = [
                ['Content-Type: application/octet-stream', true],
                ['Pragma: public', true],
                ['Expires: 0', true],
                ['Cache-Control: must-revalidate, post-check=0, pre-check=0', true],
                ['Cache-Control: private', false],
                ['Content-Disposition: attachment; filename=data-export-' . $sFileName . ';', true],
                ['Content-Transfer-Encoding: binary', true],
            ];

            foreach ($aHeaders as $aHeader) {
                header($aHeader[0], $aHeader[1]);
            }
            readFileChunked($sCacheFile);
        }

        $this->aCacheFiles[] = $sCacheFile;

        return (object) [
            'filename' => $sFileName,
            'path'     => $sCacheFile,
        ];
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
