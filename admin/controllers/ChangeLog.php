<?php

namespace Nails\Admin\Admin;

use Nails\Admin\Controller\DefaultController;
use Nails\Admin\Factory\IndexFilter;
use Nails\Auth;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Service\Database;
use Nails\Factory;

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
    const CONFIG_SORT_DIRECTION = self::SORT_DESCENDING;
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
    const CONFIG_PERMISSION     = 'admin:logs:change';

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

    // --------------------------------------------------------------------------

    /**
     * Adds filters to the idnex page
     *
     * @return array
     * @throws FactoryException
     * @throws ModelException
     */
    protected function indexDropdownFilters(): array
    {
        return array_merge(
            parent::indexDropdownFilters(),
            array_filter([
                $this->filterByUser(),
                $this->filterByEntity(),
            ])
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an IndexFilter for the users who have made changes
     *
     * @return IndexFilter|null
     * @throws FactoryException
     * @throws ModelException
     */
    protected function filterByUser(): ?IndexFilter
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var \Nails\Admin\Model\ChangeLog $oChangeLogModel */
        $oChangeLogModel = static::getModel();
        /** @var Auth\Model\User $oUserModel */
        $oUserModel = Factory::model('User', Auth\Constants::MODULE_SLUG);

        $aUserIds = $oDb
            ->select('DISTINCT(user_id)')
            ->get($oChangeLogModel->getTableName())->result();

        $aUsers = $oUserModel->getByIds(arrayExtractProperty($aUserIds, 'user_id'));

        if (empty($aUsers) || count($aUsers) === 1) {
            return null;
        }

        /** @var IndexFilter $oFilter */
        $oFilter = Factory::factory('IndexFilter', 'nails/module-admin');
        $oFilter
            ->setLabel('User')
            ->setColumn('user_id')
            ->addOption('Everyone')
            ->addOptions(array_map(function (Auth\Resource\User $oUser) {
                /** @var IndexFilter\Option $oOption */
                $oOption = Factory::factory('IndexFilterOption', 'nails/module-admin');
                $oOption
                    ->setLabel(sprintf(
                        '#%s - %s (%s)',
                        $oUser->id,
                        $oUser->name,
                        $oUser->email
                    ))
                    ->setValue($oUser->id);

                return $oOption;
            }, $aUsers));

        return $oFilter;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an IndexFilter for the various types of entities in the changelog
     *
     * @return IndexFilter|null
     * @throws FactoryException
     * @throws ModelException
     */
    protected function filterByEntity(): ?IndexFilter
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var \Nails\Admin\Model\ChangeLog $oChangeLogModel */
        $oChangeLogModel = static::getModel();

        $aEntities = $oDb
            ->select('DISTINCT(item)')
            ->get($oChangeLogModel->getTableName())->result();

        $aEntities = arrayExtractProperty($aEntities, 'item');

        if (empty($aEntities) || count($aEntities) === 1) {
            return null;
        }

        /** @var IndexFilter $oFilter */
        $oFilter = Factory::factory('IndexFilter', 'nails/module-admin');
        $oFilter
            ->setLabel('Type')
            ->setColumn('item')
            ->addOption('All')
            ->addOptions(array_map(function (string $sType) {
                /** @var IndexFilter\Option $oOption */
                $oOption = Factory::factory('IndexFilterOption', 'nails/module-admin');
                $oOption
                    ->setLabel($sType)
                    ->setValue($sType);

                return $oOption;
            }, $aEntities));

        return $oFilter;
    }
}
