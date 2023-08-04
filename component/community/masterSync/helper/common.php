<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Helper_MasterSync_Common extends OSC_Object {

    const TBL_QUEUE_NAME = 'mastersync_queue';

    /**
     * 
     * @param string $sync_key
     * @param mixed $sync_data
     * @param array $options
     *  <table>
     *      <tr>
     *          <td colspan="2">overwrite</td>
     *      </tr>
     *      <tr>
     *          <td>running_time</td>
     *          <td>integer</td>
     *      </tr>
     *      <tr>
     *          <td>ukey</td>
     *          <td>string</td>
     *      </tr>
     *  </table>
     * @return $this
     * @throws Exception
     */
    public function addQueue($sync_key, $sync_data = [], $options = []) {
        $sync_key = trim(strval($sync_key));

        if (!$sync_key) {
            throw new Exception('Sync key is empty');
        }

        if (!is_array($options)) {
            $options = [];
        }

        $running_timestamp = $current_timestamp = time();

        if (isset($options['running_time'])) {
            $running_timestamp += intval($options['running_time']);
        }

        $queue_ukey = isset($options['ukey']) ? $options['ukey'] : null;
        $sync_data = OSC::encode($sync_data);

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        try {
            $query = 'INSERT INTO ' . $DB->getTableName(static::TBL_QUEUE_NAME) . ' (ukey, sync_key, sync_data, syncing_flag, error_message, added_timestamp, running_timestamp, modified_timestamp)' .
                    ' VALUES(:ukey, :sync_key, :sync_data, 0, \'\', ' . time() . ', ' . $running_timestamp . ', 0)';

            $params = [
                'ukey' => $queue_ukey,
                'sync_key' => $sync_key,
                'sync_data' => $sync_data
            ];

            if (in_array('overwrite', $options, true)) {
                $query .= ' ON DUPLICATE KEY UPDATE sync_data=:sync_data, running_timestamp=' . $running_timestamp;
            }

            $DB->query($query, $params, 'insert_sync_queue');

//            if ($DB->getNumAffected('insert_sync_queue') < 1) {
//                throw new Exception('Cron queue is not insertted');
//            }
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                throw new Exception($ex->getMessage());
            }
        }

        try {
            OSC::core('cron')->addQueue('masterSync/sync', null, ['ukey' => 'masterSync/sync:' . rand(1, 3), 'estimate_time' => 60*10]);
        } catch (Exception $ex) {
            
        }

        return $this;
    }

    public function syncProductConfig($sync_data) {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                switch ($sync_data['action']) {
                    case 'insert':
                        $record_columns = array_keys($sync_data['data']);
                        $group_id = $sync_data['data']['id'];

                        $insert = ['columns' => [], 'values' => []];
                        $update_config = [];

                        foreach ($record_columns as $record_column) {
                            if (is_array($sync_data['data'][$record_column])) {
                                $sync_data['data'][$record_column] = OSC::encode($sync_data['data'][$record_column]);
                            }
                            $insert['columns'][] = $record_column;
                            $insert['values'][] = ':' . $record_column;

                            $update_config[] = $record_column . '=:' . $record_column;
                        }

                        $insert['columns'] = implode(',', $insert['columns']);
                        $insert['values'] = implode(',', $insert['values']);
                        $update_config = implode(',', $update_config);

                        $DB->query("INSERT INTO {$DB->getTableName($sync_data['key'])} ({$insert['columns']}) VALUES ({$insert['values']}) ON DUPLICATE KEY UPDATE " . $update_config, $sync_data['data'], 'insert_new_data');
                        break;
                    case 'update':
                        $record_columns = array_keys($sync_data['data']);
                        $group_id = $sync_data['data']['id'];

                        $update = [];

                        foreach ($record_columns as $record_column) {
                            if (is_array($sync_data['data'][$record_column])) {
                                $sync_data['data'][$record_column] = OSC::encode($sync_data['data'][$record_column]);
                            }
                            $update[] = $record_column . '=:' . $record_column;
                        }

                        $update = implode(',', $update);

                        $DB->query("UPDATE {$DB->getTableName($sync_data['key'])} SET {$update} WHERE id =:id", $sync_data['data'], 'update_data');
                        break;
                    case 'delete':
                        $group_id = $sync_data['data'];
                        $DB->query("DELETE FROM {$DB->getTableName($sync_data['key'])} WHERE id =:value", ['value' => $sync_data['data']], 'delete_data');
                        break;
                    default:
                        throw new Exception('Data is incorrect');
                }

                if ($DB->getTableName($sync_data['key']) === OSC::model('core/country_group')->getTableName(true)) {
                    $supplier_location_rel_ids = [];
                    $location_data = 'g' . intval($group_id);
                    $DB->select('id', 'supplier_location_rel', "location_data LIKE '%{$location_data}%'", null, null, 'fetch_location_rel');
                    while ($row = $DB->fetchArray('fetch_location_rel')) {
                        if (intval($row['id']) > 0) {
                            $supplier_location_rel_ids[] = intval($row['id']);
                        }
                    }
                    $DB->free('fetch_location_rel');

                    OSC::core('cron')->addQueue('supplier/renderSupplyVariant', ['supplier_location_rel_ids' => $supplier_location_rel_ids], [
                        'overwrite',
                        'running_time' => 10,
                        'requeue_limit' => -1,
                        'estimate_time' => 60 * 60
                    ]);
                }

                $DB->commit();
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                throw new Exception($ex->getMessage());
            }

            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        }

    public function syncSupplierData($sync_data) {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $data_sync = $this->__getDataSync($sync_data, $DB->getTableName($sync_data['key']));

            $DB->query($data_sync['query'], $data_sync['data'], $data_sync['key']);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);
    }

    private function __getDataSync($sync_data, $table_name) {
        $data = [
            'query' => '',
            'data' => [],
            'key' => '',
            'group_id' => 0,
        ];

        try {
            switch ($sync_data['action']) {
                case 'insert':
                    $record_columns = array_keys($sync_data['data']);
                    $group_id = $sync_data['data']['id'];

                    $insert = ['columns' => [], 'values' => []];
                    $update_config = [];

                    foreach ($record_columns as $record_column) {
                        if (is_array($sync_data['data'][$record_column])) {
                            $sync_data['data'][$record_column] = OSC::encode($sync_data['data'][$record_column]);
                        }
                        $insert['columns'][] = $record_column;
                        $insert['values'][] = ':' . $record_column;

                        $update_config[] = $record_column . '=:' . $record_column;
                    }

                    $insert['columns'] = implode(',', $insert['columns']);
                    $insert['values'] = implode(',', $insert['values']);
                    $update_config = implode(',', $update_config);

                    $data['query'] = "INSERT INTO {$table_name} ({$insert['columns']}) VALUES ({$insert['values']}) ON DUPLICATE KEY UPDATE " . $update_config;
                    $data['data'] =  $sync_data['data'];
                    $data['key'] =  'insert_new_data';
                    $data['group_id'] =  $group_id;
                    break;
                case 'update':
                    $record_columns = array_keys($sync_data['data']);
                    $group_id = $sync_data['data']['id'];

                    $update = [];

                    foreach ($record_columns as $record_column) {
                        if (is_array($sync_data['data'][$record_column])) {
                            $sync_data['data'][$record_column] = OSC::encode($sync_data['data'][$record_column]);
                        }
                        $update[] = $record_column . '=:' . $record_column;
                    }

                    $update = implode(',', $update);

                    $data['query'] = "UPDATE {$table_name} SET {$update} WHERE id =:id";
                    $data['data'] =  $sync_data['data'];
                    $data['key'] =  'update_data';
                    $data['group_id'] = $group_id;
                    break;
                case 'delete':
                    $group_id = $sync_data['data'];

                    $data['query'] = "DELETE FROM {$table_name} WHERE id =:value";
                    $data['data'] =  ['value' => $sync_data['data']];
                    $data['key'] =  'delete_data';
                    $data['group_id'] = $group_id;
                    break;
                default:
                    throw new Exception('Data is incorrect');
            }

            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
