<?php
class Controller_CatalogItemCustomize_Api extends Abstract_CatalogItemCustomize_Controller_Api {

    public function actionSavePrinterUrl() {
        try {
            $design_key = $this->_request->get('design_key');
            $printer_url = $this->_request->get('printer_url');
            $preview_url = $this->_request->get('preview_url');

            $DB = OSC::core('database')->getWriteAdapter();

            try {
                $design = OSC::model('catalogItemCustomize/design')->loadByUkey($design_key);
                if (!$design) {
                    throw new Exception('Not exist model with design_key = ' . $design_key);
                }
            } catch (Exception $e) {
                throw new Exception('Not exist model with design_key = ' . $row['design_id']);
            }

            $design->setData(['printer_image_url' => $printer_url, 'design_image_url' => $preview_url, 'state' => 3])->save();


            $order_map_collection = OSC::model('catalogItemCustomize/orderMap')->getCollection()->addCondition('design_id', $design->getId())->load();

            $order_line_ids = [];

            foreach ($order_map_collection as $order_map) {
                $order_line_ids[] = $order_map->data['order_line_id'];
            }

            $order_line_items = OSC::model('catalog/order_item')->getCollection()->load($order_line_ids);

            foreach ($order_line_items as $order_line_item) {
                $custom_data = [];

                foreach ($order_line_item->data['custom_data'] as $key => $value) {
                    if ($value['key'] == 'customize') {
                       $value['data']['printer_image_url'] = $printer_url;
                       $value['data']['design_image_urls'] = $preview_url;
                       $value['data']['design_key'] = $design_key;

                    }
                    $custom_data[] = $value;
                }
              
                $order_line_item->setData('custom_data', $custom_data)->save(); 
                
                OSC::core('observer')->dispatchEvent('catalog/orderUpdate', $order_line_item->data['order_id']);

            }

            $this->_ajaxResponse('ok');
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

}