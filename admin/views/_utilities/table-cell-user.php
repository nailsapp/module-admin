<?php

$sNoDataClass = isset($id) && $id ? '' : 'no-data';

?>
<td class="user-cell <?=$sNoDataClass?>">
    <?php

    //  Profile image
    if (isset($profile_img) && $profile_img) {

        echo anchor(
            cdnServe($profile_img),
            img(cdnCrop($profile_img, 36, 36)),
            'class="fancybox"'
        );

    } else {

        $sGender = !empty($gender) ? $gender : 'undisclosed';
        echo img(cdnBlankAvatar(36, 36, $sGender));
    }

    ?>
    <span class="user-data">
        <?php

        $sName  = '';
        $sName .= !empty($first_name) ? $first_name . ' ' : '';
        $sName .= !empty($last_name) ? $last_name . ' ' : '';
        $sName  = $sName ? $sName : 'Unknown User';

        if (!empty($id) && userHasPermission('admin:auth:accounts:editOthers')) {

            echo anchor(
                'admin/auth/accounts/edit/' . $id,
                $sName,
                'class="fancybox" data-fancybox-type="iframe"'
            );

        } else {

            echo $sName;
        }

        if (!empty($email)) {

            echo '<small>' . mailto($email) . '</small>';

        } else {

            echo '<small>No email address</small>';
        }

        ?>
    </span>
</td>
