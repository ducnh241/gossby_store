<?php

require_once(dirname(__FILE__) . '/lib/Producer.php');
require_once(dirname(__FILE__) . '/lib/ProducerConfig.php');

use Kafka\Producer;
use Kafka\ProducerConfig;

class OSC_Kafka_Client {
    public function __construct() {
        $kafka_config = OSC::systemRegistry('kafka');
        if (!empty($kafka_config)) {
            $config = ProducerConfig::getInstance();
            $config->setMetadataRefreshIntervalMs(10000);
            $config->setMetadataBrokerList("{$kafka_config['host']}:{$kafka_config['port']}");
            $config->setRequiredAck(1);
            $config->setIsAsyn(false);
            $config->setProduceInterval(500);
        }
    }

    /**
     * @param array $value
     * @param string $topic
     * @return void
     * @throws Exception
     */
    public function sendData(array $value = [], string $topic = '') {
        try {
            $data = [
                'topic' => $topic,
                'value' => OSC::encode($value),
                'key' => OSC::makeUniqid()
            ];

            $producer = new Producer();
            $producer->send([
                $data
            ]);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}