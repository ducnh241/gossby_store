<?php
/* @var $this Helper_Backend_Template */
$collection = $params['collection'];
$shop = $params['shop_data'];
$this->push(array('backend/dashboard.scss'), 'css');
$this->push('shop/common.scss', 'css');
$this->push('shop/request_payout.js','js');
?>
<div class="refresh-top">
    Hit refresh to get lastest data about your profit
    <u class="refresh" style="color: #20B3B5;cursor: pointer;font-weight: bold;">Refresh</u>
</div>
<div class="block m25">
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless grid-hover">
            <tr>
                <th style="text-align: left;width: 6%">ID</th>
                <th style="text-align: left;width: 15%">Order ID</th>
                <th style="text-align: center;width: 15%">Current Tier</th>
                <th style="text-align: center;width: 15%">Total Profit</th>
                <th style="text-align: center;width: 15%">Action</th>
                <th style="text-align: center; width: 15%">Date</th>
            </tr>
            <?php foreach ($collection as $item) : ?>
                <tr style="cursor: pointer" class="hover-tip">
                    <td style="text-align: left"><?= $item->getId() ?></td>
                    <td style="text-align: left"><?= $item->data['order_id']; ?></td>
                    <td style="text-align: center; text-transform: capitalize;"><?= $item->data['current_tier'] ?></td>
                    <td style="text-align: center;"><?= OSC::helper('catalog/common')->formatPriceByInteger($item->data['amount']) ?></td>
                    <td style="text-align: center; text-transform: capitalize;" <?php if ($item->data['additional_data']):?> class="profit-note" data-note="<?= $item->data['additional_data']['note']; ?><?php endif;?>">Order <?= $item->data['action'] ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y - h:i A', $item->data['added_timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data found</div>
    <?php endif; ?>
</div>