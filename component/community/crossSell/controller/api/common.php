<?php

class Controller_CrossSell_Api_Common extends Abstract_CrossSell_Controller_Api {

    public function actionCreateMockup() {
        $design_id = intval($this->_request->get('id'));

        if ($design_id < 1) {
            $this->_ajaxError('design id not found');
        }

        $link = trim($this->_request->get('link'));

        if ($link == '') {
            $this->_ajaxError('design not found');
        }

        $folder = trim($this->_request->get('folder'));

        if ($folder == '') {
            $this->_ajaxError('folder not found');
        }

        $file_time = trim($this->_request->get('file_time'));

        if ($file_time == '') {
            $this->_ajaxError('file_time not found');
        }

        $side = intval($this->_request->get('side'));

        try {
            $print_template_maps_collection = OSC::model('crossSell/printTemplateMaps')->getCollection()->load();

            foreach ($print_template_maps_collection as $print_template_map) {
                OSC::model('crossSell/pushMockup')->setData([
                        'ukey' => $design_id . '_' . $print_template_map->data['product_type_variant_id'],
                        'design_id' => $design_id,
                        'product_type_variant_id' => $print_template_map->data['product_type_variant_id'],
                        'count_mockup' => 0,
                        'total_mockup' => $print_template_map->data['total_mockup'],
                        'queue_flag' => 0,
                        'added_timestamp' => time(),
                        'modified_timestamp' => time()
                    ]
                )->save();
            }

            OSC::model('catalog/product_bulkQueue')->setData([
                'ukey' => 'crossSell/createMockupCampaign:' . $design_id,
                'member_id' => 1,
                'action' => 'createCampaignMockupCrossSell',
                'queue_data' => [
                    'link' => $link,
                    'folder' => $folder,
                    'file_time' => $file_time,
                    'design_id' => $design_id,
                    'side' => $side
                ]
            ])->save();

            OSC::core('cron')->addQueue('crossSell/createCampaignMockupCrossSell', null, ['ukey' => 'crossSell/createCampaignMockupCrossSell', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);

            $this->_ajaxResponse();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}