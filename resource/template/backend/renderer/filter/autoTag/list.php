<?php
/* @var $this Helper_Backend_Template */

$this->push([
    'filter/autoTag.js'
], 'js');

?>
<div class="block m25">
    <div class="header">
        <?php if ($this->checkPermission('filter/auto_tag/setting_fields')) : ?>
            <div class="btn btn-primary btn-small ml5"
                 data-insert-cb="initSettingFieldAutoTag"
                 data-title="Setting fields product to compare tag keyword"
                 data-process-url="<?= $this->getUrl('*/*/settingFieldAutoTag') ?>"
            >
                <?= $this->getJSONTag([
                    'setting_fields' => is_array($params['setting_fields']) ? $params['setting_fields'] : [],
                ], 'data')
                ?>
                Setting Fields
            </div>
        <?php endif; ?>
    </div>

    <div class="header-grid" disabled="disabled"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length()) : ?>

        <table class="grid grid-borderless">
            <tr>
                <th style="width: 50px; text-align: center">Product ID</th>
                <th style="width: 450px; text-align: center">Auto tag</th>
                <th style="width: 450px; text-align: center">Deleted tag</th>
                <th style="width: 450px;text-align: center">Add new</th>
                <th style="width: 100px;text-align: center">% Delete</th>
                <th style="width: 100px;">% Add new</th>
                <th style="width: 100px;">Last render date</th>
                <th style="width: 100px;">Updated date</th>
            </tr>
            <?php /* @var $autoTag Model_Filter_AutoTag */ ?>
            <?php foreach ($params['collection'] as $autoTag) :
                $tags = $autoTag->getTags();
            ?>
                <tr>
                    <td style="text-align: center;text-decoration: underline;color: #188DFF"><a href="<?php echo $this->getUrl('catalog/backend_product/post', array('id' => $autoTag->data['product_id'], 'campaign_type' => 'default')); ?>" target="_blank"><?= $autoTag->data['product_id'] ?></a> </td>
                    <td style="text-align: left">
                        <?php foreach ($autoTag->data['auto_tag'] as $tag_id): ?>
                            <div class="badge badge-gray mt5"><?= $tags->getItemByPK($tag_id)->data['title'] ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td style="text-align: left">
                        <?php foreach ($autoTag->data['deleted_tag'] as $tag_id): ?>
                            <div class="badge badge-red mt5"><?= $tags->getItemByPK($tag_id)->data['title'] ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td style="text-align: left">
                        <?php foreach ($autoTag->data['new_tag'] as $tag_id): ?>
                            <div class="badge badge-green mt5"><?= $tags->getItemByPK($tag_id)->data['title'] ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td style="text-align: center"> <?= round(count($autoTag->data['deleted_tag']) * 100/count($autoTag->data['auto_tag'])) . '%' ?> </td>
                    <td style="text-align: center"> <?= round(count($autoTag->data['new_tag']) * 100/count($autoTag->data['auto_tag'])) . '%' ?> </td>
                    <td style="text-align: center"> <?= date('Y-m-d H:i:s', $autoTag->data['added_timestamp']) ?> </td>
                    <td style="text-align: center"> <?= date('Y-m-d H:i:s', $autoTag->data['modified_timestamp']) ?> </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>
