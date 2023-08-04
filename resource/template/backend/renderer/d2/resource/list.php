<?php
/* @var $this Helper_Backend_Template */
$this->push([
    'd2/resource.js'
], 'js');
$this->push(['d2/resource.scss'], 'css');
?>
<div class="block m25">
    <div class="header">
        <?php if ($this->checkPermission('d2/resource/add')) : ?>
            <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create New Resource</a>
        <?php endif; ?>


        <?php if ($this->checkPermission('d2/resource/delete')) : ?>
            <div class="btn btn-danger btn-small ml5"
                 data-insert-cb="initD2ResourceBulkDeleteBtn"
                 data-process-url="<?= $this->getUrl('*/*/bulkDelete') ?>"
                 data-confirm="<?= $this->safeString('Do you want to delete selected Resource ?') ?>"
            >Delete</div>
        <?php endif; ?>

    </div>

    <?php if ($params['collection']->length()) : ?>

        <div class="header-grid" disabled="disabled"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>

        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 50px; text-align: center">Id</th>
                <th style="width: 100px; text-align: center">Design ID</th>
                <th style="width: 450px; text-align: left">Source URL</th>
                <th style="text-align: center">Conditions</th>
                <th style="width: 100px;text-align: center">Added Time</th>
                <th style="width: 100px; text-align: right"></th>
            </tr>
            <?php /* @var $resource Model_D2_Resource */ ?>
            <?php foreach ($params['collection'] as $resource) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="resource_id" value="<?= $resource->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center"> <?= $resource->getId() ?> </td>
                    <td style="text-align: center"> <?= $resource->data['design_id'] ?> </td>
                    <td style="text-align: left" title="<?= $resource->data['resource_url'] ?>"> <?= substr($resource->data['resource_url'], 0, 40) . '...' . substr($resource->data['resource_url'], strlen($resource->data['resource_url']) - 21) ?> </td>
                    <td style="text-align: left">
                        <ol>
                            <?php $conditions = $resource->getConditions() ?>
                            <?php foreach ($conditions as $condition): ?>
                                <li><?= $condition->data['condition_key'] . ' => ' . $condition->data['condition_value'] ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </td>
                    <td style="text-align: center"> <?= date('Y-m-d H:i:s', $resource->data['added_timestamp']) ?> </td>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('d2/resource/add')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/duplicate', ['id' => $resource->getId()]); ?>"><?= $this->getIcon('duplicate') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('d2/resource/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/post', ['id' => $resource->getId()]); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('d2/resource/delete')) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the resource #{$resource->getId()}, design #{$resource->data['design_id']}?"))  ?>', '<?= $this->getUrl('*/*/delete', array('id' => $resource->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>
