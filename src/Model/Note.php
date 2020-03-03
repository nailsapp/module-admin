<?php

namespace Nails\Admin\Model;

use Nails\Auth\Constants;
use Nails\Common\Model\Base;
use Nails\Config;

class Note extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->table             = Config::geT('NAILS_DB_PREFIX') . 'admin_note';
        $this->destructiveDelete = false;
        $this->addExpandableField([
            'trigger'   => 'created_by',
            'model'     => 'User',
            'provider'  => Constants::MODULE_SLUG,
            'id_column' => 'created_by',
        ]);
    }
}
