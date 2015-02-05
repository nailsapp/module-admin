<div class="search">
    <div class="mask">
        <b class="fa fa-refresh fa-spin fa-2x"></b>
    </div>
    <?php

        $form = array(
            'method' => 'GET'
        );
        echo form_open(null, $form);

        echo '<div class="search-text">';
            echo form_input(
                'keywords',
                $keywords,
                'autocomplete="off" placeholder="Type your search term and hit enter"'
            );
        echo '</div>';

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

        echo anchor(uri_string(), 'Reset', 'class="awesome small right"');
        echo '<button type="submit" class="awesome small right">Search</button>';

        // --------------------------------------------------------------------------

        echo form_close();

    ?>
</div>

<hr />