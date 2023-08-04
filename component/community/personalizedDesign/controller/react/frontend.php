<?php

class Controller_PersonalizedDesign_React_Frontend extends Abstract_Frontend_ReactApiController {
    public function actionGetFrmConfig() {
        $id = intval($this->_request->get('id')) ?? 0;
        $product_id = intval($this->_request->get('product_id')) ?? 0;
        $is_live_priview = filter_var($this->_request->get('is_live_preview'), FILTER_VALIDATE_BOOLEAN);

        if ($id < 1) {
            $this->sendError('Design ID is incorrect', $this::CODE_NOT_FOUND);
        }

        if ($id > 0) {
            try {
                $model = OSC::helper('personalizedDesign/common')->getPersonalizedDesign($id)->first();

                if (!($model instanceof Model_PersonalizedDesign_Design) || $model->getId() < 1) {
                    throw new Exception('Not found design with #' . $id);
                }

                $options = [];
                if ($is_live_priview) {
                    $mockup_default_option = [];
                    if ($product_id) {
                        $product = OSC::model('catalog/product')->getCollection()
                            ->addField('product_id', 'meta_data')
                            ->addCondition('product_id', $product_id)
                            ->load()
                            ->first();
                        $meta_data = $product->data['meta_data'];
    
                        if (isset($meta_data['campaign_config']['print_template_config'][0]['segments'])) {
                            $segments = $meta_data['campaign_config']['print_template_config'][0]['segments'];

                            foreach ($segments as $product_type => $segment) {
                                if (                                
                                    $segment['source']['design_id'] == $id && 
                                    isset($segment['source']['option_default_values']['options'])
                                ) {
                                    $mockup_default_option = $segment['source']['option_default_values']['options'];
                                }

                                if (count($mockup_default_option)) {
                                    break;
                                }
                            }
                            
                        }
                    }
                    if (count($mockup_default_option)) {
                        $options['is_live_priview'] = [
                            'mockup_default_option' => $mockup_default_option
                        ]; 
                    }
                }

                $palette_color = $model->getPaletteColor();
                $form_data = $model->extractPersonalizedFormData($options);

                $this->sendSuccess([
                    'id' => $id, 
                    'document_type' => $model->data['design_data']['document'], 
                    'components' => $form_data['components'],
                    'image_data' => $form_data['image_data'],
                    'palette_color' => $palette_color,
                    'background_color' => $model->data['background_color'],
                    'mockup_default_option' => $mockup_default_option
                ]);
            } catch (Exception $ex) {
                $this->sendError($ex->getCode() == $this::CODE_NOT_FOUND ? 'Design is not exist' : $ex->getMessage(), $ex->getCode());
            }
        }
    }

    public function actionGetMultiFrmConfig() {
        /* @var $model Model_PersonalizedDesign_Design */
        /* @var $collection Model_PersonalizedDesign_Design_Collection */

        $ids = $this->_request->get('ids');
        $ids = array_map(function($id) {
            return intval($id);
        }, $ids);
        $ids = array_filter($ids, function($id) {
            return $id > 0;
        });

        if (count($ids) < 1) {
            $this->sendError('Design ID is incorrect', $this::CODE_NOT_FOUND);
        }

        $ids = array_unique($ids);

        try {
            $collection = OSC::model('personalizedDesign/design')->getCollection()->load($ids);

            $data = [];

            foreach ($collection as $model) {
                $form_data = $model->extractPersonalizedFormData();
                
                $data[$model->getId()] = [
                    'id' => $model->getId(), 
                    'document_type' => $model->data['design_data']['document'], 
                    'components' => $form_data['components'],
                    'image_data' => $form_data['image_data'],
                ];
            }

            $this->sendSuccess($data);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionUploadImage() {
        try {
            $uploader = new OSC_Uploader();

            $original_file_name = $uploader->getName();

            $file_size = $uploader->getSize();

            $pre_file_path = OSC::getTmpDir() . '/' . OSC::makeUniqid();

            $tmp_file_path = $pre_file_path . '.' . $uploader->getExtension();

            $uploader->save($tmp_file_path, true);

            $extension = OSC_File::verifyImage($tmp_file_path);

            if ($extension == 'webp') {
                $extension = 'png';

                exec('convert ' . $tmp_file_path . ' ' . $pre_file_path . '.png');

                $tmp_file_path = $pre_file_path . '.png';

                OSC_File::verifyImage($tmp_file_path);
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }

        try {
            $file_name = 'personalizedDesign/customer_uploaded/' . date('Ymd') . '/' . OSC::makeUniqid() . '.' . $extension;
            $thumb_file_name = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $file_name);
            $file_name_s3 = OSC::core('aws_s3')->getStoragePath($file_name);
            $thumb_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumb_file_name);

            $absolute_tmp_file_path = OSC_Storage::preDirForSaveFile($file_name);
            $absolute_tmp_thumb_file_path = OSC_Storage::preDirForSaveFile($thumb_file_name);
            OSC::core('aws_s3')->tmpSaveFile($tmp_file_path, $file_name);

            $image = new Imagick($absolute_tmp_file_path);

            $width = $image->getImageWidth();
            $height = $image->getImageHeight();

            if ($image->getImageHeight() > 400) {
                $image->scaleImage(0, 400);
            }

            $image->writeImage($absolute_tmp_thumb_file_path);

            $image->clear();
            $image->destroy();

            /* Upload thumb image to s3 */
            OSC::core('aws_s3')->upload($absolute_tmp_file_path, $file_name_s3);
            $file_url = OSC::core('aws_s3')->upload($absolute_tmp_thumb_file_path, $thumb_file_name_s3);

            if (!$file_url) {
                throw new Exception('Unable to write file to thumb storage');
            }

            /* Move image uploaded to DC */
            try {
                OSC::model('personalizedDesign/sync')->setData([
                    'ukey' => 'image/' . md5($file_name),
                    'sync_type' => 'image',
                    'sync_data' => $file_name
                ])->save();
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                    throw new Exception($ex->getMessage());
                }
            }

            $data = [
                'file' => $file_name,
                'name' => $original_file_name,
                'url' => $file_url,
                'size' => $file_size,
                'width' => $width,
                'height' => $height
            ];

            $data['token'] = OSC::helper('personalizedDesign/common')->imageUploaderGetDataToken($data);

            $this->sendSuccess($data);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}