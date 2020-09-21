<?php

use Nails\Admin\Factory;
use Nails\Admin\Model;
use Nails\Admin\Resource;
use Nails\Admin\Service;

return [
    'services'  => [
        'DataExport' => function (): Service\DataExport {
            if (class_exists('\App\Admin\Service\DataExport')) {
                return new \App\Admin\Service\DataExport();
            } else {
                return new Service\DataExport();
            }
        },
    ],
    'models'    => [
        'Admin'     => function (): Model\Admin {
            if (class_exists('\App\Admin\Model\Admin')) {
                return new \App\Admin\Model\Admin();
            } else {
                return new Model\Admin();
            }
        },
        'ChangeLog' => function (): Model\ChangeLog {
            if (class_exists('\App\Admin\Model\ChangeLog')) {
                return new \App\Admin\Model\ChangeLog();
            } else {
                return new Model\ChangeLog();
            }
        },
        'Export'    => function (): Model\Export {
            if (class_exists('\App\Admin\Model\Export')) {
                return new \App\Admin\Model\Export();
            } else {
                return new Model\Export();
            }
        },
        'Help'      => function (): Model\Help {
            if (class_exists('\App\Admin\Model\Help')) {
                return new \App\Admin\Model\Help();
            } else {
                return new Model\Help();
            }
        },
        'Note'      => function (): Model\Note {
            if (class_exists('\App\Admin\Model\Note')) {
                return new \App\Admin\Model\Note();
            } else {
                return new Model\Note();
            }
        },
        'SiteLog'   => function (): Model\SiteLog {
            if (class_exists('\App\Admin\Model\SiteLog')) {
                return new \App\Admin\Model\SiteLog();
            } else {
                return new Model\SiteLog();
            }
        },
    ],
    'resources' => [
        'DataExportFormat' => function ($mObj): Resource\DataExport\Format {
            if (class_exists('\App\Admin\Resource\DataExport\Format')) {
                return new \App\Admin\Resource\DataExport\Format($mObj);
            } else {
                return new Resource\DataExport\Format($mObj);
            }
        },
        'DataExportSource' => function ($mObj): Resource\DataExport\Source {
            if (class_exists('\App\Admin\Resource\DataExport\Source')) {
                return new \App\Admin\Resource\DataExport\Source($mObj);
            } else {
                return new Resource\DataExport\Source($mObj);
            }
        },
    ],
    'factories' => [
        'EmailDataExport'          => function (): Factory\Email\DataExport {
            if (class_exists('\App\Admin\Factory\Email\DataExport')) {
                return new \App\Admin\Factory\Email\DataExport();
            } else {
                return new Factory\Email\DataExport();
            }
        },
        'Nav'                      => function (): Factory\Nav {
            if (class_exists('\App\Admin\Factory\Nav')) {
                return new \App\Admin\Factory\Nav();
            } else {
                return new Factory\Nav();
            }
        },
        'NavAlert'                 => function (): Factory\Nav\Alert {
            if (class_exists('\App\Admin\Factory\Nav\Alert')) {
                return new \App\Admin\Factory\Nav\Alert();
            } else {
                return new Factory\Nav\Alert();
            }
        },
        'DataExportSourceResponse' => function () {
            if (class_exists('\App\Admin\DataExport\SourceResponse')) {
                return new \App\Admin\DataExport\SourceResponse();
            } else {
                return new \Nails\Admin\DataExport\SourceResponse();
            }
        },
        'IndexFilter'              => function (): Factory\IndexFilter {
            if (class_exists('\App\Admin\Factory\IndexFilter')) {
                return new \App\Admin\Factory\IndexFilter();
            } else {
                return new Factory\IndexFilter();
            }
        },
        'IndexFilterOption'        => function (): Factory\IndexFilter\Option {
            if (class_exists('\App\Admin\Factory\IndexFilter\Option')) {
                return new \App\Admin\Factory\IndexFilter\Option();
            } else {
                return new Factory\IndexFilter\Option();
            }
        },
        'ModelFieldDynamicTable'   => function (): Factory\Model\Field\DynamicTable {
            if (class_exists('\App\Admin\Factory\Model\Field\DynamicTable')) {
                return new \App\Admin\Factory\Model\Field\DynamicTable();
            } else {
                return new Factory\Model\Field\DynamicTable();
            }
        },
        'Setting'                  => function (): Factory\Setting {
            if (class_exists('\App\Admin\Factory\Setting')) {
                return new \App\Admin\Factory\Setting();
            } else {
                return new Factory\Setting();
            }
        },
    ],
];
