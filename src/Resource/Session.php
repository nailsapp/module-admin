<?php

namespace Nails\Admin\Resource;

use Nails\Auth\Resource\User;
use Nails\Common\Resource\DateTime;
use Nails\Common\Resource\Entity;

/**
 * Class Session
 *
 * @package Nails\Admin\Resource
 */
class Session extends Entity
{
    /** @var int */
    public $user_id;

    /** @var User */
    public $user;

    /** @var string */
    public $url;

    /** @var DateTime */
    public $last_pageload;

    /** @var DateTime */
    public $last_heartbeat;

    /** @var DateTime */
    public $last_interaction;

    /** @var DateTime */
    public $last_seen;
}
