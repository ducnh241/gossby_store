<?php
/* @var $this Helper_Backend_Template */

$this->push([
    'common/select2.min.js',
    'report/setting_type/power_bi.js'
], 'js');
$this->push(['common/select2.min.css'], 'css');

$members = OSC::model('user/member')->getListMemberHasPerm('power_bi');

$_members = [];
foreach($members as $member) {
    $_members[$member->data['member_id']] = $member->data['username'];
}
?>

<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="powerBiAddPoint">
    <?php
        $data = [];
        if(is_array($params['value'])):
            foreach ($params['value'] as $key => $val):
                $data[$key]['name'] = $val['name'];
                $data[$key]['url'] = $val['url'];
                $data[$key]['viewer'] = $val['viewer'];
            endforeach;
    ?>
    <?php endif;?>
    <?= $this->getJSONTag(['data' => $data, 'members' => $_members], 'power_bi_point') ?>
</div>

