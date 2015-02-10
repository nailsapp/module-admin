<?php

/**
 * This class renders the log browsers
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

class Logs extends \AdminController
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        $navGroup = new \Nails\Admin\Nav('Logs');

        if (userHasPermission('admin.logs:0.can_browse_site_logs')) {

            $navGroup->addMethod('Browse Site Logs', 'site');
        }

        if (userHasPermission('admin.logs:0.can_browse_event_logs')) {

            $navGroup->addMethod('Browse Event Logs', 'event');
        }

        if (userHasPermission('admin.logs:0.can_browse_admin_logs')) {

            $navGroup->addMethod('Browse Admin Logs', 'changelog');
        }

        return $navGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Browse site log files
     * @return void
     */
    public function site()
    {
        if (!userHasPermission('admin.logs:0.can_browse_site_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Browse Logs';

        $this->asset->load('mustache.js/mustache.js', 'NAILS-BOWER');
        $this->asset->load('nails.admin.logs.site.min.js', 'NAILS');
        $this->asset->inline('logsSite = new NAILS_Admin_Logs_Site();','JS');

        \Nails\Admin\Helper::loadView('site/index');
    }

    // --------------------------------------------------------------------------

    public function site_view()
    {
        if (!userHasPermission('admin.logs:0.can_browse_site_logs')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------
        $file = $this->uri->segment(5);
        $this->data['page']->title = 'Browse Logs &rsaquo; ' . $file;

        $this->load->model('admin/admin_sitelog_model');
        $this->data['logs'] = $this->admin_sitelog_model->readLog($file);

        \Nails\Admin\Helper::loadView('site/view');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Site Events
     * @return void
     */
    public function event()
    {
        if (!userHasPermission('admin.logs:0.can_browse_event_logs')) {

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

            //  Downloading, fetch the complete dataset
            //  =======================================

            //  Fetch events
            $this->data['events'] = new \stdClass();
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
            \Nails\Admin\Helper::loadView('event/csv');

        } else {

            //  Viewing, make sure we paginate
            //  =======================================

            $this->data['pagination']             = new \stdClass();
            $this->data['pagination']->page       = $this->input->get('page')     ? $this->input->get('page')     : 0;
            $this->data['pagination']->per_page   = $this->input->get('per_page') ? $this->input->get('per_page') : 50;
            $this->data['pagination']->total_rows = $this->event->count_all($where);

            //  Fetch all the items for this page
            $this->data['events'] = $this->event->get_all($order, $limit, $where);

            // --------------------------------------------------------------------------

            $this->data['types'] = $this->event->get_types_flat();

            // --------------------------------------------------------------------------

            //  Load views
            \Nails\Admin\Helper::loadView('event/index');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse Admin Changelog
     * @return void
     */
    public function changelog()
    {
        if (!userHasPermission('admin.logs:0.can_browse_admin_logs')) {

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
            $this->data['items'] = new \stdClass();
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
            \Nails\Admin\Helper::loadView('changelog/csv');

        } else {

            //  Viewing, make sure we paginate
            //  =======================================

            //  Define and populate the pagination object
            $page     = $this->input->get('page')     ? $this->input->get('page')     : 0;
            $per_page = $this->input->get('per_page') ? $this->input->get('per_page') : 50;

            $this->data['pagination']             = new \stdClass();
            $this->data['pagination']->page       = $page;
            $this->data['pagination']->per_page   = $per_page;
            $this->data['pagination']->total_rows = $this->admin_changelog_model->count_all($data);

            //  Fetch all the items for this page
            $this->data['items'] = $this->admin_changelog_model->get_all($page, $per_page, $data);

            // --------------------------------------------------------------------------

            //  Load views
            \Nails\Admin\Helper::loadView('changelog/index');
        }
    }
}
