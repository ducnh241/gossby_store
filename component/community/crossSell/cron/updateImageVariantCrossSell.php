<?php

class Cron_CrossSell_UpdateImageVariantCrossSell extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 200;

        $counter = 0;

        $count_error = 0;

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'updateImageVariantCrossSell'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                $data = $model->data['queue_data'];

                $image_id = $data['image_id'];

                $design_id = $data['callback']['design_id'];
                $list_variant_ids = $data['callback']['list_variant_ids'];


                $ukeys_push_mockup = [];

                foreach ($list_variant_ids as $variant_id) {
                    $ukeys_push_mockup[] = $design_id . '_' . $variant_id;
                }

                $queue_push_mockup_collection = OSC::model('crossSell/pushMockup')->getCollection()->getItemsByUkeys($ukeys_push_mockup);

                foreach ($queue_push_mockup_collection as $queue_push_mockup) {
                    $data_push_mockup = $queue_push_mockup->data['data'];
                    $count_mockup = $queue_push_mockup->data['count_mockup'];

                    $data_push_mockup['mockups'][] = $image_id;

                    $queue_push_mockup->setData(
                        [
                            'data' => $data_push_mockup,
                            'count_mockup' => $count_mockup + 1
                        ]
                    )->save();
                }

                if (OSC::model('crossSell/pushMockup')->getCollection()->checkFullMockups($design_id)) {
                    OSC::model('crossSell/pushMockup')->getCollection()->setQueueFlagRunning($design_id);
                    OSC::core('cron')->addQueue('crossSell/pushMockup', ['design_id' => $design_id], ['ukey' => 'crossSell/pushMockup' . $design_id , 'requeue_limit' => -1, 'skip_realtime','estimate_time' => 60 * 20]);
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 1, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
