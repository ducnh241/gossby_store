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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC Backend Controller
 *
 * @package Controller_Backend_Search
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Controller_Backend_Search extends Abstract_Backend_Controller {

    protected $_check_hash = true;

    const PAGE_SIZE = 30;

    public function __construct() {
        parent::__construct();
        OSC::core('language')->load('user/common');
    }

    public function actionIndex() {
        $search = OSC::core('search')->getAdapter('backend');

        try {
            $keyword = strtolower(trim($this->_request->get('keywords')));
            $keywords = $search->cleanKeywords($keyword);
            $keywordSearch = '';
            foreach ($keywords as $value) {
                $keywordSearch .= $value['value'];
            }

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $search->addFilter('module_key', 'catalog', OSC_Search::OPERATOR_EQUAL)
                ->addFilter('keywords', $keywordSearch, OSC_Search::OPERATOR_CONTAINS);
            $search->setLikeMode(true)->setOffset(($page - 1) * self::PAGE_SIZE)->setPageSize(self::PAGE_SIZE);

            $result = $search->fetch();

            if ($result['total_item'] < 1) {
                $this->_ajaxError('-1');
            }

            if (count($result['docs']) < 1) {
                $this->_ajaxError('-2');
            }

            $docs = [];
            foreach ($result['docs'] as $doc) {
                $doc['index_data'] = unserialize($doc['index_data']);
                $docs[] = OSC::helper('backend/backend_search_result')->render($doc);
            }

            $this->_ajaxResponse(array(
                'keywords' => $result['keywords'],
                'total' => count(array_filter($docs)),
                'offset' => $result['offset'],
                'current_page' => $result['current_page'],
                'page_size' => $result['page_size'],
                'docs' => $docs
            ));
        } catch (OSC_Search_Exception_Condition $e) {
            $this->_ajaxError($e->getMessage(), $e->getCode());
        }
    }

}
