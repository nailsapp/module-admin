<?php

namespace Nails\Admin\Model;

use Nails\Auth\Constants;
use Nails\Common\Model\Base;
use Nails\Config;

class Note extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_note';

    /**
     * Whether this model uses destructive delete or not
     *
     * @var bool
     */
    const DESTRUCTIVE_DELETE = false;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();
        $this->addExpandableField([
            'trigger'   => 'created_by',
            'model'     => 'User',
            'provider'  => Constants::MODULE_SLUG,
            'id_column' => 'created_by',
        ]);
    }
}
