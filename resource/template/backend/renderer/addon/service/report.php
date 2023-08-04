<?php
/* @var $this Helper_Backend_Template */
$this->push('core/setting.scss', 'css');
$this->addComponent('daterangepicker');
$this->push(['report/common.scss', 'addon/service.scss', 'backend/dashboard.scss'], 'css');
$this->push(['report/common.js', 'addon/report.js'], 'js');

$data = $params['data'];

$addon_service = $params['addon_service']

?>
<div class="dashboard">
    <div class="filter-menu">
        <ul class="date-range" id="range" data-id='<?= $addon_service->getId() ?>'>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='today'>Today</a>
            </li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='yesterday'>Yesterday</a>
            </li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='thisweek'>This
                    Week</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='lastweek'>Last
                    Week</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='thismonth'>This
                    Month</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='lastmonth'>Last
                    Month</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='alltime'>All
                    Time</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initCustomDateRange" data-range='custom'>Custom</a>
            </li>
            <?= $this->getJSONTag($params['product_titles'], 'product-titles') ?>
        </ul>
    </div>
</div>

<div class="setting-config-panel post-frm m0 p25">
    <div class="js-result-table wrap-response-table">
        <div class="frm-grid--separate">
            <div class="setting-item clearfix">
                <h3 class="mb10">Report for Add-on</h3>
                <div class="setting-table">
                    <table class="">
                        <tbody>
                        <tr>
                            <th>Version</th>
                            <th>Unique visitor</th>
                            <th>Page view</th>
                            <th>Total order</th>
                            <th>Total sale</th>
                            <th>Total quantity</th>
                            <th>Revenue</th>
                            <th>AOV</th>
                            <th>CR</th>
                        </tr>
                        <tr>
                            <td>No data</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="wrap-loader" style="display: none">
            <div class="loader">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <div class="action-bar">
            <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
                Go Back
            </a>
        </div>
    </div>
</div>
