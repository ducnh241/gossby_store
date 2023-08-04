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
class Helper_Backend_Template extends Abstract_Core_Template {

    protected $_app_item = null;
    protected $_current_menu_item_key = null;
    protected $_menu_items = null;
    protected $_dock_enabled = true;
    protected $_content = false;
    protected static $_instance = null;

    public function __construct() {
        parent::__construct();
        OSC::core('language')->load('backend/common');
        $this->resetBreadcrumb();
    }

    public function checkPermission($perm_key) {
        /* @var $controller Abstract_Backend_Controller */
        $controller = OSC::controller();
        return $controller->checkPermission($perm_key, false);
    }

    /**
     * 
     * @return Helper_Backend_Template
     */
    public function resetBreadcrumb() {
        parent::resetBreadcrumb();

        $this->addBreadcrumb(array('dashboard', OSC::core('language')->get('backend.dashboard')), $this->getUrl('backend/index/dashboard'));

        return $this;
    }

    protected function _setBase() {
        $this->_tpl_base_path = OSC_RES_PATH . '/template/backend';
        $this->_tpl_base_url = OSC::$base_url . '/resource/template/backend';
    }

    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }

    public function render() {
        $HTML = $this->build(
                'backend/html', array(
            'content' => $this->_content,
            'title' => $this->_title,
                )
        );

        return $HTML;
    }

    /**
     *
     * @param boolean $enable
     * @return OSCProcessor_Backend
     */
    public function setDock($enable = true) {
        $this->_dock_enabled = $enable;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function dockEnabled() {
        return $this->_dock_enabled;
    }

    /**
     *
     * @return array
     */
    public function getBackendMenu() {
        /*
          [
          'key' => 'menu_key',
          'position' => 1,
          'parent_key' => 'menu_parent_key',
          'perm_key' => 'perm_key',
          'icon' => 'nav',
          'title' => 'Backend menu',
          'tooltip' => 'Backend menu tooltip',
          'url' => 'https://osecore.com',
          'notify_counter' => 0
          ]
         */

        $response = OSC::core('observer')->dispatchEvent('backend/collect_menu');

        $buff = [];

        $key_used = [];

        foreach ($response as $menus) {
            if (!is_array($menus)) {
                continue;
            }

            if (isset($menus['title'])) {
                $menus = [$menus];
            }

            foreach ($menus as $menu) {
                if (!is_array($menu) || !isset($menu['title']) || !isset($menu['url'])) {
                    continue;
                }

                if (!isset($menu['tooltip'])) {
                    $menu['tooltip'] = $menu['title'];
                }

                if (!isset($menu['key'])) {
                    $menu['key'] = OSC::makeUniqid();
                } else if (isset($key_used[$menu['key']])) {
                    continue;
                } else {
                    $key_used[$menu['key']] = 1;
                }

                if (!isset($menu['position'])) {
                    $menu['position'] = 0;
                } else {
                    $menu['position'] = intval($menu['position']);
                }
                $menu['divide'] = $menu['divide'] === true ?? false;

                if (!isset($menu['parent_key'])) {
                    $menu['parent_key'] = 'root';
                }
                
                $menu['disabled'] = OSC::controller()->checkPermission(isset($menu['perm_key']) ? $menu['perm_key'] : false, false);
                $menu['activated'] = $this->_checkActivatedMenu($menu['key']);

                if (!isset($buff[$menu['parent_key']])) {
                    $buff[$menu['parent_key']] = [];
                }

                if (!isset($buff[$menu['parent_key']])) {
                    $buff[$menu['parent_key']] = [];
                }

                $buff[$menu['parent_key']][] = $menu;
            }
        }

        $backend_menu = [];

        $this->_backendMenuTreeBuilder($backend_menu, $buff, 'root');

        foreach ($buff as $group) {
            foreach ($group as $menu) {
                $menu['sub_items'] = [];

                $backend_menu[] = $menu;
            }
        }
        
        $this->_backendMenuSort($backend_menu);

        return $backend_menu;
    }

    private function _checkActivatedMenu($menu_key)
    {
        $parent_key = explode('/', $this->getCurrentMenuItemKey());
        if ($menu_key == $parent_key[0] || $menu_key == $this->getCurrentMenuItemKey()) {
            return true;
        }
        return false;
    }

    protected function _backendMenuSort(&$menus) {
        uasort($menus, function($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] > $b['position']) ? -1 : 1;
        });
    }

    protected function _backendMenuTreeBuilder(&$pointer, &$buff, $key) {
        if (isset($buff[$key])) {
            foreach ($buff[$key] as $menu) {
                $menu['sub_items'] = [];

                $this->_backendMenuTreeBuilder($menu['sub_items'], $buff, $menu['key']);

                $this->_backendMenuSort($menu['sub_items']);

                $pointer[] = $menu;
            }

            unset($buff[$key]);
        }
    }

    /**
     *
     * @param string $menuItemKey
     * @return $this
     */
    public function setCurrentMenuItemKey($menuItemKey) {
        $this->_current_menu_item_key = $menuItemKey;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCurrentMenuItemKey() {
        return $this->_current_menu_item_key;
    }

    public function getUrl($action_path = null, $params = [], $inc_hash = true) {
        return OSC::getUrl($action_path, $params, $inc_hash);
    }

    public function rebuildUrl($params = []) {
        return OSC::rebuildUrl($params);
    }
    
    public function getCurrentUrl() {
        return OSC::getCurrentUrl();
    }

    /**
     * 
     * @param string $instance
     * @return Helper_Backend_Template_Grid
     */
    public function getGrid($instance = OSC::SINGLETON) {
        return OSC::helper('backend/template_grid', $instance);
    }

    /**
     * 
     * @param string $instance
     * @return Helper_Backend_Template_Form
     */
    public function getForm($instance = OSC::SINGLETON) {
        return OSC::helper('backend/template_form', $instance);
    }

}
