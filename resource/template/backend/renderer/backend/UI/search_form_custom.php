<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');

$this->push('catalog/search/custom.js', 'js');
$search_filter_field = $params['selected_filter_field'];
$default_select_field = OSC::cookieGet($params['default_search_field_key']);
if (empty($search_filter_field)) {
    $search_filter_field = $default_select_field;
}
?>

<form method="post" action="<?= $params['process_url'] ?>" class="flex--grow" data-insert-cb="initSearchFormCustom">
    <?= $this->getJSONTag($params['filter_config'], 'filter-config') ?>
    <div class="styled-search">
        <button type="button" class="filter filter_frm">
            <span>Filter <?= $this->getIcon('angle-down-solid') ?></span>
        </button>

        <?php if (!empty($params['filter_field'])): ?>
            <?= $this->getJSONTag($params['filter_field'], 'filter-field') ?>
            <button type="button" class="filter filter_field">
                <span id="lbl_selected_field"><?= $params['filter_field'][$search_filter_field] ?? 'Select field' ;?></span> <?= $this->getIcon('angle-down-solid') ?>
            </button>
            <input type="hidden" id="selected_field" name="filter_field" value="<?= $search_filter_field; ?>" />
            <input type="hidden" id="default_search_field_key" value="<?= $params['default_search_field_key']; ?>" />
        <?php endif; ?>

        <input type="text" name="keywords" placeholder="Please enter keywords for search" value="<?= $this->safeString($params['search_keywords']) ?>" />
        <button type="submit"><?= $this->getIcon('search') ?>Search</button>
    </div>
</form>