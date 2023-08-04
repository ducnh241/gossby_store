<?php

class Cron_Catalog_Marketing_Email_Process extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $condition = [];

        $begin_date = $data['begin'];
        $end_date = $data['end'];

        if ($begin_date) {
            $begin_date = explode('/', $begin_date);
            $condition[] = 'added_timestamp >= ' . mktime(0, 0, 0, $begin_date[1], $begin_date[0], $begin_date[2]);
        }

        if ($end_date) {
            $end_date = explode('/', $end_date);
            $condition[] = 'added_timestamp <= ' . mktime(23, 59, 59, $end_date[1], $end_date[0], $end_date[2]);
        }

        if (count($condition) > 0) {
            $condition = implode(' AND ', $condition);
        } else {
            $condition = '';
        }

        $rows = [];

        try {
            foreach ($data['type'] as $type) {
                if ($type == 'abandonment') {
                    $DB->query("SELECT receiver_email,receiver_name FROM osc_post_office_email WHERE subject = 'You left items in your basket...'" . ($condition ? (' AND ' . $condition) : ''));
                } else if ($type == 'purchaser') {
                    $DB->getAdapter('db_master');
                    $DB->query("SELECT email,shipping_full_name,billing_full_name FROM osc_catalog_order" . ($condition ? (' WHERE ' . $condition) : ''));
                }

                if ($DB->rowCount() < 1) {
                    continue;
                }

                while ($row = $DB->fetchArray()) {
                    $email = isset($row['email']) ? $row['email'] : $row['receiver_email'];

                    if (isset($rows[$email])) {
                        continue;
                    }

                    if (isset($row['receiver_name'])) {
                        $full_name = $row['receiver_name'];
                    } else if ($row['billing_full_name'] == '') {
                        $full_name = $row['shipping_full_name'];
                    } else {
                        $full_name = $row['billing_full_name'];
                    }

                    $full_name = explode(' ', $full_name, 2);

                    $rows[$email] = [$email, $full_name[0], isset($full_name[1]) ? $full_name[1] : ''];
                }

                $DB->free();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            die;
        }

        foreach ($rows as $row) {
            try {
                OSC::helper('postOffice/email')->create([
                    'priority' => 1000,
                    'email_callback' => [
                        'validate' => [
                            'helper' => 'catalog/emailMarketing',
                            'function' => 'validateAbandoned',
//                            todo chua khai bao $cart
                            'params' => [$cart->getUkey()]
                        ]
                    ],
                    'subject' => 'You left items in your basket...',
                    'receiver_email' => $cart->data['email'],
                    'receiver_name' => $cart->getFullName(),
//                    todo chua khai bao $first_name
                    'html_content' => OSC::core('template')->build('catalog/email_template/html/main', ['is_marketing_email' => true, 'template' => 'catalog/email_template/html/checkout/abandoned/first', 'cart' => $cart, 'first_name' => $first_name])
                ]);
            } catch (Exception $ex) {
                
            }
        }
    }

}
