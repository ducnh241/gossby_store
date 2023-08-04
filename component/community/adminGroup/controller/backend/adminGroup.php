<?php

class Controller_AdminGroup_Backend_AdminGroup extends Abstract_User_Controller_Backend {

    protected $_model = null;

    public function __construct() {
        parent::__construct();

        if (! OSC::helper('user/authentication')->getMember()->isAdmin()) {
            $this->_output->redirect($this->getUrl('/'));
        }

        $this->getTemplate()->setCurrentMenuItemKey('user/admin')
            ->setPageTitle('Manage Group Administrators');
    }

    public function actionIndex() {
        $this->forward('adminGroup/backend_adminGroup/list');
    }

    public function actionList() {
        $tpl = $this->getTemplate();

        $tpl->addBreadcrumb(array('file-text', 'Manage Group Administrators'));

        $collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection();

        $collection->sort('added_timestamp', OSC_Database::ORDER_DESC)
                ->setPageSize(25)
                ->setCurrentPage($this->_request->get('page'))
                ->load();

        $admin_groups = [];

        foreach ($collection as $key => $admin_group) {
            try {
                $admin_groups[$key]['id'] = $admin_group->getId();
                $admin_groups[$key]['member'] = $admin_group->getMember();
                $admin_groups[$key]['groups'] = $admin_group->getListGroups();
                if (count($admin_groups[$key]['groups']) > 0) {
                    $list = [];
                    foreach ($admin_groups[$key]['groups'] as $group) {
                        $list[] = $group->data['title'];
                    }
                    $admin_groups[$key]['list_group'] = implode(' , ', $list);
                }
            } catch (Exception $ex) {
                unset($admin_groups[$key]);
            }
        }

        $this->output($tpl->build('adminGroup/adminGroup/list', array(
            'admin_groups' => $admin_groups,
            'current_page' => $collection->getCurrentPage(),
            'total' => $collection->collectionLength(),
            'page_size' => $collection->getPageSize()
        )));
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));
        $tpl = $this->getTemplate();
        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('member', 'Edit Group Administrator'));

            try {
                $model = OSC::model('adminGroup/memberGroupsAdmin')->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(array('member', 'Create Group Administrator'));

            $model = OSC::model('adminGroup/memberGroupsAdmin');
        }

        if ($this->_request->get('member_id')) {
            $data = array();

            $data['member_id'] = intval($this->_request->get('member_id'));

            $data['group_ids'] = $this->_request->get('group_ids');

            try {
                if (!$this->getAccount()->isAdmin()) {
                    throw new Exception('Group is not able');
                }
                if ($data['member_id'] < 1) {
                    throw new Exception('Member id is incorrect');
                }
                if (!is_array($data['group_ids'])) {
                    $data['group_ids'] = [];
                }
                if (count($data['group_ids']) < 1) {
                    throw new Exception('Group id is incorrect');
                }
                if ($id > 0) {
                    unset($data['member_id']);
                }

                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Your update has been saved successfully.';
                } else {
                    $message = 'Account "' . $model->getMember()->data['username'] . '" has been made a group administrator.';
                }

                $this->addMessage($message);

                static::redirect($this->getUrl('list', array('id' => $model->getId())));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $member_collection = OSC::model('user/member')->getCollection()->addCondition('group_id', OSC::systemRegistry('root_group')['admin'], OSC_Database::NEGATION_MARK . OSC_Database::OPERATOR_EQUAL);

        if ($id > 0) {
            $member_collection->addCondition('member_id', $model->data['member_id']);
        } else {
            $group_admin_collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->load();

            $member_admin_ids = [];

            foreach ($group_admin_collection as $group_admin) {
                $member_admin_ids[] = $group_admin->data['member_id'];
            }

            $member_collection->addCondition('member_id', $member_admin_ids, OSC_Database::OPERATOR_NOT_IN);
        }

        $member_collection->load();

        $group_collection = OSC::model('user/group')->getCollection()->addCondition('group_id', OSC::systemRegistry('root_group')['admin'], OSC_Database::NEGATION_MARK . OSC_Database::OPERATOR_EQUAL)->load();

        $output_html = $this->getTemplate()->build('adminGroup/adminGroup/post', array(
            'form_title' => $model->getId() > 0 ? ('Assign Admin Privileges #' . $model->getId() . ': ' . $model->getData('username', true)) : 'Assign Admin Privileges',
            'model' => $model,
            'group_collection' => $group_collection,
            'member_collection' => $member_collection
        ));

        $this->output($output_html);
    }

    public function actionDelete() {
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                try {
                    $model = OSC::model('adminGroup/memberGroupsAdmin')->load($id);
                } catch (Exception $ex) {
                    $this->addMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }

                $model->delete();

                $this->addMessage($model->getMember()->data['username'] . '" has been removed as group administrator.');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl('list'));
    }

}
