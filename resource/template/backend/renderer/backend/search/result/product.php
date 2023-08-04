<?php
$product = $params['product'];
?>

<div class="clearfix backend-search-layout">
    <div class="img-col">
        <img src="<?= $this->imageOptimize($product->getFeaturedImageUrl(), 60, 60, false) ?>" alt="">
    </div>
    <div class="content-col">
        <div class="title">
            <h4>
                <a href="<?= $product->getDetailUrl() ?>"><?= OSC::helper('catalog/product')->getSomeWords($product->data['title'], 10) ?></a>
            </h4>
        </div>
        <div class="action-bar clearfix">
            <a class="btn btn-small btn-icon" href="<?= $product->getDetailUrl() ?>">
                <?= $this->getIcon('eye-regular') ?>
            </a>
            <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit')) : ?>
                <a class="btn btn-small btn-icon" href="<?= $this->getUrl('catalog/backend_product/post', ['id' => $product->getId()]) ?>">
                    <?= $this->getIcon('pencil') ?>
                </a>
            <?php else : ?>
                <a class="btn btn-small btn-icon" href="javascript:void(0)">
                    <?= $this->getIcon('pencil') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
