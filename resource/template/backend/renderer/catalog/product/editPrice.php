<?php
$this->push('catalog/product_edit_price.js', 'js');
$this->push('vendor/bootstrap/bootstrap-grid.min.css', 'css');
$product_id = $params['product_id'];
?>
<div class='product-type-variants__actions'>
    <div data-insert-cb="initEditProductTypeVariantModal">
        <i><?= $this->getIcon('pencil') ?></i>
        <span>Edit</span>
    </div>
    <div class="product-type-variants__actions-delete" data-insert-cb="initDeleteAllSelectedVariants" data-product-id="<?=$product_id?>">
        <span class='d-flex'>
            <i><?= $this->getIcon('trash-alt-regular') ?></i>
            <span>Reset All</span>
        </span>
        <span class='deleting'>Deleting...</span>
    </div>
</div>
<div class="block m25 custom-product-type-variant p25" id="stock-container">
    <div class="product-search__input">
        <span class='d-flex'><?= $this->getIcon('search') ?></span>
        <input type="text" placeholder="Search product type or variant" />
    </div>
    <div class="collapse-expand-btn">
        <div>Collapse All</div>
        <div>Expand All</div>
    </div>

    <div class="product-type-variants-container" data-insert-cb="initProductTypeVariantPriceList" data-product-id="<?=$product_id?>">
        <div class="product-type-variants-container__header">
            <div class='w-60'>
                <strong>Product Type</strong>
            </div>

            <div class='w-10 tex-center'>
                <strong>Price</strong>
            </div>
            <div class='w-10 tex-center'>
                <strong>Compare Price</strong>
            </div>
            <div class='w-10 tex-center'>
                <strong>Plus Price</strong>
            </div>
            <div class='w-10'>
            </div>
        </div>
        <div class="contain-list">

        </div>
    </div>
    <!-- <div class='select-product-type-variants-component' data-insert-cb="initSelectProductTypeVariantsComponent">
        <div class='select-product-type-variants-component__list'>

        </div>
    </div> -->
    <div class="loader"></div>
</div>