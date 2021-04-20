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
    /** @var string */
    public $token;

    /** @var int */
    public $user_id;

    /** @var User */
    public $user;

    /** @var string */
    public $url;

    /** @var DateTime */
    public $heartbeat;

    /** @var DateTime */
    public $inactive;
}
