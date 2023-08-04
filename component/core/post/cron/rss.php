<?php

class Cron_Post_Rss extends OSC_Cron_Abstract
{
    const CRON_SCHEDULER_FLAG = 1;
    protected $cron_name = '[PostRSS]: ';

    public function process($params, $queue_added_timestamp)
    {
        try {
            //Post
            $posts = OSC::model('post/post')->getCollection()
                ->addField('post_id', 'title', 'slug', 'image', 'description', 'modified_timestamp', 'meta_tags', 'added_timestamp')
                ->addCondition('published_flag', 1)
                ->sort('post_id', OSC_Database::ORDER_ASC)
                ->load();

            $site_name = OSC::helper('core/setting')->get('theme/site_name');

            $rss_file = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?>
            <rss version="2.0">
            </rss>
            ');

            $channel = $rss_file->addChild('channel');
            $channel->addChild('title', $site_name);
            $channel->addChild('link', OSC_FRONTEND_BASE_URL);
            $channel->addChild('description', $site_name.' posts');
            $channel->addChild('language', 'en-us');

            foreach ($posts as $post) {
                $item = $channel->addChild('item');
                $date = new DateTime('@'. $post->data['added_timestamp']);

                $item->addChild('title', $post->data['title']);
                $item->addChild('link', $post->getDetailUrl());
                $guid = $item->addChild('guid', $post->getDetailUrl());
                $guid->addAttribute('isPermaLink', 'true');
                $item->addChild('pubDate', $date->format('D, d-m-Y H:i:s'));
                $item->addChild('image', $post->getImageUrl());
                $item->addChild('description', $post->data['description']);
            }

            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($rss_file->asXML());

            OSC::writeToFile(OSC_VAR_PATH . '/rss/posts.rss', $dom->saveXML());
        } catch (Exception $ex) {
        }
    }
}