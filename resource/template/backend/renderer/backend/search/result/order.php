<?php
$order = $params['order'];
?>

<div class="clearfix backend-search-layout">
    <div class="img-col">
        <img src="/resource/template/backend/image/backend/search_order.png" alt="">
    </div>
    <div class="content-col">
        <div class="title">
            <h4>
                <a href="<?= $order->getDetailUrl() ?>"><?= OSC::helper('catalog/product')->getSomeWords($order->data['code']) ?></a>
            </h4>
        </div>
        <div class="action-bar clearfix">
            <a class="btn btn-small btn-icon" href="<?= $order->getDetailUrl() ?>" target="_blank">
                <?= $this->getIcon('eye-regular') ?>
            </a>
            <a class="btn btn-small btn-icon" href="javascript:void(0)">
                <?= $this->getIcon('pencil') ?>
            </a>
        </div>
    </div>
</div>
