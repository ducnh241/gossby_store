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
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package Helper_Core_Session
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Core_Image extends OSC_Object {

    public function __construct() {
        parent::__construct();
    }

    protected $_optimize_image_mapping = [];

    public function imageOptimize(
        string $image_url,
        int $width,
        int $height,
        bool $crop = false,
        bool $keep_extension = false,
        $skip_check_crawler_request = false,
        bool $skip_domain = false
    ) {
        return $image_url;

        if (OSC::isCrawlerRequest() && !$skip_check_crawler_request) {
            return '';
        }

        if (!class_exists('Imagick', false) || isset($_REQUEST['skip_optimize_img']) || $image_url == '') {
            return $image_url;
        }

        if (!$keep_extension) {
            $extension = 'jpg';
        } else {
            $extension = strtolower(preg_replace('/^.+\.([^\.]+)$/i', '\\1', $image_url));
        }

        if (!in_array($extension, ['png', 'gif', 'jpg'])) {
            return $image_url;
        }

        $s3_root_dir_url = OSC::core('aws_s3')->getRootDirUrl();

        $is_s3_image = false;
        if (substr($image_url, 0, strlen($s3_root_dir_url)) == $s3_root_dir_url) {
            $is_s3_image = true;
            $substr_counter = strlen($s3_root_dir_url . '/');
        } else if (substr($image_url, 0, strlen(OSC::$base_url)) == OSC::$base_url) {
            $substr_counter = strlen(OSC::$base_url . '/');
        } else if (substr($image_url, 0, strlen(OSC_SITE_PATH)) == OSC_SITE_PATH) {
            $substr_counter = strlen(OSC_SITE_PATH . '/');
        } else {
            return $image_url;
        }

        $image_path = substr($image_url, $substr_counter);

        $optimize_image_name = md5($image_path) . '.' . $width . 'x' . $height . '.' . ($crop ? 1 : 0) . '.' . $extension;
        $optimize_image_path = 'var/opt_images/' . $optimize_image_name;

        if (file_exists(OSC_VAR_PATH . '/opt_images/' . $optimize_image_name)) {
            return $skip_domain ? $optimize_image_path : OSC::core('aws_s3')->getFileUrl($optimize_image_path);
        }

        $this->_optimize_image_mapping[$optimize_image_path] = [
            'original_path' => $image_path,
            'optimize_path' => $optimize_image_path,
            'config' => [
                'width' => $width,
                'height' => $height,
                'crop' => $crop ? 1 : 0,
                'extension' => $extension
            ]
        ];

        $original_image_url = $is_s3_image ?
            OSC::core('aws_s3')->getRootDirUrl() . '/' . $image_path :
            OSC::$base_url . '/' .$image_path;

        return $skip_domain ? $image_path : $original_image_url;
    }

    public function getOptimizeImagesMapping() {
        return $this->_optimize_image_mapping;
    }
}
