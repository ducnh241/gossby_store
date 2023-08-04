<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('select2');
?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 550px">     
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20 form_user_group_select">
            <div class="frm-grid">
                <div>
                    <label for="input-title">Group name</label>
                    <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']); ?>" /></div>
                </div>
            </div>
            <div class="frm-grid">
                <div>
                    <label for="input-permmask">Permission mask</label>
                    <div>
                        <select name="perm_mask[]" class="styled-select select_user_group" style="height: 100%"  id="input-permmask" multiple="multiple">
                            <?php foreach ($params['perm_mask_collection'] as $perm_mask) : ?>
                                <option value="<?php echo $perm_mask->getId(); ?>"<?php if (in_array($perm_mask->getId(), $params['model']->data['perm_mask_ids'])) : ?> selected="selected"<?php endif; ?>><?php echo $perm_mask->data['title']; ?></option>
                            <?php endforeach; ?>
                        </select>                        
                    </div>
                </div>
            </div>
            <div class="frm-grid">
                <div>
                    <label for="input-lock" class="label-inline mr10">Lock group</label>
                    <input type="checkbox" data-insert-cb="initSwitcher" name="lock_flag" value="1" id="input-lock"<?php if ($params['model']->data['lock_flag'] == 1) : ?> checked="checked"<?php endif; ?> />
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('.select_user_group').select2({
            placeholder: "Select permission mask"
        });
    })
</script>

