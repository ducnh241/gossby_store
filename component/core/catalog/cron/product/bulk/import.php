<?php

class Cron_Catalog_Product_Bulk_Import extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database');

        $limit = 15;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'import'", '`added_timestamp` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter ++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                if ($model->data['queue_data']['product']['product_id'] > 0) {
                    $product = OSC::model('catalog/product')->load($model->data['queue_data']['product']['product_id']);

                    if ($product->checkMasterLock()) {
                        throw new Exception('You do not have the right to perform this function');
                    }
                }

                $this->_processImport($model->data['queue_data']);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage()])->save();
            }
        }

        if ($counter == $limit) {
            OSC::core('cron')->addQueue('catalog/product_bulk_import', null, ['requeue_limit' => -1, 'estimate_time' => 60*5]);
        }
    }

    protected function _processImport($import_data) {
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            /* @var $product Model_Catalog_Product */
            $product = OSC::model('catalog/product');

            if ($import_data['product']['product_id'] > 0) {
                $product->load($import_data['product']['product_id']);
            } else {
                unset($import_data['product']['product_id']);
                unset($import_data['product']['sku']);
            }

            $product->setData($import_data['product'])->save();

            $image_position = $product->getImages()->getItem($product->getImages()->length() - 1)->data['position'];
            $image_position = intval($image_position);

            foreach ($import_data['images'] as $idx => $url) {
                if (!is_file($url)) {
                    $image_hash = md5($url);

                    if (!file_exists(OSC::getTmpDir() . '/' . $image_hash)) {
                        $url_info = OSC::core('network')->curl($url, array('browser', 'timeout' => 60));

                        if (!$url_info['content']) {
                            throw new Exception('Cannot get image data from: ' . $url);
                        }

                        $extension = OSC_File::verifyImageByData($url_info['content']);

                        if (OSC::writeToFile(OSC::getTmpDir() . '/' . $image_hash, $url_info['content']) === false) {
                            throw new Exception('Cannot save image file to TMP directory');
                        }
                    }

                    $url = OSC::getTmpDir() . '/' . $image_hash;
                }

                if (!$extension) {
                    $extension = OSC_File::verifyImage($url);
                }

                $file_name = 'product/' . $product->getId() . '/' . OSC::makeUniqid() . '.' . $extension;
                $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);

                OSC::core('aws_s3')->upload($url, $file_name_s3);

                $image = OSC::model('catalog/product_image');

                $image->setData([
                    'product_id' => $product->getId(),
                    'position' => $image_position,
                    'alt' => '',
                    'filename' => $file_name
                ])->save();

                $product->getImages()->addItem($image);

                $image_position ++;

                $import_data['image_map'][array_search($idx, $import_data['image_map'])] = $image->getId();
            }

            $variant_ids = [];

            foreach ($import_data['variants'] as $variant_data) {
                $variant = OSC::model('catalog/product_variant');

                if ($variant_data['variant_id'] > 0) {
                    $variant->load($variant_data['variant_id']);
                } else {
                    unset($variant_data['variant_id']);
                    unset($variant_data['sku']);
                }

                if (is_array($variant_data['image']) && count($variant_data['image']) > 0) {
                    $variant_data['image_id'] = [];

                    foreach ($variant_data['image'] as $image_url) {
                        if (isset($import_data['image_map'][$image_url])) {
                            $variant_data['image_id'][] = $import_data['image_map'][$image_url];
                        }
                    }
                } else {
                    $variant_data['image_id'] = [];
                }

                unset($variant_data['image']);

                if ($variant->getId() > 0 && $variant->data['product_id'] != $product->getId()) {
                    throw new Exception('Variant #' . $variant->getId() . ' is not belong to product #' . $product->getId());
                }

                $variant_data['product_id'] = $product->getId();

                $variant->setProduct($product);
                $variant->setData($variant_data)->save();

                $variant_ids[] = $variant->getId();
            }

            foreach ($product->getVariants(true) as $variant) {
                if (!in_array($variant->getId(), $variant_ids)) {
                    $variant->delete();
                }
            }

            $DB->commit();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }
    }

}
