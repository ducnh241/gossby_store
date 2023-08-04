<?php
$this->addComponent('select2');
$this->addComponent('location_group');
?>
<div class='product-type-variants__actions'>
    <div data-insert-cb="initEditProductTypeVariantModal">
        <i><?= $this->getIcon('pencil') ?></i>
        <span>Edit</span>
    </div>
    <div class="product-type-variants__actions-delete" data-insert-cb="initDeleteAllSelectedVariants">
        <span class='d-flex'>
            <i><?= $this->getIcon('trash-alt-regular') ?></i>
            <span>Delete All</span>
        </span>
        <span class='deleting'>Deleting...</span>
    </div>
</div>
<div class="block m25 custom-product-type-variant p25" id="stock-container">
    <div class="location-select" data-insert-cb="initCustomSelect">
        
    </div>
    <div class="product-search">
        <div class="product-search__input">
            <span class='d-flex'><?= $this->getIcon('search') ?></span>
            <input type="text" placeholder="Search product type or variant"/>
        </div>
    </div>
    <div class="collapse-expand-btn">
        <div>Collapse All</div>
        <div>Expand All</div>
    </div>

    <div class="product-type-variants-container" data-insert-cb="initProductTypeVariantPriceList">
        <div class="product-type-variants-container__header">
            <div class='w-60'>
                <strong>Product Type</strong>
            </div>
            <div class='w-10 tex-center'>
                <strong>Best Price</strong>
            </div>
            <div class='w-10 tex-center'>
                <strong>Price</strong>
            </div>
            <div class='w-10 tex-center'>
                <strong>Compare Price</strong>
            </div>
            <div class='w-10 tex-center'>
                <strong>Base Cost Config</strong>
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
    
    <div class="add-more-product" data-insert-cb="initAddMoreProductTypeModal">
        <?= $this->getIcon('icon-add-more-product') ?>
        <strong>Add More Product</strong>
    </div>
</div>
<style>
    .select2-container--open {
        z-index: 9999999;
    }
</style>