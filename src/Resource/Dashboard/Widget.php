<?php

namespace Nails\Admin\Resource\Dashboard;

use Nails\Common\Resource\Entity;

/**
 * Class Widget
 *
 * @package Nails\Admin\Resource\Dashboard
 */
class Widget extends Entity
{
    /** @var string */
    public $slug;

    /** @var int */
    public $x;

    /** @var int */
    public $y;

    /** @var int */
    public $w;

    /** @var int */
    public $h;

    /** @var array */
    public $config;

    // --------------------------------------------------------------------------

    public function __construct($mObj = [])
    {
        parent::__construct($mObj);
        $this->config = json_decode($this->config, JSON_OBJECT_AS_ARRAY) ?? [];
    }
}
