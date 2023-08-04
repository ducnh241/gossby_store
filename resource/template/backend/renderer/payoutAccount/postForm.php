<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->addComponent('datePicker', 'timePicker');
$this->push(['catalog/meta-seo-image.css', 'post/post.scss'], 'css');
$this->push(['catalog/catalog_seo_meta.js'], 'js');

$images = $params['model']->data['images'] ? OSC::decode($params['model']->data['images']) : [];
?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post"
      class="post-frm p25 page-post-frm" style="width: 600px">

    <div class="block p20">
        <div class="frm-grid">
            <div>
                <label for="input-title">Title</label>
                <div><input type="text" class="styled-input" name="title" id="input-title"
                            value="<?= $this->safeString($params['model']->data['title']) ?>" required />
                </div>
            </div>
        </div>
        <div class="frm-grid">
            <div>
                <label for="input-title">Payment Provider</label>
                <div>
                    <div class="styled-select">
                        <select name="account_type">
                            <option value="payoneer" <?= $this->safeString($params['model']->data['account_type']) == 'payoneer' ? 'selected' : '' ?>>
                                Payoneer
                            </option>
                            <option value="pingpong"<?= $this->safeString($params['model']->data['account_type']) == 'pingpong' ? 'selected' : '' ?>>
                                Pingpong
                            </option>
                        </select>
                        <ins></ins>
                    </div>
                </div>
            </div>
        </div>
        <div class="frm-grid">
            <div>
                <input type="checkbox" name="activated_flag"
                       data-insert-cb="initSwitcher" <?php echo (isset($params['model']->data['activated_flag']) && $params['model']->data['activated_flag'] == 1) ? 'checked' : '' ?> /><label
                        class="label-inline ml10">Activated flag</label>
            </div>
            <div>
                <input type="checkbox" name="default_flag"
                       data-insert-cb="initSwitcher" <?php echo (isset($params['model']->data['default_flag']) && $params['model']->data['default_flag'] == 1) ? 'checked' : '' ?> /><label
                        class="label-inline ml10">Default flag</label>
            </div>
        </div>

        <div>
            <div class="frm-heading">
                <div class="frm-heading__main">
                    <div class="frm-heading__title">Account Info</div>
                </div>
            </div>

            <div class="frm-grid">
                <div>
                    <div style="display: flex">
                        <label for="input-seo-title">Email</label>
                    </div>

                    <div><input type="text" autocomplete="off" class="styled-input" name="email"
                                value="<?= $this->safeString($params['model']->data['account_info']['email']) ? $this->safeString($params['model']->data['account_info']['email']) : '' ?>" required />
                    </div>
                    <label id="warnning_character_title" style="color: #FFC107" for="input-title"></label>
                </div>
            </div>
        </div>

        <input type="hidden" value="1" name="submit_form">
        <div class="action-bar">
            <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
            <button type="submit" class="btn btn-secondary" name="continue" value="1">Save & Continue</button>
            <button type="submit" class="btn btn-primary btn-shadow"><?= $this->_('core.save') ?></button>
        </div>
    </div>
</form>