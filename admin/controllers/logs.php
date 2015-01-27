<?php

//  Include NAILS_Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * Browse the site logs
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class NAILS_Logs extends NAILS_Admin_Controller
{
    /**
     * Announces this controllers details
     * @return stdClass
     */
    public static function announce()
    {
        $d = new stdClass();

        // --------------------------------------------------------------------------

        //  Configurations
        $d->name = 'Logs';
        $d->icon = 'fa-archive';

        // --------------------------------------------------------------------------

        //  Navigation options
        if (user_has_permission('admin.logs:0.can_browse_site_logs')) {

            $d->funcs['site'] = 'Browse Site Logs';
        }

        if (user_has_permission('admin.logs:0.can_browse_event_logs')) {

            $d->funcs['event'] = 'Browse Event Logs';
        }

        if (user_has_permission('admin.logs:0.can_browse_admin_logs')) {

            $d->funcs['changelog'] = 'Browse Admin Logs';
        }

        // --------------------------------------------------------------------------

        return $d;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null)
    {
        $permissions = parent::permissions($classIndex);

        // --------------------------------------------------------------------------

        $permissions['can_browse_site_logs']  = 'Can browse site logs';
        $permissions['can_browse_event_logs'] = 'Can browse event logs';
        $permissions['can_browse_admin_logs'] = 'Can browse admin logs';

        // --------------------------------------------------------------------------

        return $permissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Browse site log files
     * @return void
     */
    public function site()
    {
        if (!user_has_permission('admin.logs:0.can_browse_site_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Browse Logs';

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('admin/logs/site/index', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Site Events
     * @return void
     */
    public function event()
    {
        if (!user_has_permission('admin.logs:0.can_browse_event_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Events';

        // --------------------------------------------------------------------------

        //  Load event library
        $this->load->library('event');

        // --------------------------------------------------------------------------

        /**
         * Define limit and order
         * A little messy but it's because the Event library doesn't follow the
         * same standard as the models - it should. @TODO.
         */

        $per_page = $this->input->get('per_page') ? $this->input->get('per_page') : 50;
        $page     = (int) $this->input->get('page');
        $page--;
        $page     = $page < 0 ? 0 : $page;
        $offset   = $page * $per_page;
        $limit    = array($per_page, $offset);
        $order    = array(
                        $this->input->get('sort') ? $this->input->get('sort') : 'e.created',
                        $this->input->get('order') ? $this->input->get('order') : 'DESC'
                    );

        // --------------------------------------------------------------------------

        //  Define the data user & type restriction and the date range
        $where = array();

        if ($this->input->get('date_from')) {

            $where[] = '(e.created >= \'' . $this->input->get('date_from') . '\')';
        }

        if ($this->input->get('date_to')) {

            $where[] = '(e.created <=\'' . $this->input->get('date_to') . '\')';
        }

        if ($this->input->get('user_id')) {

            $where[] = 'e.created_by IN (' . implode(',', $this->input->get('user_id')) . ')';
        }

        if ($this->input->get('event_type')) {

            $where[] = 'e.type IN ("' . implode('","', $this->input->get('event_type')) . '")';
        }

        $where = implode(' AND ', $where);

        // --------------------------------------------------------------------------

        //  Are we downloading? Or viewing?
        if ($this->input->get('dl')) {

            //  Fetch events
            $this->data['events'] = new stdClass();
            $this->data['events'] = $this->event->get_all($order, null, $where);

            // --------------------------------------------------------------------------

            //  Send header
            $this->output->set_content_type('application/octet-stream');
            $this->output->set_header('Pragma: public');
            $this->output->set_header('Expires: 0');
            $this->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->output->set_header('Cache-Control: private', false);
            $this->output->set_header('Content-Disposition: attachment; filename=stats-export-' . date('Y-m-d_h-i-s') . '.csv;');
            $this->output->set_header('Content-Transfer-Encoding: binary');

            // --------------------------------------------------------------------------

            //  Render view
            $this->load->view('admin/logs/event/csv', $this->data);

        } else {

            //  Viewing, make sure we paginate
            //  =======================================
            $this->data['pagination']             = new stdClass();
            $this->data['pagination']->page       = $this->input->get('page')     ? $this->input->get('page')     : 0;
            $this->data['pagination']->per_page   = $this->input->get('per_page') ? $this->input->get('per_page') : 50;
            $this->data['pagination']->total_rows = $this->event->count_all($where);

            //  Fetch all the items for this page
            $this->data['events'] = $this->event->get_all($order, $limit, $where);

            // --------------------------------------------------------------------------

            //  Fetch users
            $this->data['users'] = $this->user_model->get_all_minimal();
            $this->data['types'] = $this->event->get_types_flat();

            // --------------------------------------------------------------------------

            //  Load views
            $this->load->view('structure/header', $this->data);
            $this->load->view('admin/logs/event/index', $this->data);
            $this->load->view('structure/footer', $this->data);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Admin Changelog
     * @return void
     */
    public function changelog()
    {
        if (!user_has_permission('admin.logs:0.can_browse_admin_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Admin Changelog';

        // --------------------------------------------------------------------------

        //  Define the $data variable, this'll be passed to the get_all() and count_all() methods
        $data = array('where' => array());

        // --------------------------------------------------------------------------

        if ($this->input->get('date_from')) {

            $data['where'][] = array(
                'column' => 'acl.created >=',
                'value'  => $this->input->get('date_from')
            );
        }

        if ($this->input->get('date_to')) {

            $data['where'][] = array(
                'column' => 'acl.created <=',
                'value'  => $this->input->get('date_to')
            );
        }

        // --------------------------------------------------------------------------

        //  Are we downloading? Or viewing?
        if ($this->input->get('dl')) {

            //  Downloading, fetch the complete dataset
            //  =======================================

            //  Fetch events
            $this->data['items'] = new stdClass();
            $this->data['items'] = $this->admin_changelog_model->get_all(null, null, $data);

            // --------------------------------------------------------------------------

            //  Send header
            $this->output->set_content_type('application/octet-stream');
            $this->output->set_header('Pragma: public');
            $this->output->set_header('Expires: 0');
            $this->output->set_header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->output->set_header('Cache-Control: private', false);
            $this->output->set_header('Content-Disposition: attachment; filename=admin-changelog-export-' . date('Y-m-d_h-i-s') . '.csv;');
            $this->output->set_header('Content-Transfer-Encoding: binary');

            // --------------------------------------------------------------------------

            //  Render view
            $this->load->view('admin/logs/changelog/csv', $this->data);

        } else {

            //  Viewing, make sure we paginate
            //  =======================================

            //  Define and populate the pagination object
            $page     = $this->input->get('page')     ? $this->input->get('page')     : 0;
            $per_page = $this->input->get('per_page') ? $this->input->get('per_page') : 50;

            $this->data['pagination']             = new stdClass();
            $this->data['pagination']->page       = $page;
            $this->data['pagination']->per_page   = $per_page;
            $this->data['pagination']->total_rows = $this->admin_changelog_model->count_all($data);

            //  Fetch all the items for this page
            $this->data['items'] = $this->admin_changelog_model->get_all($page, $per_page, $data);

            // --------------------------------------------------------------------------

            //  Fetch users
            $this->data['users'] = $this->user_model->get_all_minimal();

            // --------------------------------------------------------------------------

            //  Load views
            $this->load->view('structure/header', $this->data);
            $this->load->view('admin/logs/changelog/index', $this->data);
            $this->load->view('structure/footer', $this->data);
        }
    }
}

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * The following block of code makes it simple to extend one of the core admin
 * controllers. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_LOGS')) {

    /**
     * Proxy class for NAILS_Logs
     */
    class Logs extends NAILS_Logs
    {
    }
}
