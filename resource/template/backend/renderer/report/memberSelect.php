<?php
/* @var $this Helper_Backend_Template */
$key = $params['action'];
$flag_selected = 0;
$list_members = OSC::helper('report/common')->getListMemberActiveAnalytic();

?>
<?php if (count($list_members) > 0) : ?>
    <div style="font-weight: 600; margin-right: 10px">Account:</div>
    <div class="members-report">
        <div>
            <div class="styled-select styled-select--small">
                <select data-insert-cb="initReportFilterMember">
                    <?php foreach ($list_members as $member) : ?>
                        <option value="<?= $member->getId(); ?>" <?php if ($params['sref_member_id'] == $member->getId()) : $flag_selected = 1; ?>  selected="selected" <?php endif; ?>
                                data-link="<?= $this->getUrl('srefReport/backend/' . $key, ['sref_member_id' => $member->getId()]); ?>"><?= $member->data['username']; ?></option>
                    <?php endforeach; ?>
                </select>
                <ins></ins>
            </div>
        </div>
    </div>
<?php endif; ?>
