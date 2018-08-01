<?php

namespace Nails\Admin\Model;

use Nails\Common\Model\Base;

class Notes extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->table = NAILS_DB_PREFIX . 'admin_notes';
    }
}
