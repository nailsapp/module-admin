<div class="group-settings notification overview">
    <p>
        Configure who gets email notifications when certain events happen on site.
        Separate multiple email addresses using a comma; leaving blank will disable
        the notification
    </p>
    <hr/>
    <?php

    if ($notifications) {

        echo form_open();

        foreach ($notifications as $grouping => $noti) {

            ?>
            <fieldset>
                <?=$noti->label ? '<legend>' . $noti->label . '</legend>' : ''?>
                <?=$noti->description ? '<p>' . $noti->description . '</p>' : ''?>
                <table>
                    <thead>
                        <tr>
                            <th class="event-label">Event Name</th>
                            <th class="value">Value</th>
                        </tr>
                    <thead>
                    <tbody>
                        <?php

                        $oAppNotificationModel = \Nails\Factory::model('AppNotification');

                        foreach ($noti->options as $key => $data) {

                            $_default = implode(', ', $oAppNotificationModel->get($key, $grouping));

                            ?>
                            <tr>
                                <td class="event-label">
                                    <?php

                                    echo !empty($data->label) ? $data->label : 'Unknown';
                                    if (!empty($data->sub_label)) {
                                        echo '<small>' . $data->sub_label . '</small>';
                                    }

                                    ?>
                                </td>
                                <?php $_has_tip = !empty($data->tip) ? 'has-tip' : '' ?>
                                <td class="value <?=$_has_tip?>">
                                    <div class="input-wrapper">
                                        <?php

                                        $_value = isset($_POST['notification'][$grouping][$key]) ? $_POST['notification'][$grouping][$key] : $_default;
                                        echo form_input('notification[' . $grouping . '][' . $key . ']', $_value, 'placeholder="Separate multiple email addresses using a comma"');

                                        ?>
                                    </div>
                                    <?=$_has_tip ? '<b class="fa fa-question-circle fa-lg pull-right" rel="tipsy" title="' . str_replace('"', '&quot;', $data->tip) . '"></b>' : ''?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </fieldset>
            <?php
        }

        ?>
        <p>
            <?=form_submit('submit', lang('action_save_changes'), 'class="btn btn-primary"')?>
        </p>
        <?php
        echo form_close();

    } else {
        ?>
        <p class="alert alert-danger">
            Sorry, there are no configurable notifications.
        </p>
        <?php

    }

    ?>
</div>
