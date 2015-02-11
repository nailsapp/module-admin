<div class="group-logs browse events">
    <p>
        Whenever users interact with the site 'events' are created.
    </p>
    <?php

        echo \Nails\Admin\Helper::loadSearch($search);
        echo \Nails\Admin\Helper::loadPagination($pagination);

    ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="datetime">Date</th>
                    <th class="user">User</th>
                    <th class="event">Type of Event</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php

                if ($events) {

                    foreach ($events as $event) {

                        echo '<tr class="event">';

                        echo \Nails\Admin\Helper::loadDatetimeCell($event->created);
                        echo \Nails\Admin\Helper::loadUserCell($event->user);

                        // --------------------------------------------------------------------------

                        echo '<td class="event">';
                            if (!empty($event->type->label)) {

                                echo $event->type->label;

                            } else {

                                echo title_case(str_replace('_', ' ', $event->type->slug));
                            }
                            echo '<small>' . $event->type->description . '</small>';
                        echo '</td>';

                        // --------------------------------------------------------------------------

                        if ($event->data) {

                            echo '<td class="actions">';
                                echo '<a href="#data-' . $event->id . '" class="awesome small fancybox">View Data</a>';
                                echo '<div id="data-' . $event->id . '" style="display:none;">';
                                    echo '<p class="system-alert message"><strong>Note:</strong> This is raw, unformatted data associated with the event.<br />The system uses this information to specify specific items relating to this particular event.</p>';
                                    echo '<div style="white-space:pre; margin-top:1em;padding:1em;border:1px dashed #CCC;background:#EFEFEF;">';
                                        echo print_r($event->data, TRUE);
                                    echo '</div>';
                                echo '</div>';
                            echo '</td>';

                        } else {

                            echo '<td class="actions no-data">&mdash;</td>';
                        }

                        echo '</tr>';
                    }

                } else {

                    echo '<tr>';
                        echo '<td colspan="5" class="no-data">';
                            echo 'No Events found';
                        echo '</td>';
                    echo '</tr>';
                }

            ?>
            </tbody>
        </table>
    </div>
    <?php

        echo \Nails\Admin\Helper::loadPagination($pagination);

    ?>
</div>