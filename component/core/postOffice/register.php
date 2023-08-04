<?php

OSC_Controller::registerBind('postOffice', 'postOffice');

//OSC_Cron::registerScheduler('postOffice/email_queue_resend', null, '* * * * *', ['estimate_time' => 60*60]);

OSC_Observer::registerObserver('initialize', ['Observer_PostOffice_Common', 'initialize']);
