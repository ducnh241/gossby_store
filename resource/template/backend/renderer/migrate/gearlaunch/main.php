<?php
/* @var $this Helper_Backend_Template */
?>
<form action="<?php echo $this->getUrl('*/*/*'); ?>" method="post" class="post-frm p25">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid frm-grid--separate">
                        <div>
                            <label for="input-url">Store URL</label>
                            <div><input type="text" class="styled-input" name="url" id="input-url" value="<?= $this->safeString(OSC::core('request')->get('url', '')) ?>" /></div>
                        </div>
                        <div>
                            <label for="input-migrate_type">Migrate Type</label>
                            <div class="styled-select">
                                <select name="migrate_type" id="input-migrate_type">
                                    <option value="store"<?php if (OSC::core('request')->get('migrate_type', '') == 'store') : ?> selected="selected"<?php endif; ?>>All Store</option>
                                    <option value="collection"<?php if (OSC::core('request')->get('migrate_type', '') == 'collection') : ?> selected="selected"<?php endif; ?>>Collection</option>
                                    <option value="campaign"<?php if (OSC::core('request')->get('migrate_type', '') == 'campaign') : ?> selected="selected"<?php endif; ?>>Campaign</option>
                                </select>
                                <ins></ins>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid frm-grid--separate">
                        <div>
                            <label for="input-filter--product_type">Product types</label>
                            <div>
                                <input type="text" placeholder="Format: Unisex Short Sleeve Classic Tee, MUG, Womens Relaxed Fit Tee..." class="styled-input" name="filter_product_type" id="input-filter--product_type" value="<?= $this->safeString(OSC::core('request')->get('filter_product_type', '')) ?>" />
                            </div>
                        </div>
                        <div>
                            <label for="input-filter--color">Product colors</label>
                            <div>
                                <input type="text" placeholder="Format: Black, Carolina Blue, Deep Forest..." class="styled-input" name="filter_color" id="input-filter--color" value="<?= $this->safeString(OSC::core('request')->get('filter_color', '')) ?>" />
                            </div>
                        </div>
                        <div>
                            <label for="input-filter--size">Product sizes</label>
                            <div>
                                <input type="text" placeholder="Format: S, M, L..." class="styled-input" name="filter_size" id="input-filter--size" value="<?= $this->safeString(OSC::core('request')->get('filter_size', '')) ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <button type="submit" class="btn btn-primary"><?= $this->getIcon('save-regular', array('class' => 'mr5')) ?>Add new migrate task</button>
    </div>
</form>
<?php if ($params['collection']->length() > 0) : ?>  
    <div class="block m25">
        <div class="header">
            <div class="header__main-group"><div class="header__heading">Queue list</div></div>
        </div>      
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="text-align: left">Key</th>
                <th style="text-align: left">Title</th>
                <th style="text-align: left">Added date</th>
                <th style="text-align: left">Status</th>
                <th style="width: 70px; text-align: right"></th>
            </tr>
            <?php /* @var $navigation Model_Migrate_Gearlaunch */ ?>
            <?php foreach ($params['collection'] as $queue) : ?>
                <tr>
                    <td style="text-align: center"><?= $queue->getId() ?></td>
                    <td style="text-align: left"><?= $queue->data['queue_key'] ?></td>
                    <td style="text-align: left"><div><?= $queue->getDisplayTitle() ?></div><?php if ($queue->data['error_flag'] == 1) : ?><div class="badge badge-red"><?= $queue->data['error_message'] ?></div><?php endif; ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i:s', $queue->data['added_timestamp']) ?></td>
                    <td style="text-align: left"><div class="badge badge-<?= $queue->data['queue_flag'] == 1 ? 'green' : ($queue->data['error_flag'] == 1 ? 'red' : 'blue') ?>"><?= $queue->data['queue_flag'] == 1 ? 'Queue' : ($queue->data['error_flag'] == 1 ? 'Error' : 'Running') ?></div></td>
                    <td style="text-align: right">                        
                        <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/queueRerun', ['id' => $queue->getId()]); ?>"><?= $this->getIcon('pencil') ?></a>
                        <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the queue #{$queue->getId()}?") ?>', '<?= $this->getUrl('*/*/queueDelete', ['id' => $queue->getId()]) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>      
    </div>  
<?php endif; ?>