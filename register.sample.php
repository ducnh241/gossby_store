<?php

OSC::systemRegister(array(
    'rewrite' => 1,
    'template_frontend' => 'default',
    'db_prefix' => 'osc_',
    'cache_model' => 0,
    'environment' => 'local'
));

OSC_Database::registerDBInstance('default', array(
    'persistent' => false,
    'port' => '3306',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '1q23456',
    'database' => 'shop3'
));

OSC_Database::registerDBBind('read', 'default');
OSC_Database::registerDBBind('write', 'default');

// Multiple database & read + write
OSC_Database::registerDBInstance('db_master', array(
    'persistent' => false,
    'port' => '3306',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'Dlsinc@0104',
    'database' => 'master'
));

OSC_Database::registerDBBind('db_master', 'db_master');

// Multiple database & just read
OSC_Database::registerDBInstance('db_master_read', array(
    'persistent' => false,
    'port' => '3306',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'Dlsinc@0104',
    'database' => 'master'
));

OSC_Database::registerDBBind('db_master_read', 'db_master_read');

OSC::systemRegister('session_adapter', 'session');

OSC::systemRegister('CRON_ADAPTER', 'database');
OSC::systemRegister('CRON_METHOD', 'html');
OSC_Database::registerDBBind('cron', 'default');

OSC::systemRegister('aws_s3', [
    'region' => 'ap-southeast-1',
    'bucket' => '9printsdemos3',
    'version' => 'latest',
    'key' => 'AK47XXXXXXXXXXXX',
    'secret' => 'dsllansndawi%$#'
]);

OSC_Cache::registerCacheInstance('system', array('adapter' => 'file', 'dirname' => 'system'));
OSC_Cache::registerCacheBind('system', 'system');

OSC_Search::registerSearchInstance(
        'catalog_solr_product_index', array(
    'adapter' => 'solr',
    'host' => '127.0.0.1',
    'port' => '8983',
    'path' => 'solr/osecore_shop',
    'keyword_field' => 'keywords'
        )
);
OSC_Search::registerSearchBind('catalog_solr_product_index', 'catalog_solr_product_index');

OSC::systemRegister('catalog/redisgraph', [
    'host' => '127.0.0.1',
    'port' => '6379',
    'user' => '',
    'pass' => ''
]);

OSC::systemRegister('CDN_CONFIG', [
    'enable' => true,
    'base_url' => 'https://cdn.osecore.net/gossby.com'
]);

OSC_Cache::registerCacheInstance('redis', array('adapter' => 'redis', 'host' => '127.0.0.1', 'port' => '6379'));
OSC_Cache::registerCacheBind('cache', 'redis');
//OSC_Cache::registerCacheBind('controller', 'redis');
//OSC_Cache::registerCacheBind('model', 'redis');
//OSC::systemRegister('cache_model', 1);
//OSC::systemRegister('controller_caching_prefix', time());

OSC_Mongodb::registerInstance('mongodb', [
	'username' => '',
	'password' => '',
	'host' => 'localhost',
	'port' => 27017,
    'env' => '',
    'tls_enable' => true,
    'tls_dir' => '/var/www/9prints/rds-combined-ca-bundle.pem',
    'auth_dbname' => '',
	'dbname' => 'store_batsatla'
]);

OSC_Mongodb::registerBind('report', 'mongodb');

//Start config ES product
OSC_Search::registerSearchInstance('catalog_product_elastic_search_index',
    [
        'adapter' => 'elasticsearch',
        'host' => [
            [
                'host' => 'localhost',
                'port' => '9200',
                'scheme' => 'http',
                'path' => '',
                'user' => '',
                'pass' => ''
            ]
        ],
        //'host' => ['localhost:9200'],
        'index' => 'catalog_product'
    ]
);
OSC_Search::registerSearchBind('catalog_product_elastic_search_index', 'catalog_product_elastic_search_index');
//End config ES product

//Start config ES order
OSC_Search::registerSearchInstance(
    'catalog_order_elastic_search_index', [
        'adapter'   => 'elasticsearch',
        'host' => [
            [
                'host' => 'localhost',
                'port' => '9200',
                'scheme' => 'http',
                'path' => '',
                'user' => '',
                'pass' => ''
            ]
        ],
        //'host' => ['localhost:9200'],
        'prefix' => 'osecore',
        'index' => 'catalog_order'
    ]
);
OSC_Search::registerSearchBind('catalog_order_elastic_search_index', 'catalog_order_elastic_search_index');
//End config ES order

OSC::systemRegister('kafka', [
    'host' => 'localhost',
    'port' => '9092',
    'topic' => 'sync-data',
    'source' => 'store'
]);

OSC::systemRegister('kafka_d2', [
    'host' => 'localhost',
    'port' => '9092',
    'topic' => 'd2-flows'
]);

OSC::systemRegister('kafka_d2_reply', [
    'host' => 'localhost',
    'port' => '9092',
    'topic' => 'd2-flows.reply'
]);