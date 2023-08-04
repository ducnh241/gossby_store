<?php $params['elements'] = implode('', $params['elements']); ?>
<div class="field-group">
    <?php if ($params['title']) : ?><div class="head"><?php echo $params['title']; ?></div><?php endif; ?>
    <div class="grid-filter-frm-row"><?php echo $params['elements']; ?></div>
</div>
