<?php

    // @todo: check the validity of the date, perhaps using strtotime()

    if (!empty($date) && $date != '0000-00-00') {

        echo '<td class="date">';
            echo niceTime($date);
            echo '<small>';
                echo toUserDate($date);
            echo '</small>';
        echo '</td>';

    } else {

        echo '<td class="date no-data">';
            echo $noData;
        echo '</td>';
    }
