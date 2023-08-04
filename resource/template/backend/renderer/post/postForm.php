<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->addComponent('datePicker', 'timePicker');
$this->push(['catalog/meta-seo-image.css', 'post/post.scss'], 'css');
$this->push(['catalog/catalog_seo_meta.js'], 'js');
$this->push(['post/postForm.js'], 'js');
$this->push(['post/common.js'], 'js');

$images = $params['model']->data['images'] ? OSC::decode($params['model']->data['images']) : [];

$pc_footer_banner_image_value = $params['model']->data['footer_banner_image']['pc'];
$mobile_footer_banner_image_value = $params['model']->data['footer_banner_image']['mobile'];

$pc_footer_banner_image = !empty($pc_footer_banner_image_value)
    ? OSC::core('aws_s3')->getStorageUrl($pc_footer_banner_image_value)
    : '';

$mobile_footer_banner_image = !empty($mobile_footer_banner_image_value)
    ? OSC::core('aws_s3')->getStorageUrl($mobile_footer_banner_image_value)
    : '';
?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post"
      class="post-frm p25 page-post-frm" style="width: 1150px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block p20">
                <div class="frm-grid">
                    <div>
                        <label for="input-title">Title</label>
                        <div><input type="text" class="styled-input" name="title" id="input-title"
                                    value="<?= $this->safeString($params['model']->data['title']) ?>"/>
                        </div>
                    </div>
                </div>
                <div class="frm-grid">
                    <div>
                        <label for="input-description">Description</label>
                        <div><textarea class="styled-textarea" name="description"
                                       id="input-description"><?= $this->safeString($params['model']->data['description']) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="frm-grid">
                    <div>
                        <label for="input-content">Content</label>
                        <div><textarea name="content" id="input-content" data-insert-cb="initEditor"
                                       style="display: none"><?= $this->safeString($params['model']->data['content']) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="frm-grid">
                    <div>
                        <label>Footer banner url</label>
                        <div><input type="text" class="styled-input" name="footer_banner_url"
                                    value="<?= $this->safeString($params['model']->data['footer_banner_url']) ?>"/>
                        </div>
                    </div>
                </div>
                <div class="frm-grid">
                    <div class="row" style="display: flex">
                        <div class="col-6" style="width: 100%">
                            <label>PC footer banner</label>
                            <div data-insert-cb="initPostFrmMetaImageUploader"
                                 data-upload-url="<?= $this->getUrl('*/*/uploadImage') ?>"
                                 data-input="pc_footer_banner"
                                 data-image="<?= $pc_footer_banner_image ?>"
                                 data-value="<?= $this->safeString($params['model']->data['footer_banner_image']['pc']) ?>">
                            </div>
                        </div>
                        <div class="col-6" style="width: 100%">
                            <label>Mobile footer banner</label>
                            <div data-insert-cb="initPostFrmMetaImageUploader"
                                 data-upload-url="<?= $this->getUrl('*/*/uploadImage') ?>"
                                 data-input="mobile_footer_banner"
                                 data-image="<?= $mobile_footer_banner_image ?>"
                                 data-value="<?= $this->safeString($params['model']->data['footer_banner_image']['mobile']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--   seo mata-->
            <div class="block mt15">
                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title' => 'SEO Meta Post']) ?>
                </div>
            </div>
            <!--   end seo mata-->
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">Visibility</div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <div class="styled-radio">
                                    <input type="radio" name="publish_mode" id="input-publish"
                                           value="1" <?php if ($params['model']->data['post_id'] > 0 && $params['model']->data['published_flag'] == 1) : ?> checked="checked"<?php endif; ?> />
                                    <ins></ins>
                                </div>
                                <label class="label-inline ml5" for="input-publish">Publish the post</label>
                            </div>
                            <div class="mt5">
                                <div class="styled-radio">
                                    <input type="radio" name="publish_mode" id="input-no_publish"
                                           value="0" <?php if ($params['model']->data['published_flag'] == 0) : ?> checked="checked"<?php endif; ?>  />
                                    <ins></ins>
                                </div>
                                <label class="label-inline ml5" for="input-no_publish">Don't publish</label>
                            </div>
                        </div>
                    </div>
                    <div class="e20 frm-line"></div>
                    <div class="frm-heading mb10">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">Collection</div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <?php foreach ($params['all_collection'] as $key => $post_collection) : ?>
                                <div class="mb5">
                                    <div class="styled-checkbox">
                                        <input type="checkbox" name="collection_ids[]"
                                               id="<?= 'id_' . $post_collection->getId() ?>"
                                               value="<?= $post_collection->getId() ?>"<?php if (in_array($post_collection->getId(), $params['selected_collection_ids'])) : ?> checked="checked"<?php endif; ?> />
                                        <ins><?= $this->getIcon('check-solid') ?></ins>
                                    </div>
                                    <label class="label-inline ml5"
                                           for="<?= 'id_' . $post_collection->getId() ?>"><?= $post_collection->data['title'] ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-position_index"><strong>Priority (DESC)</strong></label>
                            <div>
                                <input class="styled-input" name="priority"
                                       value="<?= $params['model']->data['priority'] ?>">
                            </div>
                        </div>
                    </div>
                    <div class="e20 frm-line"></div>
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">Author</div>
                        </div>
                    </div>
                        <div class="styled-select">
                            <select name="author_id" >
                                <?php if (!$params['model']->data['author_id']) :?>
                                    <option value="0">Please select an author</option>
                                <?php endif;?>
                                <?php foreach ($params['authors'] as $author) : ?>
                                    <option value="<?= $author->data['author_id'] ?>"  <?php if ($params['model']->data['author_id'] == $author->data['author_id']) : ?>selected<?php endif;?>>
                                        <?= $author->data['name']?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <ins></ins>
                        </div>
                    <div class="e20 frm-line"></div>
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">Post image</div>
                        </div>
                    </div>
                    <div data-insert-cb="initPostFrmSidebarImageUploader"
                         data-upload-url="<?= $this->getUrl('post/backend_post/uploadImage') ?>"
                         data-input="image" data-image="<?= $params['model']->getImageUrl() ?>"
                         data-value="<?= $params['model']->data['image'] ?>">
                    </div>
                    <div class="e20 frm-line"></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-publish_start_date">Display last updated </label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="modified_date" id="input-updated_date"
                                                <?php if ($params['model']->data['modified_timestamp']):?>
                                                    value="<?= date('d/m/Y', $params['model']->data['modified_timestamp'])?>"
                                                <?php endif;?>
                                                data-datepicker-config="<?= $this->safeString(OSC::encode(['date_format' => 'DD/MM/YYYY'])) ?>"
                                                data-insert-cb="initDatePicker"/>
                                    </div>
                                    <div class="time-input">
                                        <?= $this->getIcon('clock') ?>
                                        <input type="text" name="modified_time" id="input-updated_time"
                                                <?php if ($params['model']->data['modified_timestamp']):?>
                                                    value="<?= date('H:i', $params['model']->data['modified_timestamp'])?>"
                                                <?php endif;?>
                                               data-datepicker-config="<?= $this->safeString(OSC::encode(['date_format' => 'DD/MM/YYYY'])) ?>"
                                               data-insert-cb="initTimePicker"/>
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
        <a href="<?= $this->getUrl('*/*/list') ?>"
           class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <?php if ($params['model']->data['post_id'] > 0) : ?>
            <a href="<?= $params['model']->getDetailUrl() . '?token=' . base64_encode(Model_Post_Post::POST_PREVIEW_CODE) ?>" target="_blank"
               class="btn btn-outline">Preview</a>
        <?php endif; ?>
        <button type="submit" class="btn btn-secondary" name="continue"
                value="1">Save & Continue
        </button>
        <button type="submit"
                class="btn btn-primary"><?= $this->_('core.save') ?></button>

    </div>
</form>