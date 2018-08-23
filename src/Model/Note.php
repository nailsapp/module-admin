<?php

namespace Nails\Admin\Model;

use Nails\Common\Model\Base;

class Note extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->table             = NAILS_DB_PREFIX . 'admin_note';
        $this->destructiveDelete = false;
    }
}
