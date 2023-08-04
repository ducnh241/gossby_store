<?php

class Helper_Catalog_Campaign_Mockup_Command extends OSC_Object {

    public function parse($command_config, $params) {
        if(! isset($command_config['params'])) {
            $command_config['params'] = [];
        }

        $command_config['params'] = $this->_replaceMapping($command_config['params'], $this->_getMappingData($params));

        foreach($command_config['params'] as $param_builder) {
            $func_params = isset($param_builder['params']) && is_array($param_builder['params']) ? $param_builder['params'] : [];

            array_unshift($func_params, $params);

            $_params = call_user_func_array([OSC::helper($param_builder['helper']), $param_builder['function']], $func_params);
            $params = array_merge($params, $_params);
        }

        $mapping = $this->_getMappingData($params);

        foreach ($command_config['commands'] as $idx => $command) {

            $command = $this->_replaceMapping($command, $mapping);

            if($command['type'] == 'helper') {
                $command = call_user_func_array(OSC::helper($command['helper']), $command['function'], isset($command['params']) && is_array($command['params']) ? $command['params'] : []);
            }

            $command_config['commands'][$idx] = $command;
        }

        return $command_config['commands'];
    }

    protected function _getMappingData($mapping) {
        $mapping_data = ['search' => [], 'replace' => []];

        foreach ($mapping as $k => $v) {
            $mapping_data['search'][] = '{map:' . $k . '}';
            $mapping_data['replace'][] = $v;
        }

        return $mapping_data;
    }

    protected function _replaceMapping($data, $mapping) {
        if (is_string($data)) {
            $data = str_replace($mapping['search'], $mapping['replace'], $data);
        } else if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->_replaceMapping($v, $mapping);
            }
        }

        return $data;
    }

    protected function _paramsBuilder_templateImagePath($params, $param_key, $image_file_path) {
        $url = OSC_CMS_BASE_URL;
        $image_core = 'resource/template/core/image';

        return [
            $param_key => $url .'/' . $image_core .'/'. $image_file_path,
            "{$param_key}_mtime" => preg_replace('/^(.+)\.([a-z0-9]+)$/i', "\\1." . filemtime(OSC_ROOT_PATH . '/' . $image_core . '/' . $image_file_path) . ".\\2", $image_file_path)
        ];
    }

    protected function _paramsBuilder_templateTxtPath($params, $param_key, $image_file_path) {
        $url = OSC_CMS_BASE_URL;
        $image_core = 'resource/template/core/image';

        return [
            $param_key => $url .'/' . $image_core .'/'. $image_file_path,
            "{$param_key}_mtime" => preg_replace('/^(.+)\.([a-z0-9]+)$/i', "\\1." . filemtime(OSC_ROOT_PATH . '/' . $image_core . '/' . $image_file_path) . ".\\2", $image_file_path),
            "{$param_key}_md5" => md5_file($url .'/' . $image_core .'/'. $image_file_path)
        ];
    }

    protected function _paramsBuilder_filePrefix($params, $param_key, $file_name) {
        return [$param_key => '{params:tmp_root_path}/' . md5($params['ukey']) . '.' . OSC::makeUniqid()];
    }

    protected function _paramsBuilder_calculateInsideBoxGeometry($params, $param_key, $bbox, $inside_box_width_param_key, $inside_box_height_param_key) {

        $width_ratio = $bbox['width']/$params[$inside_box_width_param_key];
        $height_ratio = $bbox['height']/$params[$inside_box_height_param_key];

        if($width_ratio > $height_ratio) {
            $height = $bbox['height'];
            $width = intval($params[$inside_box_width_param_key] * $height_ratio);
        } else {
            $width = $bbox['width'];
            $height = intval($params[$inside_box_height_param_key] * $width_ratio);
        }

        $x = $bbox['x'] + ($width - $bbox['width'])/2;
        $y = $bbox['y'] + ($height - $bbox['height'])/2;

        return [
            $param_key . '__width' => $width,
            $param_key . '__height' => $height,
            $param_key . '__x' => ($x > 0 ? '+' : '-') . abs($x),
            $param_key . '__y' => ($y > 0 ? '+' : '-') . abs($y)
        ];
    }

    protected function _paramsBuilder_multiplyOption($params,$param_key, $option_color) {
        if ($option_color == 'white') {
            $multiply =  '-compose Multiply ';
        }elsE{
            $multiply =  '';
        }

        return [$param_key => $multiply];
    }

    protected function _paramsBuilder_filePrefixColor($params, $param_key, $file_name) {
        return [$param_key => '{params:tmp_root_path}/' . md5($file_name)];
    }
}
