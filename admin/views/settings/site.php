<div class="group-settings site">
    <p>
        Configure various aspects of the site.
    </p>
    <hr/>
    <?php

    echo form_open(null, 'id="settings-form"');
    echo '<input type="hidden" name="activeTab" value="' . set_value('activeTab') . '" id="activeTab" />';

    ?>
    <ul class="tabs">
        <?php

        if (userHasPermission('admin:admin:settings:site:customjscss')) {

            $active = $this->input->post('activeTab') == 'tab-customjscss' || !$this->input->post('activeTab') ? 'active' : '';

            ?>
            <li class="tab <?=$active?>">
                <a href="#" data-tab="tab-customjscss">Custom JS &amp; CSS</a>
            </li>
            <?php
        }

        if (userHasPermission('admin:admin:settings:site:analytics')) {

            $active = $this->input->post('activeTab') == 'tab-analytics' ? 'active' : '';

            ?>
            <li class="tab <?=$active?>">
                <a href="#" data-tab="tab-analytics">Analytics</a>
            </li>
            <?php
        }

        if (userHasPermission('admin:admin:settings:site:maintenance')) {

            $active = $this->input->post('activeTab') == 'tab-maintenance' ? 'active' : '';

            ?>
            <li class="tab <?=$active?>">
                <a href="#" data-tab="tab-maintenance">Maintenance Mode</a>
            </li>
            <?php
        }

        ?>
    </ul>
    <section class="tabs">
        <?php

        if (userHasPermission('admin:admin:settings:site:customjscss')) {

            $display = $this->input->post('activeTab') == 'tab-customjscss' || !$this->input->post('activeTab') ? 'active' : '';

            ?>
            <div class="tab-page <?=$display?> tab-customjscss">
                <fieldset>
                    <legend>
                        Custom JS &amp; CSS
                    </legend>
                    <?php

                    $aField                = [];
                    $aField['key']         = 'site_custom_js';
                    $aField['label']       = 'JavaScript';
                    $aField['default']     = appSetting($aField['key'], 'site');
                    $aField['placeholder'] = 'Specify any custom JS to include at the foot of the page.';
                    $aField['info']        = 'You should <strong>not</strong> wrap this in &lt;script&gt; tags.';

                    echo form_field_textarea($aField);

                    // --------------------------------------------------------------------------

                    $aField                = [];
                    $aField['key']         = 'site_custom_css';
                    $aField['label']       = 'CSS';
                    $aField['default']     = appSetting($aField['key'], 'site');
                    $aField['placeholder'] = 'Specify any custom CSS to include at the head of the page.';
                    $aField['info']        = 'You should <strong>not</strong> wrap this in &lt;style&gt; tags.';

                    echo form_field_textarea($aField);

                    ?>
                </fieldset>
            </div>
            <?php

        }

        if (userHasPermission('admin:admin:settings:site:analytics')) {

            $display = $this->input->post('activeTab') == 'tab-analytics' ? 'active' : '';

            ?>
            <div class="tab-page <?=$display?> tab-analytics">
                <p>
                    Configure your analytics accounts. If field is left empty then that provider will not be used.
                </p>
                <hr/>
                <fieldset id="site-settings-google">
                    <legend>Google Analytics</legend>
                    <?php

                    $aField                = [];
                    $aField['key']         = 'google_analytics_account';
                    $aField['label']       = 'Profile ID';
                    $aField['default']     = appSetting($aField['key']);
                    $aField['placeholder'] = 'UA-XXXXX-YY';

                    echo form_field($aField);

                    ?>
                </fieldset>
            </div>
            <?php
        }

        if (userHasPermission('admin:admin:settings:site:maintenance')) {

            $display = $this->input->post('activeTab') == 'tab-maintenance' ? 'active' : '';

            ?>
            <div class="tab-page <?=$display?> tab-maintenance">
                <p>
                    Maintenance mode disables disables access to the site with the exception
                    for those IP addresses listed in the whitelist.
                </p>
                <p class="alert alert-warning">
                    <strong>Note:</strong> Maintenance mode can be enabled via this setting,
                    or by placing a file entitled <code>.MAINTENANCE</code> at the site's root.
                    If the <code>.MAINTENANCE</code> file is found then the site will forcibly
                    be placed into maintenance mode, regardless of this setting.
                </p>
                <hr/>
                <fieldset>
                    <legend>Maintenance Mode</legend>
                    <?php

                    $aField            = [];
                    $aField['id']      = 'maintenance-mode-enabled';
                    $aField['key']     = 'maintenance_mode_enabled';
                    $aField['label']   = 'Enabled';
                    $aField['default'] = appSetting($aField['key'], 'site');

                    echo form_field_boolean($aField);

                    $display = $aField['default'] ? 'block' : 'none';

                    ?>
                    <div id="maintenance-mode-extras" style="display:<?=$display?>">
                        <?php

                        $aField                = [];
                        $aField['key']         = 'maintenance_mode_whitelist';
                        $aField['label']       = 'Whitelist';
                        $aField['default']     = trim(implode("\n", (array) appSetting($aField['key'], 'site')));
                        $aField['placeholder'] = 'Specify IP addresses to whitelist either comma seperated or on new lines.';
                        $aField['info']        = 'Your current IP address is: ' . \Nails\Factory::service('Input')->ipAddress();

                        echo form_field_textarea($aField);

                        // --------------------------------------------------------------------------

                        $aField                = [];
                        $aField['key']         = 'maintenance_mode_title';
                        $aField['label']       = 'Title';
                        $aField['default']     = appSetting($aField['key'], 'site');
                        $aField['placeholder'] = 'Optionally specify a custom title for the maintenance page.';

                        echo form_field($aField);

                        // --------------------------------------------------------------------------

                        $aField                = [];
                        $aField['key']         = 'maintenance_mode_body';
                        $aField['label']       = 'Body';
                        $aField['default']     = appSetting($aField['key'], 'site');
                        $aField['placeholder'] = 'Optionally specify a custom body for the maintenance page.';

                        echo form_field_wysiwyg($aField);

                        ?>
                    </div>
                </fieldset>
            </div>
            <?php
        }

        ?>
    </section>
    <p>
        <?=form_submit('submit', lang('action_save_changes'), 'class="btn btn-primary"')?>
    </p>
    <?=form_close()?>
</div>
