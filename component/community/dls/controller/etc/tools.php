<?php

class Controller_Dls_Etc_Tools extends Abstract_Core_Controller {

    public function actionMergeAndMigrate() {
        die;
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $products = [];

        $DB->getAdapter('trendy')->select('*', 'catalog_product');

        while ($row = $DB->getAdapter('trendy')->fetchArray()) {
            $products['TSD_' . count($products)] = $row;
        }

        $DB->getAdapter('floytee')->select('*', 'catalog_product');

        while ($row = $DB->getAdapter('floytee')->fetchArray()) {
            $products['FT_' . count($products)] = $row;
        }

        $diff = [];

        foreach ($products as $idx => $product) {
            $hash = md5(strtolower(trim($product['title'])));

            if (!isset($diff[$hash])) {
                $diff[$hash] = [];
            }

            $diff[$hash][] = $idx;
        }

        foreach ($diff as $idx => $items) {
            if (count($items) < 2) {
                continue;
            }

            $timestamp = 0;
            $keep_idx = '';

            foreach ($items as $_idx) {
                if ($products[$_idx]['modified_timestamp'] > $timestamp) {
                    $timestamp = $products[$_idx]['modified_timestamp'];
                    $keep_idx = $_idx;
                }
            }

            foreach ($items as $_idx) {
                if ($_idx != $keep_idx) {
                    unset($products[$_idx]);
                }
            }
        }

        $_products = [
            'trendy' => [],
            'floytee' => []
        ];

        foreach ($products as $idx => $product) {
            $product['variants'] = [];
            $product['images'] = [];

            if (preg_match('/^TSD_/', $idx)) {
                $_products['trendy'][$product['product_id']] = $product;
            } else {
                $_products['floytee'][$product['product_id']] = $product;
            }
        }

        foreach ($_products as $adapter => $products) {
            $DB->getAdapter($adapter)->select('*', 'catalog_product_variant', "FIND_IN_SET(product_id, '" . implode(',', array_keys($products)) . "')");

            while ($row = $DB->getAdapter($adapter)->fetchArray()) {
                $_products[$adapter][$row['product_id']]['variants'][] = $row;
            }

            $DB->getAdapter($adapter)->select('*', 'catalog_product_image', "FIND_IN_SET(product_id, '" . implode(',', array_keys($products)) . "')");

            while ($row = $DB->getAdapter($adapter)->fetchArray()) {
                $_products[$adapter][$row['product_id']]['images'][] = $row;
            }
        }

        $product_id = 0;
        $image_id = 0;
        $variant_id = 0;

        foreach ($_products as $adapter => $products) {
            $image_map = [];

            foreach ($products as $product) {
                $product_id ++;

                $variants = $product['variants'];
                $images = $product['images'];

                unset($product['variants']);
                unset($product['images']);

                $product['product_id'] = $product_id;
                $product['views'] = 0;
                $product['solds'] = 0;

                $DB->insert('catalog_product', $product);

                $product_img_path = OSC_Storage::getStoragePath('product/' . $product_id);

                OSC::makeDir($product_img_path);

                foreach ($images as $image) {
                    $image_id ++;

                    $image_map[$image['image_id']] = $image_id;

                    $image['product_id'] = $product_id;
                    $image['image_id'] = $image_id;

                    $filename = preg_replace('/^.+\/([^\/]+)$/', '\\1', $image['filename']);

                    if (copy(OSC_SITE_PATH . '/../' . ($adapter == 'floytee' ? 'floytee.com' : 'trendystuffdeal.com') . '/storage/' . $image['filename'], $product_img_path . '/' . $filename) === false) {
                        echo "FAILED COPY IMAGE\n";
                        continue;
                    }

                    $image['filename'] = 'product/' . $product_id . '/' . $filename;

                    $DB->insert('catalog_product_image', $image);
                }

                foreach ($variants as $variant) {
                    $variant_id ++;

                    $variant['product_id'] = $product_id;
                    $variant['variant_id'] = $variant_id;

                    $variant['image_id'] = explode(',', $variant['image_id']);
                    $variant['image_id'] = array_map(function($id) {
                        return intval($id);
                    }, $variant['image_id']);
                    $variant['image_id'] = array_filter($variant['image_id'], function($id) {
                        return $id > 0;
                    });

                    $image_ids = [];

                    foreach ($variant['image_id'] as $_img_id) {
                        if (isset($image_map[$_img_id])) {
                            $image_ids[] = $image_map[$_img_id];
                        }
                    }

                    $variant['image_id'] = count($image_ids) > 0 ? implode(',', $image_ids) : '';

                    $DB->insert('catalog_product_variant', $variant);
                }
            }
        }
    }

    public function actionCookieStringToArray() {
        if (!isset($_REQUEST['content'])) {
            echo <<<EOF
<form method="post" action="{$this->getUrl()}">
<input type="text" name="fname_idx" style="width: 100%; height: 500px; margin: 0px;" placeholder="File name start number" /><br /><br />
<textarea name="content" style="width: 100%; height: 500px; resize: vertical; margin: 0px;"></textarea><br /><br />
<button type="submit" style="padding: 10px; min-width: 300px;">Process</button>
</form>
EOF;

            die;
        }

        $zip_file = OSC::makeUniqid() . '.zip';
        $zip_file_path = OSC_Storage::preDirForSaveFile($zip_file);

        $zip = new ZipArchive();
        $zip->open($zip_file_path, ZipArchive::CREATE);

        $rows = explode("\n", $_REQUEST['content']);

        $counter = max(1, intval($this->_request->get('fname_idx')));

        foreach ($rows as $row) {
            $row = trim($row);
            $row = preg_replace('/(^;+|;+$)/', '', $row);
            $row = preg_replace('/;{2,}/', ';', $row);

            if (!$row) {
                continue;
            }

            $row = explode(';', $row);
            $row = array_map(function($entry) {
                $entry = explode('=', $entry);

                return [
                    'Name' => $entry[0],
                    'Value' => $entry[1],
                    'Path' => '/',
                    'Secure' => in_array($entry[0], ['dbln', 'sb', 'datr', 'c_user', 'xs', 'spin', 'fr', 'wd'], true) ? true : false,
                    'HttpOnly' => in_array($entry[0], ['dbln', 'sb', 'datr', 'xs', 'spin', 'fr', 'ATN', 'AA003', 'IDE'], true) ? true : false,
                    'Domain' => '.facebook.com',
                    'Url' => 'https://www.facebook.com',
                    'Expires' => date('Y-m-d\TH:i:s.856\Z', time() + (60 * 60 * 24 * 365))
                ];
            }, $row);

            $row = json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $zip->addFromString(str_pad($counter, 2, 0, STR_PAD_LEFT) . '.txt', $row);

            $counter ++;
        }

        $zip->close();

        echo OSC_Storage::tmpGetFileUrl($zip_file);
    }

}
