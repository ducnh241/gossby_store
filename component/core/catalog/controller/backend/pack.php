<?php

class Controller_Catalog_Backend_Pack extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();

        $this->checkPermission();

        $this->getTemplate()
            ->setCurrentMenuItemKey('catalog/product/pack')
            ->setPageTitle('Manage Product Packs');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    public function actionList() {
        $this->checkPermission();
        $this->getTemplate()->addBreadcrumb(['user', 'Manage Product Packs']);

        $configured_product_type_ids = OSC::model('catalog/product/pack')->getCollection()
            ->addField('product_type_id')
            ->load()
            ->toArray();

        $configured_product_type_ids = array_unique(array_column($configured_product_type_ids, 'product_type_id'));

        $configured_product_types = count($configured_product_type_ids) ? OSC::model('catalog/productType')->getCollection()
            ->addField('title')
            ->addCondition('id', $configured_product_type_ids, OSC_Database::OPERATOR_IN)
            ->load() : [];

        $not_configured_product_types = OSC::model('catalog/productType')->getCollection()
            ->addField('title')
            ->addCondition('id', $configured_product_type_ids, OSC_Database::OPERATOR_NOT_IN)
            ->load()
            ->toArray();

        $this->output($this->getTemplate()->build('catalog/pack/list', [
            'configured_product_types' => $configured_product_types,
            'not_configured_product_types' => $not_configured_product_types
        ]));
    }

    public function actionDetail() {
        $product_type_id = intval($this->_request->get('id'));

        $this->checkPermission();
        $this->getTemplate()->addBreadcrumb(['user', 'Edit Product Pack']);

        try {
            $product_type = OSC::model('catalog/productType')->load($product_type_id);
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() == 404 ? 'Product type not found' : $ex->getMessage());
            static::redirect($this->getUrl('*/*/list'));
        }

        $product_type_title = isset($product_type) ? $product_type->data['title'] : '';
        $collection = OSC::model('catalog/product_pack')
            ->getCollection()
            ->addCondition('product_type_id', $product_type_id)
            ->load();

        $output_html = $this->getTemplate()->build('catalog/pack/postForm', [
            'id' => $product_type_id,
            'collection' => $collection,
            'product_type_title' => $product_type_title,
        ]);

        $this->output($output_html);
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));

        $this->checkPermission();

        $model = OSC::model('catalog/product_pack');

        if ($id > 0) {
            try {
                $model = OSC::model('catalog/product_pack')->load($id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getCode() == 404 ? 'Product pack not found' : $ex->getMessage());
                static::redirect($this->getUrl('*/*/list'));
            }
        }

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $model->setData([
                'product_type_id' => intval($this->_request->get('product_type_id')),
                'discount_type' => intval($this->_request->get('discount_type')),
                'discount_value' => floatval($this->_request->get('discount_value')),
                'marketing_point_rate' => floatval($this->_request->get('marketing_point_rate'))
            ])->save();

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'html' => $this->getTemplate()->build('catalog/pack/item', ['pack' => $model]),
        ]);
    }
}

