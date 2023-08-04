<?php
/* @var $this Helper_Backend_Template */
?>
<?php

$this->addComponent('itemBrowser', 'daterangepicker', 'autoCompletePopover');

$this->push(['common/select2.min.css', 'filter/tag.scss'], 'css');
$this->push([
    'common/select2.min.js',
    'filter/tag.js'
], 'js');

$filter_tag_model = $params['filter_tag_model'];
$old_form = $params['old_form'];
$other_title = '';
if ($filter_tag_model->data['other_title']) {
    $other_title = explode(',', trim($filter_tag_model->data['other_title'], ' ,'));
    $other_title = implode("\n", $other_title);
}

?>
<form autocomplete="off" action="<?php echo $this->getUrl('*/*/*', array('id' => $filter_tag_model->getId())); ?>"
      method="post" class="post-frm product-post-frm p25" style="width: 900px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-title"><b>Title *</b></label>
                            <div>
                                <input type="text" class="styled-input" name="title" id="input-title"
                                       value="<?= $this->safeString($old_form['title'] ?? $filter_tag_model->data['title']) ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-other_title"><b>Other title</b></label>
                            <label for="input-other_title_des">Enter another name for the title, or another spelling, in
                                plural or singular form, with a different name on each line (Ex: T-shirt, tshirt
                                ...)</label>
                            <div>
                                <textarea type="text" class="styled-textarea" name="other_title"
                                          id="input-other_title"><?= $this->safeString($old_form['other_title'] ?? $other_title) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid frm-tag-parent">
                        <div class="mt5"><label for="input-title"><b>Parent Tag</b></label>
                            <div class="mb5">
                                <select name="parent_id" class="select2__form styled-input"
                                        data-insert-cb="initTagParent" id="input-tag-parent" required="required">
                                    <option value="0">__Root__</option>
                                    <?php
                                    foreach ($params['filter_tags'] as $key => $filter_tag):
                                        $selected = ($filter_tag['id'] == ($old_form['parent_id'] ?? $filter_tag_model->data['parent_id'])) ? 'selected' : '';
                                        if ($filter_tag['lock_flag'] != Model_Filter_Tag::STATE_TAG_LOCK):
                                            echo "<option value='" . $filter_tag['id'] . "' " . $selected . ">" . $filter_tag['prefixed_title'] . "</option>";
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid frm-tag-parent">
                        <div class="mt5">
                            <label><b>Example Image (optional)</b></label>
                            <div class="mb5">
                                <div data-insert-cb="initPostFrmSidebarImageUploader"
                                     class="frm-image-uploader"
                                     data-upload-url="<?= $this->getUrl('filter/tag/uploadImage') ?>"
                                     data-input="image"
                                     data-value="<?= $filter_tag_model->data['image'] ?>"
                                     data-image="<?= $filter_tag_model->data['image'] ? OSC::core('aws_s3')->getStorageUrl($filter_tag_model->data['image']) : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid wrap-choose-type-tag <?= ($filter_tag_model->data['parent_id'] > 0) ? 'hidden' : '' ?>">
                        <div>
                            <div class="frm-grid">
                                <div class="mt5">
                                    <input type="checkbox" name="type"
                                           data-insert-cb="initSwitcher" <?= $filter_tag_model->data['type'] == Model_Filter_Tag::TYPE_ONE_CHOICE ? "checked='checked'" : '' ?>/>
                                    <label class="label-inline ml10">
                                        <b>One Choice</b> (Yes: One Choice / No: Multiple Choice)
                                    </label>
                                </div>
                            </div>
                            <div class="frm-grid">
                                <div class="mt5">
                                    <input type="checkbox" name="required"
                                           data-insert-cb="initSwitcher" <?= $filter_tag_model->data['required'] ? "checked='checked'" : '' ?>/>
                                    <label class="label-inline ml10">
                                        <b>Required Group</b> (Yes: Required / No: Not Required)
                                    </label>
                                </div>
                            </div>
                            <div class="frm-grid">
                                <div class="mt5">
                                    <input type="checkbox" name="is_break_down_keyword"
                                           data-insert-cb="initSwitcher" <?= $filter_tag_model->data['is_break_down_keyword'] ? "checked='checked'" : '' ?>/>
                                    <label class="label-inline ml10">
                                        <b>Break down search's keyword to filter and search</b> (Yes: Break down / No: Not break down)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline"><?= $this->_('core.cancel') ?></a>
        <input type="hidden" name="action" value="post_form"/>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>
