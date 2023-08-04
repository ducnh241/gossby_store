<?php

use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Api;

class Cron_Facebook_ExecuteSetEvent extends OSC_Cron_Abstract
{
    const CRON_TIMER = '*/1 * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    const FLAG_QUEUE = 0;
    const FLAG_RUNNING = 1;
    const FLAG_ERROR = 2;

    public function process($params, $queue_added_timestamp)
    {
        $enable_facebook_pixel_api = OSC::helper('core/setting')->get('tracking/facebook_pixel_api/enable');
        $access_token = OSC::helper('core/setting')->get('tracking/facebook_pixel_api/access_token');

        if (!$enable_facebook_pixel_api || !$access_token) {
            return;
        }

        $osc_site_path = OSC_SITE_PATH;
        exec("python3 " . dirname(__FILE__) . "/executeSetEvent.py --osc_site_path {$osc_site_path}", $outputs);
        if (is_array($outputs)) {
            foreach ($outputs as $output) {
                if (strpos($output, "Error: ") !== false) {
                    throw new Exception($output);
                }
            }
        }
        return;

        /* @var $DB OSC_Database */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->select('*', 'facebook_api_queue', 'queue_flag = ' . self::FLAG_QUEUE, 'queue_id ASC', 1000, 'fetch_facebook_api_queue');
        $rows = $DB->fetchArrayAll('fetch_facebook_api_queue');
        $DB->free('fetch_facebook_api_queue');

        if (count($rows) < 1) {
            return;
        }

        foreach ($rows as $row) {

            try {

                $pixel_ids = OSC::decode($row['pixel_ids']);
                $data_events = OSC::decode($row['data_events']);
                if (isset($data_events['events']) && is_array($data_events['events'])) {
                    foreach ($data_events['events'] as $event_key => $data_event) {
                        $api = Api::init(null, null, $access_token);
                        $api->setLogger(new CurlLogger());
                        $custom_data = (new CustomData());
                        $event_id = strval(time());
                        switch ($event_key) {
                            case 'ViewContent':
                                $event_name = 'ViewContent';
                                $custom_data->setContentType($data_event['content_type'])
                                    ->setContentIds($data_event['content_ids'])
                                    ->setValue($data_event['value'])
                                    ->setContentName($data_event['content_name'])
                                    ->setCurrency($data_event['currency']);

                                $event_id = $data_event['eventID'];

                                break;
                            case 'AddToCart':
                                $event_name = 'AddToCart';
                                $custom_data->setContentType($data_event['content_type'])
                                    ->setContentIds($data_event['content_ids'])
                                    ->setValue($data_event['value'])
                                    ->setContentName($data_event['content_name'])
                                    ->setCurrency($data_event['currency']);

                                $event_id = $data_event['eventID'];
                                break;
                            case 'InitiateCheckout':
                                $event_name = 'InitiateCheckout';
                                $custom_data->setContentType($data_event['content_type'])
                                    ->setContentIds($data_event['content_ids'])
                                    ->setValue($data_event['value'])
                                    ->setContentName($data_event['content_name'])
                                    ->setCurrency($data_event['currency']);

                                $event_id = $data_event['eventID'];
                                break;
                            case 'Purchase':
                                $event_name = 'Purchase';
                                $custom_data->setNumItems($data_event['num_items'])
                                    ->setOrderId($data_event['order_id'])
                                    ->setValue($data_event['value'])
                                    ->setCurrency($data_event['currency'])
                                    ->setContentName($data_event['content_name'])
                                    ->setContentType($data_event['content_type']);

                                $event_id = $data_event['eventID'];
                                break;
                            default:
                                $event_name = 'PageView';
                                break;
                        }

                        $user_data = (new UserData())
                            ->setFbc($data_events['fb_click_id'])
                            // It is recommended to send Client IP and User Agent for ServerSide API Events.
                            ->setClientIpAddress($data_events['_SERVER']['REMOTE_ADDR'])
                            ->setClientUserAgent($data_events['_SERVER']['HTTP_USER_AGENT'])
                            ->setCountryCode($data_events['client_info']['country_code'])
                            ->setEmail($data_events['user_data']['email'])
                            ->setPhone($data_events['user_data']['phone'])
                            ->setFirstName($data_events['user_data']['first_name'])
                            ->setLastName($data_events['user_data']['last_name'])
                            ->setZipCode($data_events['user_data']['zip_code']);

                        $event = (new Event())
                            ->setEventName($event_name)
                            ->setEventTime(time())
                            ->setEventSourceUrl($data_events['source_url'])
                            ->setCustomData($custom_data)
                            ->setEventId($event_id);

                        $pixel_from_fb_ads = $data_events['pixel_from_fb_ads'];
                        $fb_click_id = $data_events['fb_click_id'];
                        $browser_id = $data_events['browser_id'];
                        foreach ($pixel_ids as $pixel_id) {
                            try {
                                if (intval($pixel_id) == intval($pixel_from_fb_ads)) {
                                    $user_data->setFbc($fb_click_id)->setFbp($browser_id);
                                } else {
                                    $user_data->setFbc(null)->setFbp(null);
                                }
                                $event->setUserData($user_data);

                                $events = [];
                                array_push($events, $event);

                                $request = (new EventRequest($pixel_id))->setEvents($events)->execute();
                            } catch (Exception $ex) {
                                throw new Exception('[API] Call error: ' . $ex->getMessage() . ' Pixel id: ' . $pixel_id);
                            }
                        }
                    }
                }

                $DB->delete('facebook_api_queue', 'queue_id=' . $row['queue_id'], 1, 'delete_facebook_api_queue');

            } catch (Exception $ex) {
                $DB->update('facebook_api_queue', ['queue_flag' => self::FLAG_ERROR, 'error_message' => $row['queue_flag'] . '/' . $ex->getMessage()], 'queue_id=' . $row['queue_id'], 1, 'update_facebook_api_queue');
            }
        }
    }
}
