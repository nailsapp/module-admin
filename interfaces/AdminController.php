<?php

namespace Nails\Admin\Interfaces;

interface AdminController
{
    /**
     * Defines the admin controller
     * @return stdClass
     */
    public static function announce();

    /**
     * Returns any notifications which the suer should know about
     * @param  string $classIndex The classIndex value, used when multiple admin instances are available
     * @return array
     */
    public static function notifications($classIndex = null);

    /**
     * Returns an array of permissions which can be configured for the user
     * @param  string $classIndex The classIndex value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null);
}
