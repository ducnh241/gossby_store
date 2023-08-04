<?php

class Helper_PersonalizedDesign_Convert {
    protected $_redis = null;
    protected $_storage_root_path = null;
    protected $_list_url_slice = null;
    protected $_key_list_slice_data = null; //Key redis contain slice_data config: url => slice_data
    protected $_key_convert_id = null; //Key redis contain last converted_id

    public function __construct() {
        if ($this->_storage_root_path === null) {
            $this->_storage_root_path = OSC_Storage::getStorageRootPath();
        }

        if ($this->_list_url_slice === null) {
            $this->_list_url_slice = [];
        }

        if ($this->_redis === null) {
            try {
                $cache_config = OSC::systemRegistry('cache_config');
                $redis_config = isset($cache_config['instance']['redis']) ? $cache_config['instance']['redis'] : [];

                if (!is_array($redis_config) || empty($redis_config)) {
                    throw new Exception('Cannot get redis config');
                }

                $this->_redis = new Redis();
                $this->_redis->connect($redis_config['host'], $redis_config['port']);
            } catch (Exception $exception) {
                throw $exception;
            }
        }

        if ($this->_key_list_slice_data === null) {
            $this->_key_list_slice_data = OSC::$domain . '.' . 'convert_personalized';
        }

        if ($this->_key_convert_id === null) {
            $this->_key_convert_id = OSC::$domain . '.' . 'convert_id';
        }
    }

    //Step 1: get all list url in personalized_design_config.design_data, save to personalized_design_config
    public function getListPersonalizeUrl() {
        $designs = OSC::model('personalizedDesign/design')->getCollection()
            ->setLimit(0)
            ->sort('design_id', 'ASC')
            ->load();

        if (!empty($designs)) {
            foreach ($designs as $design) {
                try {
                    if (preg_match_all('#http[\w\d\/\:\-.]+personalizedDesign/design/images[\w\d\/\:\-.]+.(jpg|jpeg|png)#is', serialize($design->data['design_data']), $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            if (!in_array($match[0], $this->_list_url_slice)) {
                                array_push($this->_list_url_slice, $match[0]);
                            }
                        }
                    }

                } catch (Exception $exception) {
                    var_dump($exception);
                }
            }

            if (!empty($this->_list_url_slice)) {
                foreach ($this->_list_url_slice as $item) {
                    try {
                        $config_model = OSC::model('personalizedDesign/design_config');

                        $config_model->setData([
                            'url' => $item,
                            'status' => 1
                        ])->save();
                    } catch (Exception $exception) {
                        var_dump($exception);
                    }
                }
            }
        }
    }

    //Step 2: parse list image url in personalized_design_config, get config slice_data
    public function parseConfigListPersonalizedUrl() {
        $designs_config = OSC::model('personalizedDesign/design_config')->getCollection()
            ->addCondition('status', 1)
            ->addCondition('slice_data', null)
            ->setLimit(15000)
            ->sort('config_id', 'ASC')
            ->load();

        $countSuccess = 0; $countError = 0;
        if (!empty($designs_config)) {
            foreach ($designs_config as $item) {
                $image_path = 'personalizedDesign/design/images';
                $watermark_path = 'personalizedDesign/design/watermark';
                if (empty($item->data['data_slice']) && preg_match("#{$image_path}/([\d\.]+)/([a-z0-9\.]+)\.(jpg|jpeg|png)#is", $item->data['url'], $match)) {
                    try {
                        $slice_name = 'slice1';
                        $slice_rotation = 0;
                        if (!OSC::makeDir($this->_storage_root_path . '/' . $watermark_path . '/' . $match[1])) {
                            $item->setData([
                                'status' => 0,
                                'meta_data' => [
                                    'message' => 'Cannot make storage watermark directory'
                                ]
                            ])->save();

                            $countError++;
                            continue;
                        }

                        $preview_file_path = $this->_storage_root_path . '/' . preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $match[0]);
                        $watermark_file_path = $this->_storage_root_path . '/' . preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.watermark.\\2', str_replace($image_path, $watermark_path, $match[0]));
                        $slice_file_path = $this->_storage_root_path . '/' . preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', "\\1.{$slice_name}.\\2", str_replace($image_path, $watermark_path, $match[0]));

                        /*if (file_exists($watermark_file_path) || !file_exists($preview_file_path)) {
                            $item->setData([
                                'status' => 0,
                                'meta_data' => [
                                    'message' => file_exists($watermark_file_path) ? 'Watermark file exists' : 'Preview file not exists'
                                ]
                            ])->save();

                            continue;
                        }*/

                        if (!file_exists($preview_file_path)) {
                            $item->setData([
                                'status' => 0,
                                'meta_data' => [
                                    'message' => 'Preview file not exists'
                                ]
                            ])->save();

                            continue;
                        }

                        $watermark_file = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.watermark.\\2', str_replace($image_path, $watermark_path, $item->data['url']));
                        $slice_file = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', "\\1.{$slice_name}.\\2", str_replace($image_path, $watermark_path, $item->data['url']));

                        $image = new \Imagick($preview_file_path);
                        $width_preview = $image->getImageWidth();
                        $height_preview = $image->getImageHeight();

                        if (empty($width_preview) || empty($height_preview)) {
                            $item->setData([
                                'status' => 0,
                                'meta_data' => [
                                    'message' => 'Error parsing preview image'
                                ]
                            ])->save();

                            $countError++;
                            continue;
                        }

                        //Random a number from quarter of image size to half of image size
                        $slice_width = rand(intval($width_preview / 4), intval($width_preview / 2));
                        $slice_height = rand(intval($height_preview / 4), intval($height_preview / 2));

                        $slice_point = [
                            'x' => rand(0, intval($width_preview / 2)),
                            'y' => rand(0, intval($height_preview / 2)),
                        ];

                        //Get watermark image
                        $image_watermark = new Imagick($preview_file_path);
                        $imagick = new Imagick();
                        $imagick->newImage($slice_width - 30, $slice_height - 30, new ImagickPixel('lightgray'));

                        $draw = new ImagickDraw();

                        $draw->setFillColor(new ImagickPixel('lightgray'));
                        $draw->rectangle(0, 0, $slice_width - 30, $slice_height - 30);

                        $imagick->drawImage($draw);
                        //$imagick->scaleImage($slice_width, $slice_height);
                        $imagick->setImageFormat('png');

                        // Overlay the watermark on the original image
                        // Slice image will be less than watermark zone 5x5 px, to make sure fully cover without border
                        $image_watermark->compositeImage($imagick, imagick::COMPOSITE_DSTOUT, $slice_point['x'] + 15, $slice_point['y'] + 15);
                        $image_watermark->writeImage($watermark_file_path);

                        //Slice watermark image
                        $image_slice = new Imagick($preview_file_path);
                        $image_slice->cropImage($slice_width, $slice_height, $slice_point['x'], $slice_point['y']);
                        $image_slice->writeImage($slice_file_path);

                        $item->setData([
                            'slice_data' => [
                                'original_size' => [
                                    'width' => $width_preview,
                                    'height' => $height_preview,
                                ],
                                'watermark_file' => $watermark_file,
                                'items' => [
                                    [
                                        'slice_file' => $slice_file,
                                        'name' => $slice_name,
                                        'size' => [
                                            'width' => $slice_width,
                                            'height' => $slice_height,
                                        ],
                                        'position' => [
                                            'x' => $slice_point['x'],
                                            'y' => $slice_point['y']
                                        ],
                                        'rotation' => $slice_rotation
                                    ]
                                ]
                            ]
                        ])->save();

                        $countSuccess++;
                    } catch (Exception $exception) {
                        $item->setData([
                            'status' => 0,
                            'meta_data' => [
                                'message' => $exception->getMessage()
                            ]
                        ])->save();

                        $countError++;
                        continue;
                    }
                }
            }
        }

        var_dump("Success: {$countSuccess} - Error: {$countError}");
    }

    //Step 3: replace personalized_design.design_data, append config slice
    public function convertPersonalizeData() {
        /*$this->_redis->del($this->_key_list_slice_data);
        $this->_redis->del($this->_key_convert_id);*/

        $convert_id = $this->_redis->get($this->_key_convert_id);
        $convert_id = $convert_id === false ? 0 : $convert_id;

        $designs = OSC::model('personalizedDesign/design')->getCollection()
            ->addCondition('design_id', [915], OSC_Database::OPERATOR_NOT_IN)
            ->addCondition('design_id', $convert_id, OSC_Database::OPERATOR_GREATER_THAN)
            ->setLimit(100)
            ->sort('design_id', 'ASC')
            ->load();

        if (!empty($designs)) {
            foreach ($designs as $design) {
                //Save this design_id to redis, if cron run into error and must rerun, will skip this design_id
                $this->_redis->setex($this->_key_convert_id, 3600, $design->getId());

                $design_tmp = OSC::model('personalizedDesign/design_tmp');

                try {
                    $design_data = $this->_convertDesign($design);

                    $design_tmp->setData([
                        'design_id' => $design->getId(),
                        'status' => 1,
                        'design_data' => $design_data
                    ])->save();
                } catch (Exception $exception) {
                    $design_tmp->setData([
                        'design_id' => $design->getId(),
                        'status' => 2,
                        'meta_data' => [
                            'message' => $exception->getMessage()
                        ]
                    ])->save();
                }
            }
        }
    }

    //Step 4: copy personalized_design_tmp.design_data to personalized_design.design_data
    public function copyDesignDataToPersonalizeDesign() {
        $design_temps = OSC::model('personalizedDesign/design_tmp')->getCollection()
            ->addCondition('status', 1)
            ->setLimit(0)
            ->load();

        if (!empty($design_temps)) {
            foreach ($design_temps as $design_temp) {
                try {
                    if (!empty($design_temp->data['design_id']) && !empty($design_temp->data['design_data'])) {
                        $design = OSC::model('personalizedDesign/design')->load($design_temp->data['design_id']);

                        if (isset($design->data['design_id'])) {
                            $design->setData([
                                'design_data' => $design_temp->data['design_data']
                            ])->save();
                        }
                    }
                } catch (Exception $exception) {

                }
            }
        }
    }

    protected function _convertDesign(Model_PersonalizedDesign_Design $design) {
        try {
            $design_data = $design->data['design_data'] ?? [];

            if (is_array($design_data) && !empty($design_data)) {
                $this->_convertChildData($design_data);
            }

            return $design_data;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    protected function _convertChildData(&$data) {
        if (is_array($data) && !empty($data)) {
            foreach ($data as &$item) {
                if (is_array($item) && !empty($item)) {
                    $this->_convertChildData($item);
                }

                if (isset($item['type_data']['url']) && !empty($item['type_data']['url'])) {
                    $list_config_collection = OSC::model('personalizedDesign/design_config')->getCollection()
                        ->addField('url', 'slice_data')
                        ->addCondition('status', 1)
                        ->addCondition('url', $item['type_data']['url'])
                        ->setLimit(1)
                        ->load();

                    if ($list_config_collection->length() > 0) {
                        foreach ($list_config_collection as $config) {
                            if (isset($config->data['slice_data']) && !empty($config->data['slice_data'])) {
                                $item['type_data']['slice'] = $config->data['slice_data'];
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    public function convertPaletteColor(){
        $error = [];

        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        try {
            $design_id_last = OSC::core('cache')->get('convert_palette_color_design_id');

            if (!$design_id_last) {
                $design_id_last = 0;
            }

            $DB->select('design_id, palette_color', 'personalized_design', 'design_id > ' .  $design_id_last, 'design_id ASC', 100, 'fetch_designs');

            $rows = $DB->fetchArrayAll('fetch_designs');

            $DB->free('fetch_designs');

            $designs_length = count($rows);

            if ($designs_length < 1) {
                return;
            }

            $count = 0;

            $list_design_rerender_thumb = [];

            foreach ($rows as $row) {
                $count++;

                if ($count == $designs_length) {
                    OSC::core('cache')->set('convert_palette_color_design_id', $row['design_id'], 60 * 60 * 48);
                }

                if (trim($row['palette_color']) != '') {
                    continue;
                }

                $thumb_name = 'personalizedDesign/designImage/' . $row['design_id'] . '.png';

                //check design co anh thumb thi lay ma mau cua anh thumb
                if (OSC::core('aws_s3')->doesStorageObjectExist($thumb_name)) {
                    $thumb_path = OSC_Storage::getStoragePath($thumb_name);

                    try {
                        $palette_color = OSC::helper('personalizedDesign/common')->getImgPaletteColor($thumb_path);

                        $DB->update('personalized_design', ['palette_color' => OSC::encode($palette_color)], 'design_id = ' . $row['design_id'], 1, 'update_design');
                    } catch (Exception $ex) {
                        $error[] = $row['design_id'] . '_' . $ex->getMessage();
                    }

                } else {
                    //khong co render lai anh thumb cho design
                    $list_design_rerender_thumb[] = $row['design_id'];
                }
            }

            if (count($list_design_rerender_thumb) > 0) {
                $designs = OSC::model('personalizedDesign/design')->getCollection()
                    ->load($list_design_rerender_thumb);

                foreach ($designs as $design) {
                    try {
                        OSC::model('personalizedDesign/sync')->setData([
                            'ukey' => OSC::makeUniqid(),
                            'sync_type' => 'renderDesignImage',
                            'sync_data' => [
                                'design_id' => $design->getId(),
                                'svg_content' => OSC::helper('personalizedDesign/common')->renderSvg($design)
                            ]
                        ])->save();
                    } catch (Exception $ex) {
                        $error[] = $design->getId() . '_' . $ex->getMessage();
                    }
                }
            }
        } catch (Exception $ex) {
            $error[] = $ex->getMessage();
        }

        if (count($error) > 0) {
            OSC::logFile('convert_palette_color_design', OSC::encode($error));
        }

        return false;
    }
}