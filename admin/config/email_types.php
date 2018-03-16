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
        'slug'             => 'test_email',
        'name'             => 'Admin: Test Email',
        'isUnsubscribable' => false,
        'description'      => 'Test email template, normally used in admin to test if recipients can receive email sent by the system',
        'template_header'  => '',
        'template_body'    => 'admin/Email/templates/test_email',
        'template_footer'  => '',
        'default_subject'  => 'Test email sent at {{sentAt}}',
    ],

    (object) [
        'slug'             => 'data_export',
        'name'             => 'Admin: Data Export',
        'isUnsubscribable' => false,
        'description'      => 'Sent when a data export is completed',
        'template_header'  => '',
        'template_body'    => 'admin/Email/templates/data_export',
        'template_footer'  => '',
        'default_subject'  => 'Data Export Complete',
    ],
];
