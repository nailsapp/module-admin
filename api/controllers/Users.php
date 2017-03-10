<?php

/**
 * Admin API end points: Users
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Api\Admin;

class Users extends \Nails\Api\Controller\Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return array
     */
    public function getSearch()
    {
        if (!isAdmin()) {

            return array(
                'status' => 401,
                'error'  => 'You must be an administrator.'
            );

        } else {

            $avatarSize = $this->input->get('avatarSize') ? $this->input->get('avatarSize') : 50;

            $users = $this->user_model->search($this->input->get('term'), 1, 50);
            $out   = array('users' => array());

            foreach ($users->data as $user) {

                $temp              = new \stdClass();
                $temp->id          = $user->id;
                $temp->email       = $user->email;
                $temp->first_name  = $user->first_name;
                $temp->last_name   = $user->last_name;
                $temp->gender      = $user->gender;
                $temp->profile_img = cdnAvatar($temp->id, $avatarSize, $avatarSize);

                $out['users'][] = $temp;
            }

            return $out;
        }
    }
}
