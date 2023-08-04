<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div>
<?php endif; ?>
    <div>
        <textarea name="config[<?= $params['key'] ?>]"
                  class="styled-textarea"><?= $this->safeString($params['value']) ?></textarea>
    </div>
<?php if ($params['desc']): ?>
    <div class="input-desc"><?= $params['desc'] ?></div>
<?php endif; ?>