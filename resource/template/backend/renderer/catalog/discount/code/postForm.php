<?php
/* @var $this Helper_Backend_Template */
/* @var $params['model'] Model_Catalog_Discount_Code */

$this->addComponent('datePicker', 'timePicker', 'itemSelector')
    ->push(<<<EOF
var OSC_COUNTRY_BROWSE_URL = '{$this->getUrl('core/common/browseCountry')}';
var OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browse', ['variant' => 1])}';
var OSC_CATALOG_VARIANT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browseVariant')}';
var OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_collection/browse')}';
var OSC_CATALOG_CUSTOMER_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browse')}';
var OSC_CATALOG_CUSTOMER_GROUP_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browseGroup')}';
EOF
        , 'js_code');
?>

<style>
    /* Change autocomplete styles in WebKit */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
        transition: background-color 5000s ease-in-out 0s;
    }
</style>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Discount code</div></div>
                        <div class="frm-heading__action">
                            <div class="option-add-btn frm-heading__btn ml10" data-insert-cb="catalogInitDiscountCodeGenerator">Generate code</div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <input type="text" <?php if ($params['model']->getId() > 0) {echo 'readonly';} ?> class="styled-input" name="discount_code" id="input-discount_code" value="<?= $this->safeString($params['model']->data['discount_code']) ?>" />
                            </div>
                            <div class="input-desc">Customers will enter this discount code at checkout.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Discount</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-discount_type">Discount type</label>
                            <div>
                                <div class="styled-select">
                                    <select name="discount_type" id="input-discount_type" data-insert-cb="catalogInitDiscountTypeSelector">
                                        <?php foreach (Model_Catalog_Discount_Code::DISCOUNT_TYPES as $value => $label) : ?>
                                            <option value="<?= $value ?>"<?php if ($params['model']->data['discount_type'] == $value) : ?> selected="selected"<?php endif; ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <ins></ins>
                                </div>
                            </div>
                        </div>
                        <div class="separate"></div>
                        <div>
                            <label for="input-discount_value">Discount value</label>
                            <div class="styled-input-wrap">
                                <input type="text" class="styled-input" name="discount_value" id="input-discount_value" value="<?= $this->safeString($params['model']->data['discount_type'] == 'fixed_amount' ? OSC::helper('catalog/common')->integerToFloat($params['model']->data['discount_value']) : $params['model']->data['discount_value']) ?>" />
                            </div>
                        </div>
                    </div>
                    <?php /*
                    <div class="frm-grid">
                        <div>
                            <label for="input-discount_value">Maximum amount</label>
                            <div class="styled-input-wrap">
                                <div class="styled-input-icon">
                                    <div>$</div>
                                </div>
                                <input type="text"
                                    class="styled-input"
                                    name="maximum_amount"
                                    id="input-maximum_amount"
                                    value="<?= $this->safeString($params['model']->data['maximum_amount']) ?>"
                                />
                            </div>
                        </div>
                    </div>
                    */ ?>
                </div>
            </div>
            <div class="block mt15" style="display: none" data-discount-types="bxgy">
                <div class="plr20 pb20" data-insert-cb="catalogInitDiscountCodeBuyXGetY">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Customer buys</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-bxgy_prerequisite_quantity">Quantity</label>
                            <div><input type="text" name="bxgy_prerequisite_quantity" id="input-bxgy_prerequisite_quantity" value="<?= $params['model']->data['bxgy_prerequisite_quantity'] ?>" class="styled-input" /></div>
                        </div>
                        <div class="separate"></div>
                        <div>
                            <label for="input-bxgy-prerequisite_type">Any items from</label>
                            <?php $product_selected = ''; ?>
                            <?php $collection_selected = ''; ?>
                            <?php if ($params['model']->data['discount_type'] == 'bxgy') : ?>
                                <?php if (count($params['model']->data['prerequisite_collection_id']) > 0) : ?>
                                    <?php $collection_selected = ' selected="selected"'; ?>
                                    <?= $this->getJsonTag(OSC::helper('catalog/common')->loadCollectionSelectorData($params['model']->data['prerequisite_collection_id']), 'bxgy_prerequisite_collection'); ?>
                                <?php elseif (count($params['model']->data['prerequisite_product_id']) > 0) : ?>
                                    <?php $product_selected = ' selected="selected"'; ?>
                                    <?= $this->getJsonTag(OSC::helper('catalog/common')->loadProductSelectorData($params['model']->data['prerequisite_product_id'], array_map(function($row){ $row = explode(':', $row); return $row[1]; }, $params['model']->data['prerequisite_variant_id'])), 'bxgy_prerequisite_product'); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="styled-select">
                                <select id="input-bxgy-prerequisite_type"><option value="collection"<?= $collection_selected ?>>Specific collections</option><option value="product"<?= $product_selected ?>>Specific products</option></select>
                                <ins></ins>
                            </div>
                        </div>
                    </div>
                    <div class="frm-separate e20"></div>
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">Customer gets</div>
                            <div class="frm-heading__desc">Customers must add the quantity of items specified below to their cart.</div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-bxgy_entitled_quantity">Quantity</label>
                            <div><input type="text" name="bxgy_entitled_quantity" id="input-bxgy_entitled_quantity" value="<?= $params['model']->data['bxgy_entitled_quantity'] ?>" class="styled-input" /></div>
                        </div>
                        <div class="separate"></div>
                        <div>
                            <label for="input-bxgy-entitled_type">Any items from</label>
                            <?php $product_selected = ''; ?>
                            <?php $collection_selected = ''; ?>
                            <?php if ($params['model']->data['discount_type'] == 'bxgy') : ?>
                                <?php if (count($params['model']->data['entitled_collection_id']) > 0) : ?>
                                    <?php $collection_selected = ' selected="selected"'; ?>
                                    <?= $this->getJsonTag(OSC::helper('catalog/common')->loadCollectionSelectorData($params['model']->data['entitled_collection_id']), 'bxgy_entitled_collection'); ?>
                                <?php elseif (count($params['model']->data['entitled_product_id']) > 0) : ?>
                                    <?php $product_selected = ' selected="selected"'; ?>
                                    <?= $this->getJsonTag(OSC::helper('catalog/common')->loadProductSelectorData($params['model']->data['entitled_product_id'], array_map(function($row){ $row = explode(':', $row); return $row[1]; }, $params['model']->data['entitled_variant_id'])), 'bxgy_entitled_product'); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="styled-select">
                                <select id="input-bxgy-entitled_type"><option value="collection"<?= $collection_selected ?>>Specific collections</option><option value="product"<?= $product_selected ?>>Specific products</option></select>
                                <ins></ins>
                            </div>
                        </div>
                    </div>
                    <div class="frm-separate e20"></div>
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">At a discounted value</div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div class="mb5">
                                <div class="styled-radio"><input type="radio" name="bxgy-discount_type" value="free" id="input-bxgy-discount_type-free" /><ins></ins></div>
                                <label class="ml5 label-inline" for="input-bxgy-discount_type-free">Free</label>
                            </div>
                            <div class="mb5">
                                <div class="styled-radio"><input type="radio" name="bxgy-discount_type" value="percent" id="input-bxgy-discount_type-percent"<?php if ($params['model']->data['bxgy_discount_rate'] < 100) : ?> checked="checked" data-rate="<?= $params['model']->data['bxgy_discount_rate'] ?>"<?php endif; ?> /><ins></ins></div>
                                <label class="ml5 label-inline" for="input-bxgy-discount_type-percent">Percentage</label>
                            </div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <div class="styled-checkbox"><input type="checkbox" id="input-bxgy-allocation_limit-switcher"<?php if ($params['model']->data['bxgy_allocation_limit'] > 0) : ?> checked="checked" data-limit="<?= $params['model']->data['bxgy_allocation_limit'] ?>"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins></div>
                                <label class="ml5 label-inline" for="input-bxgy-allocation_limit-switcher">Set a maximum number of uses per order</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15" style="display: none" data-discount-types="free_shipping">
                <div class="plr20 pb20" data-insert-cb="catalogInitDiscountCodeFreeShippingLimit">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Countries</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div class="mb5">
                                <div class="styled-radio"><input type="radio" name="free_shipping_limit" value="0" id="input-free_shipping_limit_0" /><ins></ins></div>
                                <label class="ml5 label-inline" for="input-free_shipping_limit_0">All countries</label>
                            </div>
                            <div class="mb5">
                                <?php $checked = ''; ?>
                                <?php if (count($params['model']->data['prerequisite_country_code']) > 0) : ?>
                                    <?php $checked = ' checked="checked"'; ?>
                                    <?= $this->getJsonTag(OSC::helper('core/common')->loadCountrySelectorData($params['model']->data['prerequisite_country_code']), 'prerequisite_country'); ?>
                                <?php endif; ?>
                                <div class="styled-radio"><input type="radio" name="free_shipping_limit" value="1" id="input-free_shipping_limit_1"<?= $checked ?> /><ins></ins></div>
                                <label class="ml5 label-inline" for="input-free_shipping_limit_1">Specific countries</label>
                            </div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Shipping rates</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <div class="styled-checkbox"><input type="checkbox" id="free_shipping_limit_rate"<?php if ($params['model']->data['prerequisite_shipping_rate'] > 0) : ?> checked="checked" data-limit="<?= $params['model']->getFloatFreeShippingRateLimit() ?>"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins></div>
                                <label class="ml5 label-inline" for="free_shipping_limit_rate">Exclude shipping rates over a certain amount</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15" style="display: none" data-discount-types="percent">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Applies to</div></div>
                    </div>
                    <div class="frm-grid">
                        <div class="apply_to" data-insert-cb="catalogInitDiscountCodeApplyTypeChange">
                            <?php foreach (Model_Catalog_Discount_Code::APPLY_TYPES as $value => $label) : ?>
                                <?php $checked = ''; ?>
                                <?php if (!in_array($value, ['entire_order', 'entire_order_include_shipping', 'shipping']) && in_array($params['model']->data['discount_type'], ['percent', 'fixed_amount'], true) && count($value == 'collection' ? $params['model']->data['prerequisite_collection_id'] : $params['model']->data['prerequisite_product_id']) > 0) : ?>
                                    <?php
                                    $checked = ' checked="checked"'; ?>
                                    <?php if ($value == 'collection') : ?>
                                        <?= $this->getJsonTag(OSC::helper('catalog/common')->loadCollectionSelectorData($params['model']->data['prerequisite_collection_id']), 'prerequisite_collection'); ?>
                                    <?php else : ?>
                                        <?= $this->getJsonTag(OSC::helper('catalog/common')->loadProductSelectorData($params['model']->data['prerequisite_product_id'], array_map(function($row){ $row = explode(':', $row); return $row[1]; }, $params['model']->data['prerequisite_variant_id'])), 'prerequisite_product'); ?>
                                    <?php endif; ?>
                                <?php
                                    else:
                                        $checked = $params['model']->data['prerequisite_type'] == $value ? ' checked="checked"' : '';
                                    endif;
                                ?>
                                <div class="mb5">
                                    <div class="styled-radio"><input type="radio" name="prerequisite_type" disabled value="<?= $value ?>" id="input-prerequisite_type-<?= $value ?>"<?= $checked ?> /><ins></ins></div>
                                    <label class="ml5 label-inline" for="input-prerequisite_type-<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15" style="display: none" data-discount-types="fixed_amount">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Applies to</div></div>
                    </div>
                    <div class="frm-grid">
                        <div class="apply_to" data-insert-cb="catalogInitDiscountCodeApplyTypeChangeFixedAmount">
                            <?php foreach (Model_Catalog_Discount_Code::FIXED_AMOUNT_APPLY_TYPES as $value => $label) : ?>
                                <?php
                                $input_type = $value === 'entire_order' ? 'radio' : 'checkbox';
                                $checked = ''; ?>
                                <?php if (!in_array($value, ['entire_order']) && in_array($params['model']->data['discount_type'], ['percent', 'fixed_amount'], true) && count($value == 'collection' ? $params['model']->data['prerequisite_collection_id'] : $params['model']->data['prerequisite_product_id']) > 0) : ?>
                                    <?php
                                    $checked = ' checked="checked"'; ?>
                                    <?php if ($value == 'collection') : ?>
                                        <?= $this->getJsonTag(OSC::helper('catalog/common')->loadCollectionSelectorData($params['model']->data['prerequisite_collection_id']), 'prerequisite_collection'); ?>
                                    <?php else : ?>
                                        <?= $this->getJsonTag(OSC::helper('catalog/common')->loadProductSelectorData($params['model']->data['prerequisite_product_id'], array_map(function($row){ $row = explode(':', $row); return $row[1]; }, $params['model']->data['prerequisite_variant_id'])), 'prerequisite_product'); ?>
                                    <?php endif; ?>
                                <?php
                                    else:
                                        $checked = $params['model']->data['prerequisite_type'] == $value ? ' checked="checked"' : '';
                                    endif;
                                ?>
                                <div class="mb5 condition-type-checker input-fixed_amount_prerequisite_type-<?= $value ?>">
                                    <div class="styled-<?= $input_type ?>"><input type="<?= $input_type ?>" name="prerequisite_type" disabled value="<?= $value ?>" id="input-fixed_amount_prerequisite_type-<?= $value ?>"<?= $checked ?> />
                                        <ins><?= $input_type == 'checkbox' ? $this->getIcon('check-solid') : '' ?></ins>
                                    </div>
                                    <label class="ml5 label-inline" for="input-fixed_amount_prerequisite_type-<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15" style="display: none" data-discount-types="percent,fixed_amount">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Minimum requirement</div></div>
                    </div>
                    <div class="frm-grid">
                        <div data-insert-cb="catalogInitDiscountCodeConditionChange">
                            <?= $this->getJsonTag($params['shipping_methods'], 'prerequisite_shipping'); ?>
                            <?php foreach (Model_Catalog_Discount_Code::CONDITION_TYPES as $value => $label) : ?>
                                <?php
                                $checked = '';
                                if ($value != 'none' && ($value == 'subtotal' ? $params['model']->data['prerequisite_subtotal'] : $params['model']->data['prerequisite_quantity']) > 0) {
                                    $checked = ' checked="checked"';
                                    if ($value == 'subtotal') {
                                        $checked .= ' data-subtotal="' . $params['model']->getFloatPrerequisiteSubtotal() . '"';
                                    } else {
                                        $checked .= ' data-quantity="' . $params['model']->data['prerequisite_quantity'] . '"';
                                    }
                                }
                                ?>
                                <div style="display: none" class="mb5 condition-type-checker" data-discount-types="percent,fixed_amount">
                                    <div class="styled-radio"><input type="radio" name="condition_type" value="<?= $value ?>" id="input-condition_type-<?= $value ?>"<?= $checked ?> /><ins></ins></div>
                                    <label class="ml5 label-inline" for="input-condition_type-<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                            <?php foreach (Model_Catalog_Discount_Code::FIXED_AMOUNT_CONDITION_TYPES as $value => $label) : ?>
                                <?php
                                $checked = '';
                                if ($value != 'none') {
                                    if ($value == 'shipping' && $params['model']->data['prerequisite_shipping']) {
                                        $checked = ' checked="checked"';
                                        $checked .= ' data-shipping="' . $params['model']->data['prerequisite_shipping'] . '"';
                                    }
                                }
                                ?>
                                <div style="display: none" class="mb5 condition-type-checker" data-discount-types="fixed_amount">
                                    <div class="styled-radio">
                                        <input type="radio" name="condition_type" value="<?= $value ?>" data-shipping_selected="<?= $params['model']->data['prerequisite_shipping'] ?>" id="input-condition_type-<?= $value ?>"<?= $checked ?> />
                                        <ins></ins>
                                    </div>
                                    <label class="ml5 label-inline" for="input-condition_type-<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Customer eligibility</div></div>
                    </div>
                    <div class="frm-grid">
                        <div data-insert-cb="catalogInitDiscountCodeCustomerLimitChange">
                            <?php foreach (Model_Catalog_Discount_Code::CUSTOMER_LIMIT_TYPES as $value => $label) : ?>
                                <?php $checked = ''; ?>
                                <?php if ($value != 'none' && count($value == 'group' ? $params['model']->data['prerequisite_customer_group'] : $params['model']->data['prerequisite_customer_id']) > 0) : ?>
                                    <?php $checked = ' checked="checked"'; ?>
                                    <?php if ($value == 'group') : ?>
                                        <?= $this->getJsonTag(OSC::helper('catalog/common')->loadCustomerGroupSelectorData($params['model']->data['prerequisite_customer_group']), 'prerequisite_customer_group'); ?>
                                    <?php else : ?>
                                        <?= $this->getJsonTag(OSC::helper('catalog/common')->loadCustomerSelectorData($params['model']->data['prerequisite_customer_id']), 'prerequisite_customer'); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="mb5">
                                    <div class="styled-radio"><input type="radio" name="customer_limit_type" value="<?= $value ?>" id="input-customer_limit_type-<?= $value ?>"<?= $checked ?> /><ins></ins></div>
                                    <label class="ml5 label-inline" for="input-customer_limit_type-<?= $value ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Usage limits</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div class="mb5">
                                <div class="styled-checkbox"><input type="checkbox" id="usage_limit_switcher" data-insert-cb="catalogInitDiscountCodeUsageLimitSwitcherChange"<?php if ($params['model']->data['usage_limit'] > 0) : ?> checked="checked" data-limit="<?= $params['model']->data['usage_limit'] ?>"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins></div>
                                <label class="ml5 label-inline" for="usage_limit_switcher">Limit number of times this discount can be used in total</label>
                            </div>
                            <div class="mb5">
                                <div class="styled-checkbox"><input type="checkbox" name="once_per_customer" value="1" id="input-once_per_customer"<?php if ($params['model']->data['once_per_customer'] == 1) : ?> checked="checked"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins></div>
                                <label class="ml5 label-inline" for="input-once_per_customer">Limit to one use per customer</label>
                            </div>
                            <div class="mb5" style="display: none" data-discount-types="fixed_amount">
                                <div class="styled-checkbox"><input type="checkbox" id="input-max_item_allow" data-insert-cb="catalogInitDiscountCodeMaxItemAllowChange"<?php if ($params['model']->data['max_item_allow'] > 0) : ?> checked="checked" data-limit="<?= $params['model']->data['max_item_allow'] ?>"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins></div>
                                <label class="ml5 label-inline" for="input-max_item_allow">Maximum item allow in order</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20">
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Active dates</div></div></div>

                    <div class="frm-grid">
                        <div>
                            <label for="input-publish_start_date">Start date</label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="active_date" id="input-active_date" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY'))) ?>" value="<?= $params['model']->data['active_timestamp'] > 0 ? date('d/m/Y', $params['model']->data['active_timestamp']) : '' ?>" data-insert-cb="initDatePicker" />
                                    </div>
                                    <div class="time-input">
                                        <?= $this->getIcon('clock') ?>
                                        <input type="text" name="active_time" id="input-active_time" value="<?= $params['model']->data['active_timestamp'] > 0 ? date('H:i', $params['model']->data['active_timestamp']) : '' ?>" data-insert-cb="initTimePicker" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-publish_to_date">End date</label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="deactive_date" id="input-deactive_date" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY'))) ?>" value="<?= $params['model']->data['deactive_timestamp'] > 0 ? date('d/m/Y', $params['model']->data['deactive_timestamp']) : '' ?>" data-insert-cb="initDatePicker" />
                                    </div>
                                    <div class="time-input">
                                        <?= $this->getIcon('clock') ?>
                                        <input type="text" name="deactive_time" id="input-deactive_time" value="<?= $params['model']->data['deactive_timestamp'] > 0 ? date('H:i', $params['model']->data['deactive_timestamp']) : '' ?>" data-insert-cb="initTimePicker" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div class="frm-grid">
                        <div>
                            <div class="styled-checkbox"><input type="checkbox" name="auto_apply" value="1" id="input-auto_apply"<?php if ($params['model']->data['auto_apply'] == 1) : ?> checked="checked"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins></div>
                            <label class="ml5 label-inline" for="input-auto_apply">Auto apply code</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>