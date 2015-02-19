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

        if (userHasPermission('admin:admin:settings:site:analytics')) {

            $active = $this->input->post('activeTab') == 'tab-analytics' || !$this->input->post('activeTab') ? 'active' : '';

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
    <section class="tabs pages">
        <?php

        if (userHasPermission('admin:admin:settings:site:analytics')) {

            $display = $this->input->post('activeTab') == 'tab-analytics' || !$this->input->post('activeTab') ? 'active' : '';

            ?>
            <div id="tab-analytics" class="tab page <?=$display?> analytics">
                <p>
                    Configure your analytics accounts. If field is left empty then that provider will not be used.
                </p>
                <hr />
                <fieldset id="site-settings-google">
                    <legend>Google Analytics</legend>
                    <?php

                        $field                = array();
                        $field['key']         = 'google_analytics_account';
                        $field['label']       = 'Profile ID';
                        $field['default']     = app_setting($field['key']);
                        $field['placeholder'] = 'UA-XXXXX-YY';

                        echo form_field($field);

                    ?>
                </fieldset>
            </div>
            <?php
        }

        if (userHasPermission('admin:admin:settings:site:maintenance')) {

            $display = $this->input->post('activeTab') == 'tab-maintenance' ? 'active' : '';

            ?>
            <div id="tab-maintenance" class="tab page <?=$display?> maintenance">
                <p>
                    Maintenance mode disables disables access to the site with the exception
                    for those IP addresses listed in the whitelist.
                </p>
                <p class="system-alert message">
                    <strong>Note:</strong> Maintenance mode can be enabled via this setting,
                    or by placing a file entitled <code>.MAINTENANCE</code> at the site's root.
                    If the <code>.MAINTENANCE</code> file is found then the site will forcibly
                    be placed into maintenance mode, regardless of this setting.
                </p>
                <hr />
                <fieldset>
                    <legend>Maintenance Mode</legend>
                    <?php

                        $field            = array();
                        $field['key']     = 'maintenance_mode_enabled';
                        $field['label']   = 'Enabled';
                        $field['default'] = app_setting($field['key'], 'site');

                        echo form_field_boolean($field);

                        // --------------------------------------------------------------------------

                        $field                = array();
                        $field['key']         = 'maintenance_mode_whitelist';
                        $field['label']       = 'Whitelist';
                        $field['type']        = 'textarea';
                        $field['default']     = trim(implode("\n", (array) app_setting($field['key'], 'site')));
                        $field['placeholder'] = 'Specify IP addresses to whitelist either comma seperated or on new lines.';

                        echo form_field($field);

                    ?>
                </fieldset>
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
