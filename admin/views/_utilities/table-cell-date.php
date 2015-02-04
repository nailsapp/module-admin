<?php

    // @todo: check the validity of the date, perhaps using strtotime()

    if (!empty($datetime) && $datetime != '0000-00-00') {

        echo '<td class="datetime">';
            echo niceTime($datetime);
            echo '<small>';
                echo userDate($datetime);
            echo '</small>';
        echo '</td>';

    } else {

        echo '<td class="datetime no-data">';
            echo $noData;
        echo '</td>';
    }
