<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');
$data = $params['data'];
$range = $params['range'];
$campaign_id = $params['campaign_id'];
$adset_id = $params['adset_id'];
$sort_product_view = $params['sort_product_view'];

if (is_array($range)) {
    if ($range[0] == $range[1]) {
        $range = $range[0];
    } else {
        $range = implode('-', $range);
    }
}
?>
<div class="dashboard">
    <div class="filter-menu">
        <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
            <?= $this->build('srefReport/memberSelect', ['action' => $params['action'], 'selectors' => $params['selectors']])?>
        <?php endif; ?>

        <ul class="date-range">
            <li<?= ($params['range'] == 'today') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'today', 'page' => 1]); ?>">Today</a></li>
            <li<?= ($params['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'yesterday', 'page' => 1]); ?>">Yesterday</a></li>
            <li<?= ($params['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thisweek', 'page' => 1]); ?>">This Week</a></li>
            <li<?= ($params['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastweek', 'page' => 1]); ?>">Last Week</a></li>
            <li<?= ($params['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thismonth', 'page' => 1]); ?>">This Month</a></li>
            <li<?= ($params['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastmonth', 'page' => 1]); ?>">Last Month</a></li>
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'alltime', 'page' => 1]); ?>">All time</a></li>
            <li<?= (is_array($params['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '', 'page' => 1]); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['range']) ? $params['range'][0] : '' ?>" data-end="<?= is_array($params['range']) ? $params['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<div class="ml25 mr25">
    <b>Campaign ID: <?= $campaign_id ?></b> | <b>Adset ID: <?= $adset_id ?></b>
</div>

<div class="block m25">
    <?php if (count($data) > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left">Ad</th>
                <th style="text-align: left">
                    <a href="<?= $this->rebuildUrl(['sort_product_view' => $sort_product_view  == 1 ? -1 : 1]); ?>">
                        View <?= $this->getIcon($sort_product_view  == 1 ?'arrow-up': 'arrow-down', ['height' => 10, 'class'=> 'ml5']); ?>     
                    </a>
                </th>
                <th style="text-align: left">Add To Cart</th>
                <th style="text-align: left">Checkout</th>
                <th style="text-align: left">Purchase</th>
                <th style="text-align: left">CR (%)</th>
                <th style="text-align: left">Subtotal Revenue</th>
                <th style="text-align: left">Total Revenue</th>
            </tr>
            <?php
                foreach ($data as $item):
            ?>
                <tr>
                    <td style="text-align: left">
                        <div><b><?= $item['ad_name'] ?$item['ad_name'] :'-' ?></b></div>
                        <div><small>ID: <?= $item['ad_id'] ?></small></div>
                    </td>
                    <td style="text-align: left"><?= number_format($item['product_view_count']) ?></td>
                    <td style="text-align: left"><?= number_format($item['add_to_cart_count']) ?></td>
                    <td style="text-align: left"><?= number_format($item['checkout_initialize_count']) ?></td>
                    <td style="text-align: left"><?= number_format($item['purchase_count']) ?></td>
                    <td style="text-align: left"><?= number_format(($item['purchase_count'] / $item['product_view_count']) * 100, 2) ?>%</td>
                    <td style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPrice($item['subtotal_revenue']) ?></td>
                    <td style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPrice($item['revenue']) ?></td>
                </tr>
            <?php
                endforeach;
            ?>
        </table>
        <?php $pager = $this->buildPager($params['current_page'], $params['total_rows'], $params['page_size'], 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data to display</div>
    <?php endif; ?>
</div>