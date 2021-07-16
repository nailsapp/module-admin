<?php

namespace Nails\Admin\Event\Listener\User\Merge;

use Nails\Admin\Constants;
use Nails\Auth;
use Nails\Common\Events\Subscription;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Service\Database;
use Nails\Factory;

/**
 * Class Pre
 *
 * @package Nails\Admin\Event\Listener\User\Merge
 */
class Pre extends Subscription
{
    /**
     * Pre constructor.
     *
     * @throws FactoryException
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $oModel = Factory::model('User', Auth\Constants::MODULE_SLUG);
        $this
            ->setEvent(Auth\Events::USER_MERGE_PRE)
            ->setNamespace($oModel::getEventNamespace())
            ->setCallback([$this, 'execute']);
    }

    // --------------------------------------------------------------------------

    /**
     * @param int   $iKeepId
     * @param array $aMergeIds
     *
     * @throws FactoryException
     * @throws ModelException
     */
    public function execute(int $iKeepId, array $aMergeIds): void
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');

        $this->deleteDashboardWidgets($oDb, $aMergeIds);
        $this->deleteSessions($oDb, $aMergeIds);
        $this->deleteMeta($oDb, $aMergeIds);
    }

    // --------------------------------------------------------------------------

    /**
     * @param Database $oDb
     * @param array    $aMergeIds
     *
     * @throws FactoryException
     * @throws ModelException
     */
    private function deleteDashboardWidgets(Database $oDb, array $aMergeIds): void
    {
        $oModel = Factory::model('DashboardWidget', Constants::MODULE_SLUG);

        $oDb->or_where_in($oModel->getColumnCreatedBy(), $aMergeIds);
        $oDb->or_where_in($oModel->getColumnModifiedBy(), $aMergeIds);
        $oDb->delete($oModel->getTableName());

    }

    // --------------------------------------------------------------------------

    /**
     * @param Database $oDb
     * @param array    $aMergeIds
     *
     * @throws FactoryException
     * @throws ModelException
     */
    private function deleteSessions(Database $oDb, array $aMergeIds): void
    {
        $oModel = Factory::model('Session', Constants::MODULE_SLUG);

        $oDb->or_where_in('user_id', $aMergeIds);
        $oDb->delete($oModel->getTableName());
    }

    // --------------------------------------------------------------------------

    /**
     * @param Database $oDb
     * @param array    $aMergeIds
     *
     * @throws FactoryException
     */
    private function deleteMeta(Database $oDb, array $aMergeIds): void
    {
        /** @var \Nails\Admin\Model\Admin $oModel */
        $oModel = Factory::model('Admin', Constants::MODULE_SLUG);

        $oDb->or_where_in('user_id', $aMergeIds);
        $oDb->delete($oModel->getUserMetaTable());
    }
}
