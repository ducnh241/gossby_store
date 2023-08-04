<?php

$this->push([
    '[core]catalog/common.js',
    'common/select2.min.js',
    'autoAb/abProduct.js',
], 'js');
$this->addComponent('datePicker', 'timePicker');
$this->push([
    'common/select2.min.css',
], 'css');

$model = $params['model'];
?>

<div class="post-frm p25 ab-test-product-price" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="plr20 p20">
                    <div class="title-heading">Campaign Status :
                        <span style="color: red">
                              <?= $model->getStatusName() ?>
                        </span>
                        <div style="float: right">
                            <input
                                type="checkbox"
                                name="ab-product-status"
                                id="ab_product_status"
                                data-insert-cb="initSwitcher"
                                <?php if ($model->data['status'] == Model_AutoAb_AbProduct_Config::STATUS_IN_PROGRESS): ?>
                                    checked
                                <?php endif; ?>
                            /><label
                                    class="label-inline ml10"></label>
                        </div>
                    </div>
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title required">Campaign title</div></div></div>

                    <input type="text"
                        class="styled-input"
                        name="campaign_title"
                        id="campaign_title"
                        value="<?= $model->data['title'] ?>" required
                        <?php if ($model->data['status'] != Model_AutoAb_AbProduct_Config::STATUS_CREATED): ?>
                            disabled
                        <?php endif; ?>
                    />

                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title required">Set Test Date</div></div></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-publish_start_date">Begin time</label>
                            <div class="styled-date-time-input">
                                <div class="date-input" <?php if ($model->data['status'] != Model_AutoAb_AbProduct_Config::STATUS_CREATED): ?> style="background: #f1f1f1" <?php endif; ?>>
                                    <?= $this->getIcon('calendar-alt') ?>
                                    <input type="text"
                                           name="active_date" id="input_active_date"
                                           data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY', 'min_date' => date('m/d/Y')))) ?>"
                                           value="<?= $model->data['begin_time'] > 0 ? date('d/m/Y', $model->data['begin_time']) : '' ?>"
                                           data-insert-cb="initDatePicker"
                                           required
                                        <?php if ($model->data['status'] != Model_AutoAb_AbProduct_Config::STATUS_CREATED): ?>
                                            disabled
                                        <?php endif; ?>
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="separate"></div>
                        <div>
                            <label for="input-publish_to_date">Finish time</label>
                            <div class="styled-date-time-input">
                                <div class="date-input" <?php if ($model->data['status'] != Model_AutoAb_AbProduct_Config::STATUS_CREATED): ?> style="background: #f1f1f1" <?php endif; ?>>
                                    <?= $this->getIcon('calendar-alt') ?>
                                    <input type="text"
                                           name="deactive_date"
                                           id="input_deactive_date"
                                           data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY', 'min_date' => date('m/d/Y')))) ?>"
                                           value="<?= $model->data['finish_time'] > 0 ? date('d/m/Y', $model->data['finish_time']) : '' ?>"
                                           data-insert-cb="initDatePicker"
                                           required
                                        <?php if ($model->data['status'] != Model_AutoAb_AbProduct_Config::STATUS_CREATED): ?>
                                            disabled
                                        <?php endif; ?>
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $selected_products = $params['selected_products']; ?>

                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title required">Search Product name/ Product ID</div></div></div>
                    <select class="styled-input"
                            multiple="multiple"
                            id="input-product-ids"
                            name="fixed_product_ids[]"
                            data-insert-cb="initSelectAbProduct"
                            data-config-type="<?= Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_CAMPAIGN ?>"
                        <?php if ($model->data['status'] != Model_AutoAb_AbProduct_Config::STATUS_CREATED): ?>
                            disabled
                        <?php endif; ?>
                    >
                        <?php foreach ($selected_products as $product) : ?>
                            <option value="<?= $product['product_id'] ?>" selected="selected">
                                <?= $product['product_id'] . ' - ' . $product['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">
                                Default Product
                            </div>
                            <div class="frm-grid">
                                <div>
                                    <label id="default-product" data-default_product_id="<?= !empty($params['default_product']) ? $params['default_product']['product_id'] : '' ?>"><?php if(!empty($params['default_product'])) { echo $params['default_product']['product_id'] . ' - ' . $params['default_product']['title']; } ?></label>
                                </div>
                            </div>
                            <div class="frm-grid">
                                <button type="button" class="btn btn-secondary" data-insert-cb="initSetDefaultProduct"> Set default product </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            <?= $this->_('core.cancel') ?>
        </a>
        <button data-id="<?= $model->getId(); ?>" type="submit" class="btn btn-primary" data-insert-cb="initSubmitFormProductAbtest">
            <?= $this->_('core.save') ?>
        </button>
    </div>
</div>
