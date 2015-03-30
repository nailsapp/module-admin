<?php

namespace Nails\Api\Admin;

/**
 * Admin API end points: Users
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class Users extends \ApiController
{
    public static $requiresAuthentication = true;

    // --------------------------------------------------------------------------

    /**
     * Searches users
     * @return array
     */
    public function getSearch()
    {
        if (!$this->user_model->isAdmin()) {

            return array(
                'status' => 401,
                'error'  => 'You must be an administrator.'
            );

        } else {

            $avatarSize = $this->input->get('avatarSize') ? $this->input->get('avatarSize') : 50;

            $data = array(
                'keywords' => $this->input->get('term')
            );

            $users = $this->user_model->get_all(1, 50, $data);
            $out   = array('users' => array());

            foreach ($users as $user) {

                $temp              = new \stdClass();
                $temp->id          = $user->id;
                $temp->email       = $user->email;
                $temp->first_name  = $user->first_name;
                $temp->last_name   = $user->last_name;
                $temp->gender      = $user->gender;
                $temp->profile_img = cdn_avatar($temp->id, $avatarSize, $avatarSize);

                $out['users'][] = $temp;
            }

            return $out;
        }
    }
}