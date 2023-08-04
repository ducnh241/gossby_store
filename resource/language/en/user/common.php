<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
$lang = array(
    'usr.auth_err_account_not_match' => 'Your email or password do not match our records',
    'usr.auth_err_email_not_match' => 'No account matches, please try again. Make sure you entered the correct email connected to this account',
    'usr.auth_err_account_not_exist' => 'The account is not exist',
    'usr.auth_err_account_unactivated' => 'The account is not activated',
    'usr.auth_forgot_password' => 'Forgot your password?',
    'usr.auth_remember_me' => 'Remember me',
    'usr.auth_sign_in' => 'Sign in',
    'usr.auth_sign_out' => 'Sign out',
    'usr.auth_forget_password' => 'Send password reset email',
    'usr.backend_auth_desc' => '<span>Are you admin?</span> Please sign in to perform actions',
    'usr.forget_password_description' => 'Enter your email to receive new password',
    'usr.err_email_empty' => 'The email is empty',
    'usr.err_email_incorrect' => 'The email is incorrect',
    'usr.err_password_empty' => 'The password is empty',
    'usr.err_password_weak' => 'The password is too weak',
    'usr.err_password_exceed' => 'The password is too long',
    'usr.err_auth_secret_key_incorrect' => 'Authenticator secret key is incorrect',
    'usr.err_group_root' => 'This action cannot perform with root group',
    'usr.err_group_not_empty' => 'The group is not empty',
    'usr.err_group_name_already' => 'This group name already taken by another group',
    'usr.err_group_name_empty' => 'Group name is empty',
    'usr.err_member_root' => 'This action cannot perform with root member',
    'usr.group_manage' => 'Manage Groups',
    'usr.group_list' => 'Group list',
    'usr.group_add' => 'Add new group',
    'usr.member_add' => 'Add new account',
    'usr.member_confirm_del' => 'Do you want to delete the account "{{username}}"?',
    'usr.member_edit' => 'Edit account',
    'usr.member_manage' => 'Manage Accounts',
    'usr.member_no_result' => 'No accounts created yet.',
    'usr.member_list' => 'Member list',
    'usr.member_err_not_exist' => 'The account is not exist',
    'usr.user_and_group' => 'Users & Groups',
    'usr.permmask' => 'Permission mask',
    'usr.permmask_add' => 'Add new permission mask',
    'usr.permmask_confirm_del' => 'Do you want to delete the permission mask "{{title}}"?',
    'usr.permmask_copy' => 'Copy permission mask',
    'usr.permmask_edit' => 'Edit permission mask',
    'usr.permmask_err_not_exists' => 'The permission mask #{{id}} is not exists',
    'usr.permmask_err_name_already' => 'This permission mask name already taken by another',
    'usr.permmask_err_name_empty' => 'Permission mask name is empty',
    'usr.permmask_groups' => 'Groups',
    'usr.permmask_manage' => 'Manage Permission Masks',
    'usr.permmask_members' => 'Members',
    'usr.permmask_no_result' => 'No Permission Masks created yet.',
    'usr.permmask_list' => 'Permission mask list',
    'usr.permmask_tooltip_multi' => 'You can chose multiple permission mask',
    'usr.permmasks' => 'Permission masks',
    'usr.your_email' => 'Your email',
    'usr.your_password' => 'Your password',
    'usr.admin_group_management' => 'Manage Group Administrators',
    'usr.auth_send_reset_password_email_success' => 'An email with new password has been sent to your mailbox',
    'usr.reset_password_email_subject' => 'Gossby Backend - Password Updated',
        /*
          'usr.id' => 'ID',
          'usr.guest' => 'Guest',
          'usr.is_admin' => 'Are you admin?',
          'usr.your_username' => 'Your username',

          'usr.manage_group' => 'Manage group',
          'usr.sort_by_username' => 'Sort by username',
          'usr.sort_by_email' => 'Sort by email',
          'usr.username' => 'Username',
          'usr.password' => 'Password',

          'usr.account_add_notify_tooltip' => 'Send a alert email to the registration email address.<br /><span style="font-style: italic; color: #a20000; font-weight: bold; display: block; margin-top: 5px">Notice: The password will send to the member in the email content</span>',
          'usr.notify_email' => 'Send notify email to the account',
          'usr.account_updated' => 'The account has been updated',
          'usr.log_edit_account' => 'Edit account <span class="highlight">"{{username}}"</span> [#{{id}}]',
          'usr.account_added' => 'The account has been added',
          'usr.log_add_account' => 'Add account <span class="highlight">"{{username}}"</span> [#{{id}}]',
          'usr.member_id' => 'Member ID',
          'usr.email' => 'Email',
          'usr.group' => 'Group',
          'usr.group_list' => 'Group list',
          'usr.activated' => 'Activated',
          'usr.access_offline' => 'Can access offline',
          'usr.register_date' => 'Register date',
          'usr.suspension' => 'Suspension',
          'usr.suspended' => 'Suspended',
          'usr.suspend_expire_date' => 'Expire date',
          'usr.suspend_reason' => 'Suspend reason',
          'usr.email_address' => 'Email address',
          'usr.edit_account' => 'Edit account',
          'usr.avatar' => 'Avatar',
          'usr.ava_cropper' => 'Avatar cropper',
          'usr.del_account' => 'Delete account',
          'usr.confirm_close_ava_cropper' => 'Do you want to close avatar cropper?',
          'usr.err_ava_ext_incorrect' => 'The avatar extension is incorrect',
          'usr.rmv_ava' => 'Remove',
          'usr.confirm_rmv_ava' => 'Do you want to delete the avatar?',
          'usr.suspend_member' => 'Suspend the account',
         */
);
?>
