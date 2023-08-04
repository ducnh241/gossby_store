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
 * OSC_Framework::Setting
 *
 * @package OSC_Setting
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Setting extends OSC_Object {

    protected $_settings = array('root' => array());

    /**
     *
     * @var array
     */
    protected $_loaded = array();

    public function get($setting_key, $scopes = null) {
        if (substr_count($setting_key, '/') < 1) {
            return null;
        }

        $group_key = preg_replace('/^(.+)\/[^\/]+$/', '\\1', $setting_key);
        $setting_key = preg_replace('/.+\/([^\/]+)$/', '\\1', $setting_key);

        $scopes = $this->_preload($group_key, $scopes);

        if (count($scopes) < 1) {
            return null;
        }

        foreach ($scopes as $scope) {
            if (isset($this->_settings[$scope][$group_key][$setting_key])) {
                return $this->_settings[$scope][$group_key][$setting_key];
            }
        }

        return null;
    }

    public function getGroup($group_key, $scopes = null) {
        $scopes = $this->_preload($group_key, $scopes);

        if (count($scopes) < 1) {
            return array();
        }

        $settings = $this->_settings['root'][$group_key];

        if (count($scopes) == 1) {
            return $settings;
        }

        unset($scopes[0]);

        $setting_keys = array_keys($settings);

        foreach ($setting_keys as $setting_key) {
            foreach ($scopes as $scope) {
                if (isset($this->_settings[$scope][$group_key][$setting_key])) {
                    $settings[$setting_key] = $this->_settings[$scope][$group_key][$setting_key];
                    break;
                }
            }
        }
    }

    protected function _preload($group_key, $scopes) {
        if (!is_array($scopes)) {
            $scopes = trim($scopes);

            if (!$scopes) {
                $scopes = 'root';
            }

            if ($scopes == 'root') {
                $scopes = array($scopes);
            } else {
                $scopes = array('root', $scopes);
            }
        } else {
            $buff = array('root');

            foreach ($scopes as $scope) {
                $scope = trim($scope);

                if (!$scope) {
                    continue;
                }

                if (in_array($scope, $buff)) {
                    $buff[] = $scope;
                }
            }

            $scopes = $buff;
        }

        foreach ($scopes as $idx => $scope) {
            if (!isset($this->_settings[$scope][$group_key])) {
                $this->_loadSetting($group_key, $scope);
            }

            if ($this->_settings[$scope][$group_key] === null) {
                unset($scopes[$idx]);
            }
        }

        return $scopes;
    }

    protected function _loadSetting($group_key, $scope = 'root') {
        $collection = OSC::model('core/setting')->getCollection()->loadGroup($group_key, $scope);

        if ($collection->length() < 1) {
            $settings = null;
        } else {
            $settings = array();

            foreach ($collection as $model) {
                $settings[$model->data['setting_key']] = $model->data['setting_value'];
            }
        }

        $this->_settings[$scope][$group_key] = $settings;
    }

    /**
     * 
     * @param string $key
     * @param boolean $auto_make_default
     * @return boolean
     */
    public function load($key, $auto_make_default = true) {
        var_dump(debug_backtrace());
        die('OK');

        if (!isset($this->_loaded[$key]) || !is_object($this->_loaded[$key])) {
            $path = $this->getPath($key);

            if (file_exists($path)) {
                $class = 'setting__' . str_replace('.', '_', $key);

                include_once $path;

                $this->_loaded[$key] = OSC::obj($class);
            } else {
                $sett = false;

                if ($auto_make_default) {
                    $sett = & $this->_makeDefaultSetting($key);
                }

                if (!$sett) {
                    return false;
                } else {
                    $this->_loaded['{$key}'] = $sett;
                }
            }
        }

        return $this->_loaded[$key];
    }

    protected function _makeDefaultSetting($key) {
        static $settingConstruct = null;

        if (!preg_match('/^(.+)\.([^\.]+)$/', $key, $matches)) {
            return false;
        }

        $keyRoot = $matches[1];
        $keyBranch = $matches[2];

        if (!$settingConstruct) {
            $XMLFile = $this->getXMLConstructFile($keyRoot);

            foreach ($XMLFile as $file) {
                $this->XMLToSettingStruct(OSC::core('network')->loadURLContent($file), $settingConstruct);
            }
        }

        if (isset($settingConstruct[$keyBranch])) {
            $settData = array();

            foreach ($settingConstruct[$keyBranch]['items'] as $k => $item) {
                $settData[$item['key']] = $item['default'];
            }

            $this->save($key, $settData);

            return $this->load($key, true, false);
        }

        return false;
    }

    /**
     * Get setting object
     *
     * @param  Array   $keys
     * @return Object
     */
    public function loadAuto($keys, $autoMakeDefaultForKey = false) {
        if (!is_array($keys)) {
            $keys = array($keys);
        }

        $sett = false;

        foreach ($keys as $key) {
            if ($key == '') {
                continue;
            }

            $sett = & $this->load($key, $autoMakeDefaultForKey == $key);

            if ($sett !== false) {
                break;
            }
        }

        if ($sett === false) {
            OSC::core('output')->error('err_setting_not_exist');
        }

        return $sett;
    }

    /**
     * Set value for setting
     *
     * @param String $key
     * @param String $itemKey
     * @param Mixed  $value
     */
    public function set($key, $itemKey, $value) {
        $this->load($key)->$itemKey = $value;
    }

    /**
     * Save setting by key
     *
     * @param  String $key
     * @param  Mixed  $data
     */
    public function save($key, $data = null) {
        if (!is_array($data) || count($data) < 1) {
            $data = get_object_vars($this->load($key));
        }

        $content = '';

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = OSC::core('string')->arrToStr($v);
            } else {
                $v = '"' . OSC::core('string')->cleanQuote($v, '"') . '"';
            }

            $content[] = "public \${$k} = {$v};";
        }

        $content = implode("\r\n\t", $content);

        $class = 'setting__' . str_replace('.', '_', $key);

        $content = <<<EOF
<?php
if( ! defined('OSC_INNER') ) {
    header("HTTP/1.0 404 Not Found");
    die;
}

class {$class} {
    {$content}
/*
    public function __set(\$key, \$val ){}
*/
}
?>
EOF;

        $path = $this->getPath($key);

        $dir = dirname($path);

        if (!file_exists($dir)) {
            if (!OSC::core('filesystem_directory')->make($dir, 777, true)) {
                OSC::core('output')->error(OSC::core('language')->get('core.err_dir_make_failed'), array('directory' => $dir));
            }
        }

        $fp = fopen($path, 'w');

        $locked = OSC::core('filesystem_file')->lockFile($fp, 2, 10);

        if ($locked) {
            ftruncate($fp, 0);
            fwrite($fp, $content);
            flock($fp, LOCK_UN);
        } else {
            
        }

        fclose($fp);

        OSC::core('filesystem')->chmod($path, 777);

        return $locked;
    }

    /**
     * Check if setting object exist
     *
     * @param  String $key
     * @return Boolean
     */
    public function exists($key) {
        return file_exists(dirname($this->getPath($key . '.none')));
    }

    /**
     * 
     * @param string $XMLData
     * @param array &$settings
     */
    public function XMLToSettingStruct($XMLData, &$settings) {
        $XMLData = OSC::core('xml_parser', 'settingConstruct')->parse($XMLData);
        $XMLData = current(current($XMLData));

        if (array_key_exists('name', $XMLData)) {
            $buff = $XMLData;
            $XMLData = array($buff);
        }

        foreach ($XMLData as $k1 => $group) {
            if (!isset($settings[$group['key']])) {
                $settings[$group['key']] = array('key' => $group['key'],
                    'title' => $group['name'],
                    'editable' => $group['editable'],
                    'items' => array());
            }

            $items = $group['items']['item'];

            if (array_key_exists('key', $items)) {
                $buff = $items;
                $items = array($buff);
            }

            foreach ($items as $k2 => $item) {
                $settings[$group['key']]['items'][$item['key']] = array('key' => $item['key'],
                    'title' => $item['title'],
                    'desc' => $item['description'],
                    'inputMethod' => $item['inputMethod'],
                    'optionContent' => $item['optionContent'],
                    'notNull' => $item['notNull'],
                    'optionContentMaker' => $item['optionContentMaker'],
                    'saveCode' => $item['saveEvalCode'],
                    'showCode' => $item['showEvalCode'],
                    'default' => $item['defaultValue'],
                    'editable' => $item['editable']);
            }
        }
    }

    /**
     *
     * @param String $key
     */
    public function removeSetting($key) {
        OSC::core('filesystem')->delete(dirname($this->getPath($key . '.none')), true);
    }

    public function getPath($sett_key) {
        $path = explode('.', $sett_key);

        switch ($path[0]) {
            case 'module':
            case 'section':
                $dir = $path[0];

                unset($path[0]);

                if (count($path) == 1) {
                    $path[] = $path[1];
                }

                $path[1] = OSC::core('function')->underscoreToUpper($path[1]);

                $path = str_replace('.', DS, implode('.', $path));

                $settingFile = VAR_PATH . "setting/{$dir}/{$path}.setting.php";
                break;
            case 'template':
            case 'layout':
                $dir = $path[0];

                unset($path[0]);

                if (count($path) == 1) {
                    $path[] = $path[1];
                }

                $path = str_replace('.', DS, implode('.', $path));

                $settingFile = VAR_PATH . "setting/{$dir}/{$path}.setting.php";
                break;
            default:
                $path = str_replace('.', DS, implode('.', $path));

                $settingFile = VAR_PATH . "setting/custom/{$path}.setting.php";
        }

        return $settingFile;
    }

    public function getXMLConstructFile($sett_key) {
        $path = explode('.', $sett_key);

        switch ($path[0]) {
            case 'module':
            case 'section':
                if ($path[0] == 'section') {
                    unset($path[2]);
                    unset($path[3]);

                    $buff = array();

                    foreach ($path as $v) {
                        $buff[] = $v;
                    }

                    $path = $buff;
                }

                unset($path[0]);

                if (count($path) == 1) {
                    $path[] = $path[1];
                }

                $modulePath = OSC::getModulePath($path[1]);

                $path[1] = 'data.xml.setting';

                $XMLFile = $modulePath . str_replace('.', DS, implode('.', $path)) . '.setting.xml';

                if (file_exists($XMLFile)) {
                    $XMLFile = array($XMLFile);
                } else {
                    $XMLFile = array();
                }
                break;
            case 'template':
            case 'layout':
                $XMLFile[] = RES_PATH . 'templates' . DS . 'frontend' . DS . 'layoutSettings.xml';
                $XMLFile[] = RES_PATH . 'templates' . DS . 'frontend' . DS . OSCCONF::$template['frontend'] . DS . 'layoutSettings.xml';

                foreach ($XMLFile as $idx => $file) {
                    if (!file_exists($file)) {
                        unset($XMLFile[$idx]);
                    }
                }
                break;
            default:
                $path = implode(DS, $path);

                $XMLFile[] = COMP_PATH . 'core' . DS . $path . '.xml';
                $XMLFile[] = COMP_PATH . 'community' . DS . $path . '.xml';

                foreach ($XMLFile as $idx => $file) {
                    if (!file_exists($file)) {
                        unset($XMLFile[$idx]);
                    }
                }
        }

        if (count($XMLFile) < 1) {
            OSC::core('output')->error(OSC::core('language')->get('core.err_setting_construct_file_not_exist'), array('key' => $sett_key));
        }

        return $XMLFile;
    }

}
