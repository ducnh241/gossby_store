<?php

class Controller_Catalog_Backend_Customer extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();
        
        $this->checkPermission('catalog');

        $this->getTemplate()->setCurrentMenuItemKey('catalog_customer')->addBreadcrumb(array('user', 'Customer list'), $this->getUrl('catalog/backend_customer/list'));
    }

    public function actionBrowseGroup() {
        try {
            $groups = [];

            foreach (OSC::helper('catalog/common')->collectCustomerGroup() as $group_key => $group_data) {
                $groups[] = [
                    'id' => $group_key,
                    'title' => $group_data['title']
                ];
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(array(
            'keywords' => [],
            'total' => count($groups),
            'offset' => 0,
            'current_page' => 1,
            'page_size' => count($groups),
            'items' => $groups
        ));
    }

    public function actionBrowse() {
        /* @var $search OSC_Search_Adapter */
        $search = OSC::core('search')->getAdapter('backend');

        $page_size = intval($this->_request->get('page_size'));

        if ($page_size == 0) {
            $page_size = 25;
        } else if ($page_size < 5) {
            $page_size = 5;
        } else if ($page_size > 100) {
            $page_size = 100;
        }

        try {
            $search->setKeywords($this->_request->get('keywords'));
            $search->addFilter('module_key', 'catalog', OSC_Search::OPERATOR_EQUAL)
                    ->addFilter('item_group', 'customer', OSC_Search::OPERATOR_EQUAL);

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $search->setOffset(($page - 1) * $page_size)->setPageSize($page_size);

            $result = $search->fetch(['allow_no_keywords', 'auto_fix_page']);

            $customers = [];

            if (count($result['docs']) > 0) {
                $customer_ids = [];

                foreach ($result['docs'] as $doc) {
                    $customer_ids[] = $doc['item_id'];
                }
                try {
                    $list_customers = OSC::helper('account/customer')->getCustomerByListIds($customer_ids);
                } catch (Exception $ex) {
                    $list_customers = [];
                }

                $customers = [];
                if (count($list_customers) > 0) {
                    foreach ($list_customers as $customer) {
                        $customers[] = array(
                            'id' => $customer['customer_id'],
                            'title' => $customer['name'],
                            'email' => $customer['email'],
                            'phone' => $customer['phone']
                        );
                    }
                }
            }

            $this->_ajaxResponse(array(
                'keywords' => $result['keywords'],
                'total' => $result['total_item'],
                'offset' => $result['offset'],
                'current_page' => $result['current_page'],
                'page_size' => $result['page_size'],
                'items' => $customers
            ));
        } catch (OSC_Search_Exception_Condition $e) {
            $this->_ajaxError($e->getMessage(), $e->getCode());
        }
    }

}
