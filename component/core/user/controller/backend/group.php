<?php

class Controller_User_Backend_Group extends Abstract_User_Controller_Backend {

    /**
     *
     * @var Model_User_Group
     */
    protected $_model = null;

    public function __construct() {
        parent::__construct();

        $this->getTemplate()
            ->setCurrentMenuItemKey('user/group')
            ->setPageTitle(OSC::core('language')->get('usr.group_manage'));
    }

    /**
     * 
     * @param int $id
     * @return \Model_User_Group
     * @throws OSC_Exception_Condition
     */
    protected function _getModel($id = null) {
        if ($this->_model !== null) {
            return $this->_model;
        }

        $this->_model = OSC::model('user/group');

        if ($id === null) {
            $id = $this->_request->get('id');
        }

        $id = intval($id);

        if ($id < 1) {
            throw new OSC_Exception_Condition('Group ID is incorrect');
        }

        try {
            $this->_model->load($id);
        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                throw new OSC_Exception_Condition($this->_('usr.err_group_not_exist'));
            }

            throw new Exception($ex->getMessage());
        }

        if ($this->_model->data['lock_flag'] && !$this->getAccount()->isRoot()) {
            throw new OSC_Exception_Condition($this->_('core.err_no_permission'));
        }

        return $this->_model;
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {
        $this->checkPermission();

        $collection = OSC::model('user/group')->getCollection();

        $collection->sort('title', OSC_Database::ORDER_ASC)->load();

        $this->getTemplate()->addBreadcrumb(array('file-text', OSC::core('language')->get('usr.group_manage')));

        $this->output($this->getTemplate()->build('user/group/list', array('collection' => $collection)));
    }

    public function actionPost() {
        $this->checkPermission();

        $id = intval($this->_request->get('id'));

        $this->getTemplate()->addBreadcrumb($id > 0 ? array('file-text', 'Edit group') : ['file-text', 'Create group']);

        /* @var $model Model_User_Group */

        if ($id > 0) {
            try {
                $model = $this->_getModel($id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/list'));
            }
        } else {
            $model = OSC::model('user/group');
        }

        if ($this->_request->get('title')) {
            $data = array();

            $data['title'] = $this->_request->get('title');
            $data['perm_mask_ids'] = $this->_request->get('perm_mask');

            if ($this->getAccount()->isRoot()) {
                $data['lock_flag'] = intval($this->_request->get('lock_flag'));
            }

            try {
                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Your update has been saved successfully.';
                } else {
                    $message = 'Group "'.$model->data['title'].'" has been saved successfully.';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl('*/*/*', array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('user/group/post', array(
            'form_title' => $model->getId() > 0 ? ('Edit group [#' . $model->getId() . ': ' . $model->getData('title', true) . ']') : 'Create group',
            'model' => $model,
            'perm_mask_collection' => OSC::model('user/permissionMask')->getCollection()->load()
        ));

        $this->output($output_html);
    }

    public function actionDelete() {
        $this->checkPermission();

        try {
            $model = $this->_getModel();

            if ($model->isRoot()) {
                throw new Exception($this->_('usr.err_group_root'));
            }

            $model->delete();

            $this->addMessage('Group "'.$model->data['title'].'" has been deleted successfully.');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirect($this->getUrl('*/*/list'));
    }

}
