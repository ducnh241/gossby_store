<?php

class Controller_Report_Frontend extends Abstract_Core_Controller
{

    public static function getTrackingCookieKey()
    {
        return OSC_Controller::makeRequestChecksum('_fp', OSC_SITE_KEY);
    }

    public function actionRecord()
    {
        try {
            if (OSC::isCli() || OSC::isCrawlerRequest()) {
                return;
            }

            $events = OSC::helper('report/common')->eventDecode($this->_request->get('events'));

            $request = $this->_request->get('req') ?: '';
            $options = [
                'source_url' => $request,
                'flag_api' => true,
            ];

            $referer_url = $this->_request->get('ref');

            $track_model = OSC::model('frontend/tracking');

            $cookie_key = static::getTrackingCookieKey();

            $track_key = OSC::cookieGet($cookie_key);

            if ($track_key) {
                try {
                    $track_model->loadByUKey($track_key);
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        return;
                    }
                }
            }

            try {
                $referer_setted = false;

                if ($track_model->getId() < 1) {
                    OSC::helper('report/common')->setReferer($referer_url);

                    $referer_setted = true;

                    $track_model->setData('added_timestamp', 0)->save()->register('IS_NEW_RECORD', 1);
                    OSC::cookieSetCrossSite($cookie_key, $track_model->getUkey());
                }

                $sref_id = 0;

                $sref = OSC::registry('DLS-SALE-REF');

                if (is_array($sref)) {
                    $sref_id = intval($sref['id']);
                }

                try {
                    $ab_key = '';
                    $ab_value = '';

                    OSC::model('frontend/tracking_footprint')->setData([
                        'track_ukey' => $track_model->getUkey(),
                        'request' => $this->_request->get('req'),
                        'referer' => $referer_url . ':sref:' . $sref_id,
                        'ab_key' => $ab_key,
                        'ab_value' => $ab_value,
                    ])->save();
                } catch (Exception $ex) {

                }

                if ($track_model->data['modified_timestamp'] < (time() - (60 * 30))) {
                    $track_model->setData('visit_timestamp', time());

                    if ($track_model->data['unique_timestamp'] < (time() - (60 * 60 * 24))) {
                        if (!$referer_setted) {
                            OSC::helper('report/common')->setReferer($referer_url);
                        }

                        OSC::helper('report/common')->increment('unique_visitor', 1);

                        $track_model->setData('unique_timestamp', time());
                    }

                    OSC::helper('report/common')->increment('visit', 1);

                    if ($track_model->registry('IS_NEW_RECORD') == 1) {
                        OSC::helper('report/common')->increment('new_visitor', 1);
                    } else {
                        OSC::helper('report/common')->increment('returning_visitor', 1);
                    }
                }

                OSC::helper('feed/common')->recordTrafficGoogle($options['source_url'] ?? '', $referer_url);

                //Todo: ToanLV update using observer
                OSC::helper('autoAb/abProduct')->trackingView($track_model, $options['source_url'] ?? '');
                OSC::core('observer')->dispatchEvent('tracking_view_ab_test_function', ['track_model' => $track_model, 'request' => $request]);

                OSC::helper('report/common')->increment('pageview', 1);

                $track_model->setData('modified_timestamp', time())->save();

                if (is_array($events) && count($events) > 0) {
                    foreach ($events as $event_key => $event_data) {
                        OSC::core('observer')->dispatchEvent('report_event:' . $event_key, [
                            'track_key' => $track_model->getUkey(),
                            'visit_timestamp' => $track_model->data['visit_timestamp'],
                            'unique_timestamp' => $track_model->data['unique_timestamp'],
                            'event_data' => $event_data
                        ]);
                    }
                }
            } catch (Exception $ex) {

            }
            return $this->_ajaxResponse();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionRec()
    {
        $track_model = OSC::model('frontend/tracking');

        $cookie_key = static::getTrackingCookieKey();

        $track_key = OSC::cookieGet($cookie_key);

        if (!$track_key) {
            $this->_ajaxError('No track key');
        }

        try {
            OSC::core('database')->insert('browser_behavior_recorded', [
                'track_ukey' => $track_key,
                'page_url' => $this->_request->get('url'),
                'event' => $this->_request->get('type'),
                'target' => $this->_request->get('target'),
                'pointer' => OSC::encode($this->_request->get('pointer')),
                'history' => intval($this->_request->get('history')),
                'added_timestamp' => time()
            ]);
        } catch (Exception $ex) {

        }

        $this->_ajaxResponse();
    }

    public function actionRecordBehavior()
    {
        if (OSC::isCli() || OSC::isCrawlerRequest()) {
            return;
        }

        $section_key = $this->_request->get('section_key');
        $section_value = $this->_request->get('section_value');

        $cookie_key = static::getTrackingCookieKey();
        $track_key = OSC::cookieGet($cookie_key);

        if (!$track_key || !$section_key || !$section_value) {
            $this->_ajaxError('Tracking invalid');
        }

        try {
            OSC::core('database')->insert('behavior_recorded', [
                'section_key' => $section_key,
                'section_value' => $section_value,
                'ab_key' => '',
                'ab_value' => '',
                'added_timestamp' => time()
            ]);
        } catch (Exception $ex) {

        }

        $this->_ajaxResponse();
    }

}
