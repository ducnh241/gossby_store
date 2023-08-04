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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Helper_User_Authentication {

    /**
     *
     * @var Model_User_Member 
     */
    protected $_member_model = null;

    const IP_SEC_FLAG = false;

    /**
     * 
     * @param int $member_id
     * @param boolean $remember_flag
     * @return $this
     * @throws Exception
     */
    public function signIn($member_id, $remember_flag = false) {
        $this->getMember()->load($member_id);

        if ($this->getMember()->getId() < 1) {
            throw new Exception('Cannot load member with the ID #' . $member_id);
        }
        
        $seckey = OSC::makeUniqid();

        $checksum = $this->_getMemberCookieChecksum($seckey);

        OSC::cookieSetCrossSite($this->_getMemberCookieIDKey(), $member_id, $this->_getMemberCookieTTL($remember_flag));
        OSC::cookieSetCrossSite($this->_getMemberCookieSecKey(), $seckey, $this->_getMemberCookieTTL($remember_flag));
        OSC::cookieSetCrossSite($checksum['key'], $checksum['value'], $this->_getMemberCookieTTL($remember_flag));
        OSC::cookieSetCrossSite($this->_getMemberCookieRememberKey(), $remember_flag ? 1 : 0, $this->_getMemberCookieTTL($remember_flag));

        return $this;
    }

    protected function _getMemberCookieTTL($remember_flag = false) {
        return 60 * 60 * 24 * 365;
        return 60 * 60 * 24 * ($remember_flag ? (30 * 3) : 1);
    }

    protected function _getMemberCookieIDKey() {
        return OSC_SITE_KEY . '-member_id';
    }

    protected function _getMemberCookieRememberKey() {
        return OSC_SITE_KEY . '-member_remember';
    }

    protected function _getMemberCookieSecKey() {
        return OSC_SITE_KEY . '-member_seckey';
    }

    protected function _getMemberCookieChecksum($seckey) {
        $client_ip = OSC::getClientIP();

        return array(
            'key' => hash_hmac('sha256', $this->_member_model->data['email'], $this->_member_model->data['password_hash'] . (static::IP_SEC_FLAG ? $client_ip : '')),
            'value' => hash_hmac('sha256', $this->_member_model->data['added_timestamp'], $this->_member_model->data['password_hash'] . (static::IP_SEC_FLAG ? $client_ip : ''))
        );
    }

    /**
     * 
     * @return $this
     */
    public function signOut() {
        if ($this->getMember()->getId() > 0) {
            $seckey = OSC::cookieGet($this->_getMemberCookieSecKey());
            
            $checksum = $this->_getMemberCookieChecksum($seckey);

            OSC::cookieRemoveCrossSite($this->_getMemberCookieIDKey());
            OSC::cookieRemoveCrossSite($this->_getMemberCookieSecKey());
            OSC::cookieRemoveCrossSite($checksum['key']);
            OSC::cookieRemoveCrossSite($this->_getMemberCookieRememberKey());
        }

        return $this;
    }

    /**
     * 
     * @return Model_User_Member
     */
    public function getMember() {
        if ($this->_member_model === null) {
            $this->_member_model = OSC::model('user/member');

            $member_id = intval(OSC::cookieGet($this->_getMemberCookieIDKey()));
            $seckey = OSC::cookieGet($this->_getMemberCookieSecKey());

            if ($member_id > 0 && $seckey) {
                try {
                    $this->_member_model->load($member_id);
                } catch (Exception $ex) {
                    $member_id = 0;
                }
            }

            $destroy_member_cookie_flag = false;

            if ($this->_member_model->getId() < 1) {
                $destroy_member_cookie_flag = true;
            } else {
                $checksum = $this->_getMemberCookieChecksum($seckey);

                if (OSC::cookieGet($checksum['key']) !== $checksum['value']) {
                    $destroy_member_cookie_flag = true;

                    OSC::cookieRemoveCrossSite($checksum['key']);
                }
            }

            if ($destroy_member_cookie_flag) {
                OSC::cookieRemoveCrossSite($this->_getMemberCookieIDKey());
                OSC::cookieRemoveCrossSite($this->_getMemberCookieSecKey());
                OSC::cookieRemoveCrossSite($this->_getMemberCookieRememberKey());

                $this->_member_model = $this->_member_model->getNullModel();

                $this->_member_model->setGuestData();
            }
        }

        return $this->_member_model;
    }

}
