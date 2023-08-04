<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('select2');
$this->push(['user/permission.js'], 'js');

$members_mkt = OSC::helper('report/common')->getListMemberMkt();
?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm product-post-frm p25" style="width: 550px">
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20">
            <div class="frm-grid form_user_group_select">
                <?php if ($params['member_collection'] !== null) : ?>
                    <div>
                        <label for="input-title">Member</label>
                        <div>
                            <select name="member_id" class="styled-select  select_user_group" id="input-password" data-insert-cb="initSelectPermissionAnalytic">
                                <?php foreach ($params['member_collection'] as $member) : ?>
                                    <option value="<?php echo $member->getId(); ?>" <?php if ($member->getId() == $params['model']->data['member_id']) : ?> selected="selected"<?php endif; ?>><?php echo $member->data['username']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="frm-grid form_user_group_select">
                <div>
                    <label for="input-perm-analytic">Viewing Permissions</label>
                    <div>
                        <select name="member_mkt_ids[]" class="styled-select select_user_group" style="height: 100%" id="input-perm-member" multiple="multiple" size="5">
                            <?php foreach ($members_mkt as $member) : ?>
                                <option value="<?php echo $member->getId(); ?>"<?php if (in_array($member->getId(), $params['model']->data['member_mkt_ids'])) : ?> selected="selected"<?php endif; ?>><?php echo $member->data['username']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            <?= $this->_('core.cancel') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?= $this->_('core.save') ?>
        </button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('.select_user_group').select2({
            placeholder: "Please select members"
        });
    })
</script>
