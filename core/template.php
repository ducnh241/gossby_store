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
 * @package OSC_Template
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Template extends OSC_Object {

    /**
     *
     * @var string
     */
    protected $_base_url;

    /**
     *
     * @var string
     */
    protected $_tpl_base_path;

    /**
     *
     * @var string 
     */
    protected $_tpl_base_url;

    /**
     *
     * @var string 
     */
    protected $_js_base_url;

    /**
     *
     * @var string
     */
    protected $_img_base_url;

    /**
     *
     * @var string 
     */
    protected $_page_title = null;

    /**
     *
     * @var string
     */
    protected $_page_desc = null;

    /**
     *
     * @var array
     */
    protected $_builders = array();

    /**
     *
     * @var string
     */
    protected $_current_template = '';

    /**
     *
     * @var array 
     */
    protected $_resource = array();

    /**
     *
     * @var array
     */
    protected $_resource_map = array();

    /**
     *
     * @var string
     */
    protected $_conf_key = 'template';

    /**
     *
     * @var boolean 
     */
    protected $_breadcrumb_lock = false;

    /**
     *
     * @var array 
     */
    protected $_breadcrumbs = array();

    /**
     *
     * @var string
     */
    protected $_key = null;

    /**
     *
     * @var Mobile_Detect
     */
    protected $_device_detector = null;

    /**
     *
     * @var bool
     */
    protected $_is_mobile = null;

    /**
     *
     * @var bool
     */
    protected $_is_tablet = null;

    /**
     *
     * @var string
     */
    protected $_page_key = null;

    /**
     *
     * @var array
     */
    protected $_component_registry = array();
    protected $_page_metadata = array();
    protected $_build_mode = null;
    protected $_convert_instance_flag = true;
    protected $_using_combine = false;

    const MESSAGE_TYPE_NOTIFY = 'notify';
    const MESSAGE_TYPE_WARNING = 'warning';
    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_MESSAGE = 'message';
    const FUNC_BUILD_MODE_CREATE = 1;
    const FUNC_BUILD_MODE_ANONYMOUS = 2;

    public function __construct() {
        $this->_base_url = OSC::$base_url;

        $this->_setBase();

        $this->_img_base_url = $this->_tpl_base_url . '/images';

        $comp_load_default = OSC::systemRegistry('template_component_load_default');

        if (count($comp_load_default) > 0) {
            call_user_func_array(array($this, 'addComponent'), $comp_load_default);
        }

        $build_mode = OSC::TPL_BUILD_MODE;

        if (!$build_mode || !in_array($build_mode, array(static::FUNC_BUILD_MODE_ANONYMOUS, static::FUNC_BUILD_MODE_CREATE))) {
            $build_mode = version_compare(PHP_VERSION, '5.4.0') < 0 ? static::FUNC_BUILD_MODE_CREATE : static::FUNC_BUILD_MODE_ANONYMOUS;
        }

        $this->_build_mode = $build_mode;
        $this->_convert_instance_flag = $build_mode != static::FUNC_BUILD_MODE_ANONYMOUS || version_compare(PHP_VERSION, '5.4.0') < 0;
        $this->_key = md5($this->_tpl_base_path);

        if (OSC::TPL_USING_COMBINE) {
            $this->_using_combine = true;
        }
    }

    protected function _setBase() {
        $this->_tpl_base_path = OSC_RES_PATH . '/template/core';
        $this->_tpl_base_url = $this->_base_url . '/resource/template/core';
        $this->_js_base_url = $this->_base_url . '/resource/script';
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    protected function _getBuilderKey($path) {
        return str_replace('/', '_', strtolower($path));
    }

    protected function _buildFunctionAnonymousMode($key, $path, $tpl_path) {
        $cache_path = OSC_VAR_PATH . "/template/{$this->_key}/{$path}.tpl_cache." . filemtime($tpl_path) . ".php";

        if (!file_exists($cache_path)) {
            if (!OSC::writeToFile($cache_path, '<?php $this->_builders[$key] = function($tpl, $params) { ' . $this->_getFunctionCode($tpl_path) . ' }; ?>', array('chmod' => 0600))) {
                OSC::core('debug')->triggerError("Unable to write compiled template to disk [{$key}]");
            }
        }

        include $cache_path;
    }

    protected function _buildFunctionCreateMode($key, $path, $tpl_path) {
        $this->_builders[$key] = create_function('$tpl, $params', $this->_getFunctionCode($tpl_path)) or die(print_r(error_get_last(), 1));
    }

    protected function _getFunctionCode($tpl_path) {
        $code = trim(file_get_contents($tpl_path));

        $open_tag_counter = substr_count($code, '<?');

        if ($open_tag_counter > 0) {
            $close_tag_counter = substr_count($code, '?>');

            if ($open_tag_counter > $close_tag_counter) {
                $code .= '?>';
            }
        }

        $code = preg_replace('/<\?=\s*/', '<?php echo ', $code);
        $code = preg_replace('/([^;}])\s*\?>/', '\\1; ?>', $code);

        if (!preg_match('/^<\?(php)?/i', $code, $matches)) {
            $code = ' ?>' . $code;
        }

        if (!preg_match('/\?>$/i', $code, $matches)) {
            $code = $code . '<?php ';
        }

        $code = preg_replace('/^<\?(php)?|\?>$/i', '', $code);

        if ($this->_convert_instance_flag) {
            $code = preg_replace('/\$this->/i', '$tpl->', $code);
        }

        return $code;
    }

    /**
     * 
     * @param string $path
     * @param array $params
     * @param boolean $once
     * @return string
     * @throws OSC_Exception_Runtime
     */
    public function build($path, $params = array(), $once = false) {
        OSC::core('debug')->startProcess('OSC.Render', $path);

        $builders = & $this->_builders;

        $builder_key = $this->_getBuilderKey($path);

        if (!isset($builders[$builder_key])) {
            $tpl_path = $this->getPath($path);

            if (!file_exists($tpl_path)) {
                throw new OSC_Exception_Runtime("Can't load template file [{$path}]");
            }

            if ($this->_build_mode == static::FUNC_BUILD_MODE_ANONYMOUS) {
                $this->_buildFunctionAnonymousMode($builder_key, $path, $tpl_path);
            } else {
                $this->_buildFunctionCreateMode($builder_key, $path, $tpl_path);
            }
        } else if ($once === true) {
            OSC::core('debug')->endProcess('OSC.Render');
            return '';
        }

        $old_path = $this->registry('current_process_path');

        $this->register('current_process_path', $path);

        if (!is_callable($builders[$builder_key])) {
            OSC::core('debug')->triggerError("Unable to compile template file [{$builder_key}]");
        }

        ob_start();
        $builders[$builder_key]($this, $params);
        $HTML = ob_get_contents();
        ob_end_clean();

        $this->register('current_process_path', $old_path);

        OSC::core('debug')->endProcess('OSC.Render');

        return $HTML;
    }

    /**
     * 
     * @param string $path
     * @return boolean
     */
    public function chkPath($path) {
        $builder_key = $this->_getBuilderKey($path);

        if (!isset($this->_builders[$builder_key])) {
            if (!file_exists($this->getPath($path))) {
                return false;
            }
        }

        return true;
    }

    public function safeString($txt) {
        return OSC::safeString($txt);
    }

    /**
     * 
     * @param string $path
     * @return string
     */
    public function getPath($path) {
        return $this->_tpl_base_path . '/renderer/' . $path . '.php';
    }

    /**
     * 
     * @param string $path
     * @param boolean $get_relative
     * @return string
     */
    public function getFile($path, $get_relative = false) {
        return ($get_relative ? str_replace($this->_base_url . '/', '', $this->_tpl_base_url) : $this->_tpl_base_url) . '/' . $path;
    }

    /**
     * 
     * @param string $path
     * @param boolean $get_relative
     * @return string
     */
    public function getImage($path, $get_relative = false) {
        return $this->getFile('image/' . $path, $get_relative);
    }

    /**
     * 
     * @param array $additional_params
     * @return string
     */
    public function rebuildUrl($additional_params) {
        return OSC::rebuildUrl($additional_params);
    }

    /**
     * 
     * @return string
     */
    public function getCurrentUrl() {
        return OSC::getCurrentUrl();
    }

    /**
     * 
     * @param string $action
     * @param array $params
     * @param boolean $inc_hash
     * @return string
     */
    public function getUrl($action = null, $params = array(), $inc_hash = null) {
        return OSC::getUrl($action, $params, $inc_hash);
    }

    /**
     * 
     * @return OSC_Template
     */
    public function addComponent() {
        $component_depend_config = OSC::systemRegistry('template_component_depend');

        foreach (func_get_args() as $component_key) {
            $component_add_by = '___direct___';
            $component_key = strtolower($component_key);

            if (preg_match('/^([^:]+)\:(.+)$/', $component_key, $matches)) {
                $component_key = $matches[2];
                $component_add_by = $matches[1];
            }

            if (!isset($this->_component_registry[$component_key])) {
                $this->_component_registry[$component_key] = array();

                $depend_components = isset($component_depend_config[$component_key]) ? $component_depend_config[$component_key] : array();

                foreach ($depend_components as $depend_component_key => $value) {
                    $this->addComponent($component_key . ':' . $depend_component_key);
                }
            }

            $this->_component_registry[$component_key][] = $component_add_by;
        }

        return $this;
    }

    /**
     * 
     * @return OSC_Template
     */
    public function removeComponent() {
        $removed_keys = array();

        foreach (func_get_args() as $component_key) {
            $component_key = strtolower($component_key);

            if (isset($this->_component_registry[$component_key])) {
                $removed_keys[] = $component_key;
                unset($this->_component_registry[$component_key]);
            }
        }

        $removed_keys = array_unique($removed_keys);

        foreach ($this->_component_registry as $comp_key => $data) {
            $this->_component_registry[$comp_key] = array_diff($this->_component_registry[$comp_key], $removed_keys);

            if (count($this->_component_registry[$comp_key]) < 1) {
                unset($this->_component_registry[$comp_key]);
            }
        }

        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getComponents() {
        $component_config = OSC::systemRegistry('template_component');

        $components = '';

        foreach ($this->_component_registry as $component_key => $value) {
            if (isset($component_config[$component_key])) {
                $components .= $this->_buildComponent($component_config[$component_key]);
            }
        }

        return $components;
    }

    /**
     * 
     * @param array $component_config
     * @return string
     */
    protected function _buildComponent($component_config) {
        $component_html = '';

        foreach ($component_config['template'] as $template_path => $template_params) {
            if (preg_match('/^\[([^\[\]]+)\](.+)$/', $template_path, $matches)) {
                $template = $matches[1] == 'core' ? OSC::core('template') : OSC::helper($matches[1]);
                $template_path = $matches[2];
            } else {
                $template = OSC::template();
            }

            $component_html .= $template->build($template_path, $template_params);
        }

        unset($component_config['template']);

        foreach ($component_config as $res_type => $res_rows) {
            foreach ($res_rows as $path => $priority) {
                $this->push($path, $res_type, $priority);
            }
        }

        return $component_html;
    }

    /**
     * 
     * @param string $engine_key
     * @return OSC_Template
     */
    protected function _getTemplateEngine($engine_key) {
        if ($engine_key == '*') {
            return OSC::controller()->getTemplate();
        }

        return OSC::obj($engine_key, OSC::SINGLETON);
    }

    public static function getScssCompiler() {
        static $compiler = null;

        if ($compiler === null) {
            $compiler = new ScssPhp\ScssPhp\Compiler();
            $compiler->setFormatter(OSC_ENV == 'production' ? 'ScssPhp\ScssPhp\Formatter\Compressed' : 'ScssPhp\ScssPhp\Formatter\Expanded');
        }

        return $compiler;
    }

    protected function _processScss($file) {
        $scss_file_path = str_replace(OSC::$base_url, OSC_ROOT_PATH, $file);
        $css_file_path = str_replace(OSC::$base_url, OSC_VAR_PATH . '/scss', preg_replace('/\.(scss|sass)$/i', '.css', $file));

        if (!file_exists($scss_file_path)) {
            return $file;
        }

        if (!file_exists($css_file_path) || filemtime($scss_file_path) != filemtime($css_file_path)) {
            $css_data = static::compileScss($scss_file_path, $css_file_path . '.map');

            OSC::writeToFile($css_file_path . '.dev.css', $css_data);
            OSC::writeToFile($css_file_path, static::removeScssMap($css_data));

            touch($css_file_path, filemtime($scss_file_path));
        }

        return str_replace(OSC_VAR_PATH, OSC::$base_url . str_replace(OSC_SITE_PATH, '', OSC_VAR_PATH), $css_file_path . (isset($_REQUEST['test_scss']) ? '.dev.css' : ''));
    }

    public static function removeScssMap($css_data) {
        return preg_replace('/\/\*#\s*sourceMappingURL\s*=.+?\*\//i', '', $css_data);
    }

    public static function compileScss($scss_file_path, $map_file_path = null) {
        $scss_dir_path = dirname($scss_file_path);

        $compiler = static::getScssCompiler();
        $compiler->setImportPaths($scss_dir_path);

        if ($map_file_path) {
            $compiler->setSourceMap(ScssPhp\ScssPhp\Compiler::SOURCE_MAP_FILE);
            $compiler->setSourceMapOptions(array(
                'sourceMapWriteTo' => $map_file_path,
                'sourceMapURL' => str_replace(dirname($map_file_path) . '/', '', $map_file_path)
            ));

            OSC::makeDir(dirname($map_file_path));
        }

        $css_data = $compiler->compile(file_get_contents($scss_file_path));
        $css_data = static::_rewriteScssParsedContentUrl($css_data, $scss_dir_path);

        if ($map_file_path) {
            $map_content = file_get_contents($map_file_path);
            $map_content = preg_replace('/([\'"]sources[\'"]:\[[\'"])\(stdin\)([\'"]\])/', '\\1' . str_replace(OSC_ROOT_PATH, '', $scss_file_path) . '\\2', $map_content);
            OSC::writeToFile($map_file_path, $map_content);
        }

        return $css_data;
    }

    protected static function _rewriteScssParsedContentUrl($css_data, $scss_dir_path) {
        $scss_dir_url = str_replace(OSC_ROOT_PATH, OSC::$base_url, $scss_dir_path);

        $css_data = preg_replace_callback('/(url[^\S\n]*\([^\S\n]*[\'"]?)(?!(https?:|\/))([^\n\)\s\'"]+)([\'"]?[^\S\n]*\))/i', function($matches) use ($scss_dir_url) {
            $url = $scss_dir_url . '/' . $matches[3];

            /* A Sang bao khong dung CDN */
            /*if (!preg_match('/\.(woff|woff2|svg|ttf|otf)$/i', $url)) {
                $url = OSC::wrapCDN($url);
            }*/

            return $matches[1] . $url . $matches[4];
        }, $css_data);

        return $css_data;
    }

    public function importResource($type, $get_array_flag = false) {
        if (substr($type, -1) == '*') {
            $prefix = substr($type, 0, -1);

            $data = [];

            foreach (array_keys($this->_resource) as $_type) {
                if (strpos($_type, $prefix) === 0) {
                    $data[] = $this->importResource($_type, $get_array_flag);
                }
            }

            return $get_array_flag ? $data : implode("\n", $data);
        }

        $files = $this->_resource[$type];

        unset($this->_resource[$type]);

        if (!is_array($files)) {
            return $get_array_flag ? array() : '';
        }

        $res_type = explode('/', $type, 2);
        $res_type = $res_type[0];


        $resource_base_path = OSC::controller()->getTemplate()->tpl_base_url;
        $resource_folder = ($res_type == 'js' ? 'script' : 'style');

        $priority_groups = array();

        foreach ($files as $file => $priority) {
            if (!isset($priority_groups[$priority])) {
                $priority_groups[$priority] = array();
            }

            $priority_groups[$priority][] = $file;
        }

        krsort($priority_groups, SORT_NUMERIC);

        $files = array();

        foreach ($priority_groups as $group_files) {
            foreach ($group_files as $file) {
                $files[] = $file;
            }
        }

        $last_mtime = 0;
        $combine_files = [];

        foreach ($files as $k => $file) {
            if (preg_match('/^\/{2}[^\/]+\.[^\/\.]+(\/+.*)?$/', $file)) {
                $file = 'https:' . $file;
            } else if (!preg_match('/^(http|ftp|https):/i', $file)) {
                if (preg_match('/^\/+([^\/].*)/', $file, $matches)) {
                    $file = OSC::$base_url . '/resource/' . $matches[1];
                } else {
                    if (preg_match('/^\[([^\[\]]+)\](.+)$/', $file, $matches)) {
                        $file = ($matches[1] == 'core' ? OSC::core('template')->tpl_base_url : OSC::helper($matches[1])->tpl_base_url) . '/' . $resource_folder . '/' . $matches[2];
                    } else {
                        $file = $resource_base_path . '/' . $resource_folder . '/' . $file;
                    }
                }

                if ($res_type == 'css' && preg_match('/\.(scss|sass)$/i', $file, $matches)) {
                    $file = $this->_processScss($file);
                }

                if (OSC_ENV != 'production') {
                    $file .= (strpos($file, '?') === false ? '?' : '&') . 'v=' . time();
                }
            }

            $file = preg_replace('/(^|[^:])\/{2,}/', '\\1/', $file);

            if (OSC_ENV == 'production' && substr($file, 0, strlen(OSC::$base_url)) == OSC::$base_url) {
                $file_path = substr($file, strlen(OSC::$base_url));

                if (substr($file_path, 0, 5) === '/var/') {
                    $file_path = OSC_VAR_PATH . substr($file_path, 4);
                } else {
                    $file_path = OSC_ROOT_PATH . $file_path;
                }

                if (file_exists($file_path)) {
                    unset($files[$k]);

                    $mtime = filemtime($file_path);

                    if ($mtime > $last_mtime) {
                        $last_mtime = $mtime;
                    }

                    $combine_files[] = $file_path;

                    continue;
                }
            }

            $files[$k] = $file;
        }

        if ($last_mtime > 0 && ! isset($_REQUEST['test_cdn'])) {
            $combined_file = '/cache/' . $res_type . '/' . md5(OSC::encode($combine_files)) . '.' . $last_mtime . '.' . $res_type;

            if (!file_exists(OSC_VAR_PATH . $combined_file)) {
                $minifier = $res_type == 'css' ? (new \MatthiasMullie\Minify\CSS()) : (new \MatthiasMullie\Minify\JS());
                $minifier->add($combine_files);
                OSC::writeToFile(OSC_VAR_PATH . $combined_file, $minifier->minify(), ['chmod' => 0644]);
            }

            $files[] = OSC::$base_url . '/var' . $combined_file;
        }

        /* A Sang bao khong dung CDN */
        /*$files = OSC::wrapCDN($files);*/

        if ($get_array_flag) {
            return $files;
        }

        switch ($res_type) {
            case 'css':
                foreach ($files as $idx => $file) {
                    $files[$idx] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$file}\" />";
                }
                break;
            case 'js':
                foreach ($files as $idx => $file) {
                    $files[$idx] = "<script src=\"{$file}\"></script>";
                }
                break;
            default:
                return '';
        }

        return implode("\r\n", $files);
    }

    /**
     * 
     * @param array $resources
     * @param string $type
     * @param integer $priority
     * @return OSC_Template
     */
    public function push($resources, $type, $priority = false) {
        $type = strtolower($type);

        if (!is_array($resources)) {
            $resources = array($resources);
        }

        if (count($resources) < 1) {
            return $this;
        }

        if (!isset($this->_resource[$type])) {
            $this->_resource[$type] = array();
        }

        if (!is_int($priority)) {
            $priority = 0;
        }

        $_res = & $this->_resource[$type];

        foreach ($resources as $resource) {
            $file_priority = $priority;

            if (preg_match('/^(-?\d+)\:((\/+)?[^\/:\\\][^:]+)$/', $resource, $matches)) {
                $file_priority = intval($matches[1]);
                $resource = $matches[2];
            }

            $_res[$resource] = $file_priority;
        }

        return $this;
    }

    /**
     * 
     * @param string $key
     * @return $this
     */
    public function setPageKey(string $key) {
        $this->_page_key = $key;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getPageKey() {
        return $this->_page_key;
    }

    /**
     * 
     * @param string $title
     * @return OSC_Template
     */
    public function setPageTitle($title) {
        $this->_page_title = $title;
        return $this;
    }

    /**
     * 
     * @param string $desc
     * @return OSC_Template
     */
    public function setPageDesc($desc) {
        $this->_page_desc = $desc;
        return $this;
    }

    /**
     * 
     * @param string $image_url
     * @return \OSC_Template
     */
    public function setPageImage($image_url) {
        $this->addPageMetadata($image_url, 'og:image');
        $this->addPageMetadata($image_url, 'og:image:url');
        return $this;
    }

    /**
     *
     * @return \OSC_Template
     */
    public function setTwitterCard() {
        $this->addPageMetadata('summary', 'twitter:card');
        $this->addPageMetadata('@' . $this->setting('theme/site_name'), 'twitter:site"');
        $this->addPageMetadata('@' . $this->setting('theme/site_name'), 'twitter:creator');
        return $this;
    }

    /**
     * 
     * @param mixed $object
     * @return \OSC_Template
     */
    public function setSeoMetadata($object) {
        $metadata_keys = array('keywords' => 'seo_keywords', 'description' => 'seo_description', 'og:title' => 'seo_title', 'og:description' => 'seo_description');

        $data = array();

        if (is_object($object) && isset($object->data) && is_array($object->data)) {
            $data = $object->data;
        } else if (is_array($object)) {
            $data = $object;
        } else {
            return $this;
        }

        foreach ($metadata_keys as $metadata_key => $model_data_key) {
            if (isset($data[$model_data_key])) {
                $this->addPageMetadata($data[$model_data_key], $metadata_key);
            }
        }

        return $this;
    }

    /**
     * 
     * @param string $metadata
     * @param string $key
     * @return OSC_Template
     */
    public function addPageMetadatas() {
        if (func_num_args() > 0) {
            $this->_page_metadata = array_merge($this->_page_metadata, func_get_args());
        }

        return $this;
    }

    /**
     * 
     * @param string $metadata
     * @param string $key
     * @return OSC_Template
     */
    public function addPageMetadata($metadata, $key = null) {
        $metadata = trim($metadata);

        if (!$metadata) {
            return $this;
        }

        if (is_string($key)) {
            $this->_page_metadata[$key] = $metadata;
        } else {
            $this->_page_metadata[] = $metadata;
        }

        return $this;
    }

    /**
     * 
     * @param string $metadata
     * @return OSC_Template
     */
    public function removePageMetadata($metadata) {
        $key = array_search($metadata, $this->_page_metadata);

        if ($key !== false) {
            unset($this->_page_metadata[$key]);
        }

        return $this;
    }

    /**
     * 
     * @param string $metadata
     * @return OSC_Template
     */
    public function removePageMetadataByKey($key) {
        if (isset($this->_page_metadata[$key])) {
            unset($this->_page_metadata[$key]);
        }

        return $this;
    }

    /**
     * 
     * @param string $title
     * @param string $link
     * @param string $method
     * @return OSC_Template
     */
    public function addBreadcrumb($title, $link = false) {
        $this->_breadcrumbs[] = array($title, $link ? $link : OSC::core('request')->getUrl());
        return $this;
    }

    /**
     * 
     * @return OSC_Template
     */
    public function resetBreadcrumb() {
        $this->_breadcrumbs = array();
        return $this;
    }

    /**
     * 
     * @param int $slice
     * @return array
     */
    public function collectBreadcrumbs($slice = 0) {
        if ($this->_breadcrumb_lock) {
            return array();
        }

        $items = $this->_breadcrumbs;

        $this->_breadcrumb_lock = true;

        $total_item = count($items);

        if ($total_item < 1) {
            return array();
        }

        $slice = intval($slice);

        if ($slice > 0) {
            if ($slice < 3) {
                $slice = 3;
            } else if ($slice % 2 == 0) {
                $slice++;
            }

            if ($total_item > $slice) {
                array_splice($items, floor($slice / 2), $total_item - $slice, '...');
            }
        }

        return $items;
    }

    /**
     * 
     * @param string $message
     * @param string $type
     * @param string $identify
     * @return OSC_Template
     */
    public function addMessage($message, $type = null) {
        if (!$type) {
            $type = static::MESSAGE_TYPE_NOTIFY;
        }

        $session_key = strtolower(get_class()) . '_message';

        $message_arr = OSC::sessionGet($session_key);

        if (!is_array($message_arr)) {
            $message_arr = array();
        }

        if (!isset($message_arr[$type])) {
            $message_arr[$type] = array();
        }

        if (!is_array($message)) {
            $message = array($message);
        }

        $message_arr[$type] = array_merge($message_arr[$type], $message);

        OSC::sessionSet($session_key, $message_arr);

        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getMessage() {
        $session_key = strtolower(get_class()) . '_message';

        $message_arr = OSC::sessionGet($session_key);

        if (!is_array($message_arr)) {
            return '';
        }

        $messages = array();

        foreach ($message_arr as $type => $_messages) {
            if (!is_array($_messages) || count($_messages) < 1) {
                continue;
            }

            $messages[] = $this->build('core/message/' . $type, array('messages' => $_messages));
        }

        $messages = $this->build('core/message', array('messages' => $messages));

        OSC::sessionSet($session_key, null);

        return $messages;
    }

    /**
     * 
     * @param int $cur_page
     * @param int $total_page
     * @param array $opts request_params, page_key, section, data_builder, template
     * @return string
     */
    public function pager($cur_page, $total_page, $opts = array()) {
        $cur_page = $cur_page >= 1 ? $cur_page : 1;

        if ($total_page < 2) {
            return '';
        }

        if (!is_array($opts)) {
            $opts = array();
        }

        $section = isset($opts['section']) && $opts['section'] > 0 ? intval($opts['section']) : 7; //$this->core->sett->vars['global']['page_section'];
        $params = isset($opts['request_params']) && is_array($opts['request_params']) ? $opts['request_params'] : array();
        $page_key = isset($opts['page_key']) ? $opts['page_key'] : 'page';
        $data_builder = isset($opts['data_builder']) && is_callable($opts['data_builder'], false) ? $opts['data_builder'] : array($this, '_buildPageData');
        $template = isset($opts['template']) ? $opts['template'] : 'core/pager';

        $pager = array(
            'params' => $params,
            'page_key' => $page_key,
            'total_page' => $total_page,
            'cur_page' => $cur_page,
            'pages' => array()
        );

        $start = $cur_page - $section;

        if ($start <= 1) {
            $start = 1;
        } else if ($start > $total_page) {
            $start = $total_page;
        } else if ($total_page > $section * 2 + 1) {
            $pager['first'] = $data_builder($page_key, 1, $params);
        }

        $end = $start + $section * 2;

        if ($end >= $total_page) {
            $end = $total_page;

            $start = $end - $section * 2;

            if ($start < 1) {
                $start = 1;
            }
        } else {
            $pager['last'] = $data_builder($page_key, $total_page, $params);
        }

        for ($p = $start; $p <= $end; $p++) {
            $pager['pages'][$p] = $data_builder($page_key, $p, $params);
        }

        $next = $start - $section - 1;
        $previous = $end + $section + 1;

        if ($next > ($section + 1)) {
            $pager['previous'] = $data_builder($page_key, $next, $params);
        }

        if ($previous < ($total_page - $section)) {
            $pager['next'] = $data_builder($page_key, $previous, $params);
        }

        if ($total_page > ($section * 2 + 1)) {
            //$jump = $this->core->build_template( 'jump', 'page', array('total' => str_replace( '%n', $total_page, $this->core->lang['l_total_page'] ), 'url'   => $params['request'], 'id'    => $params['id'] ) );
        }

        return $this->build($template, $pager);
    }

    /**
     * 
     * @param type $cur_page
     * @param type $total
     * @param type $page_size
     * @param array $opts request_params, page_key, section, data_builder, template
     * @return string
     */
    public function buildPager($cur_page, $total, $page_size, $opts = array()) {
        $page_size = $page_size > 0 ? $page_size : 20; //$this->core->sett->vars['global']['per_page'];
        return $this->pager($cur_page, ceil($total / $page_size), $opts);
    }

    /**
     * 
     * @param string $page_key
     * @param string $page
     * @param array $params
     * @return string
     */
    protected function _buildPageData($page_key, $page, $params) {
        $params[$page_key] = $page;
        $url = OSC_Core::getAbsoluteUrl(null, array_merge(static::core('request')->getAll('get'), $params) , null);
        $parseUrl = OSC::core('observer')->dispatchEvent('parse_full_url', $url, false);
        if($parseUrl) $url = $parseUrl;
        return [
            'url' => OSC_Core::$base_url . '/' . $url,
            'page' => $page
        ];
    }

    /**
     * 
     * @return Mobile_Detect
     */
    public function getDeviceDetector() {
        if ($this->_device_detector === null) {
            $this->_device_detector = new Mobile_Detect();
        }

        return $this->_device_detector;
    }

    /**
     * 
     * @return bool
     */
    public function isMobile() {
        if ($this->_is_mobile === null) {
            $this->_is_mobile = $this->getDeviceDetector()->isMobile();
        }

        return $this->_is_mobile;
    }

    /**
     * 
     * @return bool
     */
    public function isTablet() {
        if ($this->_is_tablet === null) {
            $this->_is_tablet = $this->getDeviceDetector()->isTablet();
        }

        return $this->_is_tablet;
    }

    public function isAjax() {
        return OSC::core('request')->isAjax();
    }

    /**
     * 
     * @param string $classes
     * @return OSC_Template
     */
    public function addBodyClass($classes) {
        $body_classes = OSC::core('template')->registry('_BODY_CLASSES');

        if (!is_array($body_classes)) {
            $body_classes = array();
        }

        $body_classes[] = (string) $classes;

        OSC::core('template')->register('_BODY_CLASSES', $body_classes);

        return $this;
    }

    /**
     * 
     * @param string $component_key
     * @param array .....
     * 
     * @example 
     * 
     * OSC_Template::registerComponent('comp_key', array('type' => 'js', 'data' => 'js_path'));
     * OSC_Template::registerComponent(array('key' => 'comp_key', 'default' => true), array('type' => 'css', 'data' => 'css_path'));
     * OSC_Template::registerComponent(array('key' => 'comp_key', 'depends' => 'comp_key1'), array('type' => 'css', 'data' => 'css_path'));
     * OSC_Template::registerComponent(array('key' => 'comp_key', 'depends' => 'comp_key1,comp_key2'), array(type' => 'css', 'data' => 'css_path'));
     * OSC_Template::registerComponent('comp_key', array('type' => 'template', 'data' => 'template_path'));
     * OSC_Template::registerComponent(
     *      'comp_key',
     *      array(
     *          'type' => 'template',
     *          'data' => array(
     *              'path' => 'template_path',
     *              'params' => array(),
     *          )
     *      )
     * );
     * OSC_Template::registerComponent(
     *      'comp_key',
     *      array(
     *          'type' => 'template',
     *          'data' => array(
     *              array(
     *                  'path' => 'template_path',
     *                  'params' => array(),
     *              ),
     *              array(
     *                  'path' => 'template_path',
     *                  'params' => array(),
     *              )
     *          )
     *      )
     * );
     * OSC_Template::registerComponent('comp_key', array('type' => 'js', 'data' => 'js_path'), array('type' => 'css', 'data' => 'css_path'));
     * 
     * @return boolean
     */
    public static function registerComponent($component_key) {
        $total_arg = func_num_args();

        if ($total_arg < 1) {
            return false;
        }

        $depend_components = array();
        $load_default = false;

        if (is_array($component_key)) {
            if (!isset($component_key['key'])) {
                return false;
            }

            if (isset($component_key['depends'])) {
                if (!is_array($component_key['depends'])) {
                    $component_key['depends'] = (string) $component_key['depends'];
                    $component_key['depends'] = explode(',', $component_key['depends']);
                }

                foreach ($component_key['depends'] as $depend_component_key) {
                    $depend_component_key = (string) $depend_component_key;

                    $depend_component_key = trim($depend_component_key);

                    if ($depend_component_key) {
                        $depend_components[strtolower($depend_component_key)] = 1;
                    }
                }
            }

            if (isset($component_key['default'])) {
                $load_default = $component_key['default'] ? true : false;
            }

            $component_key = $component_key['key'];
        }

        $component_key = strtolower($component_key);

        $component_config = OSC::systemRegistry('template_component');
        $component_depend_config = OSC::systemRegistry('template_component_depend');

        if (!isset($component_config)) {
            $component_config = array();
        }

        if (!isset($component_depend_config)) {
            $component_depend_config = array();
        }

        $resources = [];

        if ($total_arg > 1) {
            for ($arg_idx = 1; $arg_idx < $total_arg; $arg_idx++) {
                $resource = func_get_arg($arg_idx);

                if (!is_array($resource) || !isset($resource['type']) || !isset($resource['data'])) {
                    continue;
                }

                $resource['type'] = strtolower($resource['type']);

                if ($resource['type'] == 'template') {
                    if (!is_array($resource['data']) || isset($resource['data']['path'])) {
                        $resource['data'] = array($resource['data']);
                    }

                    foreach ($resource['data'] as $template_data) {
                        if (!is_array($template_data)) {
                            $template_data = array('path' => $template_data);
                        }

                        if (!isset($template_data['path']) || !is_string($template_data['path'])) {
                            continue;
                        }

                        if (!isset($template_data['params'])) {
                            $template_data['params'] = array();
                        }

                        if (isset($resources['template'][$template_data['path']])) {
                            $resources['template'][$template_data['path']] = array_merge($resources['template'][$template_data['path']], $template_data['params']);
                            continue;
                        }

                        $resources['template'][$template_data['path']] = $template_data['params'];
                    }
                } else if ($resource['type'] == 'js' || $resource['type'] == 'css') {
                    if (!is_array($resource['data'])) {
                        $resource['data'] = array($resource['data']);
                    }

                    foreach ($resource['data'] as $file) {
                        if (!is_string($file)) {
                            continue;
                        }

                        $file = trim($file);

                        $priority = false;

                        if (preg_match('/^((-?\d+)\:)?((\/+)?[^\/:\\\][^:]+)$/', $file, $matches)) {
                            $priority = $matches[2];
                            $file = $matches[3];
                        }

                        $file = trim($file);

                        if (!$file) {
                            continue;
                        }

                        $resources[$resource['type']][$file] = $priority !== false ? intval($priority) : false;
                    }
                }
            }
        }

        if (!isset($component_config[$component_key])) {
            $component_config[$component_key] = $resources;
        } else {
            foreach ($resources as $type => $type_resources) {
                if (!isset($component_config[$component_key][$type])) {
                    $component_config[$component_key][$type] = array();
                }

                if ($type == 'template') {
                    foreach ($type_resources as $template_path => $template_params) {
                        if (isset($component_config[$component_key][$type][$template_path])) {
                            $component_config[$component_key][$type][$template_path] = array_merge($component_config[$component_key][$type][$template_path], $template_params);
                            continue;
                        }

                        $component_config[$component_key][$type][$template_path] = $template_params;
                    }
                } else {
                    $component_config[$component_key][$type] = array_merge($component_config[$component_key][$type], $type_resources);
                }
            }
        }

        if (!isset($component_depend_config[$component_key])) {
            $component_depend_config[$component_key] = $depend_components;
        } else {
            $component_depend_config[$component_key] = array_merge($component_depend_config[$component_key], $depend_components);
        }

        OSC::systemRegister('template_component', $component_config);
        OSC::systemRegister('template_component_depend', $component_depend_config);

        if ($load_default) {
            $load_default_component = OSC::systemRegistry('template_component_load_default');

            if (!is_array($load_default_component)) {
                $load_default_component = array();
            }

            if (!in_array($component_key, $load_default_component)) {
                $load_default_component[] = $component_key;
                OSC::systemRegister('template_component_load_default', $load_default_component);
            }
        }

        return true;
    }

    public function getJSONTag($json, $key, $attrs = []) {
        $json = OSC::encode($json);

        if (is_array($attrs) && count($attrs) > 0) {
            unset($attrs['type']);
            unset($attrs['data-json']);

            foreach ($attrs as $key => $value) {
                $attrs[$key] = $key . '="' . $this->safeString($value) . '"';
            }

            $attrs = ' ' . implode(' ', $attrs);
        } else {
            $attrs = '';
        }

        return <<<EOF
<script data-json="{$key}" type="application/json"{$attrs}>{$json}</script>
EOF;
    }

    /**
     * 
     * @param string $name
     * @param array $opts color, width, height, attributes, style
     * @return string
     */
    public function getIcon($name, $opts = array()) {
        if (!is_array($opts)) {
            $opts = array();
        }

        if (!isset($opts['attributes']) || !is_array($opts['attributes'])) {
            $opts['attributes'] = array();
        }

        if (isset($opts['class'])) {
            if (!isset($opts['attributes']['class'])) {
                $opts['attributes']['class'] = $opts['class'];
            } else {
                $opts['attributes']['class'] .= ' ' . $opts['class'];
            }
        }

        if (!isset($opts['style']) || !is_array($opts['style'])) {
            $opts['style'] = array();
        }

        if (isset($opts['color'])) {
            $opts['style']['color'] = $opts['color'];
        }

        if (isset($opts['width'])) {
            $opts['style']['width'] = intval($opts['width']) . 'px';
        }

        if (isset($opts['height'])) {
            $opts['style']['height'] = intval($opts['height']) . 'px';
        }

        if (count($opts['style']) > 0) {
            foreach ($opts['style'] as $k => $v) {
                $opts['style'][$k] = $k . ':' . $v;
            }

            $opts['attributes']['style'] = implode(';', $opts['style']);
        }

        if (isset($opts['attributes']['data-insert-cb'])) {
            $opts['attributes']['data-insert-cb'] = $opts['attributes']['data-insert-cb'] . ',configOSCIcon';
        } else {
            $opts['attributes']['data-insert-cb'] = 'configOSCIcon';
        }

        if (count($opts['attributes']) > 0) {
            foreach ($opts['attributes'] as $k => $v) {
                $opts['attributes'][$k] = $k . '="' . $this->safeString($v) . '"';
            }
        }

        $opts['attributes'] = implode(' ', $opts['attributes']);

        return <<<EOF
<svg data-icon="osc-{$name}" viewBox="0 0 512 512" {$opts['attributes']}><use xlink:href="#osc-{$name}"></use></svg>
EOF;
    }

}
