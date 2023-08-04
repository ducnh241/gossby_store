<?php
/* @var $this Helper_Backend_Template */
/* @var $order Model_Catalog_Order */

$payment_status_badge = [
    'pending' => 'red',
    'void' => 'red',
    'authorized' => 'green',
    'paid' => 'blue',
    'partially_paid' => 'yellow',
    'partially_refunded' => 'gray-dark',
    'refunded' => 'gray'
];

$map_fulfill_service = [
    'dls' => 'Dls',
    'custom_cat' => 'Custom Cat',
    'district_photo' => 'District Photo',
    'harrier' => 'Harrier',
    'prima' => 'Prima'
];

$order = $params['order'];

$percentRefund = 0;
if ($order->data['refunded'] && $order->data['paid']) {
    $percentRefund = OSC::helper('catalog/common')->floatToInteger($order->data['refunded'] / $order->data['paid']);
}

$this->push('catalog/order.js', 'js');
$this->push('catalog/timeline-order-logs.scss', 'css');
?>
<div class="post-frm order-detail p25" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <?php foreach ($params['line_item_groups'] as $group_key => $group) : ?>


                <div class="block mb20">
                    <div class="plr20">
                        <div class="frm-heading">
                            <div class="frm-heading__main"><div class="frm-heading__title"><?= $group_key == 'unfulfilled' ? 'Unfulfilled' : ($group_key == 'refunded' ? 'Refunded' : (is_numeric(stripos($group_key,'process')) == true  ? 'Process' : 'Fulfilled') )?> (<?= $group['quantity'] ?>)</div></div>
                            <?php if (isset($group['service'])) : ?><div> <?= $map_fulfill_service[$group['service']] ;?> </div> <?php endif;?>
                        </div>
                        <table class="grid grid-borderless e20 line-item-list" style="border-bottom: 0 !important">
                            <?php /* @var $line_item['mode'] Model_Catalog_Order_Item */ ?>
                            <?php foreach ($group['line_items'] as $line_item):
                                $customPriceData = $line_item['model']->data['custom_price_data']; ?>
                                <tr>
                                    <td class="line-item__image" valign="middle"><div class="image-preview" style="background-image: url(<?= $this->safeString($line_item['model']->getImageUrl()) ?>)"><div class="item-quantity"><?= $line_item['quantity'] ?></div></div></td>
                                    <td class="line-item__info">
                                        <div><a href="<?= $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit') ? $this->getUrl('catalog/backend_product/post', ['id' => $line_item['model']->data['product_id']]) : '' ?>"><?= $line_item['model']->data['title'] ?></a></div>
                                        <?php if (count($line_item['model']->data['options']) > 0) : ?><div><?= $line_item['model']->getVariantOptionsText() ?></div><?php endif; ?>
                                        <?php /* if ($line_item['model']->data['sku']) : ?><div>SKU: <?= $line_item['model']->data['sku'] ?></div><?php endif; */ ?>
                                        <?php if ($line_item['model']->getVariant()->data['sku']) : ?><div>SKU: <?= $line_item['model']->getVariant()->data['sku'] ?></div><?php endif; ?>
                                        <?php if (isset($customPriceData['buy_design']['buy_design_price'])) : ?>
                                            <div>Digital image: <?= OSC::helper('catalog/common')->formatPriceByInteger($customPriceData['buy_design']['buy_design_price'], 'html_with_currency', true) ?></div>
                                        <?php endif; ?>
                                        <?php if (is_array($line_item['model']->data['custom_data']) && count($line_item['model']->data['custom_data']) > 0) : ?>
                                            <?php foreach ($line_item['model']->data['custom_data'] as $custom_data) : ?>
                                                <div class="product-custom" data-key="<?= $custom_data['key'] ?>" data-order-line-id="<?= $line_item['model']->getId() ?>">
                                                    <div class="title"><?= $custom_data['title'] ?></div>
                                                    <div class="content"><?= $custom_data['text'] ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?> 
                                        <?php if ($line_item['model']->data['discount']) : ?>
                                            <div class="discount-code"><?= $this->getIcon('tag') ?><?= $line_item['model']->data['discount']['discount_code'] ?><span class="product-price">(<span class="product-price__money">- <?= OSC::helper('catalog/common')->formatPriceByInteger($line_item['model']->data['discount']['discount_price']) ?></span>)</span></div>                    
                                        <?php endif; ?>
                                        <?php if ($order->data['fulfillment_status'] == 'unfulfilled' && $group_key != 'refunded' && is_numeric(stripos($group_key,'process')) != true) : ?>
                                            <div style=" padding-top: 5px;">
                                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/edit/locked' : 'catalog/super|catalog/order/full|catalog/order/edit')) : ?>
                                                    <a class="" href="<?= $this->getUrl('catalog/backend_order/editVariant', ['item_id' => $line_item['model']->getId()]) ?>">Edit</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="line-item__price"><?= OSC::helper('catalog/common')->formatPrice($line_item['model']->getFloatPrice()) ?> x <?= $line_item['quantity'] ?></td>
                                    <td class="line-item__amount"><?= OSC::helper('catalog/common')->formatPrice($line_item['model']->getFloatAmountWithDiscountByQty($line_item['quantity'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table> 
                        <?php if ($group_key == 'unfulfilled') : ?>
                            <div class="order-action-bar e20">
                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/fulfill/locked' : 'catalog/super|catalog/order/full|catalog/order/fulfill')) : ?>
                                    <a class="btn btn-primary" href="<?= $this->getUrl('catalog/backend_order/fulfill', ['id' => $order->getId()]) ?>">Mark as fulfilled</a>
                                <?php endif; ?>
                            </div>
                        <?php elseif (substr($group_key, 0, 9) == 'fulfilled') : ?>
                            <div class="order-action-bar e20">                                
                                <?php if ($group['fulfillment']->data['tracking_number'] || $group['fulfillment']->data['tracking_url']) : ?>                    
                                    <?php $tracking_number = $group['fulfillment']->data['tracking_number'] ? $group['fulfillment']->data['tracking_number'] : 'Click here to tracking your shipment' ?>
                                    <div class="tracking-info">
                                        <?php if ($group['fulfillment']->data['shipping_carrier']) : ?>
                                            <div class="shipping-carrier"><?= $group['fulfillment']->data['shipping_carrier'] ?><?php if ($group['fulfillment']->data['tracking_number']): ?> tracking number<?php endif; ?></div>
                                        <?php endif; ?>
                                        <div class="tracking-number">
                                            <?php if (!$group['fulfillment']->data['tracking_url']) : ?>
                                                <?= $tracking_number ?>
                                            <?php else: ?>
                                                <a href="<?= $group['fulfillment']->data['tracking_url'] ?>" target="_blank"><?= $tracking_number ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/unfulfill/locked' : 'catalog/super|catalog/order/full|catalog/order/unfulfill')) : ?>
                                    <a class="btn btn-danger" href="<?= $this->getUrl('catalog/backend_order/unfulfill', ['fulfillment_id' => $group['fulfillment']->getId()]) ?>">Unfulfillment</a>
                                <?php endif; ?>
                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/fulfill/edit/locked' : 'catalog/super|catalog/order/full|catalog/order/fulfill/edit')) : ?>
                                    <a class="btn btn-primary" href="<?= $this->getUrl('catalog/backend_order/editFulfill', ['id' => $order->getId(), 'tracking_number' => $tracking_number]) ?>">Edit tracking number</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="block mb20">
                <div class="plr20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title"><?= Model_Catalog_Order::PAYMENT_STATUS[$order->data['payment_status']] ?></div></div>
                    </div>
                    <div class="order-total-lines e20">
                        <div class="total-line__row subtotal">
                            <div class="total-line__title">Subtotal</div>
                            <div class="total-line__price">
                                <span class="product-price">                    
                                    <span class="product-price__money"><?= OSC::helper('catalog/common')->formatPrice($order->getFloatSubtotalPrice()) ?></span>		
                                </span>
                            </div>
                        </div>
                        <?php
                        $discount_codes = $order->data['discount_codes'];

                        $orderCustomPriceData = isset($order->data['custom_price_data']) & !empty($order->data['custom_price_data']) && is_array($order->data['custom_price_data']) ? $order->data['custom_price_data'] : [];
                        ?>
                        <?php if (!empty($discount_codes)): ?>
                            <?php foreach ($discount_codes as $discount_code) : ?>
                                <?php
                                if ($discount_code['apply_type'] != 'entire_order') {
                                    continue;
                                }
                                ?>
                                <div class="total-line__row discount">
                                    <div class="total-line__title">Discount<span class="discount-code"><?= $this->getIcon('tag') ?><?= $discount_code['discount_code'] ?></span></div>
                                    <div class="total-line__price">
                                    <span class="product-price">
                                        <span class="product-price__money">- <?= OSC::helper('catalog/common')->formatPrice(OSC::helper('catalog/common')->integerToFloat($discount_code['discount_price'])) ?></span>
                                    </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php
                        $listBuyDesign = $order->getBuyDesign();
                        $buyDesignPrice = $order->getFloatBuyDesignPrice();
                        ?>
                        <?php if (isset($orderCustomPriceData['buy_design']) && !empty($orderCustomPriceData['buy_design']) && !empty($listBuyDesign)): ?>
                            <div class="total-line__row">
                                <div class="total-line__title">
                                    Buy <?= count($listBuyDesign); ?> Design
                                </div>
                                <div class="total-line__price">
                                    <span class="product-price">
                                        <span class="product-price__money">
                                            <?= OSC::helper('catalog/common')->formatPrice($buyDesignPrice) ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="total-line__row shipping">
                            <?php $shipping_price_data = $order->getShippingPriceData(); ?>
                            <div class="total-line__title">
                                Shipping
                                <?php if ($shipping_price_data['price'] >= 0) : ?>(<?= $shipping_price_data['title'] ?>)<?php endif; ?>
                            </div>
                            <div class="total-line__price">
                                <?php if ($shipping_price_data['price'] < 0) : ?>
                                    Calculated at next step
                                <?php else : ?>
                                    <span class="product-price">    
                                        <?php if (count($shipping_price_data['discount']) > 0) : ?>
                                            <span class="product-price__money product-price__original-price"><?= OSC::helper('catalog/common')->formatPrice(OSC::helper('catalog/common')->integerToFloat($shipping_price_data['compare_at_price'])) ?></span>
                                        <?php endif; ?> 
                                        <span class="product-price__money"><?= OSC::helper('catalog/common')->formatPrice(OSC::helper('catalog/common')->integerToFloat($shipping_price_data['price'])) ?></span>		
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="total-line__row total-price">
                            <div class="total-line__title">Total</div>
                            <div class="total-line__price">
                                <span class="product-price">               
                                    <span class="product-price__money"><?= OSC::helper('catalog/common')->formatPriceByInteger($order->getTotalPrice()) ?></span>		
                                </span>
                            </div>
                        </div>
                        <div class="total-line__row paid">
                            <div class="total-line__title"><div>Paid by customer (<?= $order->getPayment()->getTextTitleWithInfo($order) ?>)</div><div style="font-style: italic"><?= $order->getPayment()->getAccount()['title'] ?></div></div>
                            <div class="total-line__price">
                                <span class="product-price">               
                                    <span class="product-price__money">
                                        <?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['paid']) ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <?php if ($order->data['refunded'] > 0) : ?>
                            <div class="total-line__row refunded">
                                <div class="total-line__title">
                                    Refunded (<?= $percentRefund ?>%)
                                </div>
                                <div class="total-line__price">
                                    <span class="product-price">               
                                        <span class="product-price__money">
                                            - <?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['refunded']) ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!in_array($order->data['payment_status'], ['unpaid', 'refunded', 'void'])) : ?>
                        <div class="order-action-bar e20">
                            <?php if ($order->data['payment_status'] == 'authorized') : ?>
                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/cancel/locked' : 'catalog/super|catalog/order/full|catalog/order/cancel')) : ?>
                                    <a class="btn btn-danger mr5" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to cancel the order?") ?>', '<?= $this->getUrl('*/*/cancel', array('id' => $order->getId())) ?>')">Cancel order</a>
                                <?php endif; ?>
                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/capture/locked' : 'catalog/super|catalog/order/full|catalog/order/capture')) : ?>
                                    <div class="btn btn-primary" data-insert-cb="initCatalogOrderCapturePaymentBtn" data-capture-url="<?= $this->getUrl('catalog/backend_order/capture', ['id' => $order->getId()]) ?>" data-amount="<?= $order->getFloatTotalPrice() ?>" data-gateway="<?= $this->safeString($order->getPayment()->getTextTitle()) ?>">Capture payment</div>
                                <?php endif; ?>
                            <?php elseif ($order->ableToRefund()) : ?>
                                <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/refund/locked' : 'catalog/super|catalog/order/full|catalog/order/refund')) : ?>
                                    <a class="btn btn-primary" href="<?= $this->getUrl('catalog/backend_order/refund', ['id' => $order->getId()]) ?>">Refund</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($params['order_logs']->length() > 0): ?>
                <div class="frm-heading">
                    <div class="frm-heading__main"><div class="frm-heading__title">Time Line</div></div>
                </div>
                <div class="">
                    <?php if ($this->checkPermission('catalog/super|catalog/order/full|catalog/order/comment')) : ?>
                        <div class="timeline_order_comment_form">
                            <div class="timeline_order_comment">
                                <input type="text" class="timeline_order_comment_input" name="comment" placeholder="Comment..." />
                                <button class="btn btn-primary" order_id="<?= $order->getId(); ?>" url="<?php echo $this->getUrl('catalog/backend_order/postCommentDetail', ['id' => $order->getId()]); ?> " data-insert-cb="catalogPostCommentDetail" >Post</button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="timeline_order_container" data-insert-cb="initCatalogOrderLogCanResend">
                        <?php foreach ($params['order_logs'] as $log): ?>
                            <?= $this->build('catalog/order/log', ['log' => $log]); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="post-frm-grid__sub-col">
            <?php if ($order->data['note']) : ?>
                <div class="block mb20">
                    <div class="plr20 pb20">
                        <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Customer note</div></div></div>
                        <div class="frm-grid">
                            <div><?= $order->data['note'] ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="block">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Customer</div></div>
                        <?php if ($order->data['client_info']['tracking_key'] != '') : ?>
                            <div class="frm-edit-address">
                                <a href="<?= $this->getUrl('catalog/backend_order/viewHistoryCustomer', ['id' => $order->getId(), 'tracking_key' => $order->data['client_info']['tracking_key']]) ?>" >View history customer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div><a href="#"><?= $order->getFullName() ?></a></div>
                            <div><?= $order->getCustomer()['orders'] ?> order (<?= $this->helper('catalog/common')->formatPriceByInteger($order->getCustomer()['spent'], 'email_with_currency') ?>)</div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Contact information</div></div></div>
                    <div class="frm-grid">
                        <div>
                            <a href="mailto:<?= $order->data['email'] ?>"><?= $order->data['email'] ?></a>
                        </div>
                    </div>
                    <?php if (isset($order->data['fraud_data']['score'])) : ?>
                        <div class="frm-line e20"></div>
                        <div class="frm-heading">
                            <div class="frm-heading__main"><div class="frm-heading__title">Fraud level</div></div>
                        </div>
                        <div class="frm-grid">
                            <div>
                                <div class="fraud-level" data-fraud-level="<?= $order->data['fraud_risk_level'] ?>">
                                    <div class="fraud-level__title"><?= Model_Catalog_Order::FRAUD_RISK_LEVEL[$order->data['fraud_risk_level']]['title'] ?></div>
                                    <div class="fraud-level__bar"></div>                                        
                                </div>
                                <div class="mt10"><?= $order->data['fraud_data']['info'] ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($order->data['client_info']['ip'])) : ?>
                        <div class="frm-line e20"></div>
                        <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Client information</div></div></div>
                        <?php if ($order->data['cart_ukey']) : ?>
                            <div class="frm-grid">
                                <div style="max-width: 70px"><strong>Cart</strong></div>
                                <div><?= $order->data['cart_ukey'] ?> (<?= $order->data['cart_id'] ?>)</div>
                            </div>
                        <?php endif; ?>
                        <div class="frm-grid">
                            <div style="max-width: 70px"><strong>IP</strong></div>
                            <div><?= $order->data['client_info']['ip'] ?></div>
                        </div>
                        <div class="frm-grid">
                            <div style="max-width: 70px"><strong>OS</strong></div>
                            <div><?= $order->data['client_info']['os'] ?></div>
                        </div>
                        <div class="frm-grid">
                            <div style="max-width: 70px"><strong>Browser</strong></div>
                            <div><?= $order->data['client_info']['browser'] ?></div>
                        </div>
                        <?php if ($order->data['client_info']['location']) : ?>
                            <div class="frm-grid">
                                <div style="max-width: 70px"><strong>Location</strong></div>
                                <div>
                                    <?php if ($order->data['client_info']['location']['city']) : ?><?= $order->data['client_info']['location']['city'] ?>,<?php endif; ?>
                                    <?php if ($order->data['client_info']['location']['region']) : ?><?= $order->data['client_info']['location']['region'] ?>,<?php endif; ?>
                                    <?= $order->data['client_info']['location']['country_name'] ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($order->data['client_info']['referer']) : ?>
                            <div class="frm-grid">
                                <div><strong>Referer</strong></div>
                                <div>&nbsp;</div>
                            </div>
                            <div><a href="<?= $this->safeString($order->data['client_info']['referer']['url']) ?>"><?= $order->data['client_info']['referer']['host'] ?></a></div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ([['title' => 'Shipping address', 'action' => 'shipping', 'info' => $order->getShippingAddress()], ['title' => 'Billing address', 'action' => 'billing', 'info' => $order->getBillingAddress(true)]] as $address) : ?>
                        <div class="frm-line e20"></div>
                        <div class="frm-heading">
                            <div class="frm-heading__main">
                                <div class="frm-heading__title">
                                    <?= $address['title']; ?>
                                </div>
                            </div>
                            <?php if ($order->data['order_status'] != 'cancelled') : ?>
                                <div action="<?= $address['action']; ?>" data-edit-url="<?= $this->getUrl('catalog/backend_order/editAddress', ['id' => $order->getId()]) ?>" class="frm-edit-address">
                                    <?php if ($this->checkPermission($order->checkMasterLock() ? 'catalog/super|catalog/order/full/locked|catalog/order/edit/locked' : 'catalog/super|catalog/order/full|catalog/order/edit')) : ?>
                                        <a href="<?= $this->getUrl('catalog/backend_order/editAddress', ['id' => $order->getId(), 'action' => $address['action']]) ?>" >Edit</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="frm-grid address-content-<?= $address['action']; ?>">
                            <div><?= OSC::helper('catalog/common')->formatAddress($address['info'], 'div'); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
