<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Define email types for this module.
 */

$config['email_types'] = array();

$config['email_types'][0]					= new stdClass();
$config['email_types'][0]->slug				= 'test_email';
$config['email_types'][0]->name				= 'Test Email';
$config['email_types'][0]->description		= 'Test email template, normally used in admin to test if recipients can receive email sent by the system';
$config['email_types'][0]->template_header	= '';
$config['email_types'][0]->template_body	= 'admin/email/email_templates/test_email';
$config['email_types'][0]->template_footer	= '';
$config['email_types'][0]->default_subject	= 'Test Email';

/* End of file email_types.php */
/* Location: ./module-admin/admin/config/email_types.php */