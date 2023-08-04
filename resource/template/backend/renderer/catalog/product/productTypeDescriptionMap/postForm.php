<?php
/* @var $this Helper_Backend_Template */

$product_type = $params['model'];
$product_type_descriptions = $params['product_type_descriptions'];
$product_type_variants = $params['product_type_variants'];
$size_guide_data = $product_type->data['size_guide_data'];
?>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post"
      class="post-frm p25" style="width: 900px" novalidate>
    <div class="block">
        <div class="header">
            <div class="header__main-group">
                <div class="header__heading"><?= $params['form_title'] ?></div>
            </div>
        </div>
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <input type="hidden" name="is_submit" value="1">
                    <label for="select-type">Choose description for product type:
                        <b><?= $product_type->data['title'] ?></b></label>
                    <div class="styled-select">
                        <select name="product_type_description_id" class="select_product_type_description">
                            <option value="0">Please select an option</option>
                            <?php foreach ($product_type_descriptions as $product_type_description): ?>
                                <option value="<?= $product_type_description->getId() ?>" <?= intval($product_type->data['description_id']) === intval($product_type_description->data['id']) ? 'selected' : '' ?>><?= $product_type_description->data['title'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="frm-grid">
                <div>
                    <table class="table-choose-description">
                        <tr>
                            <th style="text-align: left; width: 50%">Product type variant</th>
                            <th style="text-align: left">Description</th>
                        </tr>
                        <?php foreach ($product_type_variants as $product_type_variant): ?>
                            <tr>
                                <td><?= $product_type_variant->data['title'] ?></td>
                                <td>
                                    <div class="styled-select">
                                        <select name="product_type_descriptions[<?= $product_type_variant->getId() ?>]"
                                                class="select_product_type_description">
                                            <option value="0">Please select an option</option>
                                            <?php foreach ($product_type_descriptions as $product_type_description): ?>
                                                <option value="<?= $product_type_description->getId() ?>" <?= intval($product_type_variant->data['description_id']) === intval($product_type_description->data['id']) ? 'selected' : '' ?>><?= $product_type_description->data['title'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <div class="frm-grid">
                <div>
                    <div>
                        <input
                            type="checkbox"
                            name="size_guide_allow"
                            id="size_guide_allow"
                            data-insert-cb="initSwitcher"
                            <?php if ($size_guide_data['allow']): ?>
                            checked="checked"
                            <?php endif; ?>
                        />
                        <label class="label-inline ml10">
                            <strong>Show Size Guide</strong>
                        </label>
                    </div>
                    <div class="size_guide <?php if ($size_guide_data['allow']): ?>show<?php endif; ?>">
                        <label>Custom size guide table</label>
                        <div class="size_guide_container mt-10" data-insert-cb="initCustomSizeBoard"></div>
                        <input type="hidden" value='<?= $size_guide_data['data'] ? OSC::encode($size_guide_data['data']) : "" ?>' name="size_guide_input" class="size_guide_input">
                        <label style="margin-top: 16px;">Upload image for size guide</label>
                        <div style="max-width: 400px;">
                            <div style="height: 0; overflow: hidden;">
                                <input type="text" class="input-require" value="<?= $size_guide_data['image'] ?>" <?php if ($size_guide_data['allow']): ?>required<?php endif; ?> />
                            </div>
                            <div data-insert-cb="initPostFrmSidebarImageUploader"
                                 class="frm-image-uploader"
                                 data-upload-url="<?= $this->getUrl('catalog/backend_productTypeDescriptionMap/uploadImage') ?>"
                                 data-input="size_guide_image"
                                 data-value="<?= $size_guide_data['image'] ?>"
                                 data-image="<?= $product_type->getSizeGuideImageUrl() ?>"
                                 data-trigger-change="triggerChangeImageRequire"
                            >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/index') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <?php if (OSC::controller()->checkPermission('catalog/product_config/product_type_description/map/edit', false)): ?>
            <button type="submit" class="btn btn-primary "><?= $this->_('core.save') ?></button>
        <?php endif; ?>
    </div>
</form>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $(".select_product_type_description").select2();
    });

</script>
<style>
    .select2-container--default .select2-search--inline .select2-search__field {
        background: transparent;
        border: none;
        outline: 0;
        box-shadow: none;
        -webkit-appearance: textfield;
        margin-top: 4px;
        height: 25px;
    }

    .select2-container .select2-selection--multiple {
        background: #ffffff;
        width: 100%;
        height: 35px;
        border: 1px solid #e0e0e0;
        padding: 0 10px;
        position: relative;
        -webkit-border-radius: 3px;
        border-radius: 3px;
        background-clip: padding-box;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple, .select2-container--default .select2-results > .select2-results__options {
        box-shadow: 0 0 0 1px rgba(20, 150, 245, 0.5);
        border-color: #1496f5;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border: 0px;
    }

    .table-choose-description {
        border-spacing: 0;
        width: 100%;
        border-left: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .table-choose-description tr th {
        text-align: left;
        border-top: 1px solid #e0e0e0;
        border-right: 1px solid #e0e0e0;
        padding: 7px;
        background: #e5e7f2;
    }

    .table-choose-description tr td {
        text-align: left;
        border-top: 1px solid #e0e0e0;
        border-right: 1px solid #e0e0e0;
        padding: 7px;
    }

</style>
