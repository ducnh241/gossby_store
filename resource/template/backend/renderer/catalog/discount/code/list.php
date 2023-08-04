<?php
/* @var $this Helper_Backend_Template */
/* @var $code Model_Catalog_Discount_Code */
/* @var $collection Model_Catalog_Discount_Code_Collection */

$collection = $params['collection'];
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('catalog/super|catalog/discount/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Code</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left; width: 300px;">Code</th>
                <th style="text-align: left; width: 100px;">Status</th>
                <th style="text-align: left; width: 100px;">Usage Counter</th>
                <th style="text-align: left;">Creator</th>
                <th style="text-align: left; width: 100px;">Start Date</th>
                <th style="text-align: left; width: 100px;">End Date</th>
                <th style="text-align: right; width: 70px;">Action</th>
            </tr>
            <?php foreach ($collection as $code) : ?>
                <?php
                if ($code->data['deactive_timestamp'] > 0 && $code->data['deactive_timestamp'] < time()) {
                    $label = 'Expired';
                    $label_color = 'gray';
                } else if ($code->data['active_timestamp'] > time()) {
                    $label = 'Scheduled';
                    $label_color = 'yellow';
                } else {
                    $label = 'Active';
                    $label_color = 'green';
                }
                ?>
                <tr>
                    <td style="text-align: left">
                        <div><strong><?= $code->data['discount_code'] ?></strong></div>
                        <div><?= implode(' • ', $code->getInfo()) ?></div>
                    </td>
                    <td style="text-align: left; width: 100px"><span class="badge badge-<?= $label_color ?>"><?= $label ?></span></td>
                    <td style="text-align: left; width: 100px"><?= $code->data['usage_counter'] ?> used</td>
                    <td style="text-align: left;"><?= $code->getMember() ? $code->getMember()->data['username'] : 'Unidentified'; ?></td>
                    <td style="text-align: left; width: 100px"><?= date('M d, Y', $code->data['active_timestamp'] > 0 ? $code->data['active_timestamp'] : $code->data['added_timestamp']) ?></td>
                    <td style="text-align: left; width: 100px"><?= date('M d, Y', $code->data['deactive_timestamp'] > 0 ? $code->data['deactive_timestamp'] : '') ?></td>
                    <td style="text-align: right; width: 70px">
                        <?php if ($this->checkPermission('catalog/super|catalog/discount/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $code->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/discount/delete')) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the code \"{$code->data['discount_code']}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $code->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
                Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
                No discount codes added yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>