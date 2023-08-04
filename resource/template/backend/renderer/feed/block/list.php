<?php
/* @var $this Helper_Backend_Template */
$this->push(
    ['feed/block.js'], 'js'
)->addComponent('uploader')->push(['feed/block.scss'], 'css');

$category = $params['category'];
$tab_menu = OSC::helper('feed/common')->getTabMenu($category);
?>

<div class="tab_menu m25">
    <?php foreach ($tab_menu as $item):?>
        <a href="<?= $item['url'] ?>" class="<?= $item['activated'] == true ? 'active' : '' ?> tab_menu__item"><?= $item['title'] ?></a>
    <?php endforeach; ?>
</div>

<div class="block m25">

    <div class="header">
        <?php if ($this->checkPermission('feed/block/add')) : ?>
            <a href="<?php echo $this->getUrl('*/*/post', ['is_edit' => 1, 'category' => $category]); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create New Block</a>
        <?php endif; ?>


        <?php if ($this->checkPermission('feed/block/delete')) : ?>
            <div class="btn btn-danger btn-small ml5"
                 data-insert-cb="initBlockBulkDeleteBtn"
                 data-process-url="<?= $this->getUrl('*/*/bulkDelete') ?>"
                 data-confirm="<?= $this->safeString('Do you want to delete selected countries ?') ?>"
                 data-category="<?= $category ?>"
            >Delete</div>
        <?php endif; ?>

    </div>

    <div class="header-grid" disabled="disabled"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search' . ucfirst($category)), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>

    <?php if ($params['block']->length()) : ?>

        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='country_code']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="text-align: center">Country</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $block Model_Feed_Block */ ?>
            <?php foreach ($params['block'] as $block) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="country_code" value="<?= $block->data['country_code'] ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center">
                        <?php $country_title = $block->data['country_code'] == '*' ? 'All Country' : OSC::helper('core/country')->getCountryTitle($block->data['country_code']) . " ({$block->data['country_code']})"; ?>
                        <span class="collection_block"><?= $country_title ?></span><br>
                    </td>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('feed/block')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/post', ['country_code' => $block->data['country_code'], 'is_edit' => 0, 'category' => $category]); ?>"><?= $this->getIcon('eye-regular') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('feed/block/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/post', ['country_code' => $block->data['country_code'], 'is_edit' => 1, 'category' => $category]); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('feed/block/delete')) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the country \"{$country_title}\"?"))  ?>', '<?= $this->getUrl('*/*/delete', array('country_code' => $block->data['country_code'], 'category' => $category)) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['block']->getCurrentPage(), $params['block']->collectionLength(), $params['block']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>
