<div class="group-settings site">
    <p>
        Configure various aspects of the site.
    </p>
    <hr />
    <?php

        echo form_open();
        echo '<input type="hidden" name="activeTab" value="' . set_value('activeTab') . '" id="activeTab" />'

    ?>
    <ul class="tabs">
        <?php

        if (userHasPermission('admin:admin:settings:admin:branding')) {

            $active = $this->input->post('activeTab') == 'tab-branding' || !$this->input->post('activeTab') ? 'active' : '';

            ?>
            <li class="tab <?=$active?>">
                <a href="#" data-tab="tab-branding">Branding</a>
            </li>
            <?php
        }

        if (userHasPermission('admin:admin:settings:admin:white')) {

            $active = $this->input->post('activeTab') == 'tab-whitelist' ? 'active' : '';

            ?>
            <li class="tab <?=$active?>">
                <a href="#" data-tab="tab-whitelist">Whitelist</a>
            </li>
            <?php
        }

        ?>
    </ul>
    <section class="tabs pages">
        <?php

        if (userHasPermission('admin:admin:settings:admin:branding')) {

            $display = $this->input->post('activeTab') == 'tab-branding' || !$this->input->post('activeTab') ? 'active' : '';

            ?>
            <div id="tab-branding" class="tab page <?=$display?> branding">
                <p>
                    Give admin a lick of paint using your brand colours.
                </p>
                <hr />
                <div class="fieldset" id="site-settings-google">
                <?php

                    $field                = array();
                    $field['key']         = 'primary_colour';
                    $field['label']       = 'Primary Colour';
                    $field['default']     = app_setting($field['key'], 'admin');
                    $field['placeholder'] = 'Specify a valid CSS colour value, i.e a hex code or rgb()';

                    echo form_field($field);

                    // --------------------------------------------------------------------------

                    $field                = array();
                    $field['key']         = 'secondary_colour';
                    $field['label']       = 'Secondary Colour';
                    $field['default']     = app_setting($field['key'], 'admin');
                    $field['placeholder'] = 'Specify a valid CSS colour value, i.e a hex code or rgb()';

                    echo form_field($field);

                    // --------------------------------------------------------------------------

                    $field                = array();
                    $field['key']         = 'highlight_colour';
                    $field['label']       = 'Highlight Colour';
                    $field['default']     = app_setting($field['key'], 'admin');
                    $field['placeholder'] = 'Specify a valid CSS colour value, i.e a hex code or rgb()';

                    echo form_field($field);

                ?>
                </div>
            </div>
            <?php
        }

        if (userHasPermission('admin:admin:settings:admin:whitelist')) {

            $display = $this->input->post('activeTab') == 'tab-whitelist' ? 'active' : '';

            ?>
            <div id="tab-whitelist" class="tab page <?=$display?> whitelist">
                <p>
                    Specify which IP's can access admin. If no IP addresses are specified then
                    admin will be accessible from any IP address.
                </p>
                <hr />
                <div class="fieldset">
                <?php

                    $field                = array();
                    $field['key']         = 'whitelist';
                    $field['label']       = 'Whitelist';
                    $field['type']        = 'textarea';
                    $field['default']     = trim(implode("\n", (array) app_setting($field['key'], 'admin')));
                    $field['placeholder'] = 'Specify IP addresses to whitelist either comma seperated or on new lines.';

                    echo form_field($field);

                ?>
                </div>
            </div>
            <?php
        }

        ?>
    </section>
    <p>
        <?=form_submit('submit', lang('action_save_changes'), 'class="awesome"')?>
    </p>
    <?=form_close()?>
</div>