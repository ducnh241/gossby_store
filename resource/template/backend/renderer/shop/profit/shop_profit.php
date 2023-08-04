<?php
/* @var $this Helper_Backend_Template */
$collection = $params['collection'];
$shop_data = $params['shop_data']->data;
$account_data = $params['account_data'];
$account_data_history = $params['account_data_history'];
$status_arr = $params['status_arr'];
$shop = $params['shop_data'];
$this->push(array('backend/dashboard.scss'), 'css');
$this->push('shop/common.scss', 'css');
$this->push('shop/request_payout.js', 'js');
?>

<div class="refresh-top">
    Hit refresh to get lastest data about your profit
    <u class="refresh" style="color: #20B3B5;cursor: pointer;font-weight: bold;">Refresh</u>
</div>

<div class="profit-analytics-items m25">
    <div class="section-item">
        <div class="content_box">
            <div class="icon"><?= $this->getIcon('home') ?></div>
            <div class="info">
                <div class="title">Shop Info</div>
                <div class="desc">
                    Name: <?= $shop->data['shop_name']; ?><br/>
                    Domain: <?= $shop->data['shop_domain'] ?><br/>
                    Tier: <?= $shop->data['tier'] ?>
                </div>
            </div>
        </div>
    </div>
    <div class="section-item">
        <div class="content_box">
            <div class="icon"><?= $this->getIcon('users') ?></div>
            <div class="info">
                <div class="title">Payout Accounts</div>
                <div class="desc">
                    <?php
                    if (count($params['accounts_data']) > 0) :
                        ?>
                        <ul>
                            <?php
                            foreach ($params['accounts_data'] as $key => $value):
                                ?>
                                <li>
                                    <span><?= $key; ?>:</span>
                                    <span><?php echo implode(',', $value); ?></span>
                                </li>
                            <?php
                            endforeach;
                            ?>
                        </ul>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="section-item">
        <div class="content_box">
            <div class="icon"><?= $this->getIcon('credit-card-regular') ?></div>
            <div class="info w-50">
                <div class="title">Available To Withdraw</div>
                <div class="count available_withdraw"><?= OSC::helper('catalog/common')->formatPriceByInteger($shop->data['available_withdraw']); ?></div>
                <div>
                    <a data-insert-cb="initPopupPayout" href="#"
                       class="btn btn-primary btn-small"><?= $this->getIcon('plus') ?> Request Payout</a>
                </div>
            </div>
            <div class="info w-50">
                <div class="title">Processing Profit</div>
                <div class="count"><?= OSC::helper('catalog/common')->formatPriceByInteger($shop->data['processing']); ?></div>
            </div>
        </div>
    </div>
    <div class="section-item">
        <div class="content_box">
            <div class="icon"><?= $this->getIcon('analytics') ?></div>
            <div class="info w-50">
                <div class="title">Withdraw</div>
                <div class="count"><?= OSC::helper('catalog/common')->formatPriceByInteger($shop->data['withdraw']); ?></div>
            </div>
            <div class="info w-50">
                <div class="title">All Time Profit</div>
                <div class="count"><?= OSC::helper('catalog/common')->formatPriceByInteger($shop->data['all_time_profit']); ?></div>
            </div>
        </div>
    </div>
</div>
<div class="block m25">
    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless grid-hover">
            <tr>
                <th style="text-align: center;">ID</th>
                <th style="text-align: center;">Date</th>
                <th style="text-align: center;">Store</th>
                <th style="text-align: center;">Payout Email</th>
                <th style="text-align: center;">Payment Provider</th>
                <th style="text-align: center;">Amount</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center; width: 5%;"></th>
            </tr>
            <?php foreach ($collection as $item) : ?>
                <tr style="cursor: pointer">
                    <td style="text-align: center"><?= $item->getId() ?></td>
                    <td style="text-align: center"><?= date('d/m/Y - h:i A', $item->data['added_timestamp']); ?></td>
                    <td style="text-align: center"><?= $shop_data['shop_name'] ?></td>
                    <td style="text-align: center"><?= $account_data_history[$item->data['payout_account_id']]['account_email'] ?></td>
                    <td style="text-align: center; text-transform: capitalize;"><?= $account_data_history[$item->data['payout_account_id']]['account_type'] ?></td>
                    <td style="text-align: center"><?= OSC::helper('catalog/common')->formatPriceByInteger($item->data['amount']) ?></td>
                    <td style="text-align: center; text-transform: capitalize;">
                        <div class="profit-badge <?= $item->data['status'] ?>"><?= $item->data['status'] ?></div>
                    </td>
                    <td style="text-align: center"><?php if ($item->data['status'] == 'pending'): ?>
                            <div style="position: absolute;">
                                <div class=" close cancel_payout_requeset"
                                     data-payout-request-id="<?= $item->getId() ?>"
                                     data-amount="<?= OSC::helper('catalog/common')->integerToFloat($item->data['amount']); ?>"></div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data found</div>
    <?php endif; ?>
</div>