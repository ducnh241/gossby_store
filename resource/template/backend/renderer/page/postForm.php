<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->addComponent('datePicker', 'timePicker');
$this->push('catalog/meta-seo-image.css', 'css');
$this->push('catalog/catalog_seo_meta.js', 'js');
$this->push('catalog/common.js', 'js/top');
$this->push('catalog/common.scss', 'css');
$this->push('page/common.scss', 'css');
$this->push('page/common.js', 'js');
$images = $params['model']->data['images'] ? OSC::decode($params['model']->data['images']) : [];
?>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25 page-post-frm" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Page title</label>
                            <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']) ?>" /></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-track_quantity">Page key</label>
                            <div><input type="text" class="styled-input" name="page_key" id="input-page-key" value="<?= $this->safeString($params['model']->data['page_key']);?>" /></div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-track_quantity">Parent page</label>
                            <div>
                                <div class="styled-select">
                                    <select name="parent_id" id="input-track_quantity">
                                        <option value="0">__Root__</option>
                                        <?php foreach (OSC::helper('page/common')->getOptionPageParent() as $page_id => $page_title) :?>
                                            <?php
                                            if ($page_id !== $params['model']->data['page_id']): ?>
                                                <option value="<?= $page_id ?>" <?php if ($page_id == $params['model']->data['parent_id']) :?> selected="selected" <?php endif; ?>><?= $page_title ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <ins></ins>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="block mt15">
                <div class="header">
                    <div class="header__main-group">
                        <div class="header__heading">Page Contents</div>
                        <div class="input-desc font-weight--normal">
                            <b>Unique Store Information:</b><br>
                            {{store_name}} -> <?= OSC::helper('core/setting')->get('theme/site_name') ?><br>
                            {{store_email_address}} -> <?= OSC::helper('core/setting')->get('theme/contact/email') ?><br>
                            {{store_site}} -> <?= OSC::$base_url ?>
                        </div>
                    </div>
                </div>
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <div><textarea name="content" id="input-content" data-insert-cb="initEditor" style="display: none">
                                    <?= $this->safeString($params['model']->data['content']); ?>
                                </textarea></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="block mt15" id="additional_data_box">
                <div class="p20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Additional data</div></div>
                    </div>
                    <div data-insert-cb="pageInitAdditionalData" data-name="additional_data">
                        <?= $this->getJSONTag(['data' => $params['model']->data['additional_data'] ? OSC::decode($params['model']->data['additional_data']) : []], 'additional_data') ?>
                    </div>
                </div>
            </div>

            <!--   seo mata-->
            <div class="block mt15">

                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title' => 'SEO Meta Page']) ?>
                </div>
            </div>
            <!--   end seo mata-->
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20">
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Visibility</div></div></div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <div class="styled-radio">
                                    <input type="radio" name="publish_mode" id="input-no_publish" value="0"<?php if (! $params['model']->data['published_flag'] && $params['model']->data['publish_start_timestamp'] < 1 && $params['model']->data['publish_to_timestamp'] < 1) : ?> checked="checked"<?php endif; ?> />
                                    <ins></ins>
                                </div>
                                <label class="label-inline ml5" for="input-no_publish">Private</label>
                            </div>
                            <div class="mt5">
                                <div class="styled-radio">
                                    <input type="radio" name="publish_mode" id="input-publish" value="1"<?php if ($params['model']->data['published_flag'] && $params['model']->data['publish_start_timestamp'] < 1 && $params['model']->data['publish_to_timestamp'] < 1) : ?> checked="checked"<?php endif; ?> />
                                    <ins></ins>
                                </div>
                                <label class="label-inline ml5" for="input-publish">Publish</label>
                            </div>
                            <div class="mt5">
                                <div class="styled-radio">
                                    <input type="radio" name="publish_mode" id="input-publish_custom" value="2"<?php if ($params['model']->data['publish_start_timestamp'] > 0 || $params['model']->data['publish_to_timestamp'] > 0) : ?> checked="checked"<?php endif; ?> />
                                    <ins></ins>
                                </div>
                                <label class="label-inline ml5" for="input-publish_custom">Schedule publish date/ time</label>
                            </div>
                        </div>
                    </div>
                    <div class="e20 frm-line"></div>
                    <div class="frm-heading mb10"><div class="frm-heading__main"><div class="frm-heading__title">Page type</div></div></div>
                    <div class="frm-grid">
                        <div data-insert-cb="initPageType">
                            <?php foreach (OSC::helper('page/common')->getOptionPageType() as $page_type => $page_type_name) :?>
                                <div class="mb5">
                                    <div class="styled-radio">
                                        <input type="radio" name="type" id="<?= 'id_' . $page_type ?>" value="<?= $page_type ?>"<?php if ($page_type == $params['model']->data['type']) : ?> checked="checked"<?php endif; ?> />
                                        <ins></ins>
                                    </div>
                                    <label class="label-inline ml5" for="<?= 'id_' . $page_type ?>"><?= $page_type_name ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-position_index"><strong>Priority (DESC)</strong></label>
                            <div>
                                <input class="styled-input" name="priority" value="<?= $params['model']->data['priority'] ?>">
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-position_index"><strong>Heading tag</strong></label>
                            <spa>Automatically detect heading tags for sidebar menu</spa>
                            <div>
                                <input class="styled-input" name="heading_tag" value="<?= $params['model']->data['heading_tag'] ?>">
                            </div>
                        </div>
                    </div>
                    <div class="e20 frm-line"></div>
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Page image</div></div></div>
                    <div data-insert-cb="initPostFrmSidebarImageUploader" data-upload-url="<?= $this->getUrl('page/backend/uploadImage') ?>" data-input="image" data-image="<?= $params['model']->getImageUrl() ?>" data-value="<?= $params['model']->data['image'] ?>"></div>
                    <div class="frm-line e20"></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-publish_start_date">Publish date</label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="publish_start_date" id="input-publish_start_date" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY'))) ?>" value="<?= $params['model']->data['publish_start_timestamp'] > 0 ? date('d/m/Y', $params['model']->data['publish_start_timestamp']) : '' ?>" data-insert-cb="initDatePicker" />
                                    </div>
                                    <div class="time-input">
                                        <?= $this->getIcon('clock') ?>
                                        <input type="text" name="publish_start_time" id="input-publish_start_time" value="<?= $params['model']->data['publish_start_timestamp'] > 0 ? date('H:i', $params['model']->data['publish_start_timestamp']) : '' ?>" data-insert-cb="initTimePicker" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-publish_to_date">Close date</label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="publish_to_date" id="input-publish_to_date" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY'))) ?>" value="<?= $params['model']->data['publish_to_timestamp'] > 0 ? date('d/m/Y', $params['model']->data['publish_to_timestamp']) : '' ?>" data-insert-cb="initDatePicker" />
                                    </div>
                                    <div class="time-input">
                                        <?= $this->getIcon('clock') ?>
                                        <input type="text" name="publish_to_time" id="input-publish_to_time" value="<?= $params['model']->data['publish_to_timestamp'] > 0 ? date('H:i', $params['model']->data['publish_to_timestamp']) : '' ?>" data-insert-cb="initTimePicker" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="1" name="submit_form">
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-secondary" name="continue" value="1">Save & Continue</button>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>