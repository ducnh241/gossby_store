<?php

class Controller_PersonalizedDesign_Api extends Abstract_Core_Controller_Api {

    public function actionDesignImage() {
        /* @var $DB OSC_Database_Adapter */

        $sync_data = $this->_request->get('sync_data');

        if (!is_array($sync_data) || !isset($sync_data['design_id']) || !isset($sync_data['design_url'])) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        $sync_data['design_id'] = intval($sync_data['design_id']);
        $sync_data['design_url'] = trim($sync_data['design_url']);

        if ($sync_data['design_id'] < 1 || !$sync_data['design_url']) {
            $this->_ajaxError('Syncing data is incorrect');
        }

        try {
            $design = OSC::model('personalizedDesign/design')->load($sync_data['design_id']);

            if ($design->getId() < 1) {
                $this->_ajaxResponse();
            }

            $tmp_file_name = 'personalizedDesign/designImage/' . OSC::makeUniqid() . '.png';

            OSC::core('aws_s3')->tmpSaveFile($sync_data['design_url'], $tmp_file_name);

            $storage_file_path_s3 = OSC::core('aws_s3')->getStoragePath('personalizedDesign/designImage/' . $sync_data['design_id'] . '.png');
            $tmp_file_path_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_name);

            OSC::core('aws_s3')->copy($tmp_file_path_s3, $storage_file_path_s3, [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ]);

            try {
                //save palette_color
                $palette_color = OSC::helper('personalizedDesign/common')->getImgPaletteColor($design->getImagePath());

                $design->setData(['palette_color' => $palette_color])->save();
            } catch (Exception $ex) {}

        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                $this->_ajaxResponse();
            }

            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse();
    }

    public function actionGetOptionReportForAmazon() {
        $DB = OSC::core('database')->getReadAdapter();

        $design_cloned_id = $this->_request->get('design_cloned_id');

        $DB->select('*', 'personalized_design_analytic', "design_id = {$design_cloned_id}", null , null, 'fetch_analytics');

        $report_items = $DB->fetchArrayAll('fetch_analytics');

        $DB->free('fetch_report');

        $data = [];
        foreach ($report_items as $i => $item) {
            if (!empty($data[$item['value_key']])) {
                $data[$item['value_key']] += intval($item['counter']);
            } else {
                $data[$item['value_key']] = intval($item['counter']);
            }
        }

        $this->_ajaxResponse([
            'data' => empty($data) ? null : $data
        ]);
    }

}
