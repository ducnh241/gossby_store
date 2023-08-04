<?php

class Helper_Catalog_Campaign_Common extends OSC_Object {

    public function reRenderProductId($product_ids, $account) {
        try {
            $products_collection = OSC::model('catalog/product')->getCollection()
                ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN)->load();
        } catch (Exception $ex) {
            throw new Exception($ex->getCode() === 404 ? 'Product is not exists' : $ex->getMessage());
        }

        try {
            $data = [];
            $ukeys = [];

            foreach ($products_collection as $key => $product) {
                if (!$product->isCampaignMode()) {
                    throw new Exception('Product is not campaign');
                }

                $campaign_data = $product->data['meta_data'];

                $print_apply_other_face = [];

                foreach ($campaign_data['campaign_config']['print_template_config'] as $product_config_new) {
                    if ($product_config_new['apply_other_face'] == 1 && count($product_config_new['segments']) > 1) {
                        $print_apply_other_face[] = $product_config_new['print_template_id'];
                    }
                }

                $version = time();
                $product_id = $product->getId();
                $ukeys[] = 'campaign/deleteRenderMockup:' . $product_id;

                $data[$product_id] = [
                    'ukey' => 'campaign/deleteRenderMockup:' . $product_id,
                    'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
                    'action' => 'v2DeleteRenderCampaignMockup',
                    'queue_data' => [
                        'campaign_id' => $product_id,
                        'version' => $version,
                        'data' => [
                            'product_id' => $product_id,
                            'version' => $version,
                            'timestamp_new' => [],
                            'timestamp_old' => [],
                            'print_apply_other_face' => $print_apply_other_face,
                            'member_id' => $account->getId(),
                            'rerender' => 'tool'
                        ]
                    ]
                ];
            }

            try {
                $model = OSC::model('catalog/product_bulkQueue')->loadByListUKey($ukeys);
                $model->delete();
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $total_inserted = OSC::model('catalog/product_bulkQueue')->insertMulti($data);

            OSC::core('cron')->addQueue('catalog/campaign_deleteRenderMockup', null, ['ukey' => 'catalog/deleteRenderCampaignMockup', 'requeue_limit' => -1, 'estimate_time' => 60 * 2]);

            return ['total_appended' => $total_inserted ?? 0];
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }
    }
}
?>