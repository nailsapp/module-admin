<?php

namespace Nails\Admin\Interfaces\Dashboard;

use Nails\Admin\DataExport\SourceResponse;

/**
 * Interface Alert
 *
 * @package Nails\Admin\Interfaces\Dashboard
 */
interface Alert
{
    const SEVERITY_SUCCESS = 'success';
    const SEVERITY_DANGER  = 'danger';
    const SEVERITY_INFO    = 'info';
    const SEVERITY_WARNING = 'warning';

    // --------------------------------------------------------------------------

    /**
     * What the title of the alert should be
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    // --------------------------------------------------------------------------

    /**
     * What the body of the alert should be
     *
     * @return string|null
     */
    public function getBody(): ?string;

    // --------------------------------------------------------------------------

    /**
     * The severity of the alert, expected to be one of the sEVERIT_* constants
     *
     * @return string
     */
    public function getSeverity(): string;

    // --------------------------------------------------------------------------

    /**
     * Whether the alert is currenctly alerting
     *
     * @return bool
     */
    public function isAlerting(): bool;
}
