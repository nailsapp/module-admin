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
        $this->table       = NAILS_DB_PREFIX . 'admin_help_video';
        $this->tableAlias = 'hv';
    }

    // --------------------------------------------------------------------------

    /**
     * This method applies the conditionals which are common across the get_*()
     * methods and the count() method.
     * @param array  $data Data passed from the calling method
     * @return void
     **/
    protected function getCountCommon($data = array())
    {
        if (!empty($data['keywords'])) {

            if (empty($data['or_like'])) {

                $data['or_like'] = array();
            }

            $data['or_like'][] = array(
                'column' => $this->tableAlias . '.label',
                'value'  => $data['keywords']
            );
            $data['or_like'][] = array(
                'column' => $this->tableAlias . '.description',
                'value'  => $data['keywords']
            );
        }

        parent::getCountCommon($data);
    }
}
