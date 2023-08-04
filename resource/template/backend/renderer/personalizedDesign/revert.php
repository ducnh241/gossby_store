<?php
$this->push('personalizedDesign/version_design.scss', 'css');
?>
<div class="post-frm-grid">
    <div class="post-frm-grid__main-col">
        <div class="block m25">
            <div class="container-box">
                <?php if ($params['collection']) : ?>
                    <?php foreach ($params['collection'] as $design): ?>
                        <?php $i += 1; ?>
                        <div class="box-item">
                            <form action="<?php echo $this->getUrl('*/*/postRevert', ['design_id' => $design->data['design_id'], 'version_id' => $design->data['record_id']]); ?>" method="post" class="post-frm personalized-design-post-frm" data-design="<?= $design->data['design_id']; ?>">
                                <div class="card-version <?= $design->data['active'] ? 'active_version' : ''; ?>">
                                    <div class="thumb-image-revert">
                                        <?= OSC::decode($design->data['meta_data'])['design_svg']; ?>
                                    </div>
                                    <div class="version-bottom">
                                        <div class="container-bottom">
                                            <div class="left-container">
                                                <div class="verion-title"><b>Version <?= $i; ?></b></div>
                                                <div class="time-version-design">Time
                                                    : <?= date("m/d/Y H:i:s", $design->data['added_timestamp']); ?>
                                                </div>
                                                <div class="version-account">Account
                                                    : <?= OSC::helper('personalizedDesign/common')->getAccountName($design->data['user_id']); ?>
                                                </div>
                                            </div>
                                            <div class="right-container">
                                                <button type="submit" class="btn <?= $design->data['active'] ? 'btn-secondary' : 'btn-primary' ?> btn-revert" name="revert" >
                                                    <?= $design->data['active'] ? 'Reverted'  : 'Revert'; ?>
                                                </button>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <h3 class="container-version">Sorry! No backup yet</h3>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>