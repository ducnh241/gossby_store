<?php

class Model_CatalogItemCustomize_Design extends Abstract_Core_Model {

    protected $_table_name = 'catalog_item_customize_design';
    protected $_pk_field = 'record_id';
    protected $_ukey_field = 'ukey';

    /**
     *
     * @var Model_User_Member 
     */
    protected $_member_model = null;

    const STATE_KEYS = [
        1 => 'Pending',
        2 => 'Processing',
        3 => 'Completed'
    ];

    public function getStateKey() {
        switch ($this->data['state']) {
            case 1:
                return 'pending';
            case 2:
                return 'processing';
            case 3:
                return 'completed';
        }
    }

    public function getStateCodePending() {
        return 1;
    }

    public function getStateCodeProcessing() {
        return 2;
    }

    public function getStateCodeCompleted() {
        return 3;
    }

    public function isPending() {
        return $this->data['state'] == 1;
    }

    public function isProcessing() {
        return $this->data['state'] == 2;
    }

    public function isCompleted() {
        return $this->data['state'] == 3;
    }

    public function getDesignImageUrl() {
        $images = OSC::decode($this->data['design_image_url']);
        return OSC::core('aws_s3')->getStorageUrl($images[0]);
    }

    /**
     * 
     * @return Model_User_Member
     */
    public function getMember($reset = false) {
        if ($this->data['member_id'] > 0 && ($this->_member_model === null || $reset)) {
            $this->_member_model = static::getPreLoadedModel('user/member', $this->data['member_id']);
        }

        return $this->_member_model;
    }

    public function getMemberUsername() {
        return ($this->getMember() instanceof Model_User_Member) ? $this->getMember()->data['username'] : 'Unknown';
    }

    public function getProductImageUrl() {
        return OSC::core('aws_s3')->getStorageUrl($this->data['product_image_url']);
    }

    public function getProductType(){
        $product = OSC::model('catalog/product')->load($this->data['product_id']);
        return $product->data['product_type'];
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['customize_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['customize_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key]);
            }
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        foreach (['product_title' => 'Product title', 'customize_title' => 'Customize title', 'customize_info' => 'Customize info'] as $key => $name) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if (!$data[$key]) {
                    $errors[] = $name . ' is empty';
                }
            }
        }

        foreach (['product_id' => 'Product ID', 'customize_id' => 'Customize ID', 'order_id' => 'Order ID'] as $key => $name) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 1) {
                    $errors[] = $name . ' is empty';
                }
            }
        }

        foreach (['product_image_url' => 'Product image URL', 'design_image_url' => 'Design image URL'] as $key => $name) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if (!$data[$key]) {
                    $data[$key] = null;
                }
            }
        }

        if (isset($data['customize_data'])) {
            if (!is_array($data['customize_data'])) {
                $errors[] = 'Customize data is incorrect';
            }
        }

        if (isset($data['state'])) {
            $data['state'] = intval($data['state']);

            if (!isset(static::STATE_KEYS[$data['state']])) {
                $errors[] = 'State key is incorrect';
            }
        }

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                $data['member_id'] = null;
            }
        }

        if (isset($data['ukey'])) {
            $data['ukey'] = trim($data['ukey']);

            if (!preg_match('/^\d+_\d+_[a-zA-Z0-9]+$/', $data['ukey'])) {
                $errors[] = 'Design ukey is incorrect';
            }
        }

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 1) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'ukey' => 'Customize ukey is empty',
                    'order_id' => 'Order ID is empty',
                    'product_id' => 'Product ID is empty',
                    'product_title' => 'Product title is empty',
                    'product_image_url' => 'Product image URL is empty',
                    'customize_id' => 'Customize ID is empty',
                    'customize_title' => 'Customize title is empty',
                    'customize_data' => 'Customize data is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'design_image_url' => null,
                    'state' => 1,
                    'member_id' => null,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                unset($data['ukey']);
                unset($data['order_id']);
                unset($data['product_id']);
                unset($data['product_title']);
                unset($data['product_image_url']);
                unset($data['customize_id']);
                unset($data['customize_title']);
                unset($data['customize_data']);

                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
