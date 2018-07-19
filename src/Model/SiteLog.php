<?php

/**
 * Admin site log model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Model;

use Nails\Factory;
use Nails\Common\Model\Base;

class SiteLog extends Base
{
    protected $logPath;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();
        Factory::helper('directory');

        // --------------------------------------------------------------------------

        $config =& get_config();
        $this->logPath = $config['log_path'] != '' ? $config['log_path'] : APPPATH . 'logs/';
    }

    // --------------------------------------------------------------------------

    /**
     * Get a list of log files
     * @return void
     */
    public function getAll( $iPage = NULL, $iPerPage = NULL, array $aData = [], $bIncludeDeleted = false )
    {
        $dirMap        = directory_map($this->logPath, 0);
        $logFiles      = array();
        $filenameRegex = '/^log\-(\d{4}\-\d{2}\-\d{2})\.php$/';

        foreach ($dirMap as $logFile) {

            if (preg_match($filenameRegex, $logFile)) {

                $logFiles[] = $logFile;
            }
        }

        arsort($logFiles);
        $logFiles = array_values($logFiles);

        $out = array();

        foreach ($logFiles as $file) {

            $temp        = new \stdClass();
            $temp->date  = preg_replace($filenameRegex, '$1', $file);
            $temp->file  = $file;
            $temp->lines = $this->countLines($this->logPath . $file);

            $out[] = $temp;
        }

        return $out;
    }

    // --------------------------------------------------------------------------

    public function readLog($file)
    {
        if (!is_file($this->logPath . $file)) {

            $this->setError('Not a valid log file.');
            return false;
        }

        $fh  = fopen($this->logPath . $file, 'rb');
        $out = array();
        $counter = 0;

        while (!feof($fh)) {

            $counter++;
            $line = trim(fgets($fh));

            if ($counter == 1 || empty($line)) {

                continue;
            }
            $out[] = $line;
        }

        fclose($fh);

        return $out;
    }

    // --------------------------------------------------------------------------

    protected function countLines($file)
    {
        $fh = fopen($file, 'rb');
        $lines = 0;

        while (!feof($fh)) {

            $line = fgets($fh);

            if (empty($line)) {

                continue;
            }

            $lines++;
        }

        fclose($fh);

        //  subtract 1, account for the opening <?php line
        return $lines-1;
    }
}
