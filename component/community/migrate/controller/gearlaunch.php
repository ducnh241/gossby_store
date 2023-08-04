<?php

class Controller_Migrate_GearLaunch extends Abstract_Backend_Controller {

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/product');

        $this->getTemplate()->setCurrentMenuItemKey('migrate')->addBreadcrumb(array('user', 'Migrate Tools'), $this->getUrl('migrate/gearlaunch/index'));
    }

    public function actionIndex() {
        if (key_exists('url', $this->_request->getAll('post')))  {
            try {
                $url = trim($this->_request->get('url'));

                if (!$url) {
                    throw new Exception('Please add a validate URL');
                }

                $migrate_type = $this->_request->get('migrate_type');

                if (!in_array($migrate_type, ['store', 'collection', 'campaign'], true)) {
                    throw new Exception('Please select a type for migrate');
                }

                $filter = [];

                foreach (['product_type', 'color', 'size'] as $filter_key) {
                    $filter_value = $this->_request->get('filter_' . $filter_key);

                    $filter_value = explode(',', $filter_value);
                    $filter_value = array_map(function($value) {
                        return Cron_Migrate_Gearlaunch::cleanFilterValue($value);
                    }, $filter_value);
                    $filter_value = array_filter($filter_value, function($value) {
                        return $value !== '';
                    });

                    $filter[$filter_key] = $filter_value;
                }

                $queue_key = time();

                $action_data = ['url' => $url, 'filter' => $filter];

                if ($migrate_type == 'collection') {
                    $collection_path = parse_url($url, PHP_URL_PATH);

                    if (!$collection_path) {
                        throw new Exception('Cannot detect collection path');
                    }

                    if (substr($collection_path, 0, 1) == '/') {
                        $collection_path = substr($collection_path, 1);
                    }

                    $action_data['collection'] = $collection_path;
                }

                OSC::model('migrate/gearlaunch')->setData([
                    'queue_key' => $queue_key,
                    'member_id' => $this->getAccount()->getId(),
                    'queue_flag' => 1,
                    'error_flag' => 0,
                    'error_message' => null,
                    'action_key' => $migrate_type == 'campaign' ? 'fetch_campaign' : 'fetch_collection',
                    'action_data' => $action_data,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ])->save();

                OSC::core('cron')->addQueue('migrate/gearlaunch', ['queue_key' => $queue_key], ['requeue_limit' => -1, 'skip_realtime', 'ukey' => 'migrate/gearlaunch:' . $queue_key]);

                $this->addMessage('The migrate queue has been added');

                static::redirectLastListUrl($this->getUrl('list'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $collection = OSC::model('migrate/gearlaunch')->getCollection()
                ->sort('queue_key', 'ASC')
                ->sort('added_timestamp', 'ASC')
                ->setPageSize(25)
                ->setCurrentPage($this->_request->get('page'))
                ->load();

        $this->getTemplate()->setPageTitle('GearLaunch');

        static::setLastListUrl();

        $this->output($this->getTemplate()->build('migrate/gearlaunch/main', ['collection' => $collection]));
    }

    public function actionQueueDelete() {
        try {
            $queue_id = intval($this->_request->get('id'));

            if ($queue_id < 1) {
                throw new Exception('Queue ID is incorrect');
            }

            OSC::model('migrate/gearlaunch')->load($queue_id)->delete();

            $this->addMessage('The migrate queue has been deleted');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirectLastListUrl($this->getUrl('list'));
    }

    public function actionQueueRerun() {
        try {
            $queue_id = intval($this->_request->get('id'));

            if ($queue_id < 1) {
                throw new Exception('Queue ID is incorrect');
            }

            $queue = OSC::model('migrate/gearlaunch')->load($queue_id);

            if ($queue->data['error_flag'] == 1 || $queue->data['queue_flag'] != 1) {
                $queue->setData(['error_flag' => 0, 'queue_flag' => 1, 'error_message' => null])->save();
            }

            OSC::core('cron')->addQueue('migrate/gearlaunch', ['queue_key' => $queue->data['queue_key']], ['requeue_limit' => -1, 'skip_realtime', 'ukey' => 'migrate/gearlaunch:' . $queue->data['queue_key']]);

            $this->addMessage('The migrate queue has been resetted');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        static::redirectLastListUrl($this->getUrl('list'));
    }

}
