<?php

class Cron_D2_SyncFlowReply extends OSC_Cron_Abstract
{
    const CRON_TIMER = '*/2 * * * *'; //At every 2 minute.
    const CRON_SCHEDULER_FLAG = 1;

    /**
     * document filter https://support.airtable.com/docs/formula-field-reference
     * https://codepen.io/airtable/full/MeXqOg
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getAdapter();
        $limit = 100;

        $DB->select('*', OSC::model('catalog/product_bulkQueue')->getTableName(), "`queue_flag` = 1 AND `action` = 'd2FlowReply'", '`added_timestamp` ASC', $limit, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');
        $DB->free('fetch_queue');

        OSC::helper('d2/common')->processFlowReply($rows, 'd2FlowReply');

    }
}
