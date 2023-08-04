<?php

class Controller_Core_Backend_Cron extends Abstract_Backend_Controller {

    public function __construct() {
        parent::__construct();
        if (OSC::isPrimaryStore()) {
            $this->checkPermission('developer/cron_manager');
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }
    }

    public function actionList() {
        $this->getTemplate()
            ->setCurrentMenuItemKey('developer/developer')
            ->addBreadcrumb('Cron list', $this->getUrl('*/*/*'))
            ->setPageTitle('Developer');

        $collection = OSC::model('core/cron_queue')->getCollection()
                ->setPageSize(50)
                ->addCondition('cron_name', ['feed/product', 'feed/seeding', 'feed/render'], OSC_Database::OPERATOR_NOT_IN)
                ->setCurrentPage($this->_request->get('page'))
                ->load();

        $this->output($this->getTemplate()->build('core/cron/list', array('collection' => $collection)));
    }

    public function actionInfo() {
        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            echo 'Not found';
            die;
        }

        try {
            /* @var $model Model_Core_Cron_Queue */
            $model = OSC::model('core/cron_queue')->load($id);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

        echo '<pre>';
        var_dump($model->data);
    }

    public function actionExecute() {
        if (OSC::isPrimaryStore()) {
            if (!$this->checkPermission('developer/cron_manager/requeue', false)) {
                $this->error('You don\'t have permission to view the page');
            }
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->error('Queue ID is empty');
        }

        try {
            /* @var $model Model_Core_Cron_Queue */
            $model = OSC::model('core/cron_queue')->load($id);
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        OSC::core('cron')->execute($model->getId());

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse();
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionRecron() {
        if (OSC::isPrimaryStore()) {
            if (!$this->checkPermission('developer/cron_manager/requeue', false)) {
                $this->error('You don\'t have permission to view the page');
            }
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }

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
            $this->error('No queue ID was found to requeue');
        }

        /* @var $DB OSC_Database */

        try {
            OSC::core('database')->update(
                    'core/cron_queue',
                    [
                        'error_flag' => 0,
                        'error_message' => '',
                        'locked_key' => '',
                        'locked_timestamp' => 0,
                        'system_process_id' => null
                    ],
                    'FIND_IN_SET(queue_id,\'' . implode(',', array_unique($ids)) . '\')'
            );
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Queues has been recroned']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionDelete() {
        if (OSC::isPrimaryStore()) {
            if (!$this->checkPermission('developer/cron_manager/delete', false)) {
                $this->error('You don\'t have permission to view the page');
            }
        } else {
            if (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) != 1) {
                static::notFound();
            }
        }

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
            $this->error('No queue ID was found to delete');
        }

        /* @var $DB OSC_Database */

        try {
            OSC::core('database')->delete('core/cron_queue', 'FIND_IN_SET(queue_id,\'' . implode(',', array_unique($ids)) . '\')');
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

        if ($this->_request->isAjax()) {
            $this->_ajaxResponse(['message' => 'Queues has been deleted']);
        }

        static::redirect($this->getUrl('*/*/list'));
    }

}
