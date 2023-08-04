<?php

class Cron_D2_RetrySyncFlowReply extends OSC_Cron_Abstract
{
    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return bool
     * @throws OSC_Database_Model_Exception
     */
    public function process($params, $queue_added_timestamp)
    {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getAdapter();
        $limit = 100;

        $DB->select('*', OSC::model('catalog/product_bulkQueue')->getTableName(), "`queue_flag` = 1 AND `action` = 'retry_d2FlowReply'", '`added_timestamp` ASC', $limit, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');
        $DB->free('fetch_queue');

        if (empty($rows)) {
            return true;
        }

        OSC::helper('d2/common')->processFlowReply($rows, 'retry_d2FlowReply');

        return false;
    }
}
