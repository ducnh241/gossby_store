<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');

$this->push('vendor/tooltipster/tooltipster.bundle.min.js', 'js');
$this->push(['vendor/tooltipster/tooltipster.bundle.min.css', 'vendor/tooltipster/tooltipster-sideTip-borderless.min.css'], 'css');

$chart_data = [];
$sum_data = [];

foreach ($params['data'] as $key => $records) {
    $chart_data[$key] = [
        'label' => [],
        'value' => []
    ];

    $sum_data[$key] = 0;

    foreach ($records as $record) {
        $chart_data[$key]['label'][] = $record['short_label'];
        $chart_data[$key]['value'][] = $record['value'];

        $sum_data[$key] += $record['value'];
    }
}

foreach (['revenue'] as $key) {
    foreach ($chart_data[$key]['value'] as $idx => $value) {
        $chart_data[$key]['value'][$idx] = $value;
    }
}

function __converionChartGetMaxValue($a, $b = 10) {
    if ($b < 10) {
        $b = 10;
    }

    $c = $a / $b;

    return $c > 10 ? __converionChartGetMaxValue($a, $b * 10) : (($b / 5) * (floor($a / ($b / 5)) + 1));
}

$conversion_max_level = __converionChartGetMaxValue($sum_data['pageviews']);
$conversion_levels = [];

$conversion_level_step = $conversion_max_level % 5 ? 1 : ($conversion_max_level / 5);

for ($i = ($conversion_max_level % 5 ? $conversion_max_level : 5); $i >= 0; $i --) {
    $conversion_levels[] = $conversion_level_step * $i;
}

$tax = abs($sum_data['tax_price']) - abs($sum_data['refunded_tax_price']);

?>
<div class="dashboard">
    <div class="filter-menu">
        <div class="filter-select">
        <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
            <?= $this->build('srefReport/memberSelect', ['action' => $params['action'], 'selectors' => $params['selectors']])?>
        <?php endif; ?>
        <?php if (OSC::controller()->checkPermission('report', false) && $params['is_sref_report'] == 0 && count($params['ab_test_keys']['keys']) > 0) : ?>
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
        </div>

        <ul class="date-range" data-insert-cb="initReportFilterByDateCondition">
            <li<?= ($params['range'] == 'today') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'today']); ?>">Today</a></li>
            <li<?= ($params['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'yesterday']); ?>">Yesterday</a></li>
            <li<?= ($params['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thisweek']); ?>">This Week</a></li>
            <li<?= ($params['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastweek']); ?>">Last Week</a></li>
            <li<?= ($params['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thismonth']); ?>">This Month</a></li>
            <li<?= ($params['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastmonth']); ?>">Last Month</a></li>
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
            <li<?= (is_array($params['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['range']) ? $params['range'][0] : '' ?>" data-end="<?= is_array($params['range']) ? $params['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
    </div>

    <div class="loading-overlay-wrapper white-bg">
        <div class="loading-overlay"></div>
    </div>

    <div class="loading-wrapper">
        <div class="dashboard-grid grid-col-4">
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group"><div class="header__heading">Purchases</div></div>
                    </div>
                    <ul class="action-log-widget flex-grid">
                        <li>
                            <div><span class="label">Orders</span>: <strong><?= isset($sum_data['orders']) && $sum_data['orders'] > 0 ? $sum_data['orders'] : 0; ?></strong></div>
                        </li>
                        <li>
                            <div><span class="label">Units Sold</span>: <strong><?= isset($sum_data['sales']) && $sum_data['sales'] > 0 ?  $sum_data['sales'] : 0; ?></strong></div>
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
                            <div><span class="label tooltip" title="The total income received, excluding cancelled orders">Revenue</span>: <strong><?= OSC::helper('catalog/common')->formatPrice(isset($sum_data['revenue']) ? $sum_data['revenue'] : 0); ?></strong></div>
                        </li>
                        <li>
                            <div><span class='label tooltip'
                                       title='The total amount of tips received, excluding cancelled orders'>Tip</span>:
                                <strong><?= OSC::helper('catalog/common')->formatPrice(isset($sum_data['tip_price']) ? $sum_data['tip_price'] : 0); ?></strong>
                            </div>
                        </li>
                        <li>
                            <div><span class='label tooltip'
                                       title='The total amount issued to customers from all refunded orders'>Refunded</span>:
                                <strong><?= OSC::helper('catalog/common')->formatPrice(abs($sum_data['refunded_price'])); ?></strong>
                            </div>
                        </li>
                        <li>
                            <div><span class='label tooltip'
                                       title='Tax: Amount Tax - Amount tax refunded'>Tax</span>:
                                <strong><?= OSC::helper('catalog/common')->formatPrice(abs($tax)); ?></strong>
                            </div>
                        </li>
                        <li>
                            <div><span class='label tooltip'
                                       title='Net Revenue: Revenue - Amount Refunded - Tax'>Net Revenue</span>:
                                <strong><?= OSC::helper('catalog/common')->formatPrice($sum_data['revenue'] - abs( $sum_data['refunded_price']) - abs($tax)); ?></strong>
                            </div>
                        </li>
                        <li></li>
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
                            <div><a class="label tooltip" title="Average Order Value: Units Sold/Orders">AOV</a>: <strong><?= isset($sum_data['orders']) && $sum_data['orders'] > 0 ? round(($sum_data['sales'] / $sum_data['orders']), 2) : 0; ?></strong> </div>
                        </li>
                        <li>
                            <div><a class="label tooltip" title="Average Order Value($): Revenue/Orders">AOV($)</a>: <?php if(isset($sum_data['orders']) && $sum_data['orders'] > 0) : ?> <strong><?= OSC::helper('catalog/common')->formatPrice($sum_data['revenue'] / $sum_data['orders']); ?></strong>/order <?php else : ?> 0 <?php endif; ?></div>
                        </li>
                        <li>
                            <div><a class="label tooltip" title="Conversion Rate: Orders/Unique Visitors">CR</a>:
                                <strong><?= isset($sum_data['orders']) && $sum_data['orders'] > 0 ?  round(($sum_data['orders'] / $sum_data['unique_visitors']) * 100, 2) : 0; ?>
                                    %</strong></div>
                        </li>
                        <li>
                            <div><a class="label tooltip" title="Earnings Per Visitor: Revenue/Unique Visitors">EPV</a>:
                                <strong>$<?= isset($sum_data['unique_visitors']) && $sum_data['unique_visitors'] > 0 ? round($sum_data['revenue'] / $sum_data['unique_visitors'], 2) : 0; ?></strong>
                            </div>
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
                            <div><span class="label">Visits</span>: <?= number_format($sum_data['visits']); ?></div>
                        </li>
                        <li>
                            <div><span class="label">Pageviews</span>: <?= number_format($sum_data['pageviews']); ?></div>
                        </li>
                        <li>
                            <div><span class="label">New Visitors</span>: <?= number_format($sum_data['new_visitors']); ?></div>
                        </li>
                        <li>
                            <div><span class="label">Unique Visitors</span>: <?= number_format($sum_data['unique_visitors']); ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="dashboard-grid grid-col-2">
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Order/Sale</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <canvas id="canvas_chart_order"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Revenue</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <canvas id="canvas_chart_revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-grid grid-col-2">
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Conversion rate</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <canvas id="canvas_chart_conversion_rate"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Pageviews/Visits</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <canvas id="canvas_chart_visit"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-grid grid-col-1">
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Shopping behavior</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <div class="conversion-chart">
                                <div class="chart-header">
                                    <div>&nbsp;</div>
                                    <div>
                                        <div class="label">View Products</div>
                                        <div class="value">
                                            <?= number_format($sum_data['product_unique_visitors']) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="label">Add to Cart</div>
                                        <div class="value">
                                            <span><?= number_format($sum_data['add_to_cart'] / $sum_data['product_unique_visitors'] * 100, 2) ?>%</span>
                                            <?= number_format($sum_data['add_to_cart']) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="label">Checkout</div>
                                        <div class="value">
                                            <span><?= number_format($sum_data['checkout_initialize'] / $sum_data['product_unique_visitors'] * 100, 2) ?>%</span>
                                            <?= number_format($sum_data['checkout_initialize']) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="label">Purchase</div>
                                        <div class="value">
                                            <span><?= number_format($sum_data['orders'] / $sum_data['product_unique_visitors'] * 100, 2) ?>%</span>
                                            <?= number_format($sum_data['orders']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="chart-body">
                                    <div class="scales" data-insert-cb="reportInitShoppingBehavior" data-max="<?= $conversion_max_level ?>">
                                        <?php $conversion_rate_scales = ''; ?>
                                        <div>
                                            <?php foreach ($conversion_levels as $level) : ?>
                                                <div><span><?= number_format($level) ?></span></div>
                                                <?php $conversion_rate_scales .= '<div></div>'; ?>
                                            <?php endforeach; ?>
                                        </div>
                                        <div data-value="<?= $sum_data['product_unique_visitors'] ?>"><?= $conversion_rate_scales ?></div>
                                        <div data-value="<?= $sum_data['add_to_cart'] ?>"><?= $conversion_rate_scales ?></div>
                                        <div data-value="<?= $sum_data['checkout_initialize'] ?>"><?= $conversion_rate_scales ?></div>
                                        <div data-value="<?= $sum_data['orders'] ?>"><?= $conversion_rate_scales ?></div>
                                    </div>
                                </div>
                                <div class="chart-footer">
                                    <div>&nbsp;</div>
                                    <div>
                                        <div class="label">No Cart Addition</div>
                                        <div class="value">
                                            <span><?= number_format(($sum_data['product_unique_visitors'] - $sum_data['add_to_cart']) / $sum_data['product_unique_visitors'] * 100, 2) ?>%</span>
                                            <?= number_format($sum_data['product_unique_visitors'] - $sum_data['add_to_cart']) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="label">Cart Abandonment</div>
                                        <div class="value">
                                            <span><?= number_format(($sum_data['add_to_cart'] - $sum_data['checkout_initialize']) / $sum_data['add_to_cart'] * 100, 2) ?>%</span>
                                            <?= number_format($sum_data['add_to_cart'] - $sum_data['checkout_initialize']) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="label">Checkout Abandonment</div>
                                        <div class="value">
                                            <span><?= number_format(($sum_data['checkout_initialize'] - $sum_data['orders']) / $sum_data['checkout_initialize'] * 100, 2) ?>%</span>
                                            <?= number_format($sum_data['checkout_initialize'] - $sum_data['orders']) ?>
                                        </div>
                                    </div>
                                    <div>&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.chartColors = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        blue_fill: 'rgba(54, 162, 235, 0.5)',
        blue_fill_without_opacity: 'rgba(154, 208, 245, 1)',
        purple: 'rgb(153, 102, 255)',
        purple_fill: 'rgba(153, 102, 255, 0.5)',
        purple_fill_without_color: 'rgba(204, 178, 255, 1)',
        grey: 'rgb(201, 203, 207)'
    };
    var config_order = {
        type: 'bar',
        data: {
            labels: ["<?= implode('","', $chart_data['orders']['label']) ?>"],
            datasets: [{
					order: 2,
                    label: 'Orders',
                    backgroundColor: window.chartColors.blue_fill_without_opacity,
                    borderColor: window.chartColors.blue_fill_without_opacity,
                    borderWidth: 0,
                    fill: true,
                    data: ["<?= implode('","', $chart_data['orders']['value']) ?>"]
                }, {
					order: 1,
                    label: 'Sales',
                    backgroundColor: window.chartColors.purple_fill_without_color,
                    borderColor: window.chartColors.purple_fill_without_color,
                    borderWidth: 0,
                    fill: true,
                    data: ["<?= implode('","', $chart_data['sales']['value']) ?>"]
                }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Order/Sales <?= $params["type"]; ?>'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (item) {
                        return $.digitGroupping(item.yLabel);
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                        display: true,
                        stacked: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true,
                            userCallback: function (item) {
                                return $.digitGroupping(item);
                            }
                        }
                    }]
            }
        }
    };
    var config_revenue = {
        type: 'bar',
        data: {
            labels: ["<?= implode('","', $chart_data['revenue']['label']) ?>"],
            datasets: [{
                    label: 'Revenue',
                    backgroundColor: window.chartColors.blue_fill,
                    borderColor: window.chartColors.blue,
                    borderWidth: 0,
                    fill: true,
                    data: ["<?= implode('","', $chart_data['revenue']['value']) ?>"]
                }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Revenue'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (item) {
                        return $.digitGroupping(item.yLabel);
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true,
                            userCallback: function (item) {
                                return $.digitGroupping(item);
                            }
                        }
                    }]
            }
        }
    };

    var config_conversion_rate = {
        type: 'line',
        data: {
            labels: ["<?= implode('","', $chart_data['conversion_rate']['label']) ?>"],
            datasets: [{
                    label: 'Conversion Rate',
                    backgroundColor: window.chartColors.blue_fill,
                    borderColor: window.chartColors.blue,
                    borderWidth: 2,
                    pointBorderWidth: 1,
                    pointHoverRadius: 0,
                    pointRadius: 0,
                    lineTension: 0,
                    fill: false,
                    data: ["<?= implode('","', $chart_data['conversion_rate']['value']) ?>"]
                }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Conversion Rate'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (item) {
                        return item.yLabel + '%';
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true,
                            userCallback: function (item) {
                                return item + '%';
                            }
                        }
                    }]
            }
        }
    };
    var config_visit = {
        type: 'line',
        data: {
            labels: ["<?= implode('","', $chart_data['pageviews']['label']) ?>"],
            datasets: [{
                    label: 'Pageviews',
                    backgroundColor: window.chartColors.purple_fill,
                    borderColor: window.chartColors.purple,
                    borderWidth: 2,
                    pointBorderWidth: 1,
                    pointHoverRadius: 0,
                    pointRadius: 0,
                    lineTension: 0,
                    fill: false,
                    data: ["<?= implode('","', $chart_data['pageviews']['value']) ?>"]
                }, {
                    label: 'Visits',
                    backgroundColor: window.chartColors.blue_fill,
                    borderColor: window.chartColors.blue,
                    borderWidth: 2,
                    pointBorderWidth: 1,
                    pointHoverRadius: 0,
                    pointRadius: 0,
                    lineTension: 0,
                    fill: false,
                    data: ["<?= implode('","', $chart_data['visits']['value']) ?>"]
                }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Visits/Pageviews <?= $params["type"]; ?>'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (item) {
                        return $.digitGroupping(item.yLabel);
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true,
                            userCallback: function (item) {
                                return $.digitGroupping(item);
                            }
                        }
                    }]
            }
        }
    };

    window.onload = function () {
        var chart_order_sale = document.getElementById('canvas_chart_order').getContext('2d');
        window.myLine = new Chart(chart_order_sale, config_order);
        var chart_pageview_visit = document.getElementById('canvas_chart_visit').getContext('2d');
        window.myLine = new Chart(chart_pageview_visit, config_visit);
        var chart_revenue = document.getElementById('canvas_chart_revenue').getContext('2d');
        window.myLine = new Chart(chart_revenue, config_revenue);
        var chart_conversion_rate = document.getElementById('canvas_chart_conversion_rate').getContext('2d');
        window.myLine = new Chart(chart_conversion_rate, config_conversion_rate);
    };

    $(document).ready(function () {
        $('.tooltip')?.tooltipster({
            theme: 'tooltipster-borderless'
        });
    });
</script>
