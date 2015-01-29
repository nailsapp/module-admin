<div class="group-dashboard">
    <?php

        if ($adminControllers) {

            echo '<p>' . lang('dashboard_welcome_line_1', APP_NAME) . '</p>';
            echo '<p>' . lang('dashboard_welcome_line_2') . '</p>';

            if ($this->admin_help_model->count_all()) {

                echo '<p>' . lang('dashboard_welcome_line_3', site_url('admin/dashboard/help')) . '</p>';
            }

            // --------------------------------------------------------------------------

            //  Silly little welcome happy face and positive text
            echo '<div class="welcome">';
                echo '<p class="icon">';
                    echo '<span class="fa fa-smile-o">';
                echo '</p>';
                echo '<p class="text">';
                    echo $phrase;
                echo '</p>';
            echo '</div>';

            // --------------------------------------------------------------------------

            /*

            //  Leaving all this commented out until I get time to complete it.

            <p class="system-alert">
                <strong>TODO:</strong> Widgetise the dashboard area so that individual users can pick and
                choose which widgets appear here. Widgets should be customiseable at the app level and also
                be orderable per user and persists across sessions.
            </p>

            <ul id="widgets" class="widgets">
                <li class="add-widget">
                    <a href="#" id="add-widget"></a>
                </li>
                <li class="clear"></li>
            </ul>

            <!-- TODO: Don't use tables for layout -->
            <table style="display:none;">
                <tbody>
                    <tr>
                        <td style="max-width:50%;min-width:50%;width:50%;padding:0;padding-right:10px;vertical-align:top;background:#FFF !important;">
                            <p>
                                <strong>Admin Changelog</strong>
                            </p>
                            <p>
                                The 100 most recent changes to have been made in admin.
                            </p>
                            <div style="max-height:350px;border:1px solid #CCC;background:#EFEFEF;padding:10px;overflow:auto;">
                                <ul style="padding:0;margin:0;">
                                    <?php

                                        foreach ($changelog as $item) {

                                            echo '<li style="list-style-typ:none;padding:5px;display:block;">';

                                                $sentence = array();
                                                if (!empty($item->user->first_name)) {

                                                    $sentence[] = $item->user->first_name;

                                                } else {

                                                    $sentence[] = 'Someone';
                                                }
                                                $sentence[] = $item->verb;
                                                $sentence[] = $item->article;
                                                $sentence[] = $item->title ? $item->item . ',' : $item->item;

                                                if ($item->title) {

                                                    if ($item->url) {

                                                        $sentence[] = '<strong>' . anchor($item->url, $item->title) . '</strong>';

                                                    } else {

                                                        $sentence[] = $item->title;
                                                    }
                                                }

                                                echo implode(' ', $sentence);

                                                echo '<small>' . user_datetime($item->created) . '</small>';

                                            echo '</li>';
                                        }
                                    ?>
                                </ul>
                            </div>

                        </td>
                        <td style="max-width:50%;min-width:50%;width:50%;padding:0;padding-left:10px;vertical-align:top;background:#FFF !important;">
                            <p>
                                <strong>User Event Log</strong>
                            </p>
                            <p>
                                The 100 most recent events created by users.
                            </p>
                            <div style="max-height:350px;border:1px solid #CCC;background:#EFEFEF;padding:10px;">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                                proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            </div>

                        </td>
                    </tr>
                </tbody>
            </table>
            <script type="text/javascript">
                var _NAILS_Admin_Dashboard = new NAILS_Admin_Dashboard();
                _NAILS_Admin_Dashboard.init();
            </script>
            <?php

            */

        } else {

            //  No modules
            echo '<p class="system-alert message">';
                echo '<strong>' . lang('dashboard_nomodules_title') . '</strong>';
                echo '<br />';
                echo lang('dashboard_nomodules_message', APP_DEVELOPER_EMAIL);
            echo '</p>';
        }

    ?>
</div>