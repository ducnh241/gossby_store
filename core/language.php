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

/**
 * OSC_Framework::Language
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Language extends OSC_Object {

    /**
     *
     * @var string
     */
    protected $_base;

    /**
     *
     * @var array
     */
    protected $_lang = array();

    /**
     *
     * @var string
     */
    protected $_current_lang_key;

    /**
     *
     * @var string
     */
    protected $_default_lang_key;

    /**
     *
     * @var array
     */
    protected $_lang_map = array();

    //Internationalization
    protected $_i18n_map = [
        'en-us' => 'en'
    ];

    /**
     *
     * @var array 
     */
    protected $_loaded = array();

    public function __construct() {
        $this->_lang_map = OSC::systemRegistry('language_data');

        if (!is_array($this->_lang_map)) {
            $this->_lang_map = [];
        }

        $this->_lang_map['en-us'] = 'English (United States)';

        $this->_default_lang_key = OSC::systemRegistry('language_default');

        if (!$this->_default_lang_key || !isset($this->_lang_map[$this->_default_lang_key])) {
            $this->_default_lang_key = 'en-us';
        }
        $lang_key = OSC::sessionGet('language');

        if (!$lang_key || !isset($this->_lang_map[$lang_key])) {
            $lang_key = $this->_default_lang_key;
        }

        OSC::sessionSet('language', $lang_key);

        $this->_current_lang_key = $lang_key;
        $this->_base = OSC_RES_PATH . '/language/' . $this->_i18n_map[$this->_current_lang_key];
    }

    public function languageIsExists($lang_key) {
        return isset($this->_lang_map[$lang_key]);
    }

    public function languageSwitchTo($lang_key) {
        if (!$this->languageIsExists($lang_key)) {
            throw new Exception($this->get('core.err_lang_not_exists'));
        }

        OSC::sessionSet('language', $lang_key);
    }

    public function getCurrentLanguageKey() {
        return $this->_current_lang_key;
    }

    /**
     * Load a language file. Populates static::$_lang
     *
     * @param string $path
     * @return OSC_Language
     */
    public function load($path) {
        foreach (func_get_args() as $path) {
            $path = $this->_base . '/' . $path . '.php';

            if (!in_array($path, $this->_loaded)) {
                if (!file_exists($path)) {
                    throw new OSC_Exception_Runtime("Can't load language file [{$path}]");
                }

                $this->_loaded[] = $path;

                include_once $path;

                $this->_lang = array_merge($this->_lang, $lang);
            }
        }

        return $this;
    }

    /**
     * 
     * @param string $key
     * @return string
     */
    public function get($key = '') {
        if (!$key) {
            return $this->_lang;
        }

        return $this->_lang[$key];
    }

    public function build() {
        $num_args = func_num_args();
        $args = func_get_args();

        if ($num_args < 1) {
            return '';
        }

        $message = $this->get($args[0]);

        if (is_object($args[1])) {
            return preg_replace_callback('/%([a-zA-Z0-9_]+)/', function ($matches) use ($args) {
                return $args[1]->{$matches[1]};
            }, $message);
        } else if (is_array($args[1])) {
            return preg_replace_callback('/%([\w-_]+)/', function ($matches) use ($args) {
                return $args[1][$matches[1]];
            }, $message);
        } else {
            return preg_replace_callback('/%(\d+)/', function ($matches) use ($args) {
                return $args[$matches[1]];
            }, $message);
        }
    }

    public static function registerLanguage($key, $title, $default_flag = false) {
        $language_data = OSC::systemRegistry('language_data');

        if (!$language_data || !is_array($language_data)) {
            $language_data = array();
        }

        $language_data[$key] = $title;

        OSC::systemRegister('language_data', $language_data);

        if ($default_flag) {
            OSC::systemRegister('language_default', $key);
        }
    }

    public function processLanguage(&$request_string)
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        if (preg_match('/^([a-zA-Z]{2}-[a-zA-Z]{2})\/([\w].*)$/', $request_string, $matches)) {
            if ($current_lang_key != $matches[1]) {
                try {
                    OSC::core('language')->languageSwitchTo($matches[1]);
                } catch (Exception $ex) {
                    OSC_Controller::redirect(OSC::$base_url . '/' . $current_lang_key . '/' . $matches[2]);
                }

                OSC_Controller::redirect(OSC::$base_url . '/' . $matches[1] . '/' . $matches[2]);
            }

            $request_string = $matches[2];
        }

    }
}
