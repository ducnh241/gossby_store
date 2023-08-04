CREATE TABLE `osc_post_office_email` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(27) NOT NULL,
  `email_key` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `receiver_name` varchar(255) NOT NULL,
  `receiver_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` mediumtext,
  `text_content` mediumtext,
  `opens` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `added_timestamp` int(10) NOT NULL DEFAULT '0',
  `modified_timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `email_key_UNIQUE` (`email_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_post_office_email_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(27) NOT NULL,
  `email_key` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `state` enum('queue','sending','sent','error') NOT NULL DEFAULT 'queue',
  `error_message` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `receiver_name` varchar(255) NOT NULL,
  `receiver_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` mediumtext,
  `text_content` mediumtext,
  `added_timestamp` int(10) NOT NULL DEFAULT '0',
  `modified_timestamp` int(10) NOT NULL DEFAULT '0',
  `running_timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `email_key_UNIQUE` (`email_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_post_office_email_tracking` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_id` int(11) NOT NULL,
  `event` enum('open','click') NOT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `event_data` varchar(255) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;