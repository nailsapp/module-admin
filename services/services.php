<?php

return [
    'services'  => [
        'DataExport' => function () {
            if (class_exists('\App\Admin\Library\DataExport')) {
                return new \App\Admin\Library\DataExport();
            } else {
                return new \Nails\Admin\Library\DataExport();
            }
        },
    ],
    'models'    => [
        'Admin'     => function () {
            if (class_exists('\App\Admin\Model\Admin')) {
                return new \App\Admin\Model\Admin();
            } else {
                return new \Nails\Admin\Model\Admin();
            }
        },
        'ChangeLog' => function () {
            if (class_exists('\App\Admin\Model\ChangeLog')) {
                return new \App\Admin\Model\ChangeLog();
            } else {
                return new \Nails\Admin\Model\ChangeLog();
            }
        },
        'Export'    => function () {
            if (class_exists('\App\Admin\Model\Export')) {
                return new \App\Admin\Model\Export();
            } else {
                return new \Nails\Admin\Model\Export();
            }
        },
        'Help'      => function () {
            if (class_exists('\App\Admin\Model\Help')) {
                return new \App\Admin\Model\Help();
            } else {
                return new \Nails\Admin\Model\Help();
            }
        },
        'SiteLog'   => function () {
            if (class_exists('\App\Admin\Model\SiteLog')) {
                return new \App\Admin\Model\SiteLog();
            } else {
                return new \Nails\Admin\Model\SiteLog();
            }
        },
    ],
    'factories' => [
        'EmailDataExport'          => function () {
            if (class_exists('\App\Admin\Factory\Email\DataExport')) {
                return new \App\Admin\Factory\Email\DataExport();
            } else {
                return new \Nails\Admin\Factory\Email\DataExport();
            }
        },
        'EmailTest'                => function () {
            if (class_exists('\App\Admin\Factory\Email\Test')) {
                return new \App\Admin\Factory\Email\Test();
            } else {
                return new \Nails\Admin\Factory\Email\Test();
            }
        },
        'Nav'                      => function () {
            if (class_exists('\App\Admin\Nav')) {
                return new \App\Admin\Nav();
            } else {
                return new \Nails\Admin\Nav();
            }
        },
        'NavAlert'                 => function () {
            if (class_exists('\App\Admin\NavAlert')) {
                return new \App\Admin\NavAlert();
            } else {
                return new \Nails\Admin\NavAlert();
            }
        },
        'DataExportSourceResponse' => function () {
            if (class_exists('\App\Admin\DataExport\SourceResponse')) {
                return new \App\Admin\DataExport\SourceResponse();
            } else {
                return new \Nails\Admin\DataExport\SourceResponse();
            }
        },
    ],
];
