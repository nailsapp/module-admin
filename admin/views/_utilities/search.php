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
                'search',
                $this->input->get('search'),
                'autocomplete="off" placeholder="Type your search term and hit enter"'
            );
        echo '</div>';

        // --------------------------------------------------------------------------

        //  Sort Column
        $curSortOn = $this->input->get('sortOn');

        echo 'Sort results by';
        echo form_dropdown('sortOn', $sortOn, $curSortOn);

        // --------------------------------------------------------------------------

        //  Sort order
        $sortOrder = array(
            'asc'  => 'Ascending',
            'desc' => 'Descending'
        );
        $curSortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'desc';

        echo 'and order results in';
        echo form_dropdown('sortOrder', $sortOrder, $curSortOrder);
        echo 'order, show';

        // --------------------------------------------------------------------------

        //  Results per page
        $perPage = array(
            10  => 10,
            25  => 25,
            50  => 50,
            75  => 75,
            100 => 100
        );
        $curPerPage = $this->input->get('perPage') ? $this->input->get('perPage') : 50;

        echo form_dropdown('perPage', $perPage, $curPerPage, 'style="width:75px;');
        echo 'per page.';

        // --------------------------------------------------------------------------

        //  Filters
        //  @TODO

        // --------------------------------------------------------------------------

        echo anchor(uri_string(), 'Reset', 'class="awesome small right"');
        echo '<button type="submit" class="awesome small right">Search</button>';

        // --------------------------------------------------------------------------

        echo form_close();

    ?>
</div>

<hr />