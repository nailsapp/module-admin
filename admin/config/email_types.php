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

$config['email_types'] = array();

$config['email_types'][0]					= new \stdClass();
$config['email_types'][0]->slug				= 'test_email';
$config['email_types'][0]->name				= 'Test Email';
$config['email_types'][0]->description		= 'Test email template, normally used in admin to test if recipients can receive email sent by the system';
$config['email_types'][0]->template_header	= '';
$config['email_types'][0]->template_body	= 'admin/email/email_templates/test_email';
$config['email_types'][0]->template_footer	= '';
$config['email_types'][0]->default_subject	= 'Test Email';
