<div class="pagination clearfix">
    <small>
        <?php

        use Nails\Factory;

        $iCountPage = $page ?: 1;
        $iStart     = $iCountPage * $perPage - $perPage;
        $iEnd       = $iStart + $perPage;
        $iStart++;
        if ($iEnd > $totalRows) {
            $iEnd = $totalRows;
        }
        echo 'Records ' . $iStart . ' to ' . $iEnd . ' of ' . number_format($totalRows);

        ?>
    </small>
    <?php

    //  Configure the Pagination Library
    //  ================================

    //  Build the parameters array, use any existing GET params as the base
    $oInput = \Nails\Factory::service('Input');
    parse_str($oInput->server('QUERY_STRING'), $aParams);
    $aParams = array_filter($aParams);
    unset($aParams['page']);

    echo Factory::factory('Pagination')
        ->initialize([
            //  The base URL is the current URI plus any existing GET params
            'base_url'             => site_url(uri_string()) . '?' . http_build_query($aParams),

            //  Other customisations
            'total_rows'           => $totalRows,
            'per_page'             => $perPage,
            'page_query_string'    => true,
            'query_string_segment' => 'page',
            'num_links'            => 5,
            'use_page_numbers'     => true,

            //  Surrounding HTML
            'full_tag_open'        => '<ul>',
            'full_tag_close'       => '</ul>',

            //  "First" link
            'first_link'           => lang('action_first'),
            'first_tag_open'       => '<li class="page first">',
            'first_tag_close'      => '</li>',

            //  "Previous" link
            'prev_link'            => '&lsaquo;',
            'prev_tag_open'        => '<li class="page previous">',
            'prev_tag_close'       => '</li>',

            //  "Next" link
            'next_link'            => '&rsaquo;',
            'next_tag_open'        => '<li class="page next">',
            'next_tag_close'       => '</li>',

            //  "Last" link
            'last_link'            => lang('action_last'),
            'last_tag_open'        => '<li class="page last">',
            'last_tag_close'       => '</li>',

            //  Number link markup
            'num_tag_open'         => '<li class="page">',
            'num_tag_close'        => '</li>',

            //  Current page markup
            'cur_tag_open'         => '<li class="page current"><span class="current">',
            'cur_tag_close'        => '</span></li>',
        ])
        ->generate();

    ?>
    <div style="clear:both"></div>
</div>
