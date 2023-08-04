<?php

class Controller_Report_Backend extends Abstract_Backend_Controller {

    public function __construct() {
        parent::__construct();

        $this->getTemplate()->setCurrentMenuItemKey('report/dashboard')->addBreadcrumb(array('analytics', 'Analytics'), $this->getUrl('report/backend/index'));
    }

    public function actionMigrate() {
        $this->checkPermission('report');

        OSC::core('cron')->addScheduler('report/migrate', ['processor' => 1], '* * * * *');
        OSC::core('cron')->addScheduler('report/migrate', ['processor' => 2], '* * * * *');
        OSC::core('cron')->addScheduler('report/migrate', ['processor' => 3], '* * * * *');
        OSC::core('cron')->addScheduler('report/updateOrder', null, '* * * * *');

        echo 'DONE';
    }

    protected function _getABTest() {
        $this->checkPermission('report');

        $ab_test_key = $this->_request->get('ab_test_key');

        if (!$ab_test_key) {
            return null;
        }

        $ab_test_value = $this->_request->get('ab_test_value');

        if ($ab_test_value === '') {
            return null;
        }

        return ['key' => $ab_test_key, 'value' => $ab_test_value];
    }

    public function actionIndex() {
        $this->checkPermission('report');

        $fetched_data = OSC::helper('report/process')->fetchDashboardData($this->_request->get('range'), $this->_getABTest());

        $this->getTemplate()->setPageTitle('Analytics :: ' . $fetched_data['title']);

        $this->output($this->getTemplate()->build('report/dashboard', $fetched_data));
    }

    public function actionProductList() {
        $this->checkPermission('report');

        $fetched_data = OSC::helper('report/process')->fetchProductListData($this->_request->get('range'), $this->_request->get('page'));

        $this->getTemplate()
                ->setCurrentMenuItemKey('report/product')
                ->addBreadcrumb('Product List')
                ->setPageTitle('Product List :: ' . $fetched_data['title']);

        $this->output($this->getTemplate()->build('report/product/list', $fetched_data));
    }

    public function actionProductDetail() {
        if ($this->checkPermission('report', false) || $this->checkPermission('srefReport', false)) {
            try {
                $fetched_data = OSC::helper('report/process')->fetchProductDetailData($this->_request->get('id'), $this->_request->get('range'));

                if ($fetched_data === null) {
                    static::redirect($this->getUrl('*/*/productList', ['range' => $this->_request->get('range')]));
                }
            } catch (Exception $ex) {
                static::redirect($this->getUrl('*/*/productList', ['range' => $this->_request->get('range')]));
            }

            $range = $fetched_data['range'];

            if (is_array($range)) {
                if ($range[0] == $range[1]) {
                    $range = $range[0];
                } else {
                    $range = implode('-', $range);
                }
            }

            $this->getTemplate()->setCurrentMenuItemKey('report/product')
                ->addBreadcrumb('Product', $this->getUrl('*/*/productList', ['range' => $range]))
                ->addBreadcrumb($fetched_data['product_title'] . ' :: ' . $fetched_data['title'])
                ->setPageTitle($fetched_data['product_title'] . ' :: ' . $fetched_data['title']);

            $this->output($this->getTemplate()->build('report/product/detail', $fetched_data));
        } else {
            static::notFound('You don\'t have permission to view the page');
        }
    }
}
