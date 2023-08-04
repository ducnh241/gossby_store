<?php
/* @var $this Helper_Backend_Template */
/* @var $design Model_CatalogItemCustomize_Design */
$design = $params['design'];
?>
<div class="customize-design-item" data-state="<?= $design->getStateKey() ?>" data-id="<?= $design->getId() ?>">
    <div>
        <div class="image" data-image="<?= $design->isCompleted() ? $design->getDesignImageUrl() : $design->getProductImageUrl() ?>" <?php if ($design->isProcessing()) : ?>data-insert-cb="catalogItemCustomizeInitDesignUploader" data-upload-url="<?= $this->getUrl('*/*/designUpload', ['id' => $design->getId()]) ?>"<?php else : ?>data-insert-cb="catalogItemCustomizeInitImagePreview"<?php endif; ?>></div>
        <div class="info">
            <div class="customize-title"><?= $this->safeString($design->data['customize_title']) ?></div>
            <div class="product-title">Product: <?= $this->safeString($design->data['product_title']) ?></div>
            <div class="design-ukey">SKU: <?= $this->safeString($design->data['ukey']) ?></div>
            <div class="design-ukey">Order: <?= $this->safeString($design->data['order_id']) ?></div>
            <?php if ($design->data['member_id'] > 0) : ?>
                <div class="designer">Designer: <?= $design->getMemberUsername() ?></div>
            <?php endif; ?>
            <div class="customize-info">
                <div class="title">Customize</div>
                <div class="content"><?= $design->data['customize_info'] ?></div>
            </div>
            <div class="action">
                <?php if ($design->isPending()) : ?>
                    <a href="<?= $this->getUrl('*/*/designTake', ['id' => $design->getId()]) ?>" class="btn btn-small btn-primary mr5" data-insert-cb="catalogItemCustomizeInitTakeDesignBtn">Take the design</a>
                <?php endif; ?>
                <span class="btn btn-small btn-outline" data-insert-cb="catalogItemCustomizeInitToggleCustomizeInfoBtn">Toggle customize info</span>
            </div>
        </div>
    </div>
</div>