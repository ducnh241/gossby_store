CREATE TABLE `osc_core_notify_queue` (
    `queue_id` int(11) NOT NULL AUTO_INCREMENT,
    `queue_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `queue_flag` tinyint(1) NOT NULL DEFAULT 1,
    `error_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `added_timestamp` int(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
