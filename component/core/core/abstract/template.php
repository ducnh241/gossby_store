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
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package Abstract_Core_Template
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class Abstract_Core_Template extends OSC_Template {

    /**
     *
     * @var string 
     */
    protected $_identify = 'core';

    /**
     * 
     * @param array $resources
     * @param string $type
     * @return Abstract_Core_Template
     */
    public function push($resources, $type, $priority = false) {
        OSC::core('template')->push($resources, $type, $priority);
        return $this;
    }

    /**
     * 
     * @return Abstract_Core_Template
     */
    public function addComponent() {
        call_user_func_array(array(OSC::core('template'), 'addComponent'), func_get_args());
        return $this;
    }

    public function getTimeAgo($timestamp) {
        $current_timestamp = time();

        $diff = $current_timestamp - $timestamp;

        $years = intval($diff / 31536000);

        $diff = $diff % 31536000;

        $days = intval($diff / 86400);

        $diff = $diff % 86400;

        $hours = intval($diff / 3600);

        $diff = $diff % 3600;

        $minutes = intval($diff / 60);

        if ($years >= 1) {
            return $years . ' năm trước';
        } else if ($days >= 1) {
            return $days . ' ngày trước';
        } else if ($hours >= 1) {
            return $hours . ' giờ trước';
        } else if ($minutes >= 1) {
            return $minutes . ' phút trước';
        } else {
            return 'vài giây trước';
        }
    }

    /**
     * 
     * @return Model_User_Member
     */
    public function getAccount() {
        return OSC::helper('user/authentication')->getMember();
    }

    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function setting(string $key, $default = null) {
        return OSC::helper('core/setting')->get($key, $default);
    }

    /**
     * 
     * @param string $path
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param bool $keep_extension
     * @param bool $skip_check_crawler_request
     * @return type
     */
    public function imageOptimaze(string $path, int $width, int $height, bool $crop = false, bool $keep_extension = false, $skip_check_crawler_request = false) {
        return OSC::helper('core/common')->imageOptimaze($path, $width, $height, $crop, $keep_extension, $skip_check_crawler_request);
    }

    /**
     * @param string $path
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param bool $keep_extension
     * @param false $skip_check_crawler_request
     * @return mixed
     */
    public function imageOptimize(
        string $path,
        int $width,
        int $height,
        bool $crop = false,
        bool $keep_extension = false,
        $skip_check_crawler_request = false
    ) {
        return OSC::helper('core/image')->imageOptimize(
            $path,
            $width,
            $height,
            $crop,
            $keep_extension,
            $skip_check_crawler_request
        );
    }

}
