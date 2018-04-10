<?php

return [
    'services'  => [
        'DataExport' => function () {
            if (class_exists('\App\Admin\Service\DataExport')) {
                return new \App\Admin\Service\DataExport();
            } else {
                return new \Nails\Admin\Service\DataExport();
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
            if (class_exists('\App\Admin\Factory\Nav')) {
                return new \App\Admin\Factory\Nav();
            } else {
                return new \Nails\Admin\Factory\Nav();
            }
        },
        'NavAlert'                 => function () {
            if (class_exists('\App\Admin\Factory\Nav\Alert')) {
                return new \App\Admin\Factory\Nav\Alert();
            } else {
                return new \Nails\Admin\Factory\Nav\Alert();
            }
        },
        'DataExportSourceResponse' => function () {
            if (class_exists('\App\Admin\DataExport\SourceResponse')) {
                return new \App\Admin\DataExport\SourceResponse();
            } else {
                return new \Nails\Admin\DataExport\SourceResponse();
            }
        },
        'IndexFilter' => function() {
            if (class_exists('\App\Admin\Factory\IndexFilter')) {
                return new \App\Admin\Factory\IndexFilter();
            } else {
                return new \Nails\Admin\Factory\IndexFilter();
            }
        },
        'IndexFilterOption' => function() {
            if (class_exists('\App\Admin\Factory\IndexFilter\Option')) {
                return new \App\Admin\Factory\IndexFilter\Option();
            } else {
                return new \Nails\Admin\Factory\IndexFilter\Option();
            }
        }
    ],
];
