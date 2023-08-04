<?php
/* @var $this Helper_Backend_Template */
if ($params['model']->getId() > 0) {
    $file = $params['model']->getVersion() == 1 ? 'builder' : 'builderV' . $params['model']->getVersion();
} else {
    $file = 'builderV2';
}

$this->push('personalizedDesign/psd.min.js', 'js')
        ->push("personalizedDesign/$file.js", 'js')
        ->push('[core]community/fit-curve.min.js', 'js')
        ->push('[core]community/bezier.js', 'js')
        ->push('[core]community/toastr.min.js', 'js')
        ->push('[core]community/toastr.css', 'css')
        ->push('[core]nodeTextEditor.js', 'js')
        ->push('common/select2.min.js', 'js')
        ->push('common/select2.min.css', 'css')
        ->push("personalizedDesign/$file.scss", 'css')
        ->addComponent('colorPicker')
        ->push("$.loadSVGIconSprites('{$this->getImage('personalizedDesign/sprites.svg')}');", 'js_code');

?>

<form action="<?php echo $this->getUrl('*/*/*', ['id' => $params['model']->getId()]); ?>" method="post" class="post-frm personalized-design-post-frm p25" data-design="<?= $params['model']->getId() ?>">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20" style="display: flex; justify-content: flex-end">
                    <div style="flex-grow: 10">
                        <div class="label-progress" id="label-progress"></div>
                        <div class="progress">
                            <div class="color" id="progress-bar"></div>
                        </div>
                    </div>
                    <div id="upload-psd-btn" class="btn btn-primary btn-small" style="flex-grow: 1"><?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add by PSD file</div>
                    <input type="hidden" name="folderId" id="folderId"/>
                </div>
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Title</label>
                            <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']) ?>" /></div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="background_color" value="<?= $params['model']->data['background_color'] ?>">
            <div class="block mt10">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <?= $this->getJSONTag([
                                    'content' => $params['draft_content']['design_data'],
                                    'meta_data' => $params['draft_content']['meta_data']
                                ], 'personalized_design-draft_content') 
                            ?>
                            <?= $this->getJSONTag($params['permission_edit_lock'], 'personalized_design-permission_edit_lock') ?>
                            <input type="hidden" name="design_data" value="<?= $this->safeString(OSC::encode($params['model']->data['design_data'])) ?>" />
                            <input type="hidden" name="meta_data" value="<?= $this->safeString(OSC::encode($params['model']->data['meta_data'])) ?>" />
                            <div 
                                data-font-url="<?= $this->getUrl('*/*/font') ?>" 
                                data-upload-url="<?= $this->getUrl('*/*/upload') ?>" 
                                data-upload-thumb-url="<?= $this->getUrl('*/*/uploadThumbnail') ?>" 
                                data-upload-psd-url="<?= $this->getUrl('*/*/uploadFromPSD') ?>"
                                data-upload-thumb-psd-url="<?= $this->getUrl('*/*/uploadThumbnailFromPSD') ?>"
                                data-upload-font-url="<?= $this->getUrl('*/*/uploadFont') ?>" 
                                data-insert-cb="personalizedDesignBuilderInit"
                            >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <?php if($params['permission_edit_lock']['edit_layer'] || $params['permission_edit_lock']['remove_layer'] ): ?>
        <input name="continue" type="hidden" id="contineu_input" />
        <button type="button" class="btn btn-secondary btn-submit" data-continue="1">Save & Continue</button>
        <button type="button" class="btn btn-primary btn-submit" data-continue="0"><?= $this->_('core.save') ?></button>
        <?php endif;  ?>
    </div>
</form>