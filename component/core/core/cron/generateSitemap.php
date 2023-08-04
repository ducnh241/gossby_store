<?php

class Cron_Core_GenerateSitemap extends OSC_Cron_Abstract
{
    const CRON_TIMER = '@daily';
    const CRON_SCHEDULER_FLAG = 1;
    protected $cron_name = '[GenerateSiteMap]: ';

    public function process($params, $queue_added_timestamp)
    {
        try {
            $data = [
                'home' => [
                    'url' => OSC_FRONTEND_BASE_URL,
                    'lastmod' => date('c'),
                    'changefreq' => 'daily',
                    'priority' => '1',
                ]
            ];

            //Collection
            $collections = OSC::model('catalog/collection')->getCollection()
                ->addField('collection_id', 'slug', 'modified_timestamp', 'meta_tags')
                ->sort('collection_id', OSC_Database::ORDER_ASC)
                ->load();
            foreach ($collections as $collection) {
                $key = $collection->data['collection_id'];
                $data['collection_' . $key]['url'] = $collection->getDetailUrl();
                $data['collection_' . $key]['lastmod'] = date('c', $collection->data['modified_timestamp']);
                $data['collection_' . $key]['changefreq'] = 'daily';
                $data['collection_' . $key]['priority'] = '0.8';
            }

            //Page
            $pages = OSC::model('page/page')->getCollection()
                ->addField('page_id', 'slug', 'modified_timestamp', 'meta_tags')
                ->sort('page_id', OSC_Database::ORDER_ASC)
                ->load();
            foreach ($pages as $page) {
                $key = $page->data['page_id'];
                $data['page_' . $key]['url'] = $page->getDetailUrl();
                $data['page_' . $key]['lastmod'] = date('c', $page->data['modified_timestamp']);
                $data['page_' . $key]['changefreq'] = 'daily';
                $data['page_' . $key]['priority'] = '0.7';
            }

            //Post collection
            $post_collections = OSC::model('post/collection')->getCollection()
                ->addField('collection_id', 'slug', 'modified_timestamp', 'meta_tags')
                ->sort('collection_id', OSC_Database::ORDER_ASC)
                ->load();
            foreach ($post_collections as $post_collection) {
                $key = $post_collection->data['collection_id'];
                $data['post_collection_' . $key]['url'] = $post_collection->getDetailUrl();
                $data['post_collection_' . $key]['lastmod'] = date('c', $post_collection->data['modified_timestamp']);
                $data['post_collection_' . $key]['changefreq'] = 'daily';
                $data['post_collection_' . $key]['priority'] = '0.8';
            }

            //Post
            $posts = OSC::model('post/post')->getCollection()
                ->addField('post_id', 'slug', 'modified_timestamp', 'meta_tags')
                ->addCondition('published_flag', 1)
                ->sort('post_id', OSC_Database::ORDER_ASC)
                ->load();
            foreach ($posts as $post) {
                $key = $post->data['post_id'];
                $data['post_' . $key]['url'] = $post->getDetailUrl();
                $data['post_' . $key]['lastmod'] = date('c', $post->data['modified_timestamp']);
                $data['post_' . $key]['changefreq'] = 'daily';
                $data['post_' . $key]['priority'] = '0.8';
            }

            //Product
            $products = OSC::model('catalog/product')->getCollection()
                ->addField('product_id', 'sku', 'slug', 'modified_timestamp', 'meta_tags', 'meta_data', 'selling_type')
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL)
                ->addCondition('listing', 1)
                ->sort('product_id', OSC_Database::ORDER_ASC)
                ->load();
            foreach ($products as $product) {
                if (!$product->isCampaignMode()) {
                    continue;
                }
                $key = $product->data['product_id'];
                $data['product_' . $key]['url'] = $product->getDetailUrl();
                $data['product_' . $key]['lastmod'] = date('c', $product->data['modified_timestamp']);
                $data['product_' . $key]['changefreq'] = 'daily';
                $data['product_' . $key]['priority'] = '0.9';
            }


            $site_map = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"></urlset>');

            foreach ($data as $data_site_map) {
                $url = $site_map->addChild('url');
                $url->addChild('loc', $data_site_map['url']);
                $url->addChild('lastmod', $data_site_map['lastmod']);
                $url->addChild('changefreq', $data_site_map['changefreq']);
                $url->addChild('priority', $data_site_map['priority']);
            }

            $dom = new DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($site_map->asXML());

            OSC::writeToFile(OSC_SITE_PATH . '/sitemap.xml', $dom->saveXML());
        } catch (Exception $ex) {
            OSC::logFile($this->cron_name . 'Error: ' . $ex->getMessage());
        }
    }

}