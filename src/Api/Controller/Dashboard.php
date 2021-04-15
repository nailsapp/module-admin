<?php

/**
 * Admin API end points: dashbaord
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
use Nails\Admin\Service\Dashboard\Widget;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ValidationException;
use Nails\Factory;

/**
 * Class Dashboard
 *
 * @package Nails\Admin\Api\Controller
 */
class Dashboard extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    /**
     * Returns the available dashboard widgets
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ValidationException
     */
    public function getIndex(): Api\Factory\ApiResponse
    {
        /** @var Widget $oWidgetService */
        $oWidgetService = Factory::service('DashboardWidget', Constants::MODULE_SLUG);
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);

        $oApiResponse
            ->setData([
                'widgets' => array_map(function (string $sClass) {

                    /** @var \Nails\Admin\Interfaces\Dashboard\Widget $oWidget */
                    $oWidget = new $sClass();
                    return (object) [
                        'slug'   => $sClass,
                        'title'  => $oWidget->getTitle(),
                        'config' => $oWidget->getConfig(),

                    ];

                }, $oWidgetService->getWidgets()),
            ]);

        return $oApiResponse;
    }
}
