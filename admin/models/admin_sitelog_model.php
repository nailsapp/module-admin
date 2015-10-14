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

use Nails\Factory;

class NAILS_Admin_sitelog_model extends NAILS_Model
{
    protected $logPath;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();
        Factory::helper('directory');

        // --------------------------------------------------------------------------

        $config =& get_config();
        $this->logPath = $config['log_path'] != '' ? $config['log_path'] : FCPATH . APPPATH . 'logs/';
    }

    // --------------------------------------------------------------------------

    /**
     * Get a list of log files
     * @return void
     */
    public function getAll()
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

            $this->_set_error('Not a valid log file.');
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

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' MODELS
 *
 * The following block of code makes it simple to extend one of the core Nails
 * models. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_ADMIN_SITELOG_MODEL')) {

    class Admin_sitelog_model extends NAILS_Admin_sitelog_model
    {
    }
}
