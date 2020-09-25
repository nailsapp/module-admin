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
        $this->data['sPhrase']     = $this->getWelcomePhrase();
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
     * Returns the phrases to use for the dashboard
     *
     * @return string[]
     */
    protected function getWelcomePhrases(): array
    {
        $aPhrases = [
            'Be awesome.',
            'You look nice!',
            'What are we doing today?',
        ];

        if (activeUser('first_name')) {

            $aPhrases[] = 'Today is gonna be a good day, ' . activeUser('first_name') . '.';
            $aPhrases[] = 'Hey, ' . activeUser('first_name') . '!';

        } else {

            $aPhrases[] = 'Today is gonna be a good day.';
            $aPhrases[] = 'Hey!';
        }

        return $aPhrases;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a random welcome phrase
     *
     * @return string
     */
    protected function getWelcomePhrase(): string
    {
        return random_element($this->getWelcomePhrases());
    }

    // --------------------------------------------------------------------------

    /**
     * Returns dashbaord widgets
     *
     * @return array
     */
    protected function getWidgets(): array
    {
        $oDashboardWidgetService = Factory::service('DashboardWidget', 'nailsapp/module-admin');
        return $oDashboardWidgetService::getWidgets();
    }
}
