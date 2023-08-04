<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_PersonalizedProduct_Design_Collection */
/* @var $design Model_PersonalizedProduct_Design */
$collection = $params['collection'];
$this->push('personalizedDesign/common.js', 'js')->push('personalizedDesign/common.scss', 'css');
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">&nbsp;</div>
        <?php if ($this->checkPermission('personalized_design/add')): ?>
            <div><a href="<?= $this->getUrl('*/*/post', ['type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Design</a></div>
        <?php endif; ?>
        <?php if ((($this->getAccount()->isRoot() && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) && $params['type'] != 'amazon') : ?>
            <div><a href="<?= $this->getUrl('*/*/convert') ?>" class="btn btn-primary btn-small">Convert design</a></div>
        <?php endif; ?>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search', ['type' => $params['type'] == 'amazon' ? 'amazon' : 'default']), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='item_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 10px; text-align: left">ID</th>
                <th style="text-align: left">Image</th>
                <th style="text-align: left">Title</th>
                <th style="width: 170px; text-align: left">Key</th>
                <th style="text-align: left">Creator</th>
                <th style="width: 170px; text-align: left">Date Added/Modified</th>
                <th style="width: 70px; text-align: left">&nbsp;</th>
                <?php if ($params['type'] == 'amazon') : ?>
                    <th style="width: 70px; text-align: left">Status</th>
                    <th style="width: 70px; text-align: left">Mode</th>
                <?php endif;?>
                <th style="width: 285px; text-align: right"></th>
            </tr>
            <?php foreach ($collection as $design) : ?>
                <tr>
                    <td style="text-align: center"><div class="styled-checkbox"><input type="checkbox" name="item_id" value="<?= $design->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins></div></td>
                    <td style="text-align: left">#<?= $design->getId() ?></td>
                    <td style="text-align: center">
                        <div data-insert-cb="initQuickLook" data-image="<?= $design->getImageUrl() ?>"
                             class="thumbnail-preview"
                             style="background-image: url(<?= $design->getImageUrl() ?>);">

                        </div>
                    </td>
                    <td style="text-align: left"><?= $design->data['title'] ?></td>
                    <td style="text-align: left"><?= $design->getUkey() ?></td>
                    <td style="text-align: left"><?= $design->getNameCreator() ?></td>
                    <td style="text-align: left">
                        <?= date('d/m/Y H:i:s', $design->data['added_timestamp']) ?><br>
                        <?= date('d/m/Y H:i:s', $design->data['modified_timestamp']) ?>
                    </td>
                    <td style="text-align: left"><span class="badge badge-<?= $design->data['locked_flag'] == 1 ? 'red' : 'green' ?>"><?= $design->data['locked_flag'] == 1 ? 'Locked' : 'Editable' ?></span></td>
                    <?php if ($params['type'] == 'amazon') : ?>
                        <td style="text-align: left"><span class="badge badge-<?= $design->data['is_draft'] == 1 ? 'yellow' : 'green' ?>"><?= $design->data['is_draft'] == 1 ? 'Draft' : 'Publish' ?></span></td>
                        <td style="text-align: left"><span class="badge badge-green"><?= $design->data['amz_mode'] == 1 ? '15option' : 'Surface' ?></span></td>
                    <?php endif;?>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('personalized_design/revert')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?= $this->getUrl('*/*/revert', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>"><?= $this->getIcon('time-vector') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('personalized_design/view_report')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/report', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>"><?= $this->getIcon('analytics') ?></a>
                        <?php endif; ?>
                        <?php if ((($this->checkPermission('personalized_design/rerender') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) &&  $params['type'] != 'amazon') : ?>
                            <a class="btn btn-small btn-icon" title="Rerender all orders of the design" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to rerender all orders by the design #{$design->getId()}?") ?>', '<?= $this->getUrl('*/*/bulkRerender', ['id' => $design->getId()]) ?>')"><?= $this->getIcon('redo-alt-solid') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('personalized_design/edit' . ($design->data['locked_flag'] == 1 ? '/locked' : ''))) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/post', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($design->data['locked_flag'] == 1) : ?>
                            <?php if ($this->checkPermission('personalized_design/edit/locked')) : ?>
                                <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/lockSwitch', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>"><?= $this->getIcon('key-solid') ?></a>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php if ($this->checkPermission('personalized_design/edit')) : ?>
                                <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/lockSwitch', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>"><?= $this->getIcon('lock-alt-solid') ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('personalized_design/add')) : ?>
                            <a class="btn btn-small btn-icon" title="Duplicate Design" href="<?= $this->getUrl('*/*/duplicate', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>"><?= $this->getIcon('clone') ?></a>
                        <?php endif; ?>
                        <?php if ($params['type'] != 'amazon' && $this->checkPermission('personalized_design/add')) : ?>
                            <a class="btn btn-small btn-icon" title="Duplicate to AMZ Personalized Design" href="<?= $this->getUrl('*/*/duplicateDesignToD3', ['id' => $design->getId()]) ?>"><?= $this->getIcon('clone-amazon') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('personalized_design/delete')) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the type \"{$design->data['title']}\"?")) ?>', '<?= $this->getUrl('*/*/delete', ['id' => $design->getId(), 'type' => $params['type'] == 'amazon' ? 'amazon' : 'default']) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?= $pager ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
                Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
                No designs created yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
