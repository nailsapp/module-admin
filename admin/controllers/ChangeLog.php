<?php

namespace Nails\Admin\Admin;

use Nails\Admin\Controller\DefaultController;

/**
 * Class ChangeLog
 *
 * @package Nails\Admin\Admin
 */
class ChangeLog extends DefaultController
{
    const CONFIG_MODEL_NAME     = 'ChangeLog';
    const CONFIG_MODEL_PROVIDER = 'nails/module-admin';
    const CONFIG_SIDEBAR_GROUP  = 'Logs';
    const CONFIG_SIDEBAR_FORMAT = 'Browse Admin Logs';
    const CONFIG_SORT_OPTIONS   = [
        'Created' => 'created',
    ];
    const CONFIG_INDEX_FIELDS   = [
        'User'    => 'user',
        'Changes' => null,
        'Date'    => 'created',
    ];
    const CONFIG_INDEX_DATA     = [
        'expand' => [
            'user',
        ],
    ];
    const CONFIG_CAN_CREATE     = false;
    const CONFIG_CAN_DELETE     = false;
    const CONFIG_CAN_EDIT       = false;
    const CONFIG_CAN_VIEW       = false;

    // --------------------------------------------------------------------------

    /**
     * ChangeLog constructor.
     *
     * @throws \Nails\Common\Exception\NailsException
     */
    public function __construct()
    {
        parent::__construct();
        $this->aConfig['INDEX_FIELDS']['Changes'] = function ($oLog) {
            return implode('<hr style="margin: 0.5em 0;" />', array_filter([
                $this->getLogSentence($oLog),
                $this->getLogChanges($oLog),
            ]));
        };
    }

    // --------------------------------------------------------------------------

    /**
     * Compiles a summary sentence
     *
     * @param \Nails\Admin\Resource\ChangeLog $oLog The log item
     *
     * @return string
     */
    protected function getLogSentence(\Nails\Admin\Resource\ChangeLog $oLog): string
    {
        return implode(' ', array_filter([
            $oLog->user->first_name ?? 'Someone',
            $oLog->verb,
            $oLog->article,
            $oLog->title
                ? $oLog->item . ','
                : $oLog->item,
            $oLog->url
                ? '<strong>' . anchor($oLog->url, $oLog->title) . '</strong>'
                : $oLog->title,
        ]));
    }

    // --------------------------------------------------------------------------

    /**
     * Flattens the log changes into a list/string
     *
     * @param $oLog
     *
     * @return string
     */
    protected function getLogChanges(\Nails\Admin\Resource\ChangeLog $oLog): string
    {
        return sprintf(
            '<small><ul>%s</ul></small>',
            implode(PHP_EOL, array_map(function ($oChange) {
                return sprintf(
                    '<li><strong>%s</strong>: <em>%s</em>&nbsp;&rarr;&nbsp;<em>%s</em></li>',
                    $oChange->field,
                    $oChange->old_value ?: '<span>blank</span>',
                    $oChange->new_value ?: '<span>blank</span>',
                );
            }, json_decode($oLog->changes) ?? []))
        );
    }
}
