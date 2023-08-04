<?php

OSC_Controller::registerBind('report', 'report');

OSC_Template::registerComponent('report_track', ['type' => 'template', 'data' => '[core]report/track']);
OSC_Observer::registerObserver('frontend_initialize', ['Observer_Report_Common', 'addTrack']);

OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Report_Backend', 'collectPermKey'));
OSC_Observer::registerObserver('reactjs_collect_extra_data', ['Observer_Report_Common', 'collectReactJSExtraData']);
OSC_Observer::registerObserver('increment_visit_ab', ['Observer_Report_Common', 'incrementVisitAB']);

OSC_Cron::registerScheduler('report/insertAdTracking', ['process_key' => 'insertAdTracking_1'], '*/10 * * * *', ['estimate_time' => 60 * 60]);
OSC_Cron::registerScheduler('report/insertAdTracking', ['process_key' => 'insertAdTracking_2'], '*/12 * * * *', ['estimate_time' => 60 * 60]);
OSC_Cron::registerScheduler('report/insertAdTracking', ['process_key' => 'insertAdTracking_3'], '*/14 * * * *', ['estimate_time' => 60 * 60]);
OSC_Cron::registerScheduler('report/insertAdTracking', ['process_key' => 'insertAdTracking_4'], '*/16 * * * *', ['estimate_time' => 60 * 60]);
OSC_Cron::registerScheduler('report/insertAdTracking', ['process_key' => 'insertAdTracking_5'], '*/18 * * * *', ['estimate_time' => 60 * 60]);