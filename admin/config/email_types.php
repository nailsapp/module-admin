<?php

/**
 * This config file defines email types for this module.
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Config
 * @author      Nails Dev Team
 * @link
 */

$config['email_types'] = [
    (object) [
        'slug'             => 'data_export',
        'name'             => 'Admin: Data Export',
        'can_unsubscribe'  => false,
        'description'      => 'Sent when a data export is completed',
        'template_header'  => '',
        'template_body'    => 'admin/Email/templates/data_export',
        'template_footer'  => '',
        'default_subject'  => 'Data Export Complete',
    ],
];
