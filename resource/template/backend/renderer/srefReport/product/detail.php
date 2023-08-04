<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');
$data = $params['meta_data']['data'];

$list_members = OSC::helper('report/common')->getListMemberMkt();

$referers = $params['meta_data']['referers'];
?>
    <div class="dashboard">
        <div class="filter-menu">
            <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
                <?= $this->build('srefReport/memberSelect', ['action' => $params['action'], 'selectors' => $params['selectors'], 'product_page' => $params['product_page']])?>
            <?php endif; ?>
            <ul class="date-range">
                <li<?= ($params['meta_data']['range'] == 'today') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'today']); ?>">Today</a></li>
                <li<?= ($params['meta_data']['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'yesterday']); ?>">Yesterday</a></li>
                <li<?= ($params['meta_data']['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thisweek']); ?>">This Week</a></li>
                <li<?= ($params['meta_data']['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastweek']); ?>">Last Week</a></li>
                <li<?= ($params['meta_data']['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thismonth']); ?>">This Month</a></li>
                <li<?= ($params['meta_data']['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastmonth']); ?>">Last Month</a></li>
                <li<?= ($params['meta_data']['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
                <li<?= (is_array($params['meta_data']['range'])) ? ' class="active"' : ''; ?>>
                    <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][0] : '' ?>" data-end="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][1] : '' ?>">Custom</a>
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
                            <div><span class="label">Orders</span>: <strong><?= number_format($data['orders']) ?></strong></div>
                        </li>
                        <li>
                            <div><span class="label">Units Sold</span>: <strong><?= number_format($data['sales']) ?></strong></div>
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
                            <div><span class="label">Revenue</span>: <strong><?= OSC::helper('catalog/common')->formatPrice(floatval($data['revenue'])) ?></strong></div>
                        </li>
                        <li>&nbsp;</li>
                    </ul>
                </div>
            </div>
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group"><div class="header__heading">Conversion Rate</div></div>
                    </div>
                    <ul class="action-log-widget flex-grid">
                        <li>
                            <div><a class="label">CR</a>: <strong><?= round(($data['orders'] / $data['unique_visitors']) * 100, 2) ?>%</strong></div>
                        </li>
                        <li>
                            <div><a class="label">EPV</a>: <strong>$<?= round(($data['revenue'] / $data['unique_visitors']), 2) ?></strong></div>
                        </li>
                    </ul>
                </div>
            </div>
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group"><div class="header__heading">Visits</div></div>
                    </div>
                    <ul class="action-log-widget flex-grid">
                        <li>
                            <div><span class="label">Visits</span>: <?= number_format($data['visits']); ?></div>
                        </li>
                        <li>
                            <div><span class="label">Unique Visitors</span>: <?= number_format($data['unique_visitors']); ?></div>
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
                <th style="text-align: left; width: 150px">Orders</th>
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
<?php if (count($params['meta_data']['variants']) > 0) : ?>
    <?= $this->build(
            'srefReport/productAttributeSelector',
            [
                    'product_attribute_options' => $params['product_attribute_options'] ?? [],
                    'variants' => $params['meta_data']['variants'] ?? []
            ]
    ) ?>
<?php endif; ?>
