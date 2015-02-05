<?php

$knownUser = isset($id) && $id ? '' : 'no-data';
echo '<td class="' . $knownUser . ' user-cell">';

    //  Profile image
    if (isset($profile_img) && $profile_img) {

        echo anchor(cdn_serve($profile_img) ,img(cdn_thumb($profile_img, 36, 36)), 'class="fancybox"');

    } else {

        $gender = isset($gender) ? $gender : 'undisclosed';
        echo img(cdn_blank_avatar(36, 36, $gender));
    }

    // --------------------------------------------------------------------------

    //  User details
    echo '<span class="user-data">';

        $name  = '';
        $name .= isset($first_name) && $first_name ? $first_name . ' ' : '';
        $name .= isset($last_name) && $last_name ? $last_name . ' ' : '';
        $name  = $name ? $name : 'Unknown User';

        if (isset($id) && $id && userHasPermission('admin.accoutns:0.can_edit_user')) {

            echo anchor('admin/accounts/edit/' . $id, $name, 'class="fancybox" data-fancybox-type="iframe"');

        } else {

            echo $name;
        }

        if (isset($email) && $email) {

            echo '<small>' . mailto($email) . '</small>';

        } else {

            echo '<small>No email address</small>';
        }

    echo '</span>';

echo '</td>';
