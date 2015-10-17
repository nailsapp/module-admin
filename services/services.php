<?php

return array(
    'factories' => array(
        'Nav' => function () {
            if (class_exists('\App\Admin\Nav')) {
                return new \App\Admin\Nav();
            } else {
                return new \Nails\Admin\Nav();
            }
        }
    )
);
