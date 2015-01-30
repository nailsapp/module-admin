<div class="pagination clearfix">
<?php

    //  Load the library, muy importante!
    $this->load->library('pagination');

    //  Configure the Pagination Library
    //  ================================

    $config = array();

    //  The base URL is the current URI plus any existing GET params
    $config['base_url'] = site_url(uri_string()) . '?';

    //  Build the parameters array, use any existing GET params as the base
    parse_str($this->input->server('QUERY_STRING'), $params);

    //  Filter out the useless ones and append to the base URL
    $params = array_filter($params);
    unset($params['page']);
    $config['base_url'] .= http_build_query($params);

    //  Other customisations
    $config['total_rows']           = $totalRows;
    $config['per_page']             = $perPage;
    $config['page_query_string']    = true;
    $config['query_string_segment'] = 'page';
    $config['num_links']            = 5;
    $config['use_page_numbers']     = true;

    // --------------------------------------------------------------------------

    //  Styling and markup
    //  ==================

    //  Surrounding HTML
    $config['full_tag_open']   = '<ul>';
    $config['full_tag_close']  = '</ul>';

    //  "First" link
    $config['first_link']      = lang('action_first');
    $config['first_tag_open']  = '<li class="page first">';
    $config['first_tag_close'] = '</li>';

    //  "Previous" link
    $config['prev_link']       = '&lsaquo;';
    $config['prev_tag_open']   = '<li class="page previous">';
    $config['prev_tag_close']  = '</li>';

    //  "Next" link
    $config['next_link']       = '&rsaquo;';
    $config['next_tag_open']   = '<li class="page next">';
    $config['next_tag_close']  = '</li>';

    //  "Last" link
    $config['last_link']       = lang('action_last');
    $config['last_tag_open']   = '<li class="page last">';
    $config['last_tag_close']  = '</li>';


    //  Number link markup
    $config['num_tag_open']    = '<li class="page">';
    $config['num_tag_close']   = '</li>';

    //  Current page markup
    $config['cur_tag_open']    = '<li class="page current"><span class="current">';
    $config['cur_tag_close']   = '</span></li>';

    $this->pagination->initialize($config);

    // --------------------------------------------------------------------------

    echo $this->pagination->create_links();

?>
<div style="clear:both"></div>
</div>