<?php
class Controller_MasterSync_Api_Orderdesk_Process extends Abstract_MasterSync_Controller_Api {
    public function actionIndex() {
        try {
            $lines = $this->_request->get('line_items');

            $order = OSC::model('catalog/order')->load($this->_request->get('order_id'));

            $processlable_items = OSC::helper('catalog/order')->lineItemsGetByFulfillable($order);

            if (count($processlable_items) < 1) {
                throw new Exception('The order is already processing');
            }

            $line_items = [];
            if (count($lines) > 0) {
                foreach ($lines as $key => $line) {
                    $line_items[$key] = $line;
                }
            }

            $service = $this->_request->get('service');

            OSC::helper('catalog/order')->process($order->getId(), $line_items , $service);

            $this->_ajaxResponse('ok');
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionAddLogOrder() {
        try{
            $order = OSC::model('catalog/order')->load($this->_request->get('order_id'));

            $action = $this->_request->get('action');

            $title = $this->_request->get('title');

            $order->addLog($action, $title);

            OSC::core('observer')->dispatchEvent('catalog/orderUpdate', $order->getId());

            $this->_ajaxResponse('ok');
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionAfterErrorOrderDesk(){
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try{
            $order = OSC::model('catalog/order')->load($this->_request->get('order_id'));

            $line_items_by_orderdesk = $this->_request->get('line_items');

            $line_items_by_orderdesk = OSC::decode($line_items_by_orderdesk,true);

            $a = [];

            $qty = 0;

            foreach ($line_items_by_orderdesk as $_line_item){
                $line_item = $order->getLineItemByItemId($_line_item['code']);

                if ($line_item->data['process_quantity'] < 1){
                    throw new Exception("process quantity need more than 0");
                }

                $line_item->incrementProcessQuantity(-intval($_line_item['quantity']));

                $a[$_line_item['code']] = ['quantity' => $_line_item['quantity']];

                $qty +=$_line_item['quantity'];
            }


            $process_collection = $order->getProcessCollection();

            foreach ($process_collection as $_model_process){
                if (OSC::encode($_model_process->data['line_items']) === OSC::encode($a)){
                    $_model_process->delete();
                }
            }

            $process_status = 'unprocess';

            foreach ($order->getLineItems() as $line_item) {
                if ($line_item->data['process_quantity'] > 0) {
                    $process_status = 'partially_process';
                    break;
                }
            }

            $order->setData('process_status', $process_status)->save();

            $order->addLog('REMOVE_PROCESS', 'error! Remove '.$qty.' item process');

            OSC::core('observer')->dispatchEvent('catalog/orderUpdate', $order->getId());

            $DB->commit();

            $this->_ajaxResponse('ok');

        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $this->_ajaxError($ex->getMessage());
        }
    }


}