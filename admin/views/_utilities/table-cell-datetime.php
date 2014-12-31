<?php

    if (!empty($datetime) && $datetime != '0000-00-00 00:00:00') {

        echo '<td class="datetime">';
            echo nice_time($datetime);
            echo '<small>';
                echo user_datetime($datetime);
            echo '</small>';
        echo '</td>';

    } else {

        if (isset($nodata)) {

            echo '<td class="datetime no-data">';
                echo $nodata;
            echo '</td>';

        } else {

            echo '<td class="datetime no-data">&mdash;</td>';
        }
    }