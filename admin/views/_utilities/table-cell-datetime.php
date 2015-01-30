<?php

    // @todo: check the validity of the date, perhaps using strtotime()

    if (!empty($dateTime) && $dateTime != '0000-00-00 00:00:00') {

        echo '<td class="datetime">';
            echo nice_time($dateTime);
            echo '<small>';
                echo user_datetime($dateTime);
            echo '</small>';
        echo '</td>';

    } else {

        echo '<td class="datetime no-data">';
            echo $noData;
        echo '</td>';
    }
