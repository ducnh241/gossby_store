<?php
    $this->push(['filter/giftFinder.scss'], 'css');
    $this->push([
        'filter/giftFinder.js'
    ], 'js');
?>

<form class="post-frm p25" style="width: 1188px;" action="<?= $this->getUrl('*/*/*'); ?>" method="post">
    <?= $this->getJsonTag([
        'step_config' => $params['step_config'],
        'filter_tags' => $params['filter_tags']
    ], 'gift_finder') ?>
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block" style="background-color: #F7F8FC">
                <div class="p20">
                    <div class="frm-grid">
                        <div class="mt5 mb20">
                            <input type="checkbox" name="enable_gift_finder"
                                    data-insert-cb="initSwitcher" <?= $params['enable_gift_finder'] == 1 ? "checked='checked'" : '' ?>/>
                            <label class="label-inline ml10">
                                <b>Enable Gift Finder</b>
                            </label>
                        </div>
                    </div>
                    <div id="step-container">
                        <?php foreach ($params['step_config'] as $key => $step ) :?>
                            <div class="step-form mb25" data-step-number="<?= $key ?>" data-parent-tag="<?= $step['parent_tag']?>">
                                <input type="hidden" name="step_config[<?= $key ?>][parent_tag]" value="<?= $step['parent_tag'] ?>" />
                                <div class="step-form-header">
                                    <div class="step-form-header-title">Step <?= $key ?> Title</div>
                                    <?php if (intval($key) > 2) : ?>
                                        <div class="remove-step-btn" data-insert-cb="initRemoveStep">Remove</div>
                                    <?php endif ;?>
                                </div>
                                <div class="frm-grid mt15">
                                    <input type="text" class="styled-input" name="step_config[<?= $key ?>][title]" value="<?= $step['title'] ?>"/>
                                </div>
                                <label class="mt15 step-form-label">Options</label>
                                <div class="step-form-option">
                                    <div class="frm-grid">
                                        <div>
                                            <div class="styled-checkbox mr5">
                                                <input type="checkbox" value="1" data-insert-cb="initShowImageCheckbox" id="show_image_<?= $key ?>" name="step_config[<?= $key ?>][show_image]" <?php if ($step['show_image'] == 1) :?> checked="checked" <?php endif; ?>/>
                                                <ins><?= $this->getIcon('check-solid') ?></ins>
                                            </div>
                                            <label class="label-inline label-checkbox" for="show_image_<?= $key ?>">Show Example Images</label>
                                        </div>
                                    </div>
                                    <div class="tag-children mt15" data-insert-cb="initChildrenTag"></div>
                                    <div class="add-options" data-insert-cb="initAddOptionsPopup">Add options</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: right">
                        <div class="btn btn-outline" data-insert-cb="initAddNewStep" id="add-new-step-button"><?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Step</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="1" name="submit_form">
    <div class="action-bar">
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>

