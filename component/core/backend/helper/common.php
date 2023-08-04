<?php

class Helper_Backend_Common extends OSC_Object {

    protected function _indexGetModel($lang_key, $module_key, $item_group, $item_id) {
        /* @var $model Model_Backend_Index */
        $model = OSC::model('backend/index');

        $lang_key = $model->processLangKey($lang_key);

        if ($lang_key === false) {
            throw new Exception('Lang key is incorrect');
        }

        try {
            $model->loadByUKey($model->generateUkey($module_key, $item_group, $item_id, $lang_key));
        } catch (Exception $ex) {
            if ($ex->getCode() !== 404) {
                throw new Exception($ex->getMessage());
            }
        }

        return $model;
    }

    /**
     * 
     * @param mixed $lang_key
     * @param string $module_key
     * @param string $item_group
     * @param int $item_id
     * @param string $keywords
     * @param mixed $options index_data, filter_data
     * @return boolean
     */
    public function indexAdd($lang_key, $module_key, $item_group, $item_id, $keywords, $options = array()) {
        try {
            $model = $this->_indexGetModel($lang_key, $module_key, $item_group, $item_id);
        } catch (Exception $ex) {
            return false;
        }

        $default = array(
            'item_data' => array(),
            'filter_data' => array()
        );

        if (!is_array($options)) {
            $options = array();
        }

        foreach ($default as $k => $v) {
            if (!isset($options[$k])) {
                $options[$k] = $v;
            }
        }

        try {
            $model->setData(array(
                'lang_key' => $lang_key,
                'module_key' => $module_key,
                'item_group' => $item_group,
                'item_id' => $item_id,
                'item_data' => $options['item_data'],
                'filter_data' => $options['filter_data'],
                'keywords' => $keywords
            ))->save();
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * 
     * @param mixed $lang_key
     * @param string $module_key
     * @param string $item_group
     * @param int $item_id
     * @return boolean
     */
    public function indexDelete($lang_key, $module_key, $item_group, $item_id) {
        try {
            $model = $this->_indexGetModel($lang_key, $module_key, $item_group, $item_id);

            if ($model->getId() > 0) {
                $model->delete();
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    public function addCronLogs($data_insert) {
        try {
            if (!is_array($data_insert)
                || !isset($data_insert['content'])
                || !isset($data_insert['log_data'])
            ) {
                throw new Exception('data is incorrect'. OSC::encode($data_insert));
            }
            $data_insert['ip'] = '127.0.0.1';
            $data_insert['member_id'] = Model_User_Member::SYSTEM_MEMBER_ID;
            $data_insert['username'] = Model_User_Member::SYSTEM_MEMBER_USERNAME;

            OSC::model('backend/log')->setData($data_insert)->save();
        }catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
