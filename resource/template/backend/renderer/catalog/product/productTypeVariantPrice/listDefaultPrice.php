<?php
?>

<div class="block m25" id="stock-container">

    <div class="header-grid">
        <div class="flex--grow"></div>
        <div>
            <?php if ($this->checkPermission()) : ?>
                <a href="<?= $this->getUrl('*/*/index'); ?>" class="btn btn-outline btn-small">Go Back</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['product_type_variants']->length()) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 100px; text-align: center">ID</th>
                <th style="text-align: left; width: 300px">Product Type Variant</th>
                <th style="text-align: left; width: 150px">Price</th>
                <th style="text-align: left; width: 150px">Compare At Price</th>
            </tr>
            <?php foreach ($params['product_type_variants'] as $key => $product_type_variant) : ?>
                <tr data-content="1">
                    <td style="text-align: center"><?= $key + 1 ?></td>
                    <td><?= $product_type_variant->data['title'] ?></td>
                    <td>
                        <?= OSC::helper('catalog/common')->formatPriceByInteger($product_type_variant->data['price']) ?>
                    </td>
                    <td>
                        <?= OSC::helper('catalog/common')->formatPriceByInteger($product_type_variant->data['compare_at_price']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>
