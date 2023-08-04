<?php

class Controller_User_Backend_PermissionAnalytic extends Abstract_User_Controller_Backend {

    /**
     *
     * @var Model_User_PermissionAnalytic
     */
    protected $_model = null;

    public function __construct() {
        parent::__construct();

        $this->getTemplate()->setCurrentMenuItemKey('user/perm_analytic')
            ->setPageTitle('Manage Analytics Viewing Permissions');
    }

    /**
     *
     * @param int $id
     * @return \Model_User_PermissionAnalytic
     * @throws OSC_Exception_Condition
     */
    protected function _getModel($id = null) {
        if ($this->_model !== null) {
            return $this->_model;
        }

        $this->_model = OSC::model('user/permissionAnalytic');

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
                throw new Exception('The permission analytic #' . $id . ' is not exists');
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

        $collection = OSC::model('user/permissionAnalytic')->getCollection();

        $collection->sort('member_id', OSC_Database::ORDER_ASC)->load();

        $this->getTemplate()->addBreadcrumb(array('file-text', 'Viewing Permissions'));

        $this->output($this->getTemplate()->build('user/permission_analytic/list', ['collection' => $collection]));
    }

    public function actionPost() {
        $this->checkPermission();

        $id = intval($this->_request->get('id'));

        /* @var $model Model_User_PermissionAnalytic */
        $model = OSC::model('user/permissionAnalytic');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Permission analytic is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        }

        if ($this->_request->get('member_id')) {
            $data = [];
            $data['member_id'] = intval($this->_request->get('member_id'));
            $data['member_mkt_ids'] = $this->_request->get('member_mkt_ids');

            try {
                $model->setData($data)->save();
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                $this->redirect(OSC::getUrl('*/*/*'));
            }
            $message = $id > 0 ? 'Viewing permissions for "' . $model->getNameOfMember() . '" has been updated successfully' : 'Viewing permissions for "' . $model->getNameOfMember() . '" has been created successfully';
            $this->addMessage($message);
            $this->redirect(OSC::getUrl('*/*/list'));
        }

        $member_collection = OSC::model('user/member')->getCollection()->addCondition('group_id', OSC::systemRegistry('root_group')['admin'], OSC_Database::NEGATION_MARK . OSC_Database::OPERATOR_EQUAL);

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('file-text', 'Edit Viewing Permission'));
            $member_collection->addCondition('member_id', $model->data['member_id']);
        } else {
            $this->getTemplate()->addBreadcrumb(array('file-text', 'Create Viewing Permission'));
            $group_admin_collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->load();

            $member_admin_ids = [];

            foreach ($group_admin_collection as $group_admin) {
                $member_admin_ids[] = $group_admin->data['member_id'];
            }

            $member_collection->addCondition('member_id', $member_admin_ids, OSC_Database::OPERATOR_NOT_IN);
        }

        $member_collection->load();

        $output_html = $this->getTemplate()->build('user/permission_analytic/post', [
            'form_title' => $model->getId() > 0 ?
                'Edit Viewing Permission #' . $model->getId() :
                'Create Viewing Permission',
            'member_collection' => $member_collection,
            'model' => $model
        ]);

        $this->output($output_html);
    }

    public function actionDelete() {
        $this->checkPermission();

        try {
            $model = $this->_getModel();

            $model->delete();

            $this->addMessage('Permission analytic "' . $model->getNameOfMember() . '" has been deleted successfully');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirect($this->getUrl('*/*/list'));
    }

}
