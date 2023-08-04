<?php

define('OSC_INNER', 1);
define('OSC_SITE_PATH', dirname(__FILE__));
define('OSC_SITE_KEY', 'osecore');

include OSC_SITE_PATH . '/app.php';
echo "<pre>";
$collection = OSC::model('report/report')->getCollection()
        ->addCondition('report_key', 'frontend_tpl:', OSC_Database::OPERATOR_LIKE_RIGHT)
        ->addCondition('report_key', '::catalog/revenue', OSC_Database::OPERATOR_LIKE_LEFT)
        ->load();

$keys = [];

foreach ($collection as $model) {
    if (isset($keys[$model->data['report_key']])) {
        continue;
    }

    $keys[$model->data['report_key']] = 1;

    echo $model->data['report_key'], "\n";
    var_dump(OSC::helper('core/report')->getReportDataByYear($model->data['report_key']));
}

$collection = OSC::model('report/report')->getCollection()
        ->addCondition('report_key', 'frontend_tpl:', OSC_Database::OPERATOR_LIKE_RIGHT)
        ->addCondition('report_key', '::unique_visitor', OSC_Database::OPERATOR_LIKE_LEFT)
        ->load();

$keys = [];

foreach ($collection as $model) {
    if (isset($keys[$model->data['report_key']])) {
        continue;
    }

    $keys[$model->data['report_key']] = 1;

    echo $model->data['report_key'], "\n";
    var_dump(OSC::helper('core/report')->getReportDataByYear($model->data['report_key']));
}

$collection = OSC::model('report/report')->getCollection()
        ->addCondition('report_key', 'frontend_tpl:', OSC_Database::OPERATOR_LIKE_RIGHT)
        ->addCondition('report_key', '::catalog/order', OSC_Database::OPERATOR_LIKE_LEFT)
        ->load();

$keys = [];

foreach ($collection as $model) {
    if (isset($keys[$model->data['report_key']])) {
        continue;
    }

    $keys[$model->data['report_key']] = 1;

    echo $model->data['report_key'], "\n";
    var_dump(OSC::helper('core/report')->getReportDataByYear($model->data['report_key']));
}