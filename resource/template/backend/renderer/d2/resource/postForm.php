<?php

/* @var $this Helper_Backend_Template */

$this->push([
    'd2/resource.js'
], 'js');
$this->push(['d2/resource.scss'], 'css');

$model = $params['model'];

?>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $model->getId())); ?>"
      method="post" class="post-frm p25 page-post-frm" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div>
                        <label for="design_id" class="required"> Design ID </label>
                        <div>
                            <input type="number"
                                   class="styled-input"
                                   name="design_id"
                                   min="1"
                                   id="design_id"
                                   value="<?= $model->data['design_id'] ?>"
                                   required
                            />
                        </div>
                    </div>

                    <div class="mt20">
                        <label for="resource_url" class="required"> PSD File URL </label>
                        <div>
                            <input type="text"
                                   class="styled-input"
                                   name="resource_url"
                                   id="resource_url"
                                   value="<?= $model->data['resource_url'] ?>"
                                   required
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="block">
                <div class="plr20 pb20" data-insert-cb="initD2ResourceAddCondition">
                    <button type="button" id="add-condition" class="btn btn-secondary">Add Conditions</button>
                    <div class="mt20 required" >Conditions</div>
                    <div id="list-conditions">
                    </div>
                    <?= $this->getJSONTag([
                        'conditions' => $params['conditions'],
                    ], 'data')
                    ?>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="1" name="submit_form">
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary" ><?= $this->_('core.save') ?></button>
    </div>
</form>
