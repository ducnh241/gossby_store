<?php

class Controller_Post_React_Post extends Abstract_Frontend_ReactApiController
{
    public function __construct() {
        parent::__construct();
    }

    public function actionGetPostLists() {
        try {
            $page = intval($this->_request->get('page', 1));
            $size = intval($this->_request->get('size', 12));
            $collection_id = intval($this->_request->get('collection_id'));
            $list_post = [];
            $total_page = 0;

            if ($collection_id > 0) {
                $post_collection = OSC::model('post/collection')->load($collection_id);
                $selected_collections = OSC::model('post/postCollectionRel')
                    ->getCollection()
                    ->addCondition('collection_id', $post_collection->getId())
                    ->addField('post_id')
                    ->load()
                    ->toArray();
                $selected_post_ids = array_column($selected_collections, 'post_id');

                if(count($selected_post_ids) > 0) {
                    $posts = OSC::model('post/post')->getCollection()
                        ->addCondition('post_id', $selected_post_ids, OSC_Database::OPERATOR_IN)
                        ->addCondition('published_flag', 1)
                        ->sort('priority', OSC_Database::ORDER_DESC)
                        ->sort('added_timestamp', OSC_Database::ORDER_DESC)
                        ->setPageSize($size)
                        ->setCurrentPage($page)
                        ->load();

                    $list_post = OSC::helper('post/post')->formatPostApi($posts);
                    $total_page = intval($posts->collectionLength());
                }

                $post_collection_url = $post_collection->getDetailUrl();

                if ($page > 1) {
                    $post_collection_url = $post_collection_url . '/page/' . $page;
                }

                $result = [
                    'title' => $post_collection->data['title'],
                    'meta_data' => [
                        'canonical'=> $post_collection_url,
                        'url' => $post_collection_url,
                        'seo_title' => $post_collection->data['meta_tags']['title'] ? $post_collection->data['meta_tags']['title'] : $post_collection->data['title'],
                        'seo_image' => $post_collection->getOgImageUrl(),
                        'seo_description' => $post_collection->data['meta_tags']['description'],
                        'seo_keywords' => $post_collection->data['meta_tags']['keywords']
                    ],
                    'page_size' => $size,
                    'total_page' => $total_page,
                    'list_post' => $list_post
                ];

                $options = [];

                if ($post_collection->data['meta_tags']['description']) {
                    $options['sref_desc'] = 1;
                }

                $this->sendSuccess($result, $options);
            } else {
                $this->sendError('Collection ID is incorrect', $this::CODE_BAD_REQUEST);
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetPostListsOfAuthor() {
        try {
            $page = intval($this->_request->get('page', 1));
            $size = intval($this->_request->get('size', 12));
            $author_slug = $this->_request->get('author_slug');
            $list_post = [];
            $total_page = 0;

            $author = OSC::model('post/author')->setCondition([
                'condition' => 'slug = :slug',
                'params' => ['slug' => $author_slug]
            ])->load();

            $author_id = $author->data['author_id'];
            $posts = OSC::model('post/post')->getCollection()
                ->addCondition('author_id', $author_id)
                ->addCondition('published_flag', 1)
                ->sort('modified_timestamp', OSC_Database::ORDER_DESC)
                ->setPageSize($size)
                ->setCurrentPage($page)
                ->load();

            $list_post = OSC::helper('post/post')->formatPostApi($posts);
            $format_author = OSC::helper('post/post')->formatPostAuthorApi($author);

            $total_page = intval($posts->collectionLength());

            $result = [
                'list_post'     => $list_post,
                'author'        => $format_author,
                'meta_data'=> [
                    'canonical'=> $author->getDetailUrl(),
                    'url' => $author->getDetailUrl(),
                    'seo_title' => $author->data['meta_tags']['title'] ?: $author->data['title'],
                    'seo_image' => $author->getMetaImageUrl(),
                    'seo_description' => $author->data['meta_tags']['description'],
                    'seo_keywords' => $author->data['meta_tags']['keywords']
                ],
                'total_page'    => $total_page,
                'page_size'     => $size,
            ];

            $this->sendSuccess($result);

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetPostDetail() {
        $id = intval($this->_request->get('id'));
        $token = trim($this->_request->get('token'));

        try {
            $post = OSC::model('post/post');

            if ($id > 0) {
                $collection = [];

                $post->load($id);

                $is_preview = $token === base64_encode(Model_Post_Post::POST_PREVIEW_CODE);

                if ($post->data['published_flag'] !== 1  && !$is_preview) {
                    $this->sendError('Post is not publish', $this::CODE_NOT_FOUND);
                }

                $selected_collections = OSC::model('post/postCollectionRel')->getCollection()->addCondition('post_id', $id)->addField('collection_id')->load()->toArray();
                $selected_collection_ids = array_column($selected_collections, 'collection_id');

                if (count($selected_collection_ids) > 0) {
                    $post_collection = OSC::model('post/collection')
                        ->getCollection()
                        ->sort('priority', OSC_Database::ORDER_DESC)
                        ->sort('added_timestamp', OSC_Database::ORDER_DESC)
                        ->load($selected_collection_ids)
                        ->getItem();

                    if ($post_collection) {
                        $collection = OSC::helper('post/post')->formatPostCollectionApi($post_collection);
                        $breadcrumb[] = [
                            'title' => $post_collection->data['title'],
                            'link' => $post_collection->getDetailUrl(false),
                        ];
                    }
                }

                $breadcrumb[] = [
                    'title' => $post->data['title'],
                    'link' => $post->getDetailUrl(false),
                ];

                $result = [
                    'collection' => $collection ,
                    'id' => $post->data['post_id'],
                    'title' => $post->data['title'],
                    'author' => $post->getAuthor(),
                    'meta_data'=>[
                        'canonical'=> $post->getDetailUrl(),
                        'url' => $post->getDetailUrl(),
                        'seo_title' => $post->data['meta_tags']['title'] ? $post->data['meta_tags']['title'] : $post->data['title'],
                        'seo_image' => $post->getOgImageUrl(),
                        'seo_description' => $post->data['meta_tags']['description'],
                        'seo_keywords' => $post->data['meta_tags']['keywords']
                    ],
                    'image' => OSC::wrapCDN($post->getImageUrl()),
                    'footer_banner' => $post->getFooterBanner(),
                    'link' => $post->getDetailUrl(true),
                    'content' => $post->data['content'],
                    'description' => $post->data['description'],
                    'breadcrumb' => $breadcrumb,
                    'comment_facebook' => [
                        'facebook_app_id' => OSC::helper('core/setting')->get('post/comment/facebook/app_id'),
                        'facebook_number_comment' => OSC::helper('core/setting')->get('post/comment/facebook/number_of_comment'),
                        'facebook_enable_comment' => OSC::helper('core/setting')->get('post/comment/facebook/enable'),
                        'facebook_lazy_load_comment' => OSC::helper('core/setting')->get('post/comment/facebook/lazy_load')
                    ],
                    'added_timestamp' => (int)$post->data['added_timestamp'],
                    'modified_timestamp' => (int)$post->data['modified_timestamp']
                ];

                $options = [];

                if ($post->data['meta_tags']['description']) {
                    $options['sref_desc'] = 1;
                }

                $this->sendSuccess($result, $options);
            } else {
                $this->sendError('Post ID is incorrect', $this::CODE_BAD_REQUEST);
            }

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetRelatedPostLists() {
        $id = intval($this->_request->get('id'));

        try {
            if ($id > 0) {
                $post = OSC::model('post/post')->load($id);

                $related_posts = OSC::model('post/post')->getOtherPosts($post->getId(), $post->data['collection_id'], 3);

                $list_post =  OSC::helper('post/post')->formatPostApi($related_posts);

                $this->sendSuccess($list_post);
            } else {
                $this->sendError('Post ID is incorrect', $this::CODE_BAD_REQUEST);
            }
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetRencentPostList() {
        try {
            $related_posts = OSC::model('post/post')->getRecentPosts();

            $list_post = OSC::helper('post/post')->formatPostApi($related_posts);

            $this->sendSuccess($list_post);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionBlogPage() {
        try {
            $page = intval($this->_request->get('page', 1));
            $size = intval($this->_request->get('size', 12));
            $collection_title = OSC::helper('core/setting')->get("post/config_post/collection_title") ?: 'BLOG';

            $posts = OSC::model('post/post')->getCollection();

            $sort = OSC::helper('core/setting')->get("post/config_post/sort_post");

            switch ($sort) {
                case 'oldest':
                    $posts->sort('post_id', 'ASC');
                    break;
                case 'priority':
                    $posts->sort('priority', 'DESC');
                    break;
                default:
                    $posts->sort('post_id', 'DESC');
                    break;
            }

            $posts->addCondition('published_flag', 1)
                ->setPageSize($size)
                ->setCurrentPage($page)->load();

            $post_collection_url =  OSC::helper('post/common')->getUrlAllPost();

            if ($page > 1) {
                $post_collection_url = $post_collection_url . '/page/' . $page;
            }

            $result = [
                'title' => $collection_title,
                'link' => OSC::helper('post/common')->getUrlAllPost(false),
                'meta_data' => [
                    'canonical'=> $post_collection_url,
                    'url' => $post_collection_url,
                    'seo_title' => OSC::helper('core/setting')->get("post/config_post/meta_title") ?  OSC::helper('core/setting')->get("post/config_post/meta_title") : $collection_title,
                    'seo_image' => OSC::helper('core/setting')->get("post/config_post/meta_image") ? OSC::core('aws_s3')->getStorageUrl(OSC::helper('core/setting')->get("post/config_post/meta_image")['file']) : OSC::helper('frontend/template')->getMetaImage()->url,
                    'seo_description' => OSC::helper('core/setting')->get("post/config_post/meta_description"),
                    'seo_keywords' => OSC::helper('core/setting')->get("post/config_post/meta_keyword")
                ],
                'page_size' => $size,
                'total_blog' => intval($posts->collectionLength()),
                'list_post' => OSC::helper('post/post')->formatPostApi($posts)
            ];

            $this->sendSuccess($result);
        } catch (Exception $ex){
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionPostTracking() {
        try {
            $id = intval($this->_request->get('id'));

            if ($id < 1) {
                $this->sendError('Data is incorrect', $this::CODE_BAD_REQUEST);
            }

            $referer_url = strtolower(trim(strval($this->_request->get('ref'))));

            if (!$referer_url) {
                $referer = 'direct';
            } else {
                $referer_info = parse_url($referer_url);

                if ($referer_info['host']) {
                    $referer = ($referer_info['host'] == OSC::$domain) ? 'direct' : $referer_info['host'];
                }
            }

            if (!$referer) {
                $this->sendError('Not detect referer');
            }

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $this::CODE_NOT_FOUND);
        }

        try {
            $post = OSC::model('post/post')->load($id);
            $post->trackingVisits($referer);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }

        $this->sendSuccess();
    }
}