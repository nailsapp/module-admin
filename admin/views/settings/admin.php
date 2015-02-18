<div class="group-settings site">
    <p>
        Configure various aspects of the site.
    </p>

    <hr />

        <ul class="tabs">
            <?php $_active = $this->input->post('update') == 'branding' || ! $this->input->post() ? 'active' : ''?>
            <li class="tab <?=$_active?>">
                <a href="#" data-tab="tab-branding">Branding</a>
            </li>

            <?php $_active = $this->input->post('update') == 'whitelist' ? 'active' : ''?>
            <li class="tab <?=$_active?>">
                <a href="#" data-tab="tab-whitelist">Whitelist</a>
            </li>

        </ul>

        <section class="tabs pages">

            <?php $_display = $this->input->post('update') == 'branding' || ! $this->input->post() ? 'active' : ''?>
            <div id="tab-branding" class="tab page <?=$_display?> branding">
                <?=form_open(null, 'style="margin-bottom:0;"')?>
                <?=form_hidden('update', 'branding')?>
                <p>
                    Give admin a lick of paint using your brand colours.
                </p>
                <hr />
                <div class="fieldset" id="site-settings-google">
                <?php

                    $field                 = array();
                    $field['key']          = 'primary_colour';
                    $field['label']        = 'Primary Colour';
                    $field['default']      = app_setting($field['key'], 'admin');
                    $field['placeholder']  = 'Specify a valid CSS colour value, i.e a hex code or rgb()';

                    echo form_field($field);

                    // --------------------------------------------------------------------------

                    $field                 = array();
                    $field['key']          = 'secondary_colour';
                    $field['label']        = 'Secondary Colour';
                    $field['default']      = app_setting($field['key'], 'admin');
                    $field['placeholder']  = 'Specify a valid CSS colour value, i.e a hex code or rgb()';

                    echo form_field($field);

                    // --------------------------------------------------------------------------

                    $field                 = array();
                    $field['key']          = 'highlight_colour';
                    $field['label']        = 'Highlight Colour';
                    $field['default']      = app_setting($field['key'], 'admin');
                    $field['placeholder']  = 'Specify a valid CSS colour value, i.e a hex code or rgb()';

                    echo form_field($field);

                ?>
                </div>
                <p style="margin-top:1em;margin-bottom:0;">
                    <?=form_submit('submit', lang('action_save_changes'), 'class="awesome" style="margin-bottom:0;"')?>
                </p>
                <?=form_close()?>
            </div>

            <?php $_display = $this->input->post('update') == 'whitelist' ? 'active' : ''?>
            <div id="tab-whitelist" class="tab page <?=$_display?> whitelist">
                <?=form_open(null, 'style="margin-bottom:0;"')?>
                <?=form_hidden('update', 'whitelist')?>
                <p>
                    Specify which IP's can access admin. If no IP addresses are specified then
                    admin will be accessible from any IP address.
                </p>
                <hr />
                <div class="fieldset">
                <?php

                    $field                 = array();
                    $field['key']          = 'whitelist';
                    $field['label']        = 'Whitelist';
                    $field['type']         = 'textarea';
                    $field['default']      = trim(implode("\n", (array) app_setting($field['key'], 'admin')));
                    $field['placeholder']  = 'Specify IP addresses to whitelist either comma seperated or on new lines.';

                    echo form_field($field);

                ?>
                </div>
                <p style="margin-top:1em;margin-bottom:0;">
                    <?=form_submit('submit', lang('action_save_changes'), 'class="awesome" style="margin-bottom:0;"')?>
                </p>
                <?=form_close()?>
            </div>

        </section>
</div>