<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('autoCompletePopover');
$this->addComponent('daterangepicker');

$this->push([
        'components/productTypeVariantSelector.js',
        'components/image/image-uploader.js',
        'components/video/video-uploader.js',
        'common/select2.min.js',
        'addon/service.js'
    ], 'js')
    ->push([
        'components/productTypeVariantSelector.scss',
        'components/image/image-uploader.scss',
        'components/video/video-uploader.scss',
        'common/select2.min.css',
        'addon/service.scss'
    ], 'css');

$model = $params['model'];
$response = $params['response'];
$auto_apply_product_type = $response['auto_apply_for_product_type'];
?>

<form action="<?php echo $this->getUrl('*/*/*', ['id' => $model->getId()]); ?>" method="post"
      class="post-frm p25 form-container" style="width: 950px">

    <input class="js-version-data" type="hidden" name="version_data" value="<?= $this->safeString(OSC::encode($response['version_data'])) ?>" />

    <div class="post-frm-grid mb20">
        <div class="post-frm-grid__main-col block p20">
            <div class="frm-grid">
                <label class="title">Campaign Title<span class="text-danger">*</span></label>
                <input type="text" class="styled-input" name="title"
                    id="input-title"
                    value="<?= $this->safeString($response['title']) ?>"
                    placeholder="example"
                    maxlength="80"
                    required
                />
            </div>
        </div>
    </div>

    <div class="post-frm-grid mb20">
        <div class="post-frm-grid__main-col block p20">
            <div class="form-input mb25">
                <div class="form-inline mr20">
                    <div class="styled-radio">
                        <input
                            type="radio"
                            name="type"
                            value="<?= Model_Addon_Service::TYPE_ADDON ?>"
                            id="input-type-addon"
                            class="js-addon-type-input"
                            data-config='{"viewMode": <?= $params['mode'] === 'view' ? 1 : 0 ?>,"label":"Option","field":"field-addon","requireOptionImage":1}'
                            <?php if ($response['type'] === Model_Addon_Service::TYPE_ADDON): ?>checked<?php endif; ?>
                            <?php if (count($model->data)): ?>readonly<?php endif; ?>
                            required
                        /><ins></ins>
                    </div>
                    <label class="ml5 label-inline" for="input-type-addon">Addon</label>
                </div>
                <div class="form-inline mr20">
                    <div class="styled-radio">
                        <input
                            type="radio"
                            name="type"
                            value="<?= Model_Addon_Service::TYPE_VARIANT ?>"
                            id="input-type-variant"
                            class="js-addon-type-input"
                            data-config='{"viewMode": <?= $params['mode'] === 'view' ? 1 : 0 ?>,"label":"Variant","field":"field-variant","requireOptionImage":0}'
                            <?php if ($response['type'] === Model_Addon_Service::TYPE_VARIANT): ?>checked<?php endif; ?>
                            <?php if (count($model->data)): ?>readonly<?php endif; ?>
                            required
                        /><ins></ins>
                    </div>
                    <label class="ml5 label-inline" for="input-type-variant">Variant</label>
                </div>
            </div>

            <div class="form-input">
                <label class="title" for="addon-status">Add-on Status<span class="text-danger">*</span></label>
                <div>
                    <input
                        type="checkbox"
                        name="status"
                        id="addon-status"
                        data-insert-cb="initSwitcher"
                        value="<?= $response['status'] ?>"
                        <?php if ($response['status']): ?>checked<?php endif; ?>
                    />
                </div>
            </div>

            <div class="form-input mb0">
                <label class="title">A/B Test Status<span class="text-danger">*</span></label>
                <div>
                    <input
                        type="checkbox"
                        name="ab_test_enable"
                        id="ab-test-enable"
                        class="js-ab-test-enable"
                        data-insert-cb="initSwitcher"
                        value="<?= $response['ab_test_enable'] ?>"
                        <?php if ($response['ab_test_enable']): ?>checked<?php endif; ?>
                    />
                </div>
            </div>

            <div class="form-input js-ab-test-time mt15">
                <label for="ab_test_time" class="title">A/B Test Time<span class="text-danger">*</span></label>
                <input id="ab_test_time" type="text" class="styled-input" name="ab_test_time" value="<?= $response['ab_test_time'] ?>" data-insert-cb="initDateRangePicker" autocomplete="off" required />
            </div>
        </div>
    </div>

    <div class="post-frm-grid mb20">
        <div class="post-frm-grid__main-col block p20">
            <div class="form-input field field-addon mb0">
                <div id="productTypeVariantSelector"
                     class="js-addon-product-type"
                     data-insert-cb="initProductTypeVariantSelector"
                     data-init="<?= $this->safeString(OSC::encode($params['product_type_variants'])) ?>"
                     data-selected="<?= $this->safeString(OSC::encode($response['auto_apply_for_product_type_variants'])) ?>"
                     data-input-name="auto_apply_for_product_type_variants"
                ></div>
            </div>

            <div class="form-input field field-addon set-active-time mt15">
                <div class="form-input">
                    <label class="title">Set Active Time</label>
                    <input type="text" class="styled-input js-active-time" name="active_time" value="<?= $response['active_time'] ?>" data-insert-cb="initDateRangePicker" data-drops="up" autocomplete="off" required />
                </div>
            </div>

            <div class="form-input field field-variant">
                <label class="title">Product Type<span class="text-danger">*</span></label>
                <select id="productTypeSelect" class="styled-select js-variant-product-type" name="product_type_info" required>
                    <option value=""></option>
                    <?php foreach ($params['product_types'] as $product_type_id => $product_type): ?>
                        <option value="<?= $product_type_id ?>/<?= $product_type['max_price'] ?>/<?= $product_type['title'] ?>" <?php if ($response['product_type_id'] == $product_type_id): ?>selected<?php endif; ?>><?= $product_type['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?php if ($params['mode'] === 'view'): ?>
    <div class="trigger-form-readonly"></div>
    <?php endif; ?>

    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col block p20">
            <div class="addon-tabs">
                <div class="addon-tabs__header">
                    <div class="addon-tabs__title">Set Versions</div>
                    <button class="addon-tabs__diff-btn js-show-versions-diff" type="button">See Version Differences</button>
                </div>
                <div class="addon-tabs__nav js-version-nav">
                    <div class="addon-tabs__list">
                        <div class="addon-tabs__btn js-version-add"><ins class="mr5"><?= $this->getIcon('plus') ?></ins> Add New</div>
                    </div>
                    <div class="addon-tabs__prev"></div>
                    <div class="addon-tabs__next"></div>
                </div>

                <div class="addon-tabs__body js-version-container"></div>

                <?php if ($params['mode'] !== 'view'): ?>
                    <button class="btn btn-primary mt15 js-version-add" type="button"><?= $this->getIcon('plus', ['class' => 'mr10']) ?>Add New Version</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline"><?= $this->_('core.cancel') ?></a>
        <?php if ($params['mode'] !== 'view' && ($this->checkPermission('addon_service/edit') || $this->checkPermission('addon_service/add') && !$params['model']->data['id'])): ?>
        <button type="submit" class="btn btn-secondary" name="continue" value="1">Save & Continue</button>
        <button type="submit" class="btn btn-primary ml5"><?= $this->_('core.save') ?></button>
        <?php endif; ?>
    </div>
</form>

<?= $this->build('addon/service/version-template', ['mode' => $params['mode']]) ?>
<?= $this->build('addon/service/option-template', ['mode' => $params['mode']]) ?>
