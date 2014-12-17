<?php

    /**
     * Renders a tick/cross table cell
     * @param $value    The value to 'truthy' test
     * @param $datetime A datetime to show (for truthy values only)
     */

    $value    = !empty($value) ? true: false;
    $datetime = !empty($datetime) ? $datetime : null;

    if ($value) {

        echo '<td class="boolean success">';
            echo '<b class="fa fa-check-circle fa-lg"></b>';
            if (!is_null($datetime)) {

                echo '<small class="nice-time">';
                    echo user_datetime($datetime, 'Y-m-d', 'H:i:s');
                echo '</small>';
            }
        echo '</td>';

    } else {

        echo '<td class="boolean error">';
            echo '<b class="fa fa-times-circle fa-lg"></b>';
        echo '</td>';
    }
