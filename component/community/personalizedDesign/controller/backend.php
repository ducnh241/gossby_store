<?php

class Controller_PersonalizedDesign_Backend extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();

        $this->checkPermission('personalized_design');

        $this->getTemplate()->setCurrentMenuItemKey('personalized_design')->resetBreadcrumb()->addBreadcrumb(array('magic-solid', 'Personalized Design'), $this->getUrl('personalizedDesign/backend/list'));
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionBulkRerender() {
        /* @var $model Model_PersonalizedDesign_Design */

        if (OSC::isPrimaryStore()) {
            $this->checkPermission('personalized_design/rerender');
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound('You don\'t have permission to view the page');
            }
        }

        try {
            $id = intval($this->_request->get('id'));

            if ($id < 1) {
                throw new Exception('Design ID is empty');
            }

            $design = OSC::model('personalizedDesign/design')->load($id);

            $ukey = 'rerender_order_by_design:' . $design->getId();

            try {
                $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                $model_bulk_queue->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            $account = $this->getAccount();

            OSC::model('catalog/product_bulkQueue')->setData([
                'member_id' => $account->getId(),
                'ukey' => $ukey,
                'action' => 'rerender_order_by_design',
                'queue_data' => [
                    'design_id' => $design->getId(),
                    'user_name' => $account->data['username']
                ]
            ])->save();

            OSC::core('cron')->addQueue('personalizedDesign/rerenderOrderByDesign', null, ['ukey' => 'personalizedDesign/rerenderOrderByDesign', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60]);

            $this->addMessage('Rerender design #'. $design->getId() .' task has been appended to queue');

            static::redirect($this->getUrl('*/*/list'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('list'));
        }
    }

    protected function _getFilterConfig($filter_value = null) {
        $filter_config = [];

        if (!$this->getAccount()->isAdmin()) {
            $members = OSC::helper('adminGroup/common')->getMembersGroup($this->getAccount()->getId());

            $members_array = [];
            foreach ($members as $member) {
                $members_array[$member->data['member_id']] = $member->getGroup()->data['title'] .' - '. $member->data['username'];
            }
            if (count($members_array) > 1) {
                $filter_config['member'] = [
                    'title' => 'Member',
                    'type' => 'checkbox',
                    'data' => $members_array,
                    'field' => 'member_id'
                ];
            }
        }

        $filter_config['date'] = [
            'title' => 'Added date',
            'type' => 'daterange',
            'field' => 'added_timestamp',
            'prefix' => 'Added date'
        ];
        $filter_config['mdate'] = [
            'title' => 'Modified date',
            'type' => 'daterange',
            'field' => 'modified_timestamp',
            'prefix' => 'Modified date'
        ];

        if ($filter_value !== null) {
            if (!is_array($filter_value)) {
                $filter_value = [];
            }

            foreach ($filter_config as $k => $v) {
                unset($v['field']);

                if (isset($filter_value[$k])) {
                    $v['value'] = $filter_value[$k];
                }

                $filter_config[$k] = $v;
            }
        }

        return $filter_config;
    }

    public function actionSearch() {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
                'personalizedDesign/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
                ], 60 * 60
        );

        $path = '*/*/list';

        static::redirect($this->getUrl($path, ['search' => 1]));
    }

    protected function _applyListCondition(Model_PersonalizedDesign_Design_Collection $collection): void {
        $search = OSC::sessionGet('personalizedDesign/search');

        if ($search) {
            $condition = OSC::core('search_analyzer')
                ->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('ukey', 'ukey', OSC_Search_Analyzer::TYPE_STRING, true, true)
                ->addKeyword('id', 'design_id', OSC_Search_Analyzer::TYPE_INT, true)
                ->addKeyword('member_id', 'member_id', OSC_Search_Analyzer::TYPE_INT, true)
                ->parse($search['keywords']);

            $collection->setCondition($condition);

            if (!$this->getAccount()->isAdmin()) {
                #$members_ids = OSC::helper('adminGroup/common')->getMembersGroup($this->getAccount()->getId(), true);
                #$collection->addCondition('member_id', $members_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND);
            }

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionList() {
        $collection = OSC::model('personalizedDesign/design')
            ->getCollection()
            ->addField('design_id', 'ukey', 'title', 'member_id', 'locked_flag', 'added_timestamp', 'modified_timestamp');

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        if (!$this->checkPermission('personalized_design/full', false)) {
            //$collection->addCondition('member_id', $this->getAccount()->getId(), OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);
        }

        $this->getTemplate()->setCurrentMenuItemKey('personalized_design');

        $collection
            ->addCondition('type_flag', Model_PersonalizedDesign_Design::TYPE_DESIGN_DEFAULT, OSC_Database::OPERATOR_EQUAL)
            ->sort('locked_flag', OSC_Database::ORDER_ASC)
            ->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->sort('title', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'));


        if (!$this->getAccount()->isAdmin() && !$this->checkPermission('personalized_design/view_all', false)) {
            $members_ids = OSC::helper('adminGroup/common')->getMembersGroup($this->getAccount()->getId(), true);
            if ($this->checkPermission('personalized_design/view_group', false)) {
                $members_ids = array_merge($members_ids, OSC::helper('adminGroup/common')->getMembersByGroup($this->getAccount()->getGroup()->getId(), true));
            }
            $collection->addCondition('member_id', $members_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND);
        }

        $collection->load()->preLoadMember();

        $this->getTemplate()->setPageTitle('Personalized Design');

        static::setLastListUrl();

        $this->output(
                $this->getTemplate()->build(
                        'personalizedDesign/list', [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
                        ]
                )
        );
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));
        $folderIds = $this->_request->get('folderId');
        /* @var $model Model_PersonalizedDesign_Design */
        $model = OSC::model('personalizedDesign/design');

        $list_path = 'list';

        if ($id > 0) {
            try {
                $model->load($id);

                if ($model->data['locked_flag'] == 1) {
                    $this->checkPermission('personalized_design/edit/locked');
                } else {
                    $this->checkPermission('personalized_design/edit');
                }

                if (!$this->checkPermission('personalized_design/full', false) && $model->data['member_id'] != $this->getAccount()->getId()) {
                    //throw new Exception('You unable to edit the design');
                }
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());

                static::redirectLastListUrl($this->getUrl($list_path));
            }
        } else {
            $this->checkPermission('personalized_design/add');
        }

        $permission_edit_lock = $this->__getPermissionPersonalizedDesignEditLayer($model->data['locked_flag']);

        try {
            $draft = OSC::model('personalizedDesign/design_draft')->loadByUKey($this->getAccount()->getId() . '/' . ($id > 0 ? $id : 0));
        } catch (Exception $ex) {
            $draft = null;
        }

        $draft_content = '';
        $draft_content_meta_data = [];

        if ($this->_request->get('title', null) !== null) {
            $design_data = OSC::decode($this->_request->getRaw('design_data'));
            if (!is_array($design_data['objects'])) {
                $design_data['objects'] = [];
            }

            if (count($design_data['objects']) < 1) {
                $this->addErrorMessage('Design data is empty, please try again in a few minutes.');

                static::redirect($this->getUrl(null, ['id' => $model->getId()]));
            }

            $tab_flag = OSC::helper('personalizedDesign/common')->checkTabFlag($design_data);
            $background_color = $this->_request->get('background_color');

            $data = [
                'title' => $this->_request->get('title'),
                'design_data' => $design_data,
                'tab_flag' => $tab_flag,
                'background_color' => $background_color,
                'type_flag' => Model_PersonalizedDesign_Design::TYPE_DESIGN_DEFAULT,
            ];

            if ($id < 1) {
                $data['member_id'] = $this->getAccount()->getId();
            }

            try {
                $model->setData($data)->save();

                // save folder id to upload image to S3 (PSD)
                if (!empty($folderIds)) {
                    $meta_data = $model->data['meta_data'];
                    if (isset($meta_data['image_folder_id_on_server'])) {
                        $folderIdsData = $meta_data['image_folder_id_on_server'];
                    } else {
                        $folderIdsData = [];
                    }

                    foreach (explode(',', $folderIds) as $folderId) {
                        array_push($folderIdsData, $folderId);
                        $meta_data['image_folder_id_on_server'] = $folderIdsData;

                        $model->setData([
                            'meta_data' => $meta_data,
                        ])->save();

                        OSC::core('cron')->addQueue('personalizedDesign/uploadToS3', [
                            'folderId' => $folderId,
                            'type' => 'personalizedDesign',
                            'id' => $model->getId()
                        ]);
                    }
                }

                //Save last 3 versions to table personalizedDesign versionDesign
                $model_version = OSC::model('personalizedDesign/versionDesign')
                    ->getCollection()
                    ->addCondition('design_id', $model->getId())
                    ->sort('added_timestamp', OSC_Database::ORDER_ASC)
                    ->load();

                $svg_content = OSC::helper('personalizedDesign/common')->renderSvg($model);
                if (count($model_version->getItems()) >= 3) {
                    $model_version->getItem()->setData([
                        'design_id' => $model->getId(),
                        'title_design' => $data['title'],
                        'design_data' => $data['design_data'],
                        'meta_data' => ['design_svg' => $svg_content],
                        'user_id' => $this->getAccount()->getId(),
                        'added_timestamp' => time()
                    ])->save();
                } else {
                    OSC::model('personalizedDesign/versionDesign')->setData([
                        'design_id' => $model->getId(),
                        'title_design' => $data['title'],
                        'design_data' => $data['design_data'],
                        'meta_data' => ['design_svg' => $svg_content],
                        'user_id' => $this->getAccount()->getId(),
                        'added_timestamp' => time()
                    ])->save();
                }

                if ($draft) {
                    $draft->delete();
                }

                if ($id > 0) {
                    $message = 'Personalized design #' . $model->getId() . ' has been updated';
                } else {
                    $message = 'Personalized design [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
                }

                if (!isset($model->data['meta_data']['image_folder_id_on_server']) || count($model->data['meta_data']['image_folder_id_on_server']) == 0) {
                    OSC::model('personalizedDesign/sync')->setData([
                        'ukey' => OSC::makeUniqid(),
                        'sync_type' => 'renderDesignImage',
                        'sync_data' => [
                            'design_id' => $model->getId(),
                            'svg_content' => $svg_content
                        ]
                    ])->save();
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirectLastListUrl($this->getUrl($list_path));
                } else {
                    static::redirect($this->getUrl(null, ['id' => $model->getId()]));
                }
            } catch (Exception $ex) {
                if (!empty($folderIds)) {
                    $model->data['meta_data']['image_folder_id_on_server'] = $model->data['meta_data']['image_folder_id_on_server'] ?? [];
                    foreach (explode(',', $folderIds) as $folderId) {
                        array_push($model->data['meta_data']['image_folder_id_on_server'], $folderId);
                    }
                }
                $this->addErrorMessage($ex->getMessage());
            }
        } else {
            if ($draft && ($draft->data['design_id'] > 0 ? ($draft->data['modified_timestamp'] > $model->data['modified_timestamp']) : true)) {
                $draft_content = $draft->data['design_data'];
                $draft_content_meta_data = $draft->data['meta_data'];
            }
        }

        $output_html = $this->getTemplate()->build('personalizedDesign/postForm', [
            'form_title' => $model->getId() > 0 ? ('Edit design #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new design',
            'model' => $model,
            'draft_content' => [
                'design_data' => $draft_content,
                'meta_data' => $draft_content_meta_data
            ],
            'permission_edit_lock' => $permission_edit_lock
        ]);

        $this->output($output_html);
    }

    private function __getPermissionPersonalizedDesignEditLayer($lock_flag) {
        $permission_edit_layer_design = [
            'add_layer' => true,
            'edit_layer' => true,
            'remove_layer' => true,
        ];

        if ($lock_flag) {
            foreach ($permission_edit_layer_design as $perm => $value) {
                $permission_edit_layer_design[$perm] = $this->checkPermission('personalized_design/edit/locked/' . $perm, false);
            }

            return $permission_edit_layer_design;
        }

        return $permission_edit_layer_design;
    }

    public function actionUpdateTmp() {
        /* @var $model Model_PersonalizedDesign_Design */

        $id = intval($this->_request->get('id'));
        $folderIds = $this->_request->get('folderId');

        if ($id < 1) {
            $id = 0;
            $this->checkPermission('personalized_design/add');
        } else {
            try {
                $design = OSC::model('personalizedDesign/design')
                    ->getCollection()
                    ->addField('locked_flag')
                    ->load([$id])
                    ->first();

                if ($design->data['locked_flag'] == 1) {
                    $this->checkPermission('personalized_design/edit/locked');
                } else {
                    $this->checkPermission('personalized_design/edit');
                }
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        $ukey = $this->getAccount()->getId() . '/' . $id;

        try {
            $params = [
                'member_id' => $this->getAccount()->getId(),
                'design_id' => $id,
                'design_data' => OSC::decode($this->_request->getRaw('content'), true)
            ];

            if (!empty($folderIds)) {
                $folderIdsData = [];

                foreach (explode(',', $folderIds) as $folderId) {
                    array_push($folderIdsData, $folderId);
                }
                $params['meta_data'] = [
                    'image_folder_id_on_server' => $folderIdsData
                ];
            }

            OSC::model('personalizedDesign/design_draft')->updateByUkey($ukey, $params);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }

    public function actionDuplicate() {
        /* @var $model Model_PersonalizedDesign_Design */

        $this->checkPermission('personalized_design/add');

        $id = intval($this->_request->get('id'));

        $path = 'list';

        if ($id < 1) {
            $this->addMessage('Item ID is empty');
            static::redirectLastListUrl($this->getUrl($path));
        }

        try {
            $model = OSC::model('personalizedDesign/design')->load($id);

            if (!$this->checkPermission('personalized_design/full', false) && $model->data['member_id'] != $this->getAccount()->getId()) {
                //throw new Exception('You unable to duplicate the design');
            }
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Item is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('list'));
        }

        try {
            $copy_model = $model->getNullModel();

            $model_data = &$model->data;

            if (isset($model_data['meta_data']['image_folder_id_on_server'])) {
                if (count($model_data['meta_data']['image_folder_id_on_server']) > 0) {
                    throw new Exception('You unable to duplicate the design which is uploading images to S3');
                }
                unset($model_data['meta_data']['image_folder_id_on_server']);
            }

            unset($model_data[$model->getPkFieldName()]);
            unset($model_data[$model->getUkeyFieldName()]);

            $model_data['locked_flag'] = 0;
            $model_data['added_timestamp'] = time();
            $model_data['modified_timestamp'] = time();
            $model_data['member_id'] = $this->getAccount()->getId();

            if ($model->getVersion() == 1) {
                OSC::helper('personalizedDesign/common')->convertDesignDataV1ToV2($model_data['design_data']);
            }
            $model_data['type_flag'] = Model_PersonalizedDesign_Design::TYPE_DESIGN_DEFAULT;
            $is_draft = intval($this->_request->get('is_draft'));

            $copy_model->setData($model_data)->save();

            OSC::model('personalizedDesign/sync')->setData([
                'ukey' => OSC::makeUniqid(),
                'sync_type' => 'renderDesignImage',
                'sync_data' => [
                    'design_id' => $copy_model->getId(),
                    'svg_content' => OSC::helper('personalizedDesign/common')->renderSvg($copy_model)
                ]
            ])->save();

            OSC::helper('core/common')->writeLog($copy_model->getTableName(true), "Duplicate personalized design #{$copy_model->getId()} from #{$model->getId()}", $copy_model->getModifiedData(true));

            $this->addMessage('Duplicate personalized design successfully!');

            static::redirect($this->getUrl('*/*/post', ['id' => $copy_model->getId()]));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
            static::redirectLastListUrl($this->getUrl($path));
        }
    }

    public function actionLockSwitch() {
        /* @var $model Model_PersonalizedDesign_Design */

        $id = intval($this->_request->get('id'));

        $path = 'list';

        if ($id < 1) {
            $this->addMessage('Item ID is empty');
            static::redirectLastListUrl($this->getUrl($path));
        }

        try {
            $model = OSC::model('personalizedDesign/design')->load($id);
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Item is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl($path));
        }

        $this->checkPermission('personalized_design/edit' . ($model->data['locked_flag'] == 1 ? '_locked' : ''));

        try {
            if (!$this->checkPermission('personalized_design/full', false) && $model->data['member_id'] != $this->getAccount()->getId()) {
                throw new Exception('You unable to switch lock state of the design');
            }

            $model->setData('locked_flag', $model->data['locked_flag'] == 1 ? 2 : 1)->save();

            OSC::helper('core/common')->writeLog($model->getTableName(true) , 'Personalized Design: ' . ($model->data['locked_flag'] == 1 ? 'locked' : 'unlocked') . ' design #' . $id);

            $this->addMessage('The design has been ' . ($model->data['locked_flag'] == 1 ? 'locked' : 'unlocked'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirectLastListUrl($this->getUrl($path));
    }

    public function actionUpload() {
        $this->checkPermission('personalized_design/add|personalized_design/edit');

        try {
            $uploader = new OSC_Uploader();

            $original_file_name = $uploader->getName();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $file_url = trim($this->_request->decodeValue($this->_request->get('url')));

            try {
                if (!$file_url) {
                    throw new Exception('No input data');
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($file_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($file_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception('Unable get response from URL');
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception('Unable to save TMP file');
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

            $original_file_name = preg_replace('/^.+\/([^\/]+?)([\?\#].*)?$/', '\\1', $file_url);
        }

        try {
            $tmp_file_name = 'personalizedDesign/' . preg_replace('/^(.+\/)?([^\/]+)$/', '\\2', $tmp_file_path);


            $file_size = filesize($tmp_file_path); // Lấy kích thước tệp tin của hình ảnh (đơn vị là byte)
            $size_in_mb = $file_size / (1024 * 1024);

            if (intval($size_in_mb) > 30) {
                throw new Exception('Upload file needs to be less than 30 mb');
            }

            OSC::core('aws_s3')->tmpSaveFile($tmp_file_path, $tmp_file_name);
            $tmp_file_path = OSC_Storage::preDirForSaveFile($tmp_file_name);
            $tmp_thumb_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $tmp_file_path);
            $tmp_preview_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $tmp_file_path);
            $image = new Imagick($tmp_file_path);

            $width_orig = $image->getImageWidth();
            $height_orig = $image->getImageHeight();

            //Get preview image
            if ($height_orig > 600) {
                $image->scaleImage(0, 600);
            }

            $image->writeImage($tmp_preview_file_path);

            //Get thumbnail image
            if ($image->getImageHeight() > 200) {
                $image->scaleImage(200, 200, true);
            }

            $image->writeImage($tmp_thumb_file_path);

            $image->clear();
            $image->destroy();

            $name = $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
            $date = date('d.m.Y');
            $file_name = 'personalizedDesign/design/images/' . $date . '/' . $name;

            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
            $thumb_file_name_s3 = OSC::core('aws_s3')->getStoragePath(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $file_name));
            $preview_file_name_s3 = OSC::core('aws_s3')->getStoragePath(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $file_name));

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);
            OSC::core('aws_s3')->upload($tmp_thumb_file_path, $thumb_file_name_s3, $options);
            OSC::core('aws_s3')->upload($tmp_preview_file_path, $preview_file_name_s3, $options);

            try {
                OSC::model('personalizedDesign/sync')->setData([
                    'ukey' => 'image/' . md5($file_name),
                    'sync_type' => 'image',
                    'sync_data' => $file_name
                ])->save();
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                    throw new Exception($ex->getMessage());
                }
            }

            $hash = null;
            try {
                $hash = OSC::helper('personalizedDesign/common')->generateImgHash($tmp_file_path);
            } catch (Exception $ex) {}

            $this->_ajaxResponse([
                'file' => $file_name,
                'name' => preg_replace('/^(.+)\.[^\.]*$/', '\\1', $original_file_name),
                // 'url' => $file_url,
                'url' => $file_name,
                'width' => $width_orig,
                'height' => $height_orig,
                'hash' => $hash
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionUploadFromPSD()
    {
        $this->checkPermission('personalized_design/add|personalized_design/edit');
        $indexs = $this->_request->get('index');
        $folderId = $this->_request->get('folderId');

        if (isset($indexs)) {
            $data_response = [];
            foreach (explode(',' , $indexs) as $index) {
                $tmp_file_name = 'personalizedDesign/'. date('d.m.Y') .'/'. $folderId .'/'. $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.png';
                $tmp_file_path = OSC::helper('personalizedDesign/common')->getTmpDir($tmp_file_name);
                move_uploaded_file(
                    $_FILES['image' . $index]['tmp_name'],
                    $tmp_file_path
                );

                try {
                    $tmp_thumb_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $tmp_file_path);
                    $tmp_preview_file_path = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $tmp_file_path);

                    $image = new Imagick($tmp_file_path);

                    $width_orig = $image->getImageWidth();
                    $height_orig = $image->getImageHeight();

                    //Get preview image
                    if ($height_orig > 600) {
                        $image->scaleImage(0, 600);
                    }

                    $image->writeImage($tmp_preview_file_path);

                    //Get thumbnail image
                    if ($image->getImageHeight() > 200) {
                        $image->scaleImage(200, 200, true);
                    }

                    $image->writeImage($tmp_thumb_file_path);

                    $image->clear();
                    $image->destroy();

                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }

                $data = [
                    'index' => $index,
                    'url' => $tmp_file_name,
                    'width' => $width_orig,
                    'height' => $height_orig
                ];

                array_push($data_response, $data);
            }

            $this->_ajaxResponse([
                'data' => $data_response,
            ]);
        } else {
            $this->_ajaxError("File image from PSD not found !");
        }
    }

    public function actionUploadThumbnail() {
        $this->checkPermission('personalized_design/add|personalized_design/edit');

        try {

            $uploader = new OSC_Uploader();

            $original_file_name = $uploader->getName();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            try {
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() == 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $file_url = trim(strval($this->_request->decodeValue($this->_request->get('url'))));

            try {
                if (!$file_url) {
                    throw new Exception('No input data');
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($file_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($file_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception('Unable get response from URL');
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception('Unable to save TMP file');
                    }
                }

                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

            $original_file_name = preg_replace('/^.+\/([^\/]+?)([\?\#].*)?$/', '\\1', $file_url);
        }

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(70)->setImage($tmp_file_path)->resize(100)->save();

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $img_processor->save();

            $file_name = 'personalizedDesign/design/thumbnail/' . date('d.m.Y') . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;
            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];
            OSC::core('aws_s3')->upload($tmp_file_path, $file_name_s3, $options);

            $hash = null;
            try {
                $hash = OSC::helper('personalizedDesign/common')->generateImgHash($tmp_file_path);
            } catch (Exception $ex) {}

            $this->_ajaxResponse([
                'file' => $file_name,
                'name' => preg_replace('/^(.+)\.[^\.]*$/', '\\1', $original_file_name),
                'url' => $file_name,
                'width' => $width,
                'height' => $height,
                'hash' => $hash
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionUploadThumbnailFromPSD()
    {
        $this->checkPermission('personalized_design/add|personalized_design/edit');

        $indexs = $this->_request->get('index');
        $folderId = $this->_request->get('folderId');

        if (isset($indexs)) {
            $data_response = [];
            foreach (explode(',' , $indexs) as $index) {
                if (isset($_FILES['image' . $index]['tmp_name'])) {

                    $tmp_file_name = 'personalizedDesign/'. date('d.m.Y') .'/'. $folderId .'/thumbnail/'. $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.png';
                    $tmp_file_path = OSC::helper('personalizedDesign/common')->getTmpDir($tmp_file_name);

                    move_uploaded_file(
                        $_FILES['image' . $index]['tmp_name'],
                        $tmp_file_path
                    );

                    try {
                        // $image = new Imagick($tmp_file_path);

                        // $width = $image->getImageWidth();
                        // $height = $image->getImageHeight();

                        $img_processor = new OSC_Image();
                        $img_processor->setJpgQuality(70)->setImage($tmp_file_path)->resize(100)->save();

                        $width = $img_processor->getWidth();
                        $height = $img_processor->getHeight();

                        $img_processor->save();

                        $data = [
                            'index' => $index,
                            'url' => $tmp_file_name,
                            'width' => $width,
                            'height' => $height,
                        ];
                        array_push($data_response, $data);

                    } catch (Exception $ex) {
                        $this->_ajaxError($ex->getMessage());
                    }
                }
            }

            $this->_ajaxResponse([
                'data' => $data_response,
            ]);
        } else {
            $this->_ajaxError("File image from PSD not found !");
        }
    }

    public function actionUploadFont() {
        $this->checkPermission('personalized_design/add|personalized_design/edit');

        try {
            $uploader = new OSC_Uploader();

            $font_key = $uploader->getName();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            $uploader->save($tmp_file_path, true);

            $extension = OSC_File::verifyExtension($tmp_file_path);

            if (!in_array($extension, ['ttf', 'otf'], true)) {
                throw new Exception('Just TTF, OTF extension able to upload');
            }

            $font = \FontLib\Font::load($tmp_file_path);
            $font->parse();

            $font_name = $font->getFontName();

            $font_key = preg_replace('/^(.+)\.[a-zA-Z0-9]+$/', '\\1', $font_name);
            $font_key = preg_replace('/[^a-zA-Z0-9]/', '_', $font_key);
            $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
            $font_key = preg_replace('/_{2,}/', '_', $font_key);
            $font_key = strtolower($font_key);

            $font_name = implode(' ', array_map(function($segment) {
                return ucfirst($segment);
            }, explode('_', $font_key)));

            $font_path_ttf = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.ttf';
            $font_path_woff2 = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.woff2';
            $font_path_svg = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.svg';
            $font_path_css = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.css';

            $font_path_ttf_s3 = OSC::core('aws_s3')->getStorageUrl($font_path_ttf);

            if (!OSC::core('aws_s3')->doesObjectExist($font_path_ttf_s3)) {
//            exec(OSC_ROOT_PATH . "/convertfont.sh {$tmp_file_path} {$tmp_file_path}.woff2 > /dev/null 2>&1 &");
                if ($extension !== 'ttf') {
                    //fontforge -c "import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.familyname = 'Hello Sans'; f.generate(argv[2])" SWEETANDPRETTY.TTF SWEETANDPRETTY.TTF

                    exec("fontforge -c \"import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])\" {$tmp_file_path} {$tmp_file_path}.ttf");

                    if (!file_exists($tmp_file_path . '.ttf')) {
                        throw new Exception('Cannot convert font to TTF');
                    }
                }

                exec("fontforge -c \"import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])\" {$tmp_file_path} {$tmp_file_path}.woff2");

                if (!file_exists($tmp_file_path . '.woff2')) {
                    throw new Exception('Cannot convert font to WOFF2');
                }

                exec("fontforge -c \"import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])\" {$tmp_file_path} {$tmp_file_path}.svg");

                if (!file_exists($tmp_file_path . '.svg')) {
                    throw new Exception('Cannot convert font to SVG');
                }

                $css_content = <<<EOF
@font-face {
  font-family: '{$font_name}';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local('{$font_name}'), url({$font_key}.woff2) format('woff2'), url('{$font_key}.ttf') format('truetype'), url('{$font_key}.svg#{$font_key}') format('svg');
}
  
@media screen and (-webkit-min-device-pixel-ratio:0) {
    @font-face {
        font-family: '{$font_name}';
        src: url('{$font_key}.svg#{$font_key}') format('svg');
    }
}
EOF;

                file_put_contents($tmp_file_path . '.css', $css_content);

                $system_font_path = '/usr/share/fonts/9prints/personalizedDesign/' . $font_key . '.ttf';

                if (!file_exists($system_font_path) && copy($tmp_file_path . ($extension !== 'ttf' ? '.ttf' : ''), $system_font_path) !== true) {
                    throw new Exception('Cannot copy font to share folder');
                }

                try {
                    OSC::model('personalizedDesign/sync')->setData([
                        'ukey' => 'font/' . $font_key,
                        'sync_type' => 'font',
                        'sync_data' => ['ttf' => $font_path_ttf, 'woff2' => $font_path_woff2, 'svg' => $font_path_svg, 'css' => $font_path_css]
                    ])->save();
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                        throw new Exception($ex->getMessage());
                    }
                }

                $tmp_file_path_ttf = $tmp_file_path . ($extension !== 'ttf' ? '.ttf' : '');
                $file_path_ttf_s3 = OSC::core('aws_s3')->getStoragePath($font_path_ttf);
                $file_path_woff2_s3 = OSC::core('aws_s3')->getStoragePath($font_path_woff2);
                $file_path_svg_s3 = OSC::core('aws_s3')->getStoragePath($font_path_svg);
                $file_path_css_s3 = OSC::core('aws_s3')->getStoragePath($font_path_css);
                OSC::core('aws_s3')->upload($tmp_file_path_ttf, $file_path_ttf_s3);
                OSC::core('aws_s3')->upload($tmp_file_path . '.woff2', $file_path_woff2_s3);
                OSC::core('aws_s3')->upload($tmp_file_path . '.svg', $file_path_svg_s3);
                OSC::core('aws_s3')->upload($tmp_file_path . '.css', $file_path_css_s3);
            }

            $this->_ajaxResponse([
                'font_name' => $font_name,
                'css_url' => OSC::core('aws_s3')->getStorageUrl($font_path_css)
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionFont() {
        $font_key = preg_replace('/[^a-zA-Z0-9]/', '_', $this->_request->get('font'));
        $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
        $font_key = preg_replace('/_{2,}/', '_', $font_key);
        $font_key = strtolower($font_key);

        $font_name = implode(' ', array_map(function($segment) {
                    return ucfirst($segment);
                }, explode('_', $font_key)));

        $font_path_css = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.css';

        if (!OSC::core('aws_s3')->doesStorageObjectExist($font_path_css)) {
            $this->_ajaxError('The font [' . $font_name . '] is not exists');
        }

        $this->_ajaxResponse([
            'font_name' => $font_name,
            'css_url' => OSC::core('aws_s3')->getStorageUrl($font_path_css)
        ]);
    }

    public function actionReport() {
        $this->checkPermission('personalized_design/view_report');

        $id = intval($this->_request->get('id'));

        try {
            if ($id < 1) {
                throw new Exception('No design ID was found to delete');
            }

            $model = OSC::model('personalizedDesign/design')->load($id);

            if (!$this->checkPermission('personalized_design/full', false) && $model->data['member_id'] != $this->getAccount()->getId()) {
                //throw new Exception('You unable to view report of the design');
            }

            $layers = OSC::helper('personalizedDesign/common')->extractPersonalizedLayerData($model);

            $DB = OSC::core('database')->getReadAdapter();

            $DB->select('*', 'personalized_design_analytic', "design_id = {$model->getId()}", null , null, 'fetch_report');

            $report_items = $DB->fetchArrayAll('fetch_report');

            $DB->free('fetch_report');

            if(count($report_items) < 1) {
                throw new Exception('Design #'.$model->getId() . ' does not yet have any orders to report');
            }

            foreach($report_items as $report_item) {
                if (!isset($layers[$report_item['option_key']])) {
                    continue;
                }

                switch ($layers[$report_item['option_key']]['type']) {
                    case 'checker':
                        $layers[$report_item['option_key']]['counter'] += $report_item['counter'];
                        break;
                    case 'image':
                        if (isset($layers[$report_item['option_key']]['images'][$report_item['value_key']])) {
                            $layers[$report_item['option_key']]['images'][$report_item['value_key']]['counter'] += $report_item['counter'];
                        }
                        break;
                    case 'switcher':
                        if (isset($layers[$report_item['option_key']]['scenes'][$report_item['value_key']])) {
                            $layers[$report_item['option_key']]['scenes'][$report_item['value_key']]['counter'] += $report_item['counter'];
                        }
                        break;
                    default:
                        unset($layers[$layer_key]);
                }
            }

            $sorter = function($a, $b) {
                return $a['counter'] > $b['counter'] ? -1 : 1;
            };

            foreach (array_keys($layers) as $layer_key) {
                $layer = & $layers[$layer_key];

                switch ($layer['type']) {
                    case 'checker':
                        if (!isset($layer['counter'])) {
                            unset($layers[$layer_key]);
                        }
                        break;
                    case 'image':
                        foreach ($layer['images'] as $option_key => $option) {
                            if (!isset($option['counter'])) {
                                unset($layer['images'][$option_key]);
                            }
                        }

                        if (count($layer['images']) < 1) {
                            unset($layers[$layer_key]);
                        } else {
                            uasort($layer['images'], $sorter);
                        }
                        break;
                    case 'switcher':
                        foreach ($layer['scenes'] as $option_key => $option) {
                            if (!isset($option['counter'])) {
                                unset($layer['scenes'][$option_key]);
                            }
                        }

                        if (count($layer['scenes']) < 1) {
                            unset($layers[$layer_key]);
                        } else {
                            uasort($layer['scenes'], $sorter);
                        }
                        break;
                    default:
                        unset($layers[$layer_key]);
                        break;
                }
            }

            $output_html = $this->getTemplate()->build('personalizedDesign/report', [
                'title' => 'Report for design #' . $model->getId() . ': ' . $model->data['title'],
                'layers' => $layers
            ]);

            $this->output($output_html);
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());

            $path = 'list';

            static::redirect($this->getUrl('*/*/' . $path));
        }
    }

    public function actionExport() {
        $this->checkPermission('personalized_design/export');

        $id = intval($this->_request->get('id'));

        try {
            if ($id < 1) {
                throw new Exception('No design ID was found to delete');
            }

            $model = OSC::model('personalizedDesign/design')->load($id);

            if (!$this->checkPermission('personalized_design/full', false) && $model->data['member_id'] != $this->getAccount()->getId()) {
                //throw new Exception('You unable to export the design');
            }

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="personalizedDesign.' . $model->getUkey() . '.json"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            echo OSC::encode([
                'store' => OSC::$domain,
                'ukey' => $model->getUkey(),
                'title' => $model->data['title'],
                'design_data' => $model->data['design_data']
            ]);

            die;
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());

            $path = 'list';

            static::redirect($this->getUrl('*/*/' . $path));
        }
    }

    public function actionImport() {
        die(); //Remove legacy code
    }

    public function actionConvert() {
        try {
            if (!$this->getAccount()->isAdmin()) {
                throw new Exception('No permission');
            }

            //OSC::helper('personalizedDesign/convert')->convertPersonalizeData();

            OSC::core('cron')->addQueue('personalizedDesign/convert', null, ['skip_realtime', 'requeue_limit' => -1, 'ukey' => 'personalizedDesign/convert']);

            $this->addMessage('Successfully reverted design(s) to their previous states');
            static::redirect($this->getUrl('*/*/list'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }
    }

    public function actionDelete() {
        $this->checkPermission('personalized_design/delete');

        $ids = $this->_request->get('id');

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_map(function($id) {
            return intval($id);
        }, $ids);

        $ids = array_filter($ids, function($id) {
            return $id > 0;
        });

        if (count($ids) < 1) {
            $this->error('No design ID was found to delete');
        } else if (count($ids) > 100) {
            $this->error('Unable to delete more than 100 personalized design in a time');
        }

        try {
            $collection = OSC::model('personalizedDesign/design')->getCollection()->load($ids);

            if (!$this->checkPermission('personalized_design/full', false)) {
                foreach ($collection as $model) {
                    if (!$this->checkPermission('personalized_design/full', false) && $model->data['member_id'] != $this->getAccount()->getId()) {
                        throw new Exception('You unable to delete the design #' . $model->getId());
                    }
                }
            }

            foreach ($collection as $model) {
                if ($model->data['locked_flag'] != 0) {
                    continue;
                }

                $model->delete();
            }
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Personalized design has been deleted']);
        }

        $path = 'list';

        $this->addMessage('Personalized design has been deleted');

        static::redirect($this->getUrl('*/*/' . $path));
    }

    public function actionBrowse() {
        /* @var $collection Model_CatalogItemCustomize_Item_Collection */
        /* @var $model Model_CatalogItemCustomize_Item */

        try {
            $page_size = intval($this->_request->get('page_size'));

            if ($page_size == 0) {
                $page_size = 25;
            } else if ($page_size < 5) {
                $page_size = 5;
            } else if ($page_size > 100) {
                $page_size = 100;
            }

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $keywords = $this->_request->get('keywords');

            $collection = OSC::model('personalizedDesign/design')->getCollection()->addField('title', 'ukey', 'design_id', 'member_id', 'meta_data');

            if ($keywords && $keywords != '') {
                $condition = OSC::core('search_analyzer')->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('ukey', 'ukey', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('id', 'design_id', OSC_Search_Analyzer::TYPE_INT, true)
                    ->addKeyword('member_id', 'member_id', OSC_Search_Analyzer::TYPE_INT, true)
                    ->parse($keywords);
                $collection->setCondition($condition);
            }

            $collection
                ->addCondition('type_flag', Model_PersonalizedDesign_Design::TYPE_DESIGN_DEFAULT)
                ->addCondition('is_draft', 0)
                ->setPageSize($page_size)->setCurrentPage($page)->load();

            $items = [];

            OSC::register('default_spotify', 1);

            foreach ($collection as $model) {
                $item = [
                    'id' => $model->getId(),
                    'title' => $model->data['title'],
                    'url' => '#',
                    'image_url' => $model->getImageUrl(),
                ];

                $is_uploading_s3 = false;
                if (isset($model->data['meta_data']['image_folder_id_on_server']) && count($model->data['meta_data']['image_folder_id_on_server']) > 0) {
                    $is_uploading_s3 = true;
                }
                $item['is_uploading_s3'] = $is_uploading_s3;

                $items[] = $item;
            }

            $this->_ajaxResponse([
                'keywords' => [],
                'total' => intval($collection->collectionLength()),
                'offset' => intval((($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length()),
                'current_page' => intval($collection->getCurrentPage()),
                'page_size' => intval($collection->getPageSize()),
                'items' => $items
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionLoadPersonalizedBySemitest() {
        /* @var $collection Model_CatalogItemCustomize_Item_Collection */
        /* @var $model Model_CatalogItemCustomize_Item */

        try {
            $key = $this->_request->get('keywords');
            $page = $this->_request->get('page');
            $ids = $this->_request->get('ids');
            $collection = OSC::model('personalizedDesign/design')->getCollection()->addField('design_id', 'title');
            $size = 20;
            if ($ids) {
                $collection->load($ids);
            } else {
                if ($key && $key != '') {
                    $condition = OSC::core('search_analyzer')->addKeyword('title', 'title', OSC_Search_Analyzer::TYPE_STRING, true, true)
                        ->addKeyword('id', 'design_id', OSC_Search_Analyzer::TYPE_INT, true)
                        ->parse($key);
                    $collection->setCondition($condition)
                        ->setCurrentPage($page)
                        ->setPageSize($size)
                        ->load();
                }
            }

            $list = [];
            foreach ($collection as $item) {
                $list[$item->data['design_id']] = ['id' => $item->data['design_id'], 'text' => $item->data['design_id'] . '_' . $item->data['title']];
            }

            $result = [];
            if ($ids) {
                foreach ($ids as $id) {
                    if (!isset($list[$id]) || empty($list[$id])) {
                        continue;
                    }
                    $result[] = $list[$id];
                }
                $list = $result;
            } else {
                $list = array_values($list);
            }

            $this->_ajaxResponse(['items' => $list, 'total' => count($list),'page' => $page, 'size' => $size]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionRevert()
    {
        $this->checkPermission('personalized_design/revert');
        /* @var $model Model_PersonalizedDesign_VersionDesign */

        $id = $this->_request->get('id');

        $path = 'list';

        if ($id < 1) {
            $this->addMessage('Item ID is empty');
            static::redirectLastListUrl($this->getUrl($path));
        }

        $collection = OSC::model('personalizedDesign/versionDesign')->getCollection()->addCondition('design_id', $id)->sort('added_timestamp', OSC_Database::ORDER_ASC)->load()->getItems();

        $this->getTemplate()->setPageTitle('History');
        $this->getTemplate()->addBreadcrumb("History");
        $this->output($this->getTemplate()->build('personalizedDesign/revert', array('collection' => $collection)));
    }

    public function actionPostRevert()
    {
        $design_id = intval($this->_request->get('design_id'));
        $version_id = intval($this->_request->get('version_id'));
        /* @var $model Model_PersonalizedDesign_Design */
        $model = OSC::model('personalizedDesign/design');
        $version_model = OSC::model('personalizedDesign/versionDesign');
        try {
            $model->load($design_id);
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('postRevert'));
        }

        try {
            $version_model->load($version_id);
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Version design is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('postRevert'));
        }
        $active_design = OSC::model('personalizedDesign/versionDesign')->getCollection()
            ->addCondition('design_id', $design_id, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('record_id', $version_id, OSC_Database::OPERATOR_NOT_EQUAL)
            ->load();


        if ($this->_request->get('revert', null) !== null) {

            $data = [
                'design_data' => OSC::decode($version_model->data['design_data'], true),
                'modified_timestamp' => time()
            ];

            try {
                $model->setData($data)->save();
                if (count($active_design->getItems()) > 0) {
                    foreach ($active_design->getItems() as $item) {
                        OSC::model('personalizedDesign/versionDesign')->load($item->data['record_id'])->setData(['active' => 0])->save();
                    }
                }
                OSC::model('personalizedDesign/versionDesign')->load($version_id)->setData(['active' => 1])->save();
                $this->addMessage("Revert successfully");

                $path = 'list';

                $this->forward('*/*/' . $path);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }
    }

    public function actionExportSvg() {
        $svg_content = $this->_request->getRaw('svg_content');
        $width = $this->_request->get('width');
        $height = $this->_request->get('height');

        if (!$svg_content) {
            $this->_ajaxError('SVG content cannot be null');
        }

        if (!$width || !$height) {
            $this->_ajaxError('Must pass width and height');
        }

        try {
            $store_info = OSC::getStoreInfo();
            $ratio = $width / $height;

            if ($ratio > 1) {
                $width = 3000 * $ratio;
                $height = 3000;
            } else {
                $height = 3000 / $ratio;
                $width = 3000;
            }

            $request_data = [
                'svg_content' => $svg_content,
                'is_resize' => 1,
                'width' => $width,
                'height' => $height
            ];

            $response = OSC::core('network')->curl(
                OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/renderSvg', [
                'timeout' => 900,
                'headers' => [
                    'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])
                ],
                'json' => $request_data
            ]);

            if (!is_array($response['content']) || !isset($response['content']['result'])) {
                $this->_ajaxError('Response data is incorrect: ' . print_r($response['content'], 1));
            }

            if ($response['content']['result'] != 'OK') {
                switch ($response['content']['message']) {
                    case (strpos($response['content']['message'], 'is not exists') !== false):
                        $message = 'Please try again after a few minutes';
                        break;
                    default:
                        $message = $response['content']['message'];
                        break;
                }
                $this->_ajaxError($message);
            }

            $this->_ajaxResponse([
                'url' => $response['content']['data']['url']
            ]);
        } catch (Exception $exception) {
            $this->_ajaxError($exception->getMessage());
        }

        $this->_ajaxResponse();
    }

    public function actionPreviewSpotifySvg() {
        $background_color = $this->_request->get('backgroundColor');
        $bar_color = $this->_request->get('barColor');
        $display_style = $this->_request->get('displayStyle');

        if(!$background_color) {
            $background_color = 'none';
        } else {
            $background_color = '#'.$background_color;
        }

        if(!$bar_color) {
            $bar_color = '#000000';
        } else {
            $bar_color = '#'.$bar_color;
        }

        if ($display_style === Model_PersonalizedDesign_Design::SPOTIFY_DISPLAY_STYLE['QR_CODE']) {
            $svg_content_default = OSC::helper('personalizedDesign/common')->renderQrCodeSvgDefault($background_color, $bar_color);

            $svg_content = <<<EOF
<svg width="500" height="500" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid">
{$svg_content_default}
</svg>
EOF;
        } else {
            $svg_content_default = OSC::helper('personalizedDesign/common')->renderSvgSpotifyDefault($background_color, $bar_color);

            $svg_content = <<<EOF
<svg width="640" height="160" viewBox="0 0 400 100" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
{$svg_content_default}
</svg>
EOF;
        }

        header('Content-Type: image/svg+xml');

        echo $svg_content;
    }

    public function actionDuplicateDesignToD3() {
        $this->checkPermission('personalized_design/add');

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->addMessage('Item ID is empty');

            static::redirectLastListUrl($this->getUrl('list'));
        }

        $model = OSC::model('personalizedDesign/design');
        try {
            $model = $model->load($id);
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Item is not exist' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('list'));
        }

        try {
            if (isset($model->data['meta_data']['image_folder_id_on_server']) && count($model->data['meta_data']['image_folder_id_on_server']) > 0) {
                throw new Exception('You unable to duplicate the design which is uploading images to S3');
            }
            OSC::core('cron')->addQueue('personalizedDesign/duplicateDesignToD3', ['design_id' => $id, 'member_id' => $this->getAccount()->getId()], [
                'requeue_limit' => -1,
                'estimate_time' => 60*60
            ]);

            OSC::helper('core/common')->writeLog($model->getTableName(true), "Duplicate personalized design to D3 #{$model->getId()} from #{$model->getId()}");

            $this->addMessage('Duplicate personalized design to D3 successfully!');

            static::redirect($this->getUrl('*/*/list'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());

            static::redirectLastListUrl($this->getUrl('list'));
        }
    }

    public function actionGetSvgContent() {
        $design_id = $this->_request->get('design_id');
        $options_req = $this->_request->get('options');

        $options = ['original_render'];

        if (is_array($options_req) && count($options_req) > 0) {
            $options = array_unique(array_merge($options, $options_req));
        }

        if (!isset($design_id)) {
            $this->_ajaxError('Design id is required');
        }

        try {
            $model = OSC::model('personalizedDesign/design')->load($design_id);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        try {
            $result = [
                'document' => $model->data['design_data']['document'],
                'svg_content' => OSC::helper('personalizedDesign/common')->renderSvg($model, [], $options),
            ];

            $this->_ajaxResponse($result);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
