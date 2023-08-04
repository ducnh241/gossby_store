<?php if(OSC::systemRegistry('CRON_METHOD') == 'html') : ?>
    <?php $scheduler_nextrun = intval(OSC::registry(OSC_Cron::SCHEDULER_NEXTRUN_FLAG)); ?>
    <?php if($scheduler_nextrun > 0 && $scheduler_nextrun <= time()) : ?>
        <img src="<?php echo $this->getUrl('cron/callback/html', array('type' => 'scheduler')); ?>" />
    <?php endif; ?>
    <?php $cron_nextrun = intval(OSC::registry(OSC_Cron::CRON_NEXTRUN_FLAG)); ?>
    <?php if($cron_nextrun > 0 && $cron_nextrun <= time()) : ?>
        <img src="<?php echo $this->getUrl('cron/callback/html', array('type' => 'cron')); ?>" />
    <?php endif; ?>
<?php endif; ?>