<?php

$this->push([
    '[core]catalog/common.js',
    'common/select2.min.js',
    'autoAb/productPrice.js',
], 'js');
$this->push(['common/select2.min.css'], 'css');
$this->push(['common/selectProductTypeVariants.scss'], 'css');

$model = $params['model'];
$countries = $params['countries'];
$selected_countries = $model->data['location_data'];
$selected_products = $params['selected_products'];

$status = $model->data['status'] == Model_AutoAb_ProductPrice_Config::STATUS_ALLOW ? 'On' : 'Off';
?>

<div class="post-frm p25 ab-test-product-price" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="title-heading">Campaign Status: <b style="color: red"> <?= $status; ?></b></div>
                </div>
                <div class="plr20 p20">
                    <div class="title-heading">Campaign title (*):</div>
                    <input type="text"
                           class="styled-input"
                           name="campain_title"
                           id="campain_title"
                           value="<?= $model->data['title'] ?>"
                    />
                </div>
                <div class="plr20 pb20">
                    <div class="title-heading">Country (*):</div>
                    <select class='styled-input' name="location_data[]" id="input-group_country" multiple="multiple" data-insert-cb="initSelectGroupCountry">
                        <?php foreach ($countries as $country_code => $country_name) : ?>
                            <option value="<?= $country_code ?>"
                                <?php if (in_array($country_code, $selected_countries)) : ?>
                                    selected="selected"
                                <?php endif; ?>><?= $country_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="plr20 pb20">
                    <div class="title-heading">Fees (%) (*):</div>
                    <input type="text"
                           class="styled-input"
                           name="fee"
                           id="input-fees"
                           value="<?= $model->data['fee'] ?>"
                    />
                </div>
                <div class="plr20 pb20 d-flex justify-content-between">
                    <div class='wrapped-select'>
                        <select name="condition_type" id="condition_type" class="styled-input">
                            <option value="0" selected="selected">Set for Campaign</option>
                        </select>
                        <div class="caret"></div>
                    </div>
                    <div class='d-flex'>
                        <div class='d-flex items-center'>
                            <strong class="mr-2">Start:</strong>
                            <input type="text"
                                   class="styled-input mr-2"
                                   id="begin_at"
                                   value="<?= $model->data['begin_at'] ?>"/>
                        </div>
                        <div class='d-flex items-center'>
                            <strong class='mr-2'>End:</strong>
                            <input type="text"
                                   class="styled-input"
                                   id="finish_at"
                                   value="<?= $model->data['finish_at'] ?>"/>
                        </div>
                    </div>
                </div>
                <div class="plr20 pb20">
                    <div class="title-heading">Fixed Products: </div>
                    <select class="styled-input"
                            multiple="multiple"
                            id="input-product-ids"
                            name="fixed_product_ids[]"
                            data-insert-cb="initSelectProduct"
                            data-config-type="<?= Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST ?>"
                    >
                        <?php foreach ($selected_products as $product) : ?>
                            <option value="<?= $product['product_id'] ?>" selected="selected">
                                <?= $product['product_id'] . ' - ' . $product['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="plr20 pb20">
                    <strong>Price Range ($)</strong>
                    <div class='pt20 price-range-input-container'
                         data-insert-cb="initPriceRangeInput"
                         data-config-status="<?= $model->data['status'] === Model_AutoAb_ProductPrice_Config::STATUS_ALLOW ? 1 : 0 ?>"
                    >
                        <?php foreach ($model->data['price_range'] as $p):?>
                            <div class="d-flex items-center pb20">
                                <input class="styled-input mr-2" value="<?= $p; ?>"/>
                                <?= $this->getIcon('trash-alt-regular') ?>
                            </div>
                        <?php endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            <?= $this->_('core.cancel') ?>
        </a>
        <button data-id="<?= $model->getId(); ?>"
                data-semitest="1"
                type="submit"
                class="btn btn-primary submit-form-product-price-ab"
                data-insert-cb="initSubmitFormProductPriceAbtest">
            <?= $this->_('core.save') ?>
        </button>
    </div>
</div>
