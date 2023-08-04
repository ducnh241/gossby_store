<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->push('user/member/common.js', 'js'); ?>
<div class="block m25">
    <div class="header">
        <div class="header__action-group header__action-group--search">
            <?= $this->build('backend/UI/search_form_only_keyword', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'placeholder' => 'Search with username or email']) ?>
            <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create Account</a>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <?php if (OSC::isPrimaryStore()): ?>
                <th style="width: 10px; text-align: left">Username</th>
                <?php endif; ?>
                <th style="width: 200px; text-align: left">Email</th>
                <th style="text-align: left">Group</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['collection'] as $item) : ?>        
                <tr>
                    <td style="text-align: center"><?php echo $item->getId(); ?></td>
                    <?php if (OSC::isPrimaryStore()): ?>
                    <td style="text-align: left"><?php echo $item->data['username']; ?></td>
                    <?php endif; ?>
                    <td style="text-align: left"><?php echo $item->data['email']; ?></td>
                    <td style="text-align: left"><?php echo $item->getGroup()->data['title']; ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="#!" username="<?php echo $item->data['username']; ?>" uri="<?php echo $this->getUrl('*/*/getAuthSecretForm', array('id' => $item->getId())); ?>" data-insert-cb="initUserMemberLoadAuthSecretFrm"><?= $this->getIcon('shield-check') ?></a>
                        <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $item->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php if (!$item->isRoot() && ($item->data['group_id'] != OSC::systemRegistry('root_group')->admin || $this->getAccount()->isRoot())) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?php echo $this->safeString("Do you want to delete the account \"{$item->data['username']}\"?"); ?>', '<?php echo $this->getUrl('*/*/delete', array('id' => $item->getId())); ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php else : ?>
                            <a class="btn btn-small btn-icon" href="javascript: void(0)" disabled="disabled"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
            Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
            No accounts created yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>