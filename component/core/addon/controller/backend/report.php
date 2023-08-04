<?php

class Controller_Addon_Backend_Report extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('addon_service');

        $this->getTemplate()->setCurrentMenuItemKey('product_config/addon_service');
    }

    public function actionDetail()
    {
        $this->getTemplate()
            ->setPageTitle('Report Add-on Services')
            ->setCurrentMenuItemKey('product_config/addon_service')
            ->addBreadcrumb('Add-on Services', $this->getUrl('addon/backend_service/list'));

        $this->checkPermission('addon_service/report');
        $addon_id = $this->_request->get('id');
        try {
            if (!$addon_id) {
                throw new Exception('Add-on service is not exist');
            }

            $addon_service = OSC::model('addon/service')->load($addon_id);

            $page_title = 'Report A/B Test Add-on ' . $addon_service->data['title'] . ' (' . date('d/m/Y', $addon_service->data['ab_test_start_timestamp']) . ' - ' . date('d/m/Y', $addon_service->data['ab_test_end_timestamp']) . ')';
            $this->getTemplate()->setPageTitle($page_title);
            $this->output($this->getTemplate()->build('addon/service/report', [
                'addon_service' => $addon_service
            ]));
        } catch (Exception $ex) {
            $this->addMessage($ex->getCode() == 404 ? 'Add-on service is not exist' : $ex->getMessage());
            static::redirect($this->getUrl('*/*/list'));
        }
    }

    public function actionGetTrackingRange()
    {
        $this->checkPermission('addon_service/report');

        $addon_id = $this->_request->get('id');
        $date_range = $this->_request->get('range');

        try {
            $addon_report = OSC::helper('addon/report')->getAddonReportData($addon_id, $date_range);

            $html = $this->getTemplate()->build('addon/service/reportDetail', $addon_report);

            $this->_ajaxResponse([
                'html' => $html
            ]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getCode() == 404 ? 'Addon service is not exist' : $ex->getMessage());
        }
    }
}

