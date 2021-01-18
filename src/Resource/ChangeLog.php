<?php

namespace Nails\Admin\Resource;

use Nails\Auth\Resource\User;
use Nails\Common\Resource\Entity;

/**
 * Class ChangeLog
 *
 * @package Nails\Admin\Resource
 */
class ChangeLog extends Entity
{
    /** @var int */
    public $user_id;

    /** @var User */
    public $user;

    /** @var string */
    public $verb;

    /** @var string */
    public $article;

    /** @var string */
    public $item;

    /** @var int */
    public $item_id;

    /** @var string */
    public $title;

    /** @var string */
    public $url;

    /** @var string */
    public $changes;
}
