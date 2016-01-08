<?php

$aDrivers           = !empty($drivers) ? $drivers : array();
$aEnabled           = !empty($enabled) ? $enabled : array();
$sKey               = !empty($key) ? $key : 'enabled_drivers';
$bCanSelectMultiple = !empty($canSelectMultiple) ? true : false;

if (!empty($aDrivers)) {

    ?>
    <div class="table-responsive">
        <table>
            <thead class="drivers">
                <tr>
                    <th width="80" class="selected text-center">Enabled</th>
                    <th width="*" class="label">Label</th>
                    <th width="150" class="configure text-center">Configure</th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($aDrivers as $oDriver) {

                    if ($this->input->post($sKey)) {

                        $bEnabled = in_array($oDriver->slug, $this->input->post($sKey));

                    } else {

                        $bEnabled = in_array($oDriver->slug, $aEnabled);
                    }

                    ?>
                    <tr>
                        <td class="selected text-center">
                            <?php

                            if ($bCanSelectMultiple) {
                                echo form_checkbox($sKey . '[]', $oDriver->slug, $bEnabled);
                            } else {
                                echo form_radio($sKey, $oDriver->slug, $bEnabled);
                            }

                            ?>
                        </td>
                        <td class="label">
                            <?php

                            echo $oDriver->name;
                            if (!empty($oDriver->description)) {

                                echo '<small>';
                                echo $oDriver->description;
                                echo '</small>';
                            }

                            ?>
                        </td>
                        <td class="configure text-center">
                            <?php

                            if (!empty($oDriver->data->settings)) {

                                echo anchor(
                                    'admin/admin/settings/driver?slug=' . $oDriver->slug,
                                    'Configure',
                                    'data-fancybox-type="iframe" class="fancybox btn btn-xs btn-primary"'
                                );
                            } else {

                                ?>
                                <span class="text-muted">
                                    Not configurable
                                </span>
                                <?php
                            }

                            ?>
                        </td>
                    </tr>
                    <?php
                }

                ?>
            <tbody>
        </table>
    </div>
    <?php

}