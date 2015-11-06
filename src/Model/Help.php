<?php

/**
 * Admin help model
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

class Help extends Base
{
    /**
     * Constructs the model
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = NAILS_DB_PREFIX . 'admin_help_video';
    }
}
