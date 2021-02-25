<?php

namespace Nails\Admin\Service\Dashboard;

use Nails\Admin\Interfaces;
use Nails\Auth\Resource\User;
use Nails\Components;

/**
 * Class Widget
 *
 * @package Nails\Admin\Service\Dashboard
 */
class Widget
{
    /**
     * The supported size of widgets
     */
    const SIZE_SMALL  = 'small';
    const SIZE_MEDIUM = 'medium';
    const SIZE_LARGE  = 'large';

    // --------------------------------------------------------------------------

    /** @var string[] */
    protected $aWidgets = [];

    // --------------------------------------------------------------------------

    /**
     * Widget constructor.
     */
    public function __construct()
    {
        $this->aWidgets = $this->discoverWidgets();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the available generators
     *
     * @return string[]
     */
    private function discoverWidgets(): array
    {
        $aWidgets = [];
        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('Admin\\Dashboard\\Widget')
                ->whichImplement(Interfaces\Dashboard\Widget::class)
                ->whichCanBeInstantiated();

            foreach ($aClasses as $sClass) {
                $aWidgets[] = $sClass;
            }
        }

        return $aWidgets;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns discovered widgets
     *
     * @return string[]
     */
    public function getWidgets(): array
    {
        return $this->aWidgets;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns instantiated and configured widgets for a given user
     *
     * @param User|null $oUser The suer to fetch for
     *
     * @return Interfaces\Dashboard\Widget[]
     */
    public function getWidgetsForUser(User $oUser = null): array
    {
        $oUser = $oUser ?? activeUser();

        //  @todo (Pablo 25/02/2021) - These should come from the database
        $aWidgets = $this->getWidgets();

        $aOut = [];
        foreach ($aWidgets as $sClass) {

            //  @todo (Pablo 25/02/2021) - These should come from the database
            $sUserSize   = null;
            $aUserConfig = [];

            $aOut[] = new $sClass($sUserSize, $aUserConfig);
        }

        return $aOut;
    }
}
