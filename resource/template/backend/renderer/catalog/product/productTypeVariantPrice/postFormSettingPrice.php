<?php
/* @var $this Helper_Backend_Template */

$this->push([
    'catalog/tool_setting_default_price.js'
], 'js');

$this->addComponent('select2');

if ($params['setting_location_price']) {
    $this->addComponent('location_group');
}

?>

<form action="<?= $this->getUrl('*/*/*'); ?>" method="post" class="post-frm product-post-frm p25" style="width: 950px">
    <div class="frm-title frm-title__heading ml-1 mb-3">
        <?= $params['form_title']; ?>
    </div>

    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">

                    <?php if ($params['setting_location_price']): ?>
                        <div class="frm-grid">
                            <div>
                                <label for="input-title">Location</label>
                                <div data-insert-cb="initSelectGroupLocation"
                                     data-key="location_data">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Product Type</label>
                            <select class="multiple-selection-options" name="product_type_id" data-insert-cb="initRenderProductTypeVariant">
                                <?php foreach ($params['product_type_datas'] as $product_type_id => $product_type_title) : ?>
                                    <option value="<?= $product_type_id ?>">
                                        <?= $product_type_title ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Product Type Variant</label>
                            <select class="multiple-selection-options" multiple name="product_type_variants[]" id="variant-selector">
                                <?= $this->getJSONTag($params['product_type_variants'], 'product_type_variants') ?>
                            </select>
                        </div>
                    </div>

                    <div class="frm-grid">
                        <div>
                            <label for="input-limit">Price</label>
                            <div>
                                <input type="text"
                                    class="styled-input"
                                    name="price"
                                    value="0"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="frm-grid">
                        <div>
                            <label for="input-limit">Compare At Price</label>
                            <div>
                                <input type="text"
                                    class="styled-input"
                                    name="compare_at_price"
                                    value="0"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            <?= $this->getIcon('external-link-regular', ['class' => 'mr5']) ?><?= $this->_('core.cancel') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?= $this->getIcon('save-regular', array('class' => 'mr5')) ?><?= $this->_('core.save') ?>
        </button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('.multiple-selection-options').select2({
            width: '100%',
            theme: 'default select2-container--custom',
        });
    });
</script>
