<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div class="styled-date-time-input"><div class="date-input" data-insert-cb="initDateTimeFrm"><?= $this->getIcon('calendar-alt') ?><input type="text" name="config[<?= $params['key'] ?>]" value="<?= $this->safeString($params['value']) ?>" /></div></div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>