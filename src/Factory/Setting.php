<?php

namespace Nails\Admin\Factory;

use Nails\Common\Factory\Model\Field;

/**
 * Class Setting
 *
 * @package Nails\Admin\Factory
 */
class Setting extends Field
{
    /**
     * @var bool
     */
    public $encrypted = false;

    // --------------------------------------------------------------------------

    /**
     * Ge tht eencrypted property
     *
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return $this->encrypted;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the encrypted property
     *
     * @param bool $bEncrypted
     *
     * @return $this
     */
    public function setEncrypted(bool $bEncrypted): self
    {
        $this->encrypted = $bEncrypted;
        return $this;
    }
}
