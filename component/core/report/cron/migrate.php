<?php

class Cron_Report_Migrate extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        while (true) {
            $DB->select('*', 'report_record', null, 'record_id DESC', 1000, 'fetch_old');

            while ($row = $DB->fetchArray('fetch_old')) {
                if ($DB->delete('report_record', 'record_id = ' . $row['record_id'], 1, 'delete_old') < 1) {
                    continue;
                }

                $ab_test_keys = [];

                if ($row['ab_test']) {
                    $buff = explode('|', $row['ab_test']);

                    foreach ($buff as $entry) {
                        $entry = explode(':', $entry, 2);

                        if (count($entry) != 2) {
                            continue;
                        }

                        $entry[0] = trim($entry[0]);
                        $entry[1] = trim($entry[1]);

                        if ($entry[0] === '' || $entry[1] === '') {
                            continue;
                        }

                        $ab_test_keys[$entry[0]] = $entry[1];
                    }
                }

                $referer_host = $row['referer_host'] ? $row['referer_host'] : 'direct';

                $timestamp = intval($row['added_timestamp']);
                $timestamp -= $timestamp % (60 * 15);

                switch ($row['report_key']) {
                    case 'catalog/item/view':
                    case 'catalog/item/visit':
                    case 'catalog/item/unique_visitor':
                        $this->incrementProductRecord($row['report_key'], $row['extra_key_1'], $row['report_value'], $referer_host, $ab_test_keys, $timestamp);
                        break;
                    case 'catalog/add_to_cart':
                    case 'catalog/checkout_initialize':
                        $this->incrementProductRecord($row['report_key'], 0, $row['report_value'], $referer_host, $ab_test_keys, $timestamp);
                        break;
                    case 'unique_visitor':
                    case 'visit':
                    case 'new_visitor':
                    case 'returning_visitor':
                    case 'pageview':
                        $this->increment($row['report_key'], $row['report_value'], $referer_host, $ab_test_keys, $timestamp);
                        break;
                }
            }
        }

        OSC::core('cron')->removeScheduler('report/migrate', ['processor' => $params['processor']]);
    }

    public function increment($key, $value, $referer_host, $ab_test_keys, $timestamp) {
        $DB = OSC::core('database')->getWriteAdapter();

        try {
            $DB->insert('report_record_new', ['report_key' => $key, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                try {
                    $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_new SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " LIMIT 1", ['report_key' => $key], 'update_report_record');
                } catch (Exception $ex) {
                    
                }
            }
        }

        try {
            $DB->insert('report_record_new_referer', ['report_key' => $key, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                try {
                    $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_new_referer SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND referer = :referer LIMIT 1", ['report_key' => $key, 'referer' => $referer_host], 'update_report_record');
                } catch (Exception $ex) {
                    
                }
            }
        }

        if (count($ab_test_keys) > 0) {
            foreach ($ab_test_keys as $ab_key => $ab_value) {
                try {
                    $DB->insert('report_record_new_ab', ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        try {
                            $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_new_ab SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND ab_key = :ab_key AND ab_value = :ab_value LIMIT 1", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value], 'update_report_record');
                        } catch (Exception $ex) {
                            
                        }
                    }
                }

                try {
                    $DB->insert('report_record_new_referer_ab', ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        try {
                            $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_new_referer_ab SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND referer = :referer AND ab_key = :ab_key AND ab_value = :ab_value LIMIT 1", ['report_key' => $key, 'referer' => $referer_host, 'ab_key' => $ab_key, 'ab_value' => $ab_value], 'update_report_record');
                        } catch (Exception $ex) {
                            
                        }
                    }
                }
            }
        }
    }

    public function incrementProductRecord($key, $product_id, $value, $referer_host, $ab_test_keys, $timestamp) {
        $product_id = intval($product_id);

        if ($product_id < 0) {
            $product_id = 0;
        }

        $DB = OSC::core('database')->getWriteAdapter();

        try {
            $DB->insert('report_record_product', ['report_key' => $key, 'product_id' => $product_id, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                try {
                    $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_product SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND product_id = {$product_id} LIMIT 1", ['report_key' => $key], 'update_report_record');
                } catch (Exception $ex) {
                    
                }
            }
        }

        try {
            $DB->insert('report_record_product_referer', ['report_key' => $key, 'product_id' => $product_id, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                try {
                    $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_product_referer SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND product_id = {$product_id} AND referer = :referer LIMIT 1", ['report_key' => $key, 'referer' => $referer_host], 'update_report_record');
                } catch (Exception $ex) {
                    
                }
            }
        }

        if (count($ab_test_keys) > 0) {
            foreach ($ab_test_keys as $ab_key => $ab_value) {
                try {
                    $DB->insert('report_record_product_ab', ['report_key' => $key, 'product_id' => $product_id, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        try {
                            $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_product_ab SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND product_id = {$product_id} AND ab_key = :ab_key AND ab_value = :ab_value LIMIT 1", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value], 'update_report_record');
                        } catch (Exception $ex) {
                            
                        }
                    }
                }

                try {
                    $DB->insert('report_record_product_referer_ab', ['report_key' => $key, 'product_id' => $product_id, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $timestamp], 'insert_report_record');
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        try {
                            $DB->query("UPDATE " . OSC::systemRegistry('db_prefix') . "report_record_product_referer_ab SET report_value = (report_value + {$value}) WHERE report_key = :report_key AND added_timestamp = " . $timestamp . " AND product_id = {$product_id} AND referer = :referer AND ab_key = :ab_key AND ab_value = :ab_value LIMIT 1", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'referer' => $referer_host], 'update_report_record');
                        } catch (Exception $ex) {
                            
                        }
                    }
                }
            }
        }
    }

}
