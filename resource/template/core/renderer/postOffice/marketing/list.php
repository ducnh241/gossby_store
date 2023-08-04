<?php
/* @var $this Helper_Backend_Template */
$this->push('postOffice/marketing.js', 'js');
?>
<div class="block mt25">
    <div class="header-grid">
        <div class="flex--grow">
            <div class="btn btn-primary btn-small ml5" data-url="<?= $this->getUrl('*/*/maketing') ?>" data-upload-url="<?= $this->getUrl('*/*/bulkPostOfficeMarketingUpload') ?>" data-insert-cb="initPostOfficeMarketing">Upload list email</div>
        </div>
    </div>
</div>