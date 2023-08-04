<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_Catalog_Order_Index_Collection */
$this->addComponent('datePicker');
$collection = $params['collection'];
$this->push(array('backend/dashboard.scss'), 'css');
$this->push('catalog/order.js', 'js')->addComponent('uploader');

$shipping_default = $params['shipping_default'];

$payment_status_badge = [
    'pending' => 'red',
    'void' => 'red',
    'authorized' => 'blue',
    'paid' => 'green',
    'partially_paid' => 'yellow',
    'partially_refunded' => 'yellow',
    'refunded' => 'gray-dark',
    'wait_refund' => 'gray'
];
$shipping_method_badge = [
    'other' => 'green',
    'standard' => 'blue'
];

$fulfillment_status_badge = [
    'fulfilled' => 'green',
    'unfulfilled' => 'gray',
    'partially_fulfilled' => 'yellow'
];
$fraud_risk_level_badge = [
    'unknown' => 'gray',
    'normal' => 'green',
    'elevated' => 'yellow',
    'highest' => 'red'
];

$process_status_badge = [
    'processed' => 'green',
    'unprocess' => 'gray',
    'partially_process' => 'yellow',
    'process' => 'blue',
    'hold_order' => 'gray-dark'
];
?>
<div class="block m25">
    <div class="header-grid">
        <?= $this->build('backend/UI/search_form_custom', [
            'process_url' => $this->getUrl('*/*/search'),
            'search_keywords' => $params['search_keywords'],
            'filter_config' => $params['filter_config'],
            'filter_field' => $params['filter_field'],
            'selected_filter_field' => $params['selected_filter_field'],
            'default_search_field_key' => $params['default_search_field_key']
        ]) ?>
    </div>

    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='order_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 100px; text-align: left">Code</th>
                <th style="width: 150px; text-align: left">Date</th>
                <th style="text-align: left">Customer</th>
                <th style="text-align: left; width: 100px">Risk level</th>
                <th style="text-align: left; width: 100px">Shipping</th>
                <th style="text-align: left; width: 100px">Payment</th>
                <th style="text-align: left; width: 100px">Fulfillment</th>
                <th style="text-align: left; width: 100px">Process</th>
                <th style="width: 100px; text-align: right">Total</th>
                <th style="width: 75px; text-align: right"></th>
            </tr>
            <?php /* @var $order Model_Catalog_Order */ ?>
            <?php foreach ($collection as $order) : ?>
                <tr style="cursor: pointer">
                    <td style="text-align: center"><div class="styled-checkbox"><input type="checkbox" name="order_id" value="<?= $order->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins></div></td>
                    <td style="text-align: left"><?= $order->getCode() ?></td>
                    <td style="text-align: left"><?= date('d/m/Y - h:i A', $order->data['added_timestamp']) ?></td>
                    <td style="text-align: left"><?= $order->getFullName() ?> (<?= $order->data['email'] ?>)</td>
                    <td style="text-align: left"><span class="badge badge-<?= $fraud_risk_level_badge[$order->data['fraud_risk_level']] ?>"><?= Model_Catalog_Order::FRAUD_RISK_LEVEL[$order->data['fraud_risk_level']]['title'] ?></span></td>
                    <td style="text-align: left">
                        <?php
                        $shipping_method_key = $order->getShippingMethodKey();
                        $shipping_name = $order->getShippingMethodTitle();

                        /* shipping method old */
                        if ($shipping_method_key == 'standard') {
                            $shipping_method_key = $shipping_default->data['shipping_key'];
                            $shipping_name = $shipping_default->data['shipping_name'];
                        }

                        ?>
                        <span class="badge badge-<?= $order->isShippingDefault() ? $shipping_method_badge['standard'] : $shipping_method_badge['other'] ?>" title="<?= $shipping_name; ?>"><?= $shipping_name; ?></span>
                    </td>
                    <td style="text-align: left">
                        <?php
                        $additional_data = $order->data['additional_data'];
                        if (isset($additional_data['wait_refund'])) : ?>
                            <span class="badge badge-<?= $payment_status_badge['wait_refund'] ?>">
                                Refund Requested
                            </span>
                        <?php else : ?>
                            <span class="badge badge-<?= $payment_status_badge[$order->data['payment_status']] ?>"><?= Model_Catalog_Order::PAYMENT_STATUS[$order->data['payment_status']]; ?></span>
                        <?php endif;?>
                    </td>
                    <td style="text-align: left"><span class="badge badge-<?= $fulfillment_status_badge[$order->data['fulfillment_status']] ?>"><?= Model_Catalog_Order::FULFILLMENT_STATUS[$order->data['fulfillment_status']]; ?></span></td>
                    <td style="text-align: left">
                        <?php if ($order->data['member_hold']) : ?>
                            <span class="badge badge-<?= $process_status_badge['hold_order'] ?>">
                                Hold
                            </span>
                        <?php else : ?>
                            <span class="badge badge-<?= $process_status_badge[$order->data['process_status']] ?>"><?= Model_Catalog_Order::PROCESS_STATUS[$order->data['process_status']]; ?></span>
                        <?php endif;?>
                    </td>
                    <td style="text-align: right"><?= OSC::helper('catalog/common')->formatPrice($order->getFloatTotalPrice()) ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?= $order->getDetailUrl() ?>" target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['page'], $params['total_item'], $params['page_size'], 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
                Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
                There aren't any orders yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>