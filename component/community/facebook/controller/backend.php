<?php

class Controller_Facebook_Backend extends Abstract_Catalog_Controller_Backend
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('catalog/facebook_pixel');

        $this->getTemplate()->setCurrentMenuItemKey('catalog/facebook_pixel');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $collection = OSC::model('facebook/pixel')->getCollection();

        $collection->sort('title')
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->getTemplate()->setPageTitle('Manage facebook pixel')->addBreadcrumb('Facebook pixel');

        $this->output($this->getTemplate()->build('facebook/list', ['collection' => $collection]));
    }

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));

        $this->getTemplate()->setPageTitle('Facebook pixel management')
            ->addBreadcrumb('Facebook pixel management');

        $this->checkPermission('catalog/facebook_pixel/' . ($id > 0 ? 'edit' : 'add'));

        $model = OSC::model('facebook/pixel');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Facebook pixel is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        }

        if (key_exists('title', $this->_request->getAll('post'))) {
            $data = [];

            $data['title'] = $this->_request->get('title');
            $data['pixel_id'] = trim($this->_request->get('pixel_id'));

            try {

                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Your update has been saved successfully.';
                } else {
                    $message = 'Facebook pixel has been saved successfully.';
                }

                $this->addMessage($message);

                static::redirect($this->getUrl('list'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('facebook/post_form', ['model' => $model]);

        $this->output($output_html);
    }

    public function actionDelete()
    {
        $this->checkPermission('catalog/facebook_pixel/delete');

        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                $model = OSC::model('facebook/pixel')->load($id);
                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Successfully deleted the pixel id.');
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionMapProductType()
    {
        $this->getTemplate()->setPageTitle('Facebook pixel management')
            ->addBreadcrumb('Facebook pixel', $this->getUrl('index'))
            ->addBreadcrumb('Map pixel to product type');

        $this->checkPermission('catalog/facebook_pixel/map');

        $product_types = OSC::model('catalog/productType')->getAllProductTypeInUsing();
        $facebook_pixels = OSC::model('facebook/pixel')->getAllFacebookPixel();
        $facebook_pixel_group_by_product_type = OSC::helper('facebook/common')->getFacebookPixelGroupByProductType();

        if ($this->_request->get('is_submit')) {

            $product_type_rel = $this->_request->get('product_type_rel', []);

            try {

                if (!is_array($product_type_rel)) {
                    throw new Exception('Data incorrect!');
                }

                if (md5(OSC::encode($product_type_rel)) != md5(OSC::encode($facebook_pixel_group_by_product_type))) {
                    /* @var $DB OSC_Database_Adapter */
                    $DB = OSC::core('database')->getWriteAdapter();
                    $DB->begin();

                    try {
                        $DB->delete('facebook_pixel_product_type_rel');
                        foreach ($product_type_rel as $product_type_id => $pixel_ids) {
                            foreach ($pixel_ids as $pixel_id) {
                                if (Observer_Facebook_Common::validateFacebookPixel($pixel_id)) {
                                    $DB->insert('facebook_pixel_product_type_rel', [
                                        'product_type_id' => intval($product_type_id),
                                        'pixel_id' => $pixel_id,
                                        'added_timestamp' => time(),
                                        'modified_timestamp' => time(),
                                    ], 'insert_facebook_pixel_product_type_rel');
                                }
                            }
                        }
                        $DB->commit();
                    } catch (Exception $ex) {
                        $DB->rollback();
                        if ($ex->getCode() != 404) {
                            $this->addErrorMessage($ex->getMessage());
                            static::redirect($this->getUrl('mapProductType'));
                        }
                    }
                }

                $this->addMessage('Your update has been saved successfully.');

                static::redirect($this->getUrl('mapProductType'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('facebook/map_product_type',
            [
                'facebook_pixels' => $facebook_pixels,
                'product_types' => $product_types,
                'facebook_pixel_group_by_product_type' => $facebook_pixel_group_by_product_type
            ]
        );

        $this->output($output_html);
    }
}
