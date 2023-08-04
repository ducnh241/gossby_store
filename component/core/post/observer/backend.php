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
 * @copyright    Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_Post_Backend
{

    public static function collectMenu()
    {
        if (!OSC::controller()->checkPermission('post', false)) {
            return null;
        }

        $permissions = [];
        if (OSC::controller()->checkPermission('post/post', false) || OSC::controller()->checkPermission('post/collection', false)) {
            $permissions[] = [
                'key' => 'post',
                'icon' => 'note',
                'position' => 991,
                'title' => 'Post Management',
                'url' => OSC::getUrl('post/backend_post/index', [], true)
            ];
        }
        if (OSC::controller()->checkPermission('post/post', false)) {
            $permissions[] = [
                'key' => 'post/post',
                'parent_key' => 'post',
                'title' => 'Posts',
                'url' => OSC::getUrl('post/backend_post/index', [], true)
            ];
        }
        if (OSC::controller()->checkPermission('post/collection', false)) {
            $permissions[] = [
                'key' => 'post/collection',
                'parent_key' => 'post',
                'title' => 'Collections',
                'url' => OSC::getUrl('post/backend_collection/index', [], true)
            ];
        }
        if (OSC::controller()->checkPermission('post/author', false)) {
            $permissions[] = [
                'key' => 'post/author',
                'parent_key' => 'post',
                'title' => 'Authors',
                'url' => OSC::getUrl('post/backend_author/index', [], true)
            ];
        }
        return $permissions;
    }

    public static function collectSettingSection()
    {
        return [
            [
                'key' => 'post',
                'priority' => 11,
                'icon' => 'note',
                'title' => 'Post',
                'description' => 'Customize Display for Collections & Comments'
            ]
        ];
    }

    public static function collectSettingItem()
    {
        return [
            [
                'section' => 'post',
                'key' => 'comment_facebook',
                'type' => 'group',
                'title' => 'Facebook Comments',
                'description' => 'Choose Post Comment Settings'
            ],
            [
                'section' => 'post',
                'group' => 'comment_facebook',
                'key' => 'post/comment/facebook/app_id',
                'type' => 'text',
                'title' => 'App Id'
            ],
            [
                'section' => 'post',
                'group' => 'comment_facebook',
                'key' => 'post/comment/facebook/number_of_comment',
                'type' => 'number',
                'title' => 'Number of comments displayed'
            ],
            [
                'section' => 'post',
                'group' => 'comment_facebook',
                'key' => 'post/comment/facebook/enable',
                'type' => 'switcher',
                'title' => 'Display Facebook Comments',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'comment_facebook',
                'key' => 'post/comment/facebook/lazy_load',
                'type' => 'switcher',
                'title' => 'Automatically Load Facebook Comments'
            ],
            [
                'section' => 'post',
                'key' => 'config_post',
                'type' => 'group',
                'title' => 'Customize Post Collections',
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'key' => 'post/config_post/collection_title',
                'type' => 'text',
                'title' => 'Collection Title',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'key' => 'post/config_post/slug',
                'type' => 'text',
                'title' => 'Slug',
                'desc' => 'The field under validation may have alpha-numeric characters, as well as dashes and underscores.',
                'pattern' => '^[a-zA-Z0-9-_]{3,}',
                'required' => 'true',
                'validator' => [Observer_Post_Backend, 'validateSlug'],
                'after_save' => [Observer_Post_Backend, 'saveBlogAlias'],
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'type' => 'select',
                'options' => [
                    'priority' => 'Priority DESC',
                    'newest' => 'Newest',
                    'oldest' => 'Oldest',
                ],
                'key' => 'post/config_post/sort_post',
                'title' => 'Order By',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'key' => 'post/config_post/meta_title',
                'type' => 'text',
                'title' => 'Meta Title',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'key' => 'post/config_post/meta_description',
                'type' => 'textarea',
                'title' => 'Meta Description',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'key' => 'post/config_post/meta_keyword',
                'type' => 'textarea',
                'title' => 'Meta Keyword',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'config_post',
                'key' => 'post/config_post/meta_image',
                'type' => 'image',
                'title' => 'Meta Image',
                'extension' => 'png,jpg',
                'trim' => true,
                'full_row' => true
            ],
            [
                'section' => 'post',
                'key' => 'banner_post',
                'type' => 'group',
                'title' => 'Footer Banner Post',
            ],
            [
                'section' => 'post',
                'group' => 'banner_post',
                'key' => 'post/footer_banner_post/enable',
                'type' => 'switcher',
                'title' => 'Enable Footer Banner',
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'banner_post',
                'key' => 'post/footer_banner_post/url',
                'type' => 'text',
                'title' => 'Url',
                'validator' => [Observer_Post_Backend, 'validateFooterBannerUrl'],
                'full_row' => true
            ],
            [
                'section' => 'post',
                'group' => 'banner_post',
                'key' => 'post/footer_banner_post/pc_image',
                'type' => 'image',
                'title' => 'PC default footer banner',
                'extension' => 'png,jpg',
                'trim' => true,
            ],
            [
                'section' => 'post',
                'group' => 'banner_post',
                'key' => 'post/footer_banner_post/mobile_image',
                'type' => 'image',
                'title' => 'Mobile default footer banner',
                'extension' => 'png,jpg',
                'trim' => true,
            ],
        ];
    }

    public static function navCollectItemType($params)
    {
        $params['items'][] = array(
            'icon' => 'newspaper',
            'title' => 'Post Collections',
            'browse_url' => OSC::getUrl('post/backend_collection/browse')
        );
    }

    public static function collectPermKey($params)
    {
        $params['permission_map']['post'] = [
            'label' => 'Post Management',
            'items' => [
                'post' => [
                    'label' => 'Post',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => [
                            'label' => 'Delete',
                            'items' => [
                                'bulk' => 'Bulk delete'
                            ]
                        ],
                    ]
                ],
                'collection' => [
                    'label' => 'Collection Post',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete'
                    ]
                ],
                'author' => [
                    'label' => 'Author Post',
                    'items' => [
                        'add' => 'Add',
                        'edit' => 'Edit',
                        'delete' => 'Delete'
                    ]
                ]
            ],
        ];
    }

    public function validateSlug($values)
    {
        try {
            OSC::helper('alias/common')->validate($values, 'post_collection', 'all');
            return $values;
        } catch (Exception $ex) {
            throw new Exception('Meta slug already exists or is not invalid');
        }
    }

    public function saveBlogAlias($values)
    {
        try {
            OSC::helper('alias/common')->save($values, 'post_collection', 'all');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

    }

    public function validateFooterBannerUrl($value) {
        if (!OSC::isUrl($value) && !empty($value)) {
            throw new Exception('Footer banner url is invalid');
        }
        return $value;
    }

}
