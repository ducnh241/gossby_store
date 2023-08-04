<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');
$data = $params['data'];

$referers = $params['referers'];
?>
<div class="dashboard">
    <div class="filter-menu">
        <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
            <?= $this->build('report/memberSelect',['action' => 'productDetail'])?>
        <?php endif; ?>
        <?php if (count($params['ab_test_keys']['keys']) > 0) : ?>
            <div class="ab-test">
                <div>
                    <div class="styled-select styled-select--small">
                        <?= $this->getJSONTag($params['ab_test_keys'], 'report_ab_test') ?>
                        <select data-link="<?= $this->rebuildUrl(['ab_test_key' => '', 'ab_test_value' => '']); ?>" data-insert-cb="initReportFilterABTest">
                            <option value="">All Results</option>
                            <?php foreach ($params['ab_test_keys']['keys'] as $key => $key_options) : ?>
                                <option value="<?= $this->safeString($key) ?>"<?php if (is_array($params['ab_test_keys']['current']) && $key == $params['ab_test_keys']['current']['key']) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($key) ?></option>                    
                            <?php endforeach; ?>
                        </select>
                        <ins></ins>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <ul class="date-range">
            <li<?= ($params['range'] == 'today') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'today']); ?>">Today</a></li>
            <li<?= ($params['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'yesterday']); ?>">Yesterday</a></li>
            <li<?= ($params['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thisweek']); ?>">This Week</a></li>
            <li<?= ($params['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastweek']); ?>">Last Week</a></li>
            <li<?= ($params['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thismonth']); ?>">This Month</a></li>
            <li<?= ($params['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastmonth']); ?>">Last Month</a></li>
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
            <li<?= (is_array($params['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['range']) ? $params['range'][0] : '' ?>" data-end="<?= is_array($params['range']) ? $params['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
    <div class="dashboard-grid grid-col-4">
        <div>
            <div class="block">
                <div class="header">
                    <div class="header__main-group"><div class="header__heading">Purchases</div></div>
                </div>
                <ul class="action-log-widget flex-grid">
                    <li>
                        <div><span class="label">Orders</span>: <strong><?= number_format($params['data']['orders']) ?></strong></div>
                    </li>
                    <li>
                        <div><span class="label">Units Sold</span>: <strong><?= number_format($params['data']['sales']) ?></strong></div>
                    </li>
                </ul>
            </div>
        </div>
        <div>
            <div class="block">
                <div class="header">
                    <div class="header__main-group"><div class="header__heading">Profit</div></div>
                </div>
                <ul class="action-log-widget flex-grid">
                    <li>
                        <div><span class="label">Revenue</span>: <strong><?= OSC::helper('catalog/common')->formatPrice(floatval($params['data']['revenue'])) ?></strong></div>
                    </li>
                    <li>&nbsp;</li>
                </ul>
            </div>
        </div>

        <div>
            <div class="block">
                <div class="header">
                    <div class="header__main-group"><div class="header__heading">Conversion rate</div></div>
                </div>
                <ul class="action-log-widget flex-grid">
                    <li>
                        <div><a class="label">CR</a>: <strong><?= round(($params['data']['orders'] / $params['data']['unique_visitors']) * 100, 2); ?>%</strong></div>
                    </li>
                    <li>
                        <div><a class="label">EPV</a>: <strong>$<?= round(($params['data']['revenue'] / $params['data']['unique_visitors']), 2); ?></strong></div>
                    </li>
                </ul>
            </div>
        </div>
        <div>
            <div class="block">
                <div class="header">
                    <div class="header__main-group"><div class="header__heading">Visits <?php if ($params['date_range'] === 'today') : ?> <span style="display: inline-block; width: 8px; height: 8px; background: #4eaf11; border-radius: 50%; vertical-align: middle; margin: 0 3px 0 10px;"></span> <?= $params['online'] ?> onlines<?php endif; ?></div></div>
                </div>
                <ul class="action-log-widget flex-grid">
                    <li>
                        <div><span class="label">Visits</span>: <?= number_format($params['data']['visits']); ?></div>
                    </li>
                    <li>
                        <div><span class="label">Unique Visitors</span>: <?= number_format($params['data']['unique_visitors']); ?></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php if (count($referers) > 0) : ?>
    <div class="block m25">    
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left">Referer</th>
                <th style="width: 150px; text-align: left">Orders</th>
                <th style="text-align: left; width: 100px">Items</th>
                <th style="text-align: left; width: 100px">Profit</th>
                <th style="text-align: left; width: 100px">Visits</th>
                <th style="text-align: left; width: 100px">Visitors</th>
                <th style="text-align: left; width: 100px">CR</th>
            </tr>
            <?php foreach ($referers as $referer => $referer_info) : ?>
                <tr style="cursor: pointer">
                    <td style="text-align: left"><?= $referer ?></td>
                    <td style="text-align: left"><?= number_format($referer_info['orders']) ?></td>
                    <td style="text-align: left"><?= number_format($referer_info['sales']) ?></td>
                    <td style="text-align: left"><?= OSC::helper('catalog/common')->formatPrice(floatval($referer_info['revenue'])) ?></td>
                    <td style="text-align: left"><?= number_format($referer_info['visits']) ?></td>
                    <td style="text-align: left"><?= number_format($referer_info['unique_visitors']) ?></td>
                    <td style="text-align: left"><?= round(($referer_info['orders'] / $referer_info['unique_visitors']) * 100, 2); ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>     
    </div>   
<?php endif; ?>
<?php if (count($params['variants']) > 0) : ?>
    <div class="block m25">
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 100px; text-align: left">Variant ID</th>
                <th style="text-align: left">Title</th>
                <th style="width: 150px; text-align: left">SKU</th>
                <th style="text-align: left; width: 100px">Solds</th>
                <th style="text-align: right; width: 100px">Profit</th>
            </tr>
            <?php foreach ($params['variants'] as $variant_id => $variant) : ?>
                <tr style="cursor: pointer">
                    <td style="text-align: left"><?= $variant_id ?></td>
                    <td style="text-align: left"><?= $variant['title'] ?></td>
                    <td style="text-align: left"><?= $variant['sku'] ?></td>
                    <td style="text-align: left"><?= number_format($variant['sales']) ?></td>
                    <td style="text-align: right"><?= OSC::helper('catalog/common')->formatPrice($variant['revenue'] != 0 ? $variant['revenue'] : 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>     
    </div>   
<?php endif; ?>