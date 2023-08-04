<?php

class Cron_Catalog_Order_SendMailReview extends OSC_Cron_Abstract {

	public function process($params, $queue_added_timestamp)
	{
		$DB = OSC::core('database')->getAdapter('db_master');

		$limit = 500;
		$counter = 0;

		$shop_id = OSC::getShop()->getId();

		$error_flag = false;

		while ($counter < $limit) {
			$model = OSC::model('catalog/order_bulkQueue');

			$DB->select('*', 'catalog_order_bulk_queue', "shop_id = ".$shop_id." AND `queue_flag` = 1 AND `action` LIKE 'reviewAfterFulfilled_%'", '`added_timestamp` ASC', 1, 'fetch_queue');

			$row = $DB->fetchArray('fetch_queue');

			$DB->free('fetch_queue');

			if (!$row) {
				break;
			}

			$counter++;

			$model->bind($row);

			$model->setData('queue_flag', 0)->save();

			try {
				$order = OSC::model('catalog/order')->load($model->data['order_master_record_id']);

				OSC::helper('catalog/product_review')->requestReviewAfterFulfilled($order, $model->data['queue_data']['line_items']);

				$model->delete();
			} catch (Exception $ex) {
				$model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();

				$error_flag = true;
			}

		}

		if ($counter == $limit || $error_flag) {
			return false;
		}
	}

}
