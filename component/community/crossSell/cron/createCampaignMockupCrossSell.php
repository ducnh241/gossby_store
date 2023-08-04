<?php

class Cron_CrossSell_CreateCampaignMockupCrossSell extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');
        //render mockup
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 50;

        $counter = 0;

        $count_error = 0;

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'createCampaignMockupCrossSell'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

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

                OSC::helper('crossSell/common')->renderCampaignMockup($data['design_id'], $data['side'], $data['folder'], $data['link'], $data['file_time']);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}