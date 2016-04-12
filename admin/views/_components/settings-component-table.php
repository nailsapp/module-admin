<?php

$aComponents        = $components;
$aEnabled           = $enabled;
$sKey               = $key;
$bCanSelectMultiple = $canSelectMultiple;
$sComponentType     = $componentType;

if (!empty($aComponents)) {

    ?>
    <div class="table-responsive">
        <table>
            <thead class="components">
                <tr>
                    <th width="80" class="selected text-center">Enabled</th>
                    <th width="*" class="label">Label</th>
                    <th width="150" class="configure text-center">Configure</th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($aComponents as $oComponent) {

                    if ($this->input->post($sKey)) {

                        if ($bCanSelectMultiple) {

                            $bEnabled = in_array($oComponent->slug, $this->input->post($sKey));

                        } else {

                            $bEnabled = $oComponent->slug == $this->input->post($sKey);
                        }

                    } else {

                        $bEnabled = in_array($oComponent->slug, $aEnabled);
                    }

                    ?>
                    <tr>
                        <td class="selected text-center">
                            <?php

                            if ($bCanSelectMultiple) {
                                echo form_checkbox($sKey . '[]', $oComponent->slug, $bEnabled);
                            } else {
                                echo form_radio($sKey, $oComponent->slug, $bEnabled);
                            }

                            ?>
                        </td>
                        <td class="label">
                            <?php

                            echo $oComponent->name;
                            if (!empty($oComponent->description)) {

                                echo '<small>';
                                echo $oComponent->description;
                                echo '</small>';
                            }

                            ?>
                        </td>
                        <td class="configure text-center">
                            <?php

                            if (!empty($oComponent->data->settings)) {

                                echo anchor(
                                    'admin/admin/settings/' . $sComponentType . '?slug=' . $oComponent->slug,
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