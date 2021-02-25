<?php

/**
 * This class renders the admin dashboard
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

use Nails\Admin\Constants;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Interfaces\Dashboard\Alert;
use Nails\Admin\Service\Dashboard\Widget;
use Nails\Components;
use Nails\Factory;
use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;

/**
 * Class Dashboard
 *
 * @package Nails\Admin\Admin
 */
class Dashboard extends Base
{
    /**
     * Announces this controller's navGroups
     *
     * @return stdClass
     */
    public static function announce()
    {
        /** @var Nav $oNavGroup */
        $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNavGroup
            ->setLabel('Dashboard')
            ->setIcon('fa-home')
            ->addAction('Site Overview');

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * The admin homepage/dashboard
     *
     * @return void
     */
    public function index()
    {
        $this->data['page']->title = 'Welcome';
        $this->data['aAlerts']     = $this->getDashboardAlerts();
        $this->data['aWidgets']    = $this->getWidgets();

        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any dashboard alerts
     *
     * @return Alert[]
     */
    protected function getDashboardAlerts(): array
    {
        $aAlerts = [];

        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('Admin\\Dashboard\\Alert')
                ->whichImplement(Alert::class);

            foreach ($aClasses as $sClass) {
                /** @var Alert $oAlert */
                $oAlert = new $sClass();
                if ($oAlert->isAlerting()) {
                    $aAlerts[] = $oAlert;
                }
            }
        }

        return $aAlerts;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns dashboard widgets
     *
     * @return \Nails\Admin\Interfaces\Dashboard\Widget[]
     */
    protected function getWidgets(): array
    {
        /** @var Widget $oWidgetService */
        $oWidgetService = Factory::service('DashboardWidget', Constants::MODULE_SLUG);
        return $oWidgetService->getWidgetsForUser();
    }
}
