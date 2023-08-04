<?php

class Cron_CrossSell_PushMockup extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 500;

        $counter = 0;

        $count_error = 0;

        $design_id = intval($params['design_id']);

        if ($design_id < 1) {
            return;
        }

        $total_variant = OSC::model('crossSell/pushMockup')->getCollection()->getTotalVariantByDesignId($design_id);

        $image_collection = OSC::model('crossSell/image')->getCollection()->getImagesByDesignId($design_id);

        if ($total_variant < 1) {
            $image_collection->delete();
        }

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('crossSell/pushMockup');

            $DB->select('*', $model->getTableName(), "`queue_flag` = " . Model_CrossSell_PushMockup::QUEUE_TYPE_BEGIN . " AND `design_id` =" . $design_id, '`id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                if (OSC::model('crossSell/pushMockup')->getCollection()->getTotalVariantByDesignId($design_id) < 1) {
                    $image_collection->delete();
                }

                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 3)->save();

            try {
                $request_data = [
                    'design_id' => $design_id,
                    'total_variant' => $total_variant,
                    'product_type_variant_id' => $model->data['product_type_variant_id'],
                    'mockups' => []
                ];

                $mockups = $model->data['data']['mockups'];

                foreach ($mockups as $image_id) {
                    $image = $image_collection->getItemByPK($image_id);

                    $request_data['mockups'][$image->getId()] = [
                        'position' => $image->data['position'],
                        'flag_main' => $image->data['flag_main'],
                        'url' => $image->data['filename_s3'] != '' ? $image->data['filename_s3'] : OSC_Storage::getStorageUrl($image->data['filename']),
                        'is_default_mockup' => $image->data['is_default_mockup']
                    ];
                }

                //push Cross Sell
                OSC::helper('crossSell/common')->callApi(Helper_CrossSell_Common::PUSH_MOCKUP_URL,$request_data);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error_message' => $ex->getMessage(), 'queue_flag' => Model_CrossSell_PushMockup::QUEUE_TYPE_ERROR, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}