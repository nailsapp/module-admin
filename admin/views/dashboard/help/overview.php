<div class="group-dashboard help">
    <p>
        The following videos are available to you.
    </p>
    <hr />
    <table>
        <thead>
            <tr>
                <th class="id">ID</th>
                <th class="name-desc">Name &amp; Description</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php

            if ($videos) {

                foreach ($videos as $v) {
                    echo '<tr>';
                        echo '<td class="id">' . $v->id . '</td>';
                        echo '<td class="name-desc">';
                            echo $v->title;
                            echo '<small>' . $v->description . '</small>';
                        echo '</td>';
                        echo '<td class="actions">';
                            $vimeoUrl = 'http://player.vimeo.com/video/' . $v->vimeo_id . '?autoplay=true';
                            echo anchor($vimeoUrl, lang('action_view'), 'class="awesome small video-button"');
                        echo '</td>';
                    echo '</tr>';
                }

            } else {

                echo '<tr>';
                echo '<td id="no_records" colspan="3"><p>' . lang('no_records_found') . '</p></td>';
                echo '</tr>';
            }

        ?>
        </tbody>
    </table>
    <script style="text/javascript">

        $(function(){
            $('a.video-button').fancybox({ type : 'iframe' });
        });

    </script>
</div>