<?php

/* @var $this Helper_Backend_Template */

$this->push([
    'feed/common.js'
], 'js')->addComponent('select2');
$this->push(['feed/block.scss'], 'css');

$collection = [];
$data = OSC::model('catalog/collection')->getCollection()->addField('collection_id, title')->load();

foreach ($data as $item) {
    $collection[] = [
        'id' => $item->getId(),
        'title' => $item->data['title']
    ];
}

$country_title = $params['model']->data['country_code'] == '*' ? 'All Country' : OSC::helper('core/country')->getCountryTitle($params['model']->data['country_code']) . " ({$params['model']->data['country_code']})";

$category = $params['category'];

$tab_menu = OSC::helper('feed/common')->getTabMenu($category);

?>

<div class="tab_menu m25">
    <?php foreach ($tab_menu as $item):?>
        <a href="<?= $item['url'] ?>" class="<?= $item['activated'] == true ? 'active' : '' ?> tab_menu__item"><?= $item['title'] ?></a>
    <?php endforeach; ?>
</div>

<form action="<?php echo $this->getUrl('*/*/*'); ?>"
      method="post" class="post-frm p25 page-post-frm" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <?php if (!isset($params['model']->data['country_code'])) : ?>
                    <div class="p20">
                        <div class="frm-grid form_country_select">
                            <div>
                                <label for="country-select"> Country </label>
                                <div>
                                    <select class="selection-options styled-select" id="country-select">
                                        <?php foreach ($params['countries'] as $code => $name) : ?>
                                            <option value="<?= $code ?>">
                                                <?= $name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <span class="country-selected"> <?= $country_title ?></span>
                <?php endif; ?>
            </div>
            <div class="block">
                <div class="plr20 pb20">
                    <div class="frm-grid">
                        <div id="collection_list">
                            <?php if (isset($params['model']->data['country_code'])) : ?>
                                <?php foreach ($params['model']->getCollectionBlock() as $collection_id => $products) : ?>
                                    <div
                                        class='select-product-collection-component collection_item'
                                        data-insert-cb="initSelectProductCollectionComponent"
                                        data-collection-id="<?= $collection_id ?>"
                                        data-mode="<?= $params["is_edit"] ?>"
                                        data-collection-name="<?= $collection_id == 0 ? 'All Collection' : OSC::model('catalog/collection')->load($collection_id)->data['title'] ?>"
                                    >
                                        <?= $this->getJSONTag([
                                            'product_selector_params' => is_array($products) ? $products : [],
                                        ], 'data')
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="1" name="submit_form">
    <div class="action-bar">
        <a href="<?= $this->getUrl("*/*/{$category}") ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <?php if ($params['is_edit']) : ?>
            <button type="button" class="btn btn-secondary" data-insert-cb="initAddNewCollection">
                <svg data-icon="osc-icon-plus" viewBox="0 0 12 12" class="mr5">
                    <use xlink:href="#osc-icon-plus"></use>
                </svg>
                Add new collection
                <?= $this->getJSONTag([
                    'collection' => $collection
                ], 'collection')
                ?>
            </button>
            <button type="button" class="btn btn-primary" data-insert-cb="initSubmitBlock" data-country-code="<?=  $params['model']->data['country_code'] ?>" data-category="<?= $category ?>"><?= $this->_('core.save') ?></button>
        <?php endif; ?>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('.selection-options').select2({
            width: '100%',
            height: '100%',
            placeholder: "Select country"
        });
    });
</script>
