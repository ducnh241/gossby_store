<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');
$data = $params['data'];
$total = $params['total'];
$range = $params['range'];
$sref_member_id = $params['sref_member_id'];
$sref_group_id = $params['sref_group_id'];
$sort = $params['sort'];

if (is_array($range)) {
    if ($range[0] == $range[1]) {
        $range = $range[0];
    } else {
        $range = implode('-', $range);
    }
}
$build_params = [];
if ($range) {
    $build_params['range'] = $range;
}
if (intval($sref_group_id > 0)) {
    $build_params['sref_group_id'] = $sref_group_id;
}
if (intval($sref_member_id) > 0) {
    $build_params['sref_member_id'] = $sref_member_id;
}

?>
<div class="dashboard" data-insert-cb="initAdTrackingCampaign" data-range="<?= (is_array($params['range'])) ? $params['range'][0] .'-' . $params['range'][1] :  $params['range'] ?>">
    <div class="filter-menu">
        <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
            <?= $this->build('srefReport/memberSelect', ['action' => $params['action'], 'selectors' => $params['selectors']])?>
        <?php endif; ?>

        <ul class="date-range" data-insert-cb="initReportFilterByDateCondition">
            <li<?= ($params['range'] == 'today') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'today', 'page' => 1]); ?>">Today</a></li>
            <li<?= ($params['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'yesterday', 'page' => 1]); ?>">Yesterday</a></li>
            <li<?= ($params['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thisweek', 'page' => 1]); ?>">This Week</a></li>
            <li<?= ($params['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastweek', 'page' => 1]); ?>">Last Week</a></li>
            <li<?= ($params['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thismonth', 'page' => 1]); ?>">This Month</a></li>
            <li<?= ($params['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastmonth', 'page' => 1]); ?>">Last Month</a></li>
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'alltime', 'page' => 1]); ?>">All time</a></li>
            <li<?= (is_array($params['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '', 'page' => 1]); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['range']) ? $params['range'][0] : '' ?>" data-end="<?= is_array($params['range']) ? $params['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
        <input type="hidden" id="is_search" name="is_search" value="<?= $params['is_search']; ?>">
        <input type="hidden" id="sref_group_id" name="sref_group_id" value="<?= $sref_group_id; ?>">
        <input type="hidden" id="sref_member_id" name="sref_member_id" value="<?= $sref_member_id; ?>">
        <div class="clearfix"></div>
    </div>
</div>
<div class="block m25">
    <div class="loading-overlay-wrapper white-bg">
        <div class="loading-overlay"></div>
    </div>

    <div class="loading-wrapper">
        <div class="ads-tab-container">
            <div class="ads-tab-container__item active" data-id="campaign-list">
                <span>Campaigns</span>
                <span id="count-selected-campaign" class="d-none"></span>
            </div>
            <div class="ads-tab-container__item" data-id="campaign-detail">Ad Sets</div>
            <div class="ads-tab-container__item" data-id="adset-detail">Ads</div>
        </div>
        <div id="campaign-list" class="ads-tab-content">
            <div style="margin: 15px;">
                <div class="header-grid">
                    <?= $this->build('backend/UI/search_form_custom', [
                        'process_url' => $this->getUrl('*/*/search', $build_params),
                        'search_keywords' => $params['search_keywords'],
                        'filter_config' => $params['filter_config'],
                        'filter_field' => $params['filter_field'],
                        'selected_filter_field' => $params['selected_filter_field'],
                        'default_search_field_key' => $params['default_search_field_key']
                    ]) ?>
                </div>
            </div>
        <?php if (count($data) > 0) : ?>
            <table class="grid grid-borderless">
                <tr>
                    <th style="text-align: left; width: 450px;">Campaign</th>
                    <th style="text-align: left" <?= $sort['key'] === 'product_view_count' ? 'class="active"' : ''; ?>>
                        View
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'product_view_count', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'product_view_count' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'product_view_count', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'product_view_count' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                    <th style="text-align: left" <?= $sort['key'] === 'add_to_cart_count' ? 'class="active"' : ''; ?>>
                        Add To Cart
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'add_to_cart_count', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'add_to_cart_count' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'add_to_cart_count', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'add_to_cart_count' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                    <th style="text-align: left" <?= $sort['key'] === 'checkout_initialize_count' ? 'class="active"' : ''; ?>>
                        Checkout
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'checkout_initialize_count', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'checkout_initialize_count' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'checkout_initialize_count', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'checkout_initialize_count' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                    <th style="text-align: left" <?= $sort['key'] === 'purchase_count' ? 'class="active"' : ''; ?>>
                        Purchase
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'purchase_count', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'purchase_count' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'purchase_count', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'purchase_count' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                    <th style="text-align: left" <?= $sort['key'] === 'sale_count' ? 'class="active"' : ''; ?>>
                        Sale
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'sale_count', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'sale_count' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'sale_count', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'sale_count' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                    <th style="text-align: left">
                        CR (%)
                    </th>
                    <th style="text-align: left" <?= $sort['key'] === 'revenue' ? 'class="active"' : ''; ?>>
                        Subtotal Revenue
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'revenue', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'revenue' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'revenue', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'revenue' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                    <th style="text-align: left" <?= $sort['key'] === 'subtotal_revenue' ? 'class="active"' : ''; ?>>
                        Total Revenue
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'subtotal_revenue', 'sort_order' => 0]); ?>">
                            <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($sort['key'] === 'subtotal_revenue' && $sort['order'] === -1) ? 'active ml5':'ml5']); ?>
                        </a>
                        <a href="<?= $this->rebuildUrl(['sort_by' => 'subtotal_revenue', 'sort_order' => 1]); ?>">
                            <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($sort['key'] === 'subtotal_revenue' && $sort['order'] === 1) ? 'active ml5':'ml5']); ?>
                        </a>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: left;">Total <?= number_format($total['total_record']);?> <?= $total['total_record'] > 1 ? 'campaigns' : 'campaign';?></th>
                    <th style="text-align: left"><?= number_format($total['total_view']);?></th>
                    <th style="text-align: left"><?= number_format($total['total_add_to_cart']);?></th>
                    <th style="text-align: left"><?= number_format($total['total_checkout_initialize_count']);?></th>
                    <th style="text-align: left"><?= number_format($total['total_purchase_count']);?></th>
                    <th style="text-align: left"><?= number_format($total['total_sale_count']);?></th>
                    <th style="text-align: left"><?= number_format(($total['total_purchase_count'] / $total['total_view']) * 100, 2) ?></th>
                    <th style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPriceByInteger($total['total_subtotal_revenue']) ?></th>
                    <th style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPriceByInteger($total['total_revenue']) ?></th>
                </tr>
                <?php
                    foreach ($data as $item):
                ?>
                    <tr>
                        <td style="text-align: left">
                            <label for="cam_<?= $item['campaign_id'] ?>">
                                <div><b><?= urldecode($item['utm_campaign']) ?></b></div>
                                <div>
                                    <input type='checkbox' id="cam_<?= $item['campaign_id'] ?>" data-id=<?= $item['campaign_id'] ?> />
                                    <small>ID: <?= $item['campaign_id'] ?></small>
                                </div>
                            </label>
                        </td>
                        <td style="text-align: left"><?= number_format($item['product_view_count']) ?></td>
                        <td style="text-align: left"><?= number_format($item['add_to_cart_count']) ?></td>
                        <td style="text-align: left"><?= number_format($item['checkout_initialize_count']) ?></td>
                        <td style="text-align: left"><?= number_format($item['purchase_count']) ?></td>
                        <td style="text-align: left"><?= number_format($item['sale_count']) ?></td>
                        <td style="text-align: left"><?= number_format(($item['purchase_count'] / $item['product_view_count']) * 100, 2) ?>%</td>
                        <td style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPriceByInteger($item['subtotal_revenue']) ?></td>
                        <td style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPriceByInteger($item['revenue']) ?></td>
                    </tr>
                <?php
                    endforeach;
                ?>
            </table>
            <?php $pager = $this->buildPager($params['current_page'], $params['total']['total_record'], $params['page_size'], 'page'); ?>
            <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
        <?php else : ?>
            <div class="no-result">No data to display</div>
        <?php endif; ?>
        </div>
        <div id="campaign-detail" class="ads-tab-content d-none">
            <div class="ml25 mr25">
                <b>Campaign ID: <span class="campaign-id"></span></b>
            </div>
            <div class="block m25">
                <table class="grid grid-borderless">
                    <thead>
                        <tr>
                            <th style="text-align: left; width: 450px; border-bottom: 2px solid #ddd;">Adset</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">View</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Add To Cart</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Checkout</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Purchase</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Sale</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">CR (%)</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Subtotal Revenue</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="adset-detail" class="ads-tab-content d-none">
            <div class="ml25 mr25">
                <b>Adset ID: <span class="campaign-id"></span></b>
            </div>

            <div class="block m25">
                <table class="grid grid-borderless">
                    <thead>
                        <tr>
                            <th style="text-align: left; width: 450px; border-bottom: 2px solid #ddd;">Ad</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">View</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Add To Cart</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Checkout</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Purchase</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Sale</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">CR (%)</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Subtotal Revenue</th>
                            <th style="text-align: left; border-bottom: 2px solid #ddd;">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
