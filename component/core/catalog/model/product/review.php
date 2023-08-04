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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Catalog_Product_Review extends Abstract_Core_Model
{
    protected $_table_name = 'catalog_product_review';
    protected $_pk_field = 'record_id';
    protected $_allow_write_log = true;

    const STATE_CODES = [
        0 => 'Hidden',
        1 => 'Pending',
        2 => 'Approved'
    ];
    const STATE_HIDDEN = 0;
    const STATE_PENDING = 1;
    const STATE_APPROVED = 2;

    const ROLE_CODES = [
        0 => 'Normal',
        1 => 'Admin',
    ];
    const ROLE_NORMAL = 0;
    const ROLE_ADMIN = 1;

    /**
     *
     * @var Model_Catalog_Product_Review_Image_Collection
     */
    protected $_image_collection = null;

    /**
     *
     * @var Model_Catalog_Product
     */
    protected $_product_model = null;
    protected $_list_child_review = null;
    protected $_list_slide_review_image = null;
    private $_aggregate_review;

    public function getStateKey()
    {
        switch ($this->data['state']) {
            case static::STATE_HIDDEN:
                return 'hidden';
            case static::STATE_PENDING:
                return 'pending';
            case static::STATE_APPROVED:
                return 'approved';
        }
    }

    public function isPending()
    {
        return $this->data['state'] == static::STATE_PENDING;
    }

    public function isHidden()
    {
        return $this->data['state'] == static::STATE_HIDDEN;
    }

    public function isApproved()
    {
        return $this->data['state'] == static::STATE_APPROVED;
    }

    public function getDetailUrl()
    {
        return OSC_FRONTEND_BASE_URL . '/catalog/review/' . $this->getId();
    }

    public function isRoleNormal()
    {
        return $this->data['role'] == static::ROLE_NORMAL;
    }

    public function isRoleAdmin()
    {
        return $this->data['role'] == static::ROLE_ADMIN;
    }

    public function getFormattedContent()
    {
        return nl2br($this->data['review']);
    }

    /**
     *
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_product_model = $product;
        return $this;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Product
     * @throws Exception
     */
    public function getProduct($reload = false)
    {
        if ($reload || $this->_product_model === null || ($this->_product_model->getId() > 0 && $this->_product_model->getId() != $this->data['product_id'])) {
            $this->_product_model = null;

            try {
                $product = static::getPreLoadedModel('catalog/product', $this->data['product_id']);
                $this->_product_model = $product && !$product->data['discarded'] ? $product : null;
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_product_model;
    }

    public function getProductDetailUrl()
    {
        return $this->getProduct() ? $this->getProduct()->getDetailUrl() : '#!';
    }

    public function getProductTitle()
    {
        return $this->getProduct() ? $this->getProduct()->getProductTitle() : 'Deleted product';
    }

    public function getProductAvatar()
    {
        return $this->getProduct() ? OSC::wrapCDN($this->getProduct()->getFeaturedImageUrl()) : '';
    }

    public function getAggregateReview($product_id = 0, $options = [])
    {
        $options['show_all'] = $options['show_all'] ?? 0;
        if ($this->_aggregate_review === null) {
            /* @var $DB OSC_Database */
            $DB = OSC::core('database');
            $query = '';
            $where = 'parent_id = 0 AND state = ' . Model_Catalog_Product_Review::STATE_APPROVED;
            $product_types = OSC::helper('catalog/product_review')->getListProductTypeByProductId($product_id);
            $country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();

            if (!empty($product_types)) {
                $product_types = "'" . implode("', '", $product_types) . "'";
                $where .= " AND product_type in ({$product_types})";
            }

            if (isset($options['country_code']) && $options['country_code']) {
                $where .= " AND country_code = '{$options['country_code']}'";
            }

            $query .= "SUM(if(country_code = '{$country_code}', 1, 0)) as total_locale,";
            $query = $query ? ', ' . trim($query, ', ') : '';
            $query_aggregate = <<<EOF
SELECT
	SUM(IF(has_photo = 1, 1, 0)) as total_has_photo,
	SUM(IF(has_comment = 1, 1, 0)) as total_has_comment,
	SUM(if(vote_value = 5, 1, 0)) as total_5_star,
	SUM(if(vote_value = 4, 1, 0)) as total_4_star, 
	SUM(if(vote_value = 3, 1, 0)) as total_3_star, 
	SUM(if(vote_value = 2, 1, 0)) as total_2_star, 
	SUM(if(vote_value = 1, 1, 0)) as total_1_star,
	AVG(vote_value) as avg_vote_value,
	count(*) as total_review
	{$query}
FROM `{$this->getTableName(true)}` 
WHERE {$where}
EOF;

            $DB->query($query_aggregate, null, 'aggregate_review');
            $data = $DB->fetchArray('aggregate_review');
            $DB->free('aggregate_review');

            $skip_recursive = $options['skip_recursive'] ?? null;
            if ($data['total_review'] < 4 && !$skip_recursive) {
                // If total review of product < 4 => get all review
                $options['skip_recursive'] = true;
                $options['show_all'] = 1;
                return $this->getAggregateReview(0, $options);
            }

            $data['avg_vote_value'] = number_format($data['avg_vote_value'] ?? 0, 1);
            $data['show_all'] = $options['show_all'];

            $this->_aggregate_review = $data;
        }

        return $this->_aggregate_review;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Customer
     * @throws Exception
     */
    public function getCustomer($reload = false)
    {
        if ($this->data['customer_id'] > 0 && ($reload || $this->_customer_model === null || ($this->_customer_model['customer_id'] > 0 && $this->_customer_model['customer_id'] != $this->data['customer_id']))) {
            $this->_customer_model = null;

            try {
                $this->_customer_model = OSC::helper('account/customer')->get(['customer_id' => $this->data['customer_id']]);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_customer_model;
    }

    /**
     *
     * @param Model_Catalog_Order $order
     * @return $this
     */
    public function setOrder(Model_Catalog_Order $order)
    {
        $this->_order_model = $order;
        return $this;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Order
     * @throws Exception
     */
    public function getOrder($reload = false)
    {
        if ($this->data['order_id'] > 0 && ($reload || $this->_order_model === null || ($this->_order_model->getId() > 0 && $this->_order_model->getId() != $this->data['order_id']))) {
            $this->_order_model = null;

            try {
                $this->_order_model = static::getPreLoadedModel('catalog/order', $this->data['order_id']);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_order_model;
    }

    public function getChildReview($reload = false)
    {
        if ($reload || $this->_list_child_review === null) {
            //$review_collection = OSC::model('catalog/product_review')->getNullCollection();
            $this->_list_child_review = OSC::model('catalog/product_review')->getCollection();

            $this->_list_child_review->addCondition('state', Model_Catalog_Product_Review::STATE_APPROVED)
                ->addCondition('parent_id', $this->getId())
                ->sort('added_timestamp', OSC_Database::ORDER_ASC)
                ->load();
        }

        return $this->_list_child_review;
    }

    public function getUrl()
    {
        return OSC::getUrl('catalog/frontend/review', ['code' => $this->data['ukey']]);
    }

    public function getStoragePath($filename)
    {
        return OSC::core('aws_s3')->getStorageUrl($filename);
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Product_Image_Collection
     */
    public function getImages($reload = false)
    {
        if ($this->_image_collection === null || $reload) {
            $this->_image_collection = OSC::model('catalog/product_review_image')->getCollection();

            if ($this->getId() > 0) {
                $this->_image_collection->loadByReviewId($this->getId());

                /* @var $image Model_Catalog_Product_Image */
                foreach ($this->_image_collection as $image) {
                    $image->setReview($this);
                }
            }
        }

        return $this->_image_collection;
    }

    public function getListImage($reload = false)
    {
        $images = $this->getImages($reload)->load();
        return !empty($images) ? $images->toArray() : [];
    }

    public function getImageUrl($reload = false)
    {
        $images = $this->getListImage($reload);

        return !empty($images) ? $images[0]['url'] : '';
    }

    public function getRootReview()
    {
        if ($this->data['parent_id'] == 0) {
            return $this;
        } else {
            return OSC::model('catalog/product_review')->getNullModel()->load($this->data['parent_id']);
        }
    }

    //Query all image from model `catalog/product_review_image` which belong to review has parent_id = 0
    public function getSlideImage($options = [], $pageIndex = 1, $pageSize = 10, $reload = false)
    {
        if ($reload || $this->_list_slide_review_image === null) {
            try {
                $DB = $this->getWriteAdapter();

                $condition = $DB->getCondition(true)
                    ->addCondition('tbl_review.parent_id', 0)
                    ->addCondition('tbl_review.state', Model_Catalog_Product_Review::STATE_APPROVED, OSC_Database::OPERATOR_EQUAL);

                if (isset($options['filter_option']) && !empty($options['filter_option'])) {
                    switch ($options['filter_option']) {
                        case 'star':
                            if (isset($options['filter_value']) && !empty($options['filter_value'])) {
                                $condition->addCondition('tbl_review.vote_value', $options['filter_value'], OSC_Database::OPERATOR_EQUAL);
                            }
                            break;
                        case 'has-comment':
                            $condition->addCondition('tbl_review.has_comment', 1, OSC_Database::OPERATOR_EQUAL);
                            break;
                        case 'has-photo':
                            $condition->addCondition('tbl_review.has_photo', 1, OSC_Database::OPERATOR_EQUAL);
                            break;
                        default:
                            break;
                    }
                }

                $parts = [
                    'select' => 'tbl_review_image.image_id, tbl_review_image.review_id, tbl_review_image.extension, tbl_review_image.width, tbl_review_image.height, tbl_review_image.filename',
                    'from' => [
                        'tbl_review_image' => 'catalog/product_review_image'
                    ],
                    'join' => [
                        [
                            'table' => [
                                'tbl_review' => 'catalog/product_review'
                            ],
                            'type' => OSC_Database::JOIN_TYPE_INNER,
                            'condition' => 'tbl_review_image.review_id = tbl_review.record_id'
                        ]
                    ],
                    'condition' => $condition,
                    'order' => 'tbl_review_image.image_id DESC',
                    'limit' => [
                        ($pageIndex - 1) * $pageSize,
                        $pageSize
                    ]
                ];

                $DB->selectAdvanced($parts, 'fetch_list_slide_review_image');

                $result = $DB->fetchArrayAll('fetch_list_slide_review_image');

                $DB->free('fetch_list_slide_review_image');
                if (!empty($result)) {
                    foreach ($result as &$item) {
                        $item['url'] = OSC::helper('core/image')->imageOptimize($item['filename'], 300, 300, true);
                    }
                }

                $this->_list_slide_review_image = $result;
            } catch (Exception $exception) {

            }
        }

        return $this->_list_slide_review_image;
    }

    public function getReviewImage($options = [], $pageIndex = 1, $pageSize = 10)
    {
        try {
            $DB = $this->getWriteAdapter();

            $condition = $DB->getCondition(true)
                ->addCondition('tbl_review.parent_id', 0)
                ->addCondition('tbl_review.state', Model_Catalog_Product_Review::STATE_APPROVED, OSC_Database::OPERATOR_EQUAL);
            if (isset($options['filter_option']) && !empty($options['filter_option'])) {
                switch ($options['filter_option']) {
                    case 'star':
                        if (isset($options['filter_value']) && !empty($options['filter_value'])) {
                            $condition->addCondition('tbl_review.vote_value', $options['filter_value'], OSC_Database::OPERATOR_EQUAL);
                        }
                        break;
                    case 'has-comment':
                        $condition->addCondition('tbl_review.has_comment', 1, OSC_Database::OPERATOR_EQUAL);
                        break;
                    case 'has-photo':
                        $condition->addCondition('tbl_review.has_photo', 1, OSC_Database::OPERATOR_EQUAL);
                        break;
                    default:
                        break;
                }
            }

            if (isset($options['current_image']) && !empty($options['current_image'])) {
                if (isset($options['get_next']) && !empty($options['get_next'])) {
                    $condition->addCondition('tbl_review_image.image_id', $options['current_image'], OSC_Database::OPERATOR_GREATER_THAN);
                } else if (isset($options['get_prev']) && !empty($options['get_prev'])) {
                    $condition->addCondition('tbl_review_image.image_id', $options['current_image'], OSC_Database::OPERATOR_LESS_THAN);
                }
            }

            $parts = [
                'select' => 'tbl_review_image.image_id, tbl_review_image.review_id, tbl_review_image.extension, tbl_review_image.width, tbl_review_image.height, tbl_review_image.filename',
                'from' => [
                    'tbl_review_image' => 'catalog/product_review_image'
                ],
                'join' => [
                    [
                        'table' => [
                            'tbl_review' => 'catalog/product_review'
                        ],
                        'type' => OSC_Database::JOIN_TYPE_INNER,
                        'condition' => 'tbl_review_image.review_id = tbl_review.record_id'
                    ]
                ],
                'condition' => $condition,
                'order' => 'tbl_review_image.image_id ' . (isset($options['get_next']) && !empty($options['get_next']) ? 'ASC' : 'DESC'),
                'limit' => [
                    ($pageIndex - 1) * $pageSize,
                    $pageSize
                ]
            ];

            $DB->selectAdvanced($parts, 'fetch_list_slide_review_image');

            $result = $DB->fetchArrayAll('fetch_list_slide_review_image');

            $DB->free('fetch_list_slide_review_image');
            if (!empty($result)) {
                foreach ($result as &$item) {
                    $item['url'] = OSC::helper('core/image')->imageOptimize($item['filename'], 300, 300, true);
                }
            }

            return $result;
        } catch (Exception $exception) {
            return null;
        }
    }

    protected function _preDataForSave(&$data)
    {
        parent::_preDataForSave($data);
    }

    protected function _preDataForUsing(&$data)
    {
        parent::_preDataForUsing($data);

        if (isset($data['role']) && $data['role'] == static::ROLE_ADMIN) {
            $data['customer_name'] = OSC::helper('core/setting')->get('theme/contact/name');
            $data['customer_email'] = OSC::helper('core/setting')->get('theme/contact/email');
        }
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();
        $errors = array();

        if (isset($data['photo_filename'])) {
            $data['photo_filename'] = trim($data['photo_filename']);

            if ($data['photo_filename']) {
                if (!preg_match('/^catalog\/review\/\d{2}\.\d{2}\.\d{4}\/.+/', $data['photo_filename'])) {
                    $errors[] = 'Photo filename is incorrect';
                }
            } else {
                $data['photo_filename'] = null;
            }
        }

        if (isset($data['customer_id'])) {
            $data['customer_id'] = intval($data['customer_id']);

            if ($data['customer_id'] < 1) {
                $data['customer_id'] = null;
            } else {
                try {
                    $customer = OSC::helper('account/customer')->get(['customer_id' => $data['customer_id']]);
                    if (!empty($customer['name'])) {
                        $data['customer_name'] = $customer['name'];
                    }
                    $data['customer_email'] = $customer['email'];
                } catch (Exception $ex) {
                    $data['customer_id'] = null;
                }
            }
        }

        foreach (['customer_name' => 'Customer name'] as $key => $field_name) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);

                if ($data[$key] == '') {
                    $errors[] = $field_name . ' is empty';
                }
            }
        }

        if (isset($data['customer_email'])) {
            try {
                OSC::core('validate')->validEmail($data['customer_email']);
            } catch (Exception $ex) {
                $errors[] = 'Email is invalid.';
            }
        }

        if (isset($data['order_id'])) {
            $data['order_id'] = intval($data['order_id']);

            if ($data['order_id'] < 1) {
                $errors[] = 'Order ID is empty';
            }
        }

        if (isset($data['product_id'])) {
            $data['product_id'] = intval($data['product_id']);

            if ($data['product_id'] < 1) {
                $errors[] = 'Product ID is empty';
            }
        }

        if (isset($data['vote_value'])) {
            $data['vote_value'] = intval($data['vote_value']);

            if ($data['vote_value'] < 1 || $data['vote_value'] > 5) {
                $errors[] = 'Vote value is empty';
            }
        }

        if (isset($data['state'])) {
            $data['state'] = intval($data['state']);

            if (!isset(static::STATE_CODES[$data['state']])) {
                $errors[] = 'State value is incorrect';
            }
        }

        if (isset($data['parent_id'])) {
            $data['parent_id'] = intval($data['parent_id']);
        }

        if (isset($data['role'])) {
            $data['role'] = intval($data['role']);

            if (!isset(static::STATE_CODES[$data['role']])) {
                $data['role'] = 0;
            }
        }

        //Check if content length of field review is > 0, update has_comment = 1
        if (isset($data['review'])) {
            if (!empty($data['review']) && strlen($data['review']) > 0) {
                $data['review'] = trim($data['review']);
                $data['has_comment'] = 1;
            } else {
                $data['has_comment'] = 0;
            }
        }

        //Check if length of list_photo is > 0, update has_comment = 1
        if (isset($data['list_photo'])) {
            if (count($data['list_photo']) > 0) {
                $data['has_photo'] = 1;
            } else {
                $data['has_photo'] = 0;
            }
        }

        if (isset($data['has_photo'])) {
            $data['has_photo'] = intval($data['has_photo']);
        }

        if (isset($data['has_comment'])) {
            $data['has_comment'] = intval($data['has_comment']);
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'product_id' => 'Product ID is empty',
                    'order_id' => 'Order ID is empty',
                    'vote_value' => 'Vote value is empty',
                    'customer_name' => 'Customer name is empty',
                    'customer_email' => 'Customer email is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'parent_id' => 0,
                    'role' => static::ROLE_NORMAL,
                    'state' => static::STATE_PENDING,
                    'photo_filename' => null,
                    'order_id' => null,
                    'customer_id' => null,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['ukey'] = OSC::makeUniqid(false, true);
            } else {
                unset($data['ukey']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _afterDelete()
    {
        parent::_afterDelete();

        if ($this->data['photo_filename']) {
            OSC::core('aws_s3')->deleteStorageFile($this->data['photo_filename']);
        }

        $list_image = $this->getListImage();
        if (!empty($list_image)) {
            foreach ($list_image as $item) {
                try {
                    OSC::core('aws_s3')->deleteStorageFile($item['filename']);
                } catch (Exception $exception) {
                }
            }
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG && $this->data['vote_value'] >= 3) {
            try {
                $product = $this->getProduct();

                if ($product instanceof Model_Catalog_Product && $product->getId() > 0) {
                    $additional_data = $product->data['additional_data'];
                    if (!is_array($additional_data)) {
                        $additional_data = [
                            'total_review' => 0,
                            'average_review' => 0
                        ];
                    }

                    $current_vote_value = OSC::helper('catalog/common')->integerToFloat($additional_data['average_review'] * $additional_data['total_review']);

                    $additional_data['total_review'] += 1;

                    $additional_data['average_review'] = OSC::helper('catalog/common')->floatToInteger(round(($current_vote_value + $this->data['vote_value']) / $additional_data['total_review'], 2));

                    $product->setData(['additional_data' => $additional_data])->save();
                }
            } catch (Exception $ex) {
            }
        }
    }
}