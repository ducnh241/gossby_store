<?php

class Controller_User_Backend_PermissionMask extends Abstract_User_Controller_Backend {

    /**
     *
     * @var Model_User_PermissionMask
     */
    protected $_model = null;

    public function __construct() {
        parent::__construct();

        $this->getTemplate()
            ->setCurrentMenuItemKey('user/permmask')
            ->setPageTitle(OSC::core('language')->get('usr.permmask_manage'));
    }

    /**
     * 
     * @param int $id
     * @return \Model_User_PermissionMask
     * @throws OSC_Exception_Condition
     */
    protected function _getModel($id = null) {
        if ($this->_model !== null) {
            return $this->_model;
        }

        $this->_model = OSC::model('user/permissionMask');

        if ($id === null) {
            $id = $this->_request->get('id');
        }

        $id = intval($id);

        if ($id < 1) {
            throw new Exception($this->_('core.err_data_incorrect'));
        }

        try {
            $this->_model->load($id);
        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                throw new Exception($this->_('usr.permmask_err_not_exists'));
            }

            throw new Exception($ex->getMessage());
        }

        return $this->_model;
    }

    public function actionIndex() {
        $this->forward('list');
    }

    public function actionList() {
        $this->checkPermission();

        $collection = OSC::model('user/permissionMask')->getCollection();

        $collection->sort('title', OSC_Database::ORDER_ASC)->load();

        $this->getTemplate()->addBreadcrumb(array('file-text', OSC::core('language')->get('usr.permmask_manage')));

        $this->output($this->getTemplate()->build('user/permission_mask/list', array('collection' => $collection)));
    }

    public function actionPost() {
        $this->checkPermission();

        $id = intval($this->_request->get('id'));
        $this->getTemplate()->addBreadcrumb($id > 0 ? array('file-text', 'Edit Permission Mask') : ['file-text', 'Create Permission Mask']);

        /* @var $model Model_User_PermissionMask */

        if ($id > 0) {
            try {
                $model = $this->_getModel($id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/list'));
            }
        } else {
            $model = OSC::model('user/permissionMask');
        }

        $permission_map = array();

        OSC::core('observer')->dispatchEvent('user/permmask/collect_keys', array('permission_map' => &$permission_map));

        if ($this->_request->get('title')) {
            $data = array();

            $data['title'] = $this->_request->get('title');
            $data['permission_data'] = $this->_processPermission(array(), $permission_map, $this->_request->get('permission'));
            try {
                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Your update has been saved successfully.';
                } else {
                    $message = 'Permission mask "'.$model->data['title'].'" has been saved successfully.';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('*/*/list'));
                } else {
                    static::redirect($this->getUrl('*/*/*', array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        } else if ($model->getId() < 1) {
            $model->setData('permission_data', array());
        }

        $output_html = $this->getTemplate()->build('user/permission_mask/post', array(
            'form_title' => $model->getId() > 0 ? ('Edit Permission Mask [#' . $model->getId() . ': ' . $model->getData('title', true) . ']') : 'Create Permission Mask',
            'model' => $model,
            'permission_map' => $permission_map
        ));

        $this->output($output_html);
    }

    /**
     * 
     * @param array $permission_data
     * @param array $permission_map
     * @param array $permission_input
     * @param string $prefix
     * @return array
     */
    protected function _processPermission($permission_data, $permission_map, $permission_input, $prefix = '') {
        foreach ($permission_map as $perm_key => $perm_data) {
            if (!isset($permission_input[$perm_key])) {
                continue;
            }

            $permission_data[] = $prefix . $perm_key;

            if (is_array($perm_data) && is_array($perm_data['items']) && count($perm_data['items']) > 0) {
                $permission_data = $this->_processPermission($permission_data, $perm_data['items'], $permission_input[$perm_key], $prefix . $perm_key . '/');
            }
        }

        return $permission_data;
    }

    public function actionDelete() {
        $this->checkPermission();

        try {
            $model = $this->_getModel();

            $model->delete();

            $this->addMessage('Permission mask "'.$model->data['title'].'" has been deleted successfully.');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirect($this->getUrl('*/*/list'));
    }

}
