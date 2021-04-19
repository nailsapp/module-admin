<?php

namespace Nails\Admin\Event\Listener;

use Nails\Admin\Constants;
use Nails\Admin\Events;
use Nails\Common\Events\Subscription;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Factory;

/**
 * Class Session
 *
 * @package Nails\Admin\Event\Listener
 */
class Session extends Subscription
{
    /**
     * Session constructor.
     */
    public function __construct()
    {
        $this
            ->setEvent(Events::ADMIN_READY)
            ->setNamespace(Events::getEventNamespace())
            ->setCallback([$this, 'execute']);
    }

    // --------------------------------------------------------------------------

    /**
     * @throws FactoryException
     * @throws ModelException
     */
    public function execute(): void
    {
        /** @var \Nails\Common\Service\Session $oSessionService */
        $oSessionService = Factory::service('Session');
        /** @var \Nails\Admin\Model\Session $oModel */
        $oModel = Factory::model('Session', Constants::MODULE_SLUG);

        $iAdminSessionId = (int) $oSessionService->getUserData('admin_session_id');
        /** @var \Nails\Admin\Resource\Session $oSession */
        $oSession = $oModel->getById($iAdminSessionId);

        if (empty($oSession) || $oSession->user_id !== activeUser('id')) {
            $oSession = $this->newSession($oModel, activeUser('id'));
            $oSessionService->setUserData('admin_session_id', $oSession->id);
        }

        $this->newPageLoad($oModel, $oSession);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new admin session
     *
     * @param \Nails\Admin\Model\Session $oModel
     * @param int                        $iUserId
     *
     * @return \Nails\Admin\Resource\Session
     * @throws ModelException
     */
    private function newSession(\Nails\Admin\Model\Session $oModel, int $iUserId): \Nails\Admin\Resource\Session
    {
        $aSessions = $oModel->getAll([
            'where' => [
                ['user_id', $iUserId],
            ],
        ]);

        $oSession = reset($aSessions);

        return $oSession ?: $oModel->create(['user_id' => activeUser('id')], true);
    }

    // --------------------------------------------------------------------------

    /**
     * Registers a new page laod
     *
     * @param \Nails\Admin\Model\Session    $oModel
     * @param \Nails\Admin\Resource\Session $oSession
     *
     * @throws FactoryException
     * @throws ModelException
     */
    private function newPageLoad(\Nails\Admin\Model\Session $oModel, \Nails\Admin\Resource\Session $oSession)
    {
        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        $oModel->update(
            $oSession->id,
            [
                'url'              => uri_string(),
                'last_pageload'    => $oNow->format('Y-m-d H:i:s'),
                'last_heartbeat'   => $oNow->format('Y-m-d H:i:s'),
                'last_interaction' => $oNow->format('Y-m-d H:i:s'),
                'last_seen'        => $oNow->format('Y-m-d H:i:s'),
            ]
        );
    }
}
