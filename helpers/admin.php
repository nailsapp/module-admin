<?php

use Nails\Admin\Exception\HelperException;

if (!function_exists('adminHelper'))
{
    /**
     * Call an adminHelper static method, the first parameter should be the method to call,
     * all following params will be passed as if been called directly.
     * @return mixed
     */
    function adminHelper()
    {
        $aArgs   = func_get_args();
        $sMethod = array_shift($aArgs);

        if (!is_string($sMethod)) {
            throw new HelperException('First parameter must be a string.', 1);
        }

        //  Attempt to call the static method
        $sMethod = '\Nails\Admin\Helper::' . $sMethod;
        return call_user_func_array($sMethod, $aArgs);
    }
}