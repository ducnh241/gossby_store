<?php

class Controller_User_Backend_Member extends Abstract_User_Controller_Backend {

    /**
     *
     * @var Model_User_Member
     */
    protected $_model = null;

    public function __construct() {
        parent::__construct();

        $admin_group = OSC::helper('user/authentication')->getMember()->getGroupAdmin();
        if ($this->getAccount()->getGroup()->getId() != OSC::systemRegistry('root_group')['admin'] && !$admin_group) {
            $this->error('You don\'t have permission to access this page');
        }
        $this->getTemplate()
            ->setPageTitle(OSC::core('language')->get('usr.member_manage'))
            ->setCurrentMenuItemKey('user/member');
    }

    public function actionIndex() {
        $this->forward('user/backend_member/list');
    }

    public function actionSearch() {
        OSC::sessionSet('user/member/search', trim($this->_request->get('keywords')));
        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    public function actionList() {
        $tpl = $this->getTemplate();
        $keywords = '';

        $tpl->addBreadcrumb(array('file-text', OSC::core('language')->get('usr.member_manage')));

        $collection = OSC::model('user/member')->getCollection();

        if ($this->getAccount()->isAdmin()) {
            if ($this->_request->get('search')) {
                $keywords = OSC::sessionGet('user/member/search');
                $collection->addCondition('username', '%' . $keywords . '%', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR)
                    ->addCondition('email', '%' . $keywords . '%', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR);
            }
        } else {
            $collection->addCondition('group_id', $this->getAccount()->getGroupAdmin()->data['group_ids'], OSC_Database::OPERATOR_IN);
            if ($this->_request->get('search')) {
                $keywords = OSC::sessionGet('user/member/search');
                $clause_idx = OSC::makeUniqid();
                $collection->addClause($clause_idx, 'AND')->addCondition('username', '%' . $keywords . '%', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR, $clause_idx)
                    ->addCondition('email', '%' . $keywords . '%', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR, $clause_idx);
            }
        }

        $collection->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $collection->preLoadGroup();

        $this->output($tpl->build('user/member/list', ['collection' => $collection, 'search_keywords' => $keywords]));
    }

    /**
     * 
     * @param int $id
     * @param bool $verify_permission
     * @return Model_User_Member
     * @throws OSC_Exception_Condition
     */
    protected function _getMember($id, $verify_permission = true) {
        $id = intval($id);

        if ($id < 1) {
            throw new OSC_Exception_Condition('Member ID is incorrect');
        }

        try {
            /* @var $model Model_User_Member */
            $model = OSC::model('user/member')->load($id);
        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                throw new Exception('Member is not exist', 404);
            }

            throw new Exception('Member is not exist', $ex->getCode());
        }

        if (!$verify_permission) {
            return $model;
        }

        if ($this->getAccount()->getGroup()->getId() != OSC::systemRegistry('root_group')['admin']) {
            if (!in_array($model->getId(), $this->getMemberByLeader($this->getAccount()->getId()))) {
                throw new Exception('You don\'t have permission to access this page');
            }
        }

        if (!$this->getAccount()->isRoot()) {
            if ($model->isRoot()) {
                throw new Exception('You don\'t have permission to perform the action');
            }

            if ($model->getGroup()->getId() == OSC::systemRegistry('root_group')['admin'] && $model->getId() != $this->getAccount()->getId()) {
                throw new Exception('You don\'t have permission to perform the action');
            }
        }

        return $model;
    }

    /**
     * @param $leader_id
     * @return array
     */
    private function getMemberByLeader($leader_id)
    {
        $collection = OSC::model('user/member')->getCollection();
        if (!$this->getAccount()->isAdmin()) {
            $collection->addField('member_id')->addCondition('group_id', $this->getAccount()->getGroupAdmin()->data['group_ids'], OSC_Database::OPERATOR_IN);
        }
        $collection->load();
        $member_ids = [];
        foreach ($collection as $member) {
            $member_ids[] = $member->getId();
        }
        return $member_ids;
    }

    public function actionGetAuthSecretForm() {
        try {
            $model = $this->_getMember($this->_request->get('id'));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $secret_key = '';
        $QR_code_url = '';

        if ($model->data['auth_secret_key']) {
            $google_auth = new PHPGangsta_GoogleAuthenticator();

            $secret_key = $model->data['auth_secret_key'];
            $QR_code_url = $google_auth->getQRCodeGoogleUrl(OSC_SITE_KEY . ' :: ' . $model->data['username'], $secret_key);
        }

        $this->_ajaxResponse(array('html' => $this->getTemplate()->build('user/member/auth_secret_frm', array('model' => $model, 'secret_key' => $secret_key, 'qr_code_url' => $QR_code_url))));
    }

    public function actionGenerateAuthSecretKey() {
        try {
            $model = $this->_getMember($this->_request->get('id'));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $google_auth = new PHPGangsta_GoogleAuthenticator();

        $auth_secret_key = $google_auth->createSecret();

        $model->setData('auth_secret_key', $auth_secret_key)->addObserver('save_error', array(&$this, 'saveErrorHandler'), null, 'save_error_hook', null, true)->save();

        if ($this->_action_error) {
            $this->_ajaxError($this->_action_error_message);
        }

        $this->actionGetAuthSecretForm();
    }

    public function actionRemoveAuthSecretKey() {
        try {
            $model = $this->_getMember($this->_request->get('id'));
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $model->setData('auth_secret_key', '')->addObserver('save_error', array(&$this, 'saveErrorHandler'), null, 'save_error_hook', null, true)->save();

        if ($this->_action_error) {
            $this->_ajaxError($this->_action_error_message);
        }

        $this->actionGetAuthSecretForm();
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));
        $this->getTemplate()->addBreadcrumb($id > 0 ? array('file-text', 'Edit account') : ['file-text', 'Create account']);

        /* @var $model Model_User_Member */

        if ($id > 0) {
            try {
                $model = $this->_getMember($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $model = OSC::model('user/member');
        }

        if ($this->getAccount()->isAdmin()) {
            $group_collection = OSC::model('user/group')->getCollection()->addCondition('group_id', OSC::systemRegistry('root_group')['guest'], OSC_Database::OPERATOR_NOT_EQUAL)->load();
        } else {
            $group_collection = OSC::model('user/group')->getCollection()->load($this->getAccount()->getGroupAdmin()->data['group_ids']);
        }

        $group_collection->load();

        if ($this->_request->get('email')) {
            $data = array();
            $data['username'] = OSC::isPrimaryStore() ? trim($this->_request->get('username')) : OSC::helper('user/member_common')->cleanUsername($this->_request->get('email'), '_');
            $data['email'] = trim($this->_request->get('email'));
            $data['password'] = $this->_request->get('password');
            $data['group_id'] = intval($this->_request->get('group_id'));
            $data['perm_mask_ids'] = $this->_request->get('perm_mask');
            $data['sref_type'] = $this->_request->get('sref_type', '');

            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();

            if (!$id) {
                $email_check = null;
                $username_check = null;

                $DB->query("SELECT LOWER(email) as email_check, LOWER(username) as username_check 
                                FROM {$model->getTableName(true)} 
                                WHERE (email = :email OR username = :username) 
                                AND member_id != :member_id ",
                    ['email' => strtolower($data['email']),
                    'username' => strtolower($data['username']),
                    'member_id' => $id]);

                while ($row = $DB->fetchArray()) {
                    $email_check = $row['email_check'];
                    $username_check = $row['username_check'];
                }

                if ($email_check && $email_check == $data['email']) {
                    $this->addErrorMessage('The email has already been taken.');
                    static::redirect($this->getCurrentUrl());
                }

                if ($username_check && $username_check == $data['username']) {
                    $this->addErrorMessage('The username has already been taken.');
                    static::redirect($this->getCurrentUrl());
                }
            }

            if (!$this->getAccount()->isAdmin()) {
                unset($data['sref_type']);
            }

            if (!$data['perm_mask_ids']) {
                $data['perm_mask_ids'] = [];
            }

            if (!$group_collection->getItemByKey($data['group_id'])) {
                $data['group_id'] = OSC::systemRegistry('root_group')['member'];
            }

            if (!$this->getAccount()->isRoot() && $model->data['group_id'] == OSC::systemRegistry('root_group')['admin']) {
                unset($data['group_id']);
            }

            if (!$data['password']) {
                unset($data['password']);
            }

            try {
                if(isset($data['password'])) {
                    $data['password_hash'] = 1;
                    $model->register('RAW_PASSWORD', $data['password']);
                    unset($data['password']);
                }

                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Your account has been updated successfully.';
                } else {
                    $message = 'Account "'.$model->data['email'].'" has been created successfully.';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $perm_mask_collection = OSC::model('user/permissionMask')->getCollection()->load();

        $output_html = $this->getTemplate()->build('user/member/post', array(
            'form_title' => $model->getId() > 0 ? ('Edit account #' . $model->getId() . ': ' . $model->getData('username', true)) : 'Create account',
            'model' => $model,
            'perm_mask_collection' => $perm_mask_collection,
            'group_collection' => (!$this->getAccount()->isRoot() && $model->data['group_id'] == OSC::systemRegistry('root_group')['admin']) ? null : $group_collection
        ));

        $this->output($output_html);
    }

    public function actionDelete() {
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                $model = $this->_getMember($id);

                if ($model->isRoot() || ($model->data['group_id'] == OSC::systemRegistry('root_group')['admin'] && !$this->getAccount()->isRoot())) {
                    throw new Exception('You don\'t have permission to perform the action');
                }

                $model->delete();
                $admin_group_collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->addCondition('member_id', $id)->load();
                if ($admin_group_collection->length() > 0) {
                    foreach ($admin_group_collection as $admin_group) {
                        $admin_group->delete();
                    }
                }

                $this->addMessage('Account "'.$model->data['username'].'" has been deleted successfully.');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionGetMemberById() {
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                $model = $this->_getMember($id);
            } catch (Exception $ex) {
                $message = $ex->getCode() == 404 ? 'Sref #' . $id . ' is not exist' : $ex->getMessage();
                $this->_ajaxError($message);
            }
        }
        $this->_ajaxResponse(['username' => $model->data['username']]);
    }

}
