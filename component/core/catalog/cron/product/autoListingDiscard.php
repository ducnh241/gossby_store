<?php

class Cron_Catalog_Product_AutoListingDiscard extends OSC_Cron_Abstract
{
    const CRON_TIMER = '0 1 * * *';
    const CRON_SCHEDULER_FLAG = 1;

    const LIST_PRODUCT_NOT_AUTO = [10076,10632,1067,1121,11235,11242,1131,11335,1134,11379,11400,12131,12351,12788,13090,13296,13332,13339,13340,13346,13351,13360,13361,13385,13419,13447,13643,13727,13764,13844,13966,14157,14189,14243,14553,14561,14594,14741,14762,14789,14800,14841,14866,14867,149,14925,14980,14986,14993,15056,15093,15265,15266,15272,15646,15664,157,15725,15729,15864,15943,16033,16082,16089,16090,16135,16287,16368,1645,16706,16910,16911,16914,17168,17366,17393,18152,18364,18410,18467,18612,18693,18897,18902,18903,18954,19326,19784,19785,19809,19852,19993,20073,20136,20306,20841,20852,21214,21721,22433,22460,22589,22646,22652,22691,22706,22733,22788,22796,22843,22855,22914,22988,23098,23110,23160,23230,23233,23240,23285,23305,23315,23337,23349,23383,23392,23393,23411,23421,23432,23435,23455,23541,23566,23581,23594,23597,23601,23627,23633,23716,23719,24126,24154,24158,24163,24167,24186,24334,24344,24373,24673,2469,24718,24731,24740,24741,24745,24753,24761,24767,24772,24779,24784,24789,24791,24801,24805,24811,24820,24850,24869,24870,24873,24961,2500,25047,25117,25125,25286,25309,25340,25354,25451,25500,2574,25970,25991,26336,26344,26352,26357,26364,26370,26375,26380,26389,26395,26401,26408,26440,26447,26452,26457,26489,26495,26501,26506,26508,26614,26620,26625,26629,26631,26716,26721,26728,26733,26800,26801,26861,26862,26895,26897,26919,26920,2695,26964,2697,26972,2699,2702,27029,27030,27069,27070,2744,2751,27724,28233,28566,28655,28700,28835,28859,29013,29159,29169,2918,2924,29266,29300,2944,29465,29578,29608,29680,29745,29781,29804,29810,2988,2989,2994,30102,30201,3021,30217,30361,30462,30573,30650,30687,30856,30887,30919,30947,30965,31006,31016,31086,31104,31153,31235,31305,31345,31381,31420,31421,31429,31436,31476,31534,31593,31617,31686,31763,31814,319,32010,32025,32033,32223,32279,32306,3251,32987,33026,33034,33113,33116,33117,33190,33224,33273,3359,33683,33691,33702,33791,33841,33941,33956,33976,33987,33994,34008,34051,34064,34083,34118,34148,34163,34166,34218,34221,34227,34232,34241,34263,34272,34275,34288,34299,34308,34312,34349,34411,34440,34550,34565,34586,34615,34627,34656,34661,34675,34694,34721,34764,34767,34806,34824,35036,35138,35147,35193,35258,35295,35312,35362,3537,35378,35501,35535,35536,35643,35656,35688,35703,35714,35768,35796,35805,35840,35882,36228,36294,36349,36424,36480,3653,3654,36627,36668,36703,36815,36844,36881,36912,36924,36955,36987,37108,37153,37216,37284,3729,3753,37660,37666,37839,37849,37917,37927,37934,38070,38116,38205,38245,38346,38347,3858,38591,38656,38676,38881,38909,38963,39081,39095,39098,39100,39113,39114,39138,39151,39153,39155,39179,39241,39267,39271,39416,39454,39488,39494,39652,39660,39705,39885,39898,40048,40245,40306,40662,41793,4205,4217,42540,42808,42869,42893,42932,43210,43924,43944,44060,44848,44852,45036,4687,503,504,5085,5113,5158,5242,5381,5423,5424,5432,5452,5530,5536,5547,5552,5585,6032,6084,6115,6163,6212,6243,6349,6592,660,661,6680,6798,6880,6889,6905,6978,6985,7121,7124,7252,740,7458,7548,7549,7570,7618,7629,7635,7647,7668,7675,7685,7686,7774,7776,783,785,7895,8077,8110,8140,8209,8267,8378,8426,8610,8625,8635,8673,8698,8732,8742,8789,8845,8859,8861,8865,8879,8888,8918,8961,9011,9073,9078,9152,9155,9258,9266,9314,9339,9366,9367,9410,9486,9526,9537,9592,9597,9607,9682,9809,9836,9912,9943];

    public function process($data, $queue_added_timestamp) {
        $DB = OSC::core('database');
        $logs = [];

        $deleted_logs = static::cleanProductDiscard();

        if (is_array($deleted_logs) && count($deleted_logs) > 0) {
            $logs['delete'] = $deleted_logs;
        }

        $time_format = date('d/m/Y - h:i A', time());
        try {
            $auto_listing_flag = intval(OSC::helper('core/setting')->get('catalog/auto_listing/enable')) == 1 ? 1 : 0;
            $auto_listing_quantity_sold = intval(OSC::helper('core/setting')->get('catalog/auto_listing/quantity_sold'));
            $auto_listing_quantity_sold = $auto_listing_quantity_sold > 0 ? $auto_listing_quantity_sold : 0;

            if ($auto_listing_flag == 1 && $auto_listing_quantity_sold > 0 ) {
                $collection = OSC::model('catalog/product')->getCollection()
                    ->addCondition('product_id', static::LIST_PRODUCT_NOT_AUTO, OSC_Database::OPERATOR_NOT_IN)
                    ->addCondition('listing', 0)
                    ->addCondition('solds', $auto_listing_quantity_sold, OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('meta_data', '"campaign_config":{"', OSC_Database::OPERATOR_LIKE)
                    ->load();

                if ($collection->length() > 0) {
                    $DB->select('product_id', 'catalog_product_auto_listing_discard' , 'type = "listing"', '`added_timestamp` ASC, record_id ASC', null, 'list_product_auto_listing');

                    $rows = $DB->fetchArrayAll('list_product_auto_listing');

                    $DB->free('list_product_auto_listing');

                    $listing_product_ids = count($rows) > 0 ? array_column($rows, 'product_id') : [];

                    foreach ($collection as $product) {
                        if ($product->checkMasterLock() || in_array($product->getId(), $listing_product_ids)) {
                            continue;
                        }
                        $product->setData(['listing' => 1])->save();

                        try {
                            $DB->insert('catalog_product_auto_listing_discard', [
                                "product_id" => $product->getId(),
                                "type" => "listing",
                                "added_timestamp" => time()
                            ], 'insert_product_auto_listing');
                        }catch (Exception $ex) {

                        }
                        $logs['listing'][$product->getId()] = $product->data['title'];
                    }
                }
            }

            $auto_discard_flag = intval(OSC::helper('core/setting')->get('catalog/auto_discard/enable')) == 1 ? 1 : 0;
            $auto_discard_quantity_sold = intval(OSC::helper('core/setting')->get('catalog/auto_discard/quantity_sold'));
            $auto_discard_quantity_sold = $auto_discard_quantity_sold > 0 ? $auto_discard_quantity_sold : 0;
            $auto_discard_after_number_days = intval(OSC::helper('core/setting')->get('catalog/auto_discard/time_to_discard'));
            $auto_discard_after_number_days = $auto_discard_after_number_days > 0 ? $auto_discard_after_number_days : 0;
            if ($auto_discard_flag == 1 && $auto_discard_quantity_sold > 0 && $auto_discard_after_number_days > 0) {
                $collection = OSC::model('catalog/product')->getCollection()
                    ->addCondition('product_id', static::LIST_PRODUCT_NOT_AUTO, OSC_Database::OPERATOR_NOT_IN)
                    ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL)
                    ->addCondition('solds', $auto_discard_quantity_sold, OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('added_timestamp', time() - $auto_discard_after_number_days*60*60*24, OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL, OSC_Database::RELATION_AND)
                    ->load();

                if ($collection->length() > 0) {
                    $DB->select('product_id', 'catalog_product_auto_listing_discard' , 'type = "discard"', '`added_timestamp` ASC, record_id ASC', null, 'list_product_auto_discard');

                    $rows = $DB->fetchArrayAll('list_product_auto_discard');

                    $DB->free('list_product_auto_discard');

                    $discard_product_ids = count($rows) > 0 ? array_column($rows, 'product_id') : [];

                    foreach ($collection as $product) {
                        if ($product->checkMasterLock() || in_array($product->getId(), $discard_product_ids)) {
                            continue;
                        }

                        $product->setData(['discarded' => 1])->save();

                        try {
                            $DB->insert('catalog_product_auto_listing_discard', [
                                "product_id" => $product->getId(),
                                "type" => "discard",
                                "added_timestamp" => time()
                            ], 'insert_product_auto_discard');
                        }catch (Exception $ex) {
                        }
                        $logs['discard'][$product->getId()] = $product->data['title'];
                    }
                }
            }

            foreach (['delete', 'listing', 'discard'] as $type_action) {
                if (!isset($logs[$type_action]) || !is_array($logs[$type_action]) || count($logs[$type_action]) < 1) {
                    continue;
                }
                foreach ($logs[$type_action] as $product_id => $product_title) {
                    try {
                        $data_insert = [
                            'content' => 'Catalog: '. $type_action. ' product #'.$product_id. ' by cron',
                            'log_data' => [
                                'cron_name' => 'catalog/product_autoListingDiscard',
                                'data_title' => $product_title,
                            ]
                        ];
                        OSC::helper('backend/common')->addCronLogs($data_insert);
                    }catch (Exception $ex) {
                    }
                }
            }
        } catch (Exception $ex) {
            OSC::logFile('['.$time_format.']:[ERROR]:'.$ex->getMessage(), 'auto_listing_discard');
        }
        return;
    }

    public static function cleanProductDiscard() {
        try {
            $DB = OSC::core('database');

            $DB->select('product_id', 'catalog_product_auto_listing_discard' , 'type = "discard" and added_timestamp <= '. (time() - 30*24*60*60), '`added_timestamp` ASC, record_id ASC', null, 'list_product_auto_discard_delete');

            $rows = $DB->fetchArrayAll('list_product_auto_discard_delete');

            $DB->free('list_product_auto_discard_delete');

            $product_ids = count($rows) > 0 ? array_column($rows, 'product_id') : [];

            $product_deleted = [];
            if (count($product_ids) > 0) {
                $collections = OSC::model('catalog/product')->getCollection()->load($product_ids);

                if ($collections->length() > 0) {
                    foreach ($collections as $product) {
                        if ($product->data['discarded'] == 1) {
                            $product_deleted[$product->getId()] = $product->data['title'];
                            $product->delete();
                        }
                    }
                }
            }

            if (count($product_deleted) > 0) {
                $product_deleted_ids = array_keys($product_deleted);
                $DB->delete('catalog_product_auto_listing_discard', 'type = "discard" and product_id in (' . implode(',', $product_deleted_ids) . ')', count($product_deleted_ids), 'delete_list_discard');
            }

            return $product_deleted;
        } catch (Exception $ex) {

        }
    }
}
