<?php

define('PREVIEW_KEY', 'gossby_preview_3d_count_key');
$redis = new Redis();
try {
    //$host = '127.0.0.1';
    $host = 'redis-instance-9prints.tudlce.ng.0001.use2.cache.amazonaws.com';
    $port = '6379';

    $redis->connect($host, $port);
    $redis->incr(PREVIEW_KEY);
    $result = $redis->get(PREVIEW_KEY);

    echo json_encode([
        'status' => 200,
        'data' => $result
    ]);
} catch (RedisException $e) {
    echo json_encode([
        'status' => 400,
        'data' => $e->getMessage()
    ]);
}