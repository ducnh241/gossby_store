<?php

class Observer_Backend_Backend {

    public static function collectPermKey($params) {
        $params['permission_map']['backend'] = 'Backend Access';
    }

    public static function collectWidget($params) {
        $columns = $params['columns'];

        $appItem = self::_collectAppItem();

        if ($appItem) {
            $columns[0][] = $appItem;
        }


        $analytics = self::_collectAnalytics();
        if ($analytics) {
            $columns[1][] = $analytics;
        }

        $actionLog = self::_collectActionLog();

        if ($actionLog) {
            $columns[2][] = $actionLog;
        }


        $params['columns'] = $columns;
    }

    protected static function _collectAnalytics() {
        return OSC::controller()->getTemplate()->build('backend/dashboard/analytics');
    }

    protected static function _collectActionLog() {
        if (OSC::helper('user/authentication')->getMember()->data['group_id'] != OSC::systemRegistry('root_group')['admin']) {
            return '';
        }

        $collection = OSC::model('backend/log')->getCollection()->sort('added_timestamp', 'DESC')->setLimit(5)->load();

        if ($collection->length() > 0) {
            return OSC::controller()->getTemplate()->build('backend/dashboard/actionLog', array('collection' => $collection));
        }

        return '';
    }

    protected static function _collectAppItem() {
        return '';
    }

}
