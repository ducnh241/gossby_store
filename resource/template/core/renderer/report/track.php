<?php if(! OSC::core('request')->get('dev_speed_test')) : ?>
    <?php $this->push('[core]report/track.js', 'js'); ?>
    <?= OSC::helper('report/common')->loadExternalTrackingCode(); ?>
    <span data-insert-cb="reportTrackRecord" data-events="<?= $this->safeString(OSC::helper('report/common')->eventEncode(OSC::helper('report/common')->getRecordEvent())) ?>"></span>
<?php endif; ?>