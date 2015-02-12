<hr />
<div class="search">
    <div class="mask">
        <b class="fa fa-refresh fa-2x"></b>
    </div>
    <?php

        parse_str($this->input->server('QUERY_STRING'), $query);
        unset($query['keywords']);
        unset($query['sortOn']);
        unset($query['sortOrder']);
        unset($query['perPage']);
        unset($query['page']);

        $formAttr = array(
            'method' => 'GET'
        );

        echo form_open(null, $formAttr);

        foreach ($query as $key => $value) {

            echo form_hidden($key, $value);
        }

        if ($searchable) {

            echo '<div class="search-text">';
                echo form_input(
                    'keywords',
                    $keywords,
                    'autocomplete="off" placeholder="Type your search term and hit enter"'
                );
            echo '</div>';
        }

        // --------------------------------------------------------------------------

        if (!empty($sortColumns)) {

            echo '<span style="padding-right: 1em;">';

                //  Sort Column
                echo 'Sort results by';
                echo form_dropdown('sortOn', $sortColumns, $sortOn);

                // --------------------------------------------------------------------------

                //  Sort order
                $options = array(
                    'asc'  => 'Ascending',
                    'desc' => 'Descending'
                );

                echo form_dropdown('sortOrder', $options, $sortOrder);

            echo '</span>';
        }

        // --------------------------------------------------------------------------

        echo '<span style="padding-right: 1em;">';

            //  Results per page
            $options = array(
                10  => 10,
                25  => 25,
                50  => 50,
                75  => 75,
                100 => 100
            );

            echo 'Show';
            echo form_dropdown('perPage', $options, $perPage);
            echo 'results per page.';

        echo '</span>';

        // --------------------------------------------------------------------------

        //  Filters
        if (!empty($filters)) {

            //  @TODO
        }

        // --------------------------------------------------------------------------

        echo '<div class="actions">';

            $resetUrl  = uri_string();
            $resetUrl .= $query ? '?' . http_build_query($query) : '';

            echo anchor($resetUrl, 'Reset', 'class="awesome small grey"');
            echo '<button type="submit" class="awesome small">Search</button>';

        echo '</div>';

        // --------------------------------------------------------------------------

        echo form_close();

    ?>
</div>