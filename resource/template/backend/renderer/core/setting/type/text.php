<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div>
    <input
        type="text"
        data-insert-cb="<?= $params['insert_cb']?>"
        name="config[<?= $params['key'] ?>]"
        class="styled-input"
        value="<?= $this->safeString($params['value']) ?>"
        <?= $params['required'] ? 'required' : '' ?>
        <?php if($params['pattern']): ?> pattern = "<?= $params['pattern'] ?>" <?php endif; ?>
        <?php if($params['insert_cb']): ?> data-insert-cb="<?= $params['insert_cb'] ?>" <?php endif; ?>
    />
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
