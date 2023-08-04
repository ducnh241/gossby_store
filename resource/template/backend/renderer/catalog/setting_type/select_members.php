<?php
/* @var $this Helper_Backend_Template */

$this->push([
    'common/select2.min.js'
], 'js');
$this->push(['common/select2.min.css'], 'css');
$vendors = OSC::model('user/member')->getListMemberIdeaResearch();
$name_config = "config[" . $params['key'] . "]";
?>


<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div>
<?php endif; ?>
<div class="styled-select">
    <select name="<?= $name_config ?>[]" class="js-select-members" multiple="multiple">
        <?php foreach ($vendors as $vendor): ?>
            <option value="<?= $vendor->data['member_id'] ?>" <?= in_array($vendor->getId(), $params['value']) ? 'selected' : '' ?>> <?= $vendor->data['username'] ?></option>
        <?php endforeach; ?>
    </select>
</div>

<script>
    $(document).ready(function () {
        $('.js-select-members').select2();
    });
</script>

<style>
    .select2-container--default .select2-selection--single {
        border-radius: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        min-height: 33px !important;
    }
</style>