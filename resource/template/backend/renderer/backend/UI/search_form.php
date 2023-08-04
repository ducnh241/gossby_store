<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
?>
<form method="post" action="<?= $params['process_url'] ?>" class="flex--grow" data-insert-cb="initSearchForm">
    <?= $this->getJSONTag($params['filter_config'], 'filter-config') ?>
    <div class="styled-search">
        <button type="button" class="filter">
            <span>Filter<?= $this->getIcon('angle-down-solid') ?></span>
        </button>
        <input type="text" name="keywords" placeholder="Enter keywords to search" value="<?= $this->safeString($params['search_keywords']) ?>" />
        <button type="submit"><?= $this->getIcon('search') ?>Search</button>
    </div>
</form>