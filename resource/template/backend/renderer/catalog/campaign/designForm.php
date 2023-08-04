<?php
/* @var $this Helper_Backend_Template */

$this->addComponent('uploader')
        ->push([
            'components/video/video-uploader.js',
            '[core]community/jquery.serialize-object.js',
            'catalog/product.js', 'catalog/campaign.js',
            '[frontend_template]personalizedDesign/common.js'
        ], 'js')
        ->push([
            'components/video/video-uploader.scss',
            'catalog/campaign.scss',
            '[frontend_template]personalizedDesign/common.scss',
        ], 'css');
?>
<?= $this->getJsonTag(OSC::helper('catalog/productType')->getProductTypeTabs(), 'product-type-tabs'); ?>
<div style="display:none" class="campaign-type" value="<?= $params['campaign_type']; ?>"><?= $params['campaign_type']; ?></div>
<form method="post" data-campaign-id="<?= $params['model']->getId();?>" data-apply_reorder="<?php echo ($this->checkPermission('catalog/product/apply_reorder'))?1:0;?>" action="<?= $this->getUrl('catalog/backend_campaign/post', ['state' => 'info', 'id' => $params['model']->getId(), 'campaign_type' => $params['campaign_type']]) ?>" data-insert-cb="catalogCampaignConfig">
    <div class="catalog-campaign-design-frm">
        <?= $this->getJsonTag($params['config_builder'], 'campaign-config');?>
        <div class="design-panel">
            <div class="design-tabs"></div>
            <div class="design-main-scene">
                <div class="tools-panel">
                    <div class="action-btn" data-action="vertical-center"><?= $this->getIcon('vertical-center') ?></div>
                    <div class="action-btn" data-action="horizontal-center"><?= $this->getIcon('horizontal-center') ?></div>
                    <div class="action-btn" data-action="personalized-opt-selector" style="display: none"><?= $this->getIcon('crosshair-solid') ?></div>
                    <div class="action-btn" data-action="bulk-apply"><?= $this->getIcon('duplicate') ?></div>
                    <div class="action-btn" data-action="helper-toggler"><?= $this->getIcon('eye-regular') ?></div>
                    <div class="action-btn" data-action="opacity-toggler"><?= $this->getIcon('opacity') ?></div>
                    <div class="action-btn" data-action="delete"><?= $this->getIcon('trash-alt-regular') ?></div>
                </div>
                <div class="apply-same-design"></div>
                <div class="design-scene"></div>
                <div class="uploader-panel">
                    <div>
                        <div class="image-selector btn btn-primary" campaign-type="<?= $params['campaign_type']; ?>">Browse a design file</div>
                    </div>
                    <div class="description">File type Png, Dpi 300, Max file size of 50Mb</div>
                </div>
            </div>
        </div>
        <div class="product-panel">
            <div class="panel-title">Products</div>
            <div class="product-list"></div>
        </div>
        <div class="mockup-panel">
            <div class="manage-title">
                <span>Management Mockup</span>
                <div class="manage-action-group">
                    <div class="guide">Choose many mockups to variants</div>
                    <div class="actions">
                        <a href="javascript://" class="js-cancel cancel-btn">Cancel</a>
                        <a href="javascript://" class="js-next next-btn">Next</a>
                    </div>
                </div>
            </div>
            <div class="manage-container">
                <div class="mockups-wrapper">
                </div>
            </div>
        </div>
        <div class="video-panel">
            <div class="video-title">
                <span>Management Video</span>
                <div class="video-action-group">
                    <div class="guide">Choose video to variants</div>
                    <div class="actions">
                        <a href="javascript://" class="js-cancel cancel-btn">Cancel</a>
                        <a href="javascript://" class="js-next next-btn">Next</a>
                    </div>
                </div>
            </div>
            <div class="video-uploader" data-selectable="single" data-insert-cb="initVideoUploader" data-max-size="<?= $params['max_video_size'] ?>" data-init-config='{"variantIds":[]}' data-videos="<?= $this->safeString(OSC::encode($params['videos'])) ?>" data-process-url='/catalog/backend_campaign/uploadMockupCustomer'></div>
        </div>
        <div class="continue">
            <button type="submit" class="btn btn-secondary btn-large btn--block mt10 btn__continue">Continue <i></i></button>
        </div>
    </div>
</form>
