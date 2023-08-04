<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
    <div>
        <input type="number" min="0" name="config[<?= $params['key'] ?>]" class="styled-input" value="<?= $params['value'] ? $params['value'] : '' ?>">
    </div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>