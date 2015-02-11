<div class="group-logs browse changelog">
    <p>
        Where supported, changes made on site will be shown here.
    </p>
    <?php

        // $this->load->view('admin/logs/changelog/utilities/search');
        echo \Nails\Admin\Helper::loadPagination($pagination);

    ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="user">user</th>
                    <th class="changes">Changes</th>
                    <th class="datetime">Date</th>
                </tr>
            </thead>
            <tbody>
            <?php

                if ($items) {

                    foreach ($items as $item) {

                        echo '<tr>';

                            echo \Nails\Admin\Helper::loadUserCell($item->user);

                            echo '<td class="changes">';

                            $_sentance = array();
                            if (!empty($item->user->first_name)) {

                                $_sentance[] = $item->user->first_name;

                            } else {

                                $_sentance[] = 'Someone';
                            }
                            $_sentance[] = $item->verb;
                            $_sentance[] = $item->article;
                            $_sentance[] = $item->title ? $item->item . ',' : $item->item;

                            if ($item->title) {

                                if ($item->url) {

                                    $_sentance[] = '<strong>' . anchor($item->url, $item->title) . '</strong>';

                                } else {

                                    $_sentance[] = $item->title;
                                }
                            }

                            echo implode(' ', $_sentance);

                            if ( $item->changes) {

                                echo '<hr style="margin:0.5em 0;" />';
                                echo '<small>';
                                    echo '<ul>';
                                    foreach ($item->changes as $change) {

                                        $_old = $change->old_value == '' ? '<span>blank</span>' : $change->old_value;
                                        $_new = $change->new_value == '' ? '<span>blank</span>' : $change->new_value;

                                        echo '<li>';
                                            echo '<strong>' . $change->field . '</strong>: ';
                                            echo '<em>' . $_old . '</em>';
                                            echo '&nbsp;&rarr;&nbsp;';
                                            echo '<em>' . $_new . '</em>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                echo '<small>';
                            }

                            echo '</td>';

                            echo \Nails\Admin\Helper::loadDatetimeCell($item->created);

                        echo '</tr>';
                    }

                } else {

                    echo '<tr>';
                        echo '<td colspan="5" class="no-data">';
                            echo 'No changelog items found';
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