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
 * @package Helper_Backend_Template
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Frontend_Template extends Abstract_Core_Template {

    protected $_content = false;
    protected $_page_unique_item_loaded = array();
    protected $_skip_wrap_content = false;
    protected static $_instance = null;
    protected $_template_key = '';
    protected $_csrf_token = '';

    public function __construct() {
        $template_store_key = OSC_Controller::makeRequestChecksum('frontend_tpl_key', OSC_SITE_KEY);

        $template_key = OSC::core('request')->get('tpl_key', false);

        if ($template_key == '0') {
            OSC::cookieRemoveSiteOnly($template_store_key);
        } else if ($template_key) {
            OSC::cookieSetSiteOnly($template_store_key, $template_key);
        }

        if (!$this->_template_key) {
            $template_key = OSC::cookieGet($template_store_key);

            if ($template_key) {
                $this->_template_key = $template_key;
            }
        }

        if (!$this->_template_key) {
            if (OSC::systemRegistry('TEMPLATE')) {
                $this->_template_key = OSC::systemRegistry('TEMPLATE');
            } else {
                $this->_template_key = 'default3';
            }
        }

        if (!$this->_csrf_token) {
            $this->_csrf_token = OSC::sessionGet('csrf_token');
        }

        parent::__construct();

        $this->setSeoMetadata(array(
            'seo_title' => OSC::helper('core/setting')->get('theme/metadata/title'),
            'seo_description' => OSC::helper('core/setting')->get('theme/metadata/description'),
            'seo_keywords' => OSC::helper('core/setting')->get('theme/metadata/keyword')
        ));

        $this->setTwitterCard();
    }

    protected function _setBase() {
        if ($this->_template_key != 'default' && !file_exists(OSC_RES_PATH . '/template/frontend/' . $this->_template_key)) {
            $this->_template_key = 'default';
        }

        $this->_tpl_base_path = OSC_RES_PATH . '/template/frontend/' . $this->_template_key;
        $this->_tpl_base_url = OSC::$base_url . '/resource/template/frontend/' . $this->_template_key;
    }

    public function setSkipWrapContentFlag($flag = true) {
        $this->_skip_wrap_content = $flag ? true : false;
        return $this;
    }

    public function isSkipWrapContent() {
        return $this->_skip_wrap_content;
    }

    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }

    public function render() {
        $HTML = $this->build(
                'frontend/html', array(
            'content' => $this->_content,
            'title' => $this->_page_title,
            'description' => $this->_page_desc,
            'metadata_tags' => $this->_page_metadata
                )
        );

        return $HTML;
    }

    public function getBlockContent($block) {
        $block_params = array();

        if (is_array($block)) {
            $block_params = $block[1];
            $block = $block[0];
        }

        if (!preg_match('/^([a-zA-Z0-9\_]+)\/+([a-zA-Z0-9\_]+)$/i', $block, $matches)) {
            return '';
        }

        $block_renderer = 'renderBlock' . $matches[2];

        return call_user_func(array(OSC::helper($matches[1] . '/frontend'), $block_renderer), $block_params);
    }

    public function addPageUniqueItemLoadedIds($key, $id) {
        if (!isset($this->_page_unique_item_loaded[$key])) {
            $this->_page_unique_item_loaded[$key] = array();
        }

        $this->_page_unique_item_loaded[$key][] = $id;

        return $this;
    }

    public function getPageUniqueItemLoadedIds($key, $id) {
        if (!isset($this->_page_unique_item_loaded[$key])) {
            return array();
        }

        return $this->_page_unique_item_loaded[$key];
    }

    public function getContactUrl()
    {
        return OSC::$base_url . '/' . OSC::core('language')->getCurrentLanguageKey() . '/page/contact';
    }

    public function getLogo($flag_email = false, $get_small_logo = false) {
        $logo = $get_small_logo ? $this->setting('theme/logo/small') : $this->setting('theme/logo');

        if ($flag_email) {
            $logo_email = $this->setting('theme/logo/email');
            if (is_array($logo_email) && isset($logo_email['file'])) {
                $logo = $logo_email;
            }
        }

        if (is_array($logo)) {
            $logo['file'] = OSC::core('aws_s3')->getStorageUrl($logo['file']);
        } else {
            $logo = [
                'file' => $this->getImage('logo.svg')
            ];
        }

//        $logo['file'] = OSC::wrapCDN($logo['file']);

        $return = new stdClass();

        $return->url = $logo['file'];
        $return->alt = $logo['alt'] ? $logo['alt'] : $this->setting('theme/site_name');

        return $return;
    }

    public function getToken() {
        return $this->_csrf_token;
    }

    public function getFavicon() {
        $logo = $this->setting('theme/favicon');

        if (is_array($logo)) {
            $logo['file'] = OSC::core('aws_s3')->getStorageUrl($logo['file']);
        } else {
            $logo = [
                'file' => $this->getImage('favicon.png')
            ];
        }

        /* A Sang bao khong dung CDN */
        /*$logo['file'] = OSC::wrapCDN($logo['file']);*/

        $return = new stdClass();

        $return->url = $logo['file'];
        $return->alt = $logo['alt'];

        return $return;
    }

    public function getMetaImage()
    {
        $meta_image = $this->setting('theme/metadata/image');
        if (is_array($meta_image)){
            $meta_image['file'] = OSC::core('aws_s3')->getStorageUrl($meta_image['file']);
        } else {
            $meta_image['file'] = $this->getImage('logo.svg');
        }

        $meta_image['file'] = OSC::wrapCDN($meta_image['file']);

        $return = new stdClass();

        $return->url = $meta_image['file'];
        $return->alt = $meta_image['alt'];

        return $return;
    }

    public function sanitizeOutput($buffer) {
        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

    public function renderDefaultImage() {
        return OSC::wrapCDN($this->imageOptimize($this->getImage("placeholder.png"), 300, 300, false, true));
    }

    public function getPhoneSupport()
    {
        $phone_numbers = trim(OSC::helper('core/setting')->get('theme/contact/phone_numbers'));
        if (!$phone_numbers) {
            return [];
        }
        $phone_numbers = explode("\n", $phone_numbers);
        $phone_numbers = array_filter($phone_numbers, function ($phone) {
            return trim($phone) != '';
        });

        return $phone_numbers;
    }

    public function renderSubmenu($child, $level){
        $menu = '';
        foreach ($child['children'] as $subChild) {
            $menu .= "<div ";
            if ($level) {
                $menu .= "class='padding-left-" . $level . "'";
            }
            $menu .= "><a href='" . $subChild['url'] . "'>" . $subChild['title'] . "</a></div>";
            if (count($subChild['children']) > 0) {
                $menu .= $this->renderSubmenu($subChild, $level + 1);
            }
        }
        return $menu;
    }

    public function getNavigation($position) {
        $navigation_id = OSC::helper('core/setting')->get($position) ?? 1;
        $cache_key = __FUNCTION__ . '_navigation_id_' . $navigation_id;

        $navigation = [];
        try {
            $model = OSC::model('navigation/navigation');
            $cache = OSC::core('cache')->get($cache_key);

            if ($cache) {
                $model->bind($cache);
            } else {
                $model->load($navigation_id);
                OSC::core('cache')->set($cache_key, $model->data, OSC_CACHE_TIME);
            }

            $navigation = $model->getOrderedItems(true, );
        } catch (Exception $ex) {

        }

        return $navigation;
    }
}
