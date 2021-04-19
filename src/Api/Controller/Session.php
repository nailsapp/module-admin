<?php

/**
 * Admin API end points: session
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Controller\BaseApi;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Helper\Model\Expand;
use NAils\Factory;

/**
 * Class Session
 *
 * @package Nails\Admin\Api\Controller
 */
class Session extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    /**
     * Registers a session heartbeat
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ValidationException
     */
    public function putHeartbeat(): Api\Factory\ApiResponse
    {
        $oSession = $this->getSession();

        $this->updateTimestamp($oSession, 'last_heartbeat');

        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse->setData($this->getOtherSessions($oSession));
        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * Registers a session interaction
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ModelException
     */
    public function putInteract(): Api\Factory\ApiResponse
    {
        $oSession = $this->getSession();
        $this->updateTimestamp($oSession, 'last_interaction');

        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the user's admin session
     *
     * @return \Nails\Admin\Resource\Session|null
     * @throws FactoryException
     * @throws ModelException
     */
    private function getSession(): ?\Nails\Admin\Resource\Session
    {
        /** @var \Nails\Common\Service\Session $oSessionService */
        $oSessionService = Factory::service('Session');
        /** @var \Nails\Admin\Model\Session $oModel */
        $oModel = Factory::model('Session', Constants::MODULE_SLUG);

        $iAdminSessionId = (int) $oSessionService->getUserData('admin_session_id');
        /** @var \Nails\Admin\Resource\Session $oSession */
        return $oModel->getById($iAdminSessionId);
    }

    // --------------------------------------------------------------------------

    /**
     * Updates a session timestamp
     *
     * @param \Nails\Admin\Resource\Session|null $oSession
     * @param string                             $sColumn
     *
     * @throws FactoryException
     * @throws ModelException
     */
    private function updateTimestamp(?\Nails\Admin\Resource\Session $oSession, string $sColumn)
    {
        if (!empty($oSession) && $oSession->user_id === activeUser('id')) {

            /** @var \DateTime $oNow */
            $oNow = Factory::factory('DateTime');
            /** @var \Nails\Admin\Model\Session $oModel */
            $oModel = Factory::model('Session', Constants::MODULE_SLUG);

            $oModel->update(
                $oSession->id,
                [
                    $sColumn    => $oNow->format('Y-m-d H:i:s'),
                    'last_seen' => $oNow->format('Y-m-d H:i:s'),
                ]
            );
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Gets other sessions from the same page
     *
     * @param \Nails\Admin\Resource\Session|null $oSession
     *
     * @return \Nails\Admin\Resource\Session[]
     * @throws FactoryException
     * @throws ModelException
     */
    private function getOtherSessions(?\Nails\Admin\Resource\Session $oSession): array
    {
        if (empty($oSession)) {
            return [];
        }

        /** @var \Nails\Admin\Model\Session $oModel */
        $oModel = Factory::model('Session', Constants::MODULE_SLUG);

        /** @var \Nails\Admin\Resource\Session[] $aSessions */
        $aSessions = $oModel->getAll([
            new Expand('user'),
            'where' => [
                ['url', $oSession->url],
                ['user_id !=', $oSession->user_id],
                ['last_heartbeat >', 'DATE_SUB(NOW(), INTERVAL 60 MINUTE)', false],
            ],
        ]);

        return array_map(function (\Nails\Admin\Resource\Session $oSession) {
            return (object) [
                'user' => (object) [
                    'id'                        => $oSession->user->id,
                    'name'                      => $oSession->user->name,
                    'avatar'                    => cdnAvatar($oSession->user->id),
                    'last_heartbeat'            => $oSession->last_heartbeat->raw,
                    'last_heartbeat_relative'   => $oSession->last_heartbeat->relative(),
                    'last_interaction'          => $oSession->last_interaction->raw,
                    'last_interaction_relative' => $oSession->last_interaction->relative(),
                    'last_seen'                 => $oSession->last_seen->raw,
                    'last_seen_relative'        => $oSession->last_seen->relative(),
                ],
            ];
        }, $aSessions);
    }
}
