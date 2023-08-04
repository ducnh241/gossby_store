<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->addComponent('datePicker', 'timePicker');
$this->push(['catalog/meta-seo-image.css', 'post/post.scss'], 'css');
$this->push(['catalog/catalog_seo_meta.js'], 'js');

$images = $params['model']->data['images'] ? OSC::decode($params['model']->data['images']) : [];
?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post"
      class="post-frm p25 " style="width: 600px">
    <div class="block p20">
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <label for="input-track_quantity">Payout Account</label>
                    <div>
                        <div class="styled-select">
                            <select name="payout_account_id">
                                <option value="">__choose__</option>
                                <?php foreach ($params['list_acc'] as $key => $account): ?>
                                    <option value="<?php echo $account->data['account_id'] ?>" <?php echo ($this->safeString($params['model']->data['payout_account_id']) == $account->data['account_id'] || $account->data['account_id'] == $params['choose_acc']) ? 'selected' : '' ?>><?php echo $account->data['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <ins></ins>
                        </div>
                    </div>
                </div>
            </div>

            <div class="frm-grid">
                <div>
                    <label for="input-title">Amount</label>
                    <div><input type="text" class="styled-input" name="amount" id="input-title"
                                value="<?php echo($params['model']->data['amount']) ?>"/>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <input type="hidden" value="1" name="submit_form">
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>"
           class="btn btn-light btn-shadow mr5"><?= $this->getIcon('external-link-regular', array('class' => 'mr5')) ?><?= $this->_('core.cancel') ?></a>

        <button type="submit"
                class="btn btn-primary btn-shadow"><?= $this->getIcon('save-regular', array('class' => 'mr5')) ?><?= $this->_('core.save') ?></button>

    </div>
</form>