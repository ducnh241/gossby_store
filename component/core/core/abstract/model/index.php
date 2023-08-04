<?php

class Abstract_Core_Model_Index extends Abstract_Core_Model {

    protected $_pk_field = 'index_id';
    protected $_multi_lang = true;
    protected $_allow_empty_lang = true;

    /**
     * 
     * @param string $module_key
     * @param string $extra_key
     * @param mixed $lang_key
     * @return string
     */
    public function generateUkey($module_key, $item_group, $item_id, $lang_key = null) {
        return ($lang_key ? ($lang_key . ':') : '') . $module_key . '/' . $item_group . '-' . $item_id;
    }

    public function processLangKey($lang_key) {
        if (is_bool($lang_key)) {
            if (!$lang_key) {
                $lang_key = '';
            } else {
                $lang_key = OSC::core('language')->current_lang_key;
            }
        } else {
            $lang_key = (string) $lang_key;
            $lang_key = trim($lang_key);

            if ($lang_key != '' && !isset(OSC::core('language')->lang_map[$lang_key])) {
                return false;
            }
        }

        return $lang_key;
    }

    protected function _beforeSave() {
        if (!parent::_beforeSave()) {
            return false;
        }

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['module_key'])) {
            $data['module_key'] = trim($data['module_key']);

            if (!$data['module_key']) {
                $errors[] = 'Module key is empty';
            } else {
                $module_info = OSC::getModuleInfo($data['module_key']);

                if (!isset($module_info[$data['module_key']])) {
                    $errors[] = 'Module [' . $data['module_key'] . '] is not exists';
                }
            }
        }

        if (isset($data['item_group'])) {
            $data['item_group'] = preg_replace('/[^a-zA-Z0-9]/', '_', $data['item_group']);
            $data['item_group'] = preg_replace('/[^a-zA-Z0-9]{2,}/', '_', $data['item_group']);
            $data['item_group'] = trim($data['item_group']);

            if (!$data['item_group']) {
                $errors[] = 'Index item group is empty';
            }
        }

        if (isset($data['item_id'])) {
            $data['item_id'] = intval($data['item_id']);

            if ($data['item_id'] < 1) {
                $errors[] = 'Index item ID is empty';
            }
        }

        if (isset($data['lang_key'])) {
            $data['lang_key'] = $this->processLangKey($data['lang_key']);

            if ($data['lang_key'] === false) {
                $errors[] = 'Index lang key is incorrect';
            }
        }

        if (isset($data['keywords'])) {
            $data['keywords'] = OSC::core('search')->cleanKeywords($data['keywords']);

            if (!$data['keywords']) {
                $errors[] = 'Index keywords is empty';
            }
        }

        if (isset($data['filter_data'])) {
            if (!is_array($data['filter_data'])) {
                $data['filter_data'] = array();
            }

            $buff = array();

            foreach ($data['filter_data'] as $k => $v) {
                $k = str_replace(array(':', ';'), '', $k);
                $v = str_replace(';', '', $v);

                if ($k !== '' || $v !== '') {
                    $buff[$k] = $v;
                }
            }

            $data['filter_data'] = $buff;
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {
                $require_fields = array(
                    'module_key' => 'Module key is empty',
                    'item_group' => 'Index item group is empty',
                    'item_id' => 'Index item ID is empty',
                    'keywords' => 'Index keywords is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'item_data' => array(),
                    'filter_data' => array(),
                    'lang_key' => '',
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['ukey'] = $this->generateUkey($data['module_key'], $data['item_group'], $data['item_id'], $data['lang_key']);
            } else {
                unset($data['ukey']);
                unset($data['lang_key']);
                unset($data['module_key']);
                unset($data['item_group']);
                unset($data['item_id']);

                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['item_data'])) {
            $data['item_data'] = OSC::encode($data['item_data']);
        }

        if (isset($data['filter_data'])) {
            if (count($data['filter_data']) > 0) {
                foreach ($data['filter_data'] as $k => $v) {
                    $data['filter_data'][$k] = $k . ':' . $v;
                }

                $data['filter_data'] = ';' . implode(';', $data['filter_data']) . ';';
            } else {
                $data['filter_data'] = '';
            }
        }
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['item_data'])) {
            $data['item_data'] = OSC::decode($data['item_data']);
        }

        if (isset($data['filter_data'])) {
            if ($data['filter_data'] != '') {
                $data['filter_data'] = preg_replace('/(^;+|;+$)/', '', $data['filter_data']);
                $data['filter_data'] = preg_replace('/;{2,}/', ';', $data['filter_data']);

                $buff = explode(';', $data['filter_data']);

                $data['filter_data'] = array();

                foreach ($buff as $v) {
                    $v = explode(':', $v, 2);
                    $data['filter_data'][$v[0]] = $v[1];
                }
            } else {
                $data['filter_data'] = array();
            }
        }
    }

}
