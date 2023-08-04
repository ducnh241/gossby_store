<?php
/* @var $this Helper_Backend_Template */

$this->push(<<<EOF
function __permItemSwitch() {
    $(this).find('input').click(function(){
        $(this).closest('label')[this.checked ? 'addClass' : 'removeClass']('active');
        
        if(! this.checked) {
            $(this).closest('li').find('label.active').each(function(){
                $(this).removeClass('active').find('input')[0].checked = false;
            });
        }
    }).each(function(){
        if(this.checked) {
            $(this).closest('label').addClass('active');
        }
    });
}
EOF
, 'js_code');
?>
<form action="<?= $this->getUrl('*/*/*', array('id' => $params['model']->getId())) ?>" method="post" class="post-frm p25" style="width: 550px">         
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <label for="input-title">Permission Mask Name</label>
                    <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']); ?>" /></div>
                </div>
            </div>
            <div class="frm-separate e20"></div>
            <div class="frm-grid">
                <div>
                    <label>Grant Permission:</label>
                    <div class="backend-perm-tree" data-insert-cb="__permItemSwitch">
                        <?= $this->build('user/permission_mask/post/perm_tree/item', array('key_prefix' => array(), 'permission_map' => $params['permission_map'], 'permission_data' => $params['model']->data['permission_data'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>    
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>