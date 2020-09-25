<?php

namespace Nails\Admin\Service;

class DashboardWidget
{
    protected static $aWidgets = [];

    // --------------------------------------------------------------------------

    public function __construct()
    {
        static::$aWidgets = static::getWidgets();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the available generators
     * @return array
     */
    public static function getWidgets()
    {
        $aWidgets = [];
        $aComponents = array_merge(
            [(object) ['namespace' => 'App\\']],
            _NAILS_GET_MODULES()
        );

        foreach ($aComponents as $oComponent) {
            $sClass = '\\' . $oComponent->namespace . 'Admin\DashboardWidget\Generator';
            if (class_exists($sClass) && classImplements($sClass, 'Nails\Admin\Interfaces\DashboardWidget')) {
                $aWidgets[] = $sClass;
            }
        }

        return $aWidgets;
    }
}
