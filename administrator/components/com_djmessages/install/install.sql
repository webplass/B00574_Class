CREATE TABLE IF NOT EXISTS `#__djmsg_messages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `reply_to_id` int(11) NOT NULL DEFAULT '0',
  `user_from` int(10) NOT NULL,
  `sender_name` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sender_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_to` int(10) NOT NULL,
  `recipient_name` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `recipient_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci,
  `sent_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `read_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sender_state` tinyint(3) NOT NULL DEFAULT '0',
  `recipient_state` tinyint(3) NOT NULL DEFAULT '0',
  `msg_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msg_source_id` int(11) NOT NULL DEFAULT '0',
  `additional_data` mediumtext COLLATE utf8mb4_unicode_ci,
  `attachments` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_user_from_to` (`user_from`,`user_to`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_reply_to` (`reply_to_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djmsg_templates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `subject` VARCHAR(100) NOT NULL, 
  `body` mediumtext,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) NOT NULL DEFAULT '0',
  `checked_out` int(10) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_type` (`type`)
) AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djmsg_banned` (
  `user_id` int(10) NOT NULL,
  `by_user` int(10) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`by_user`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djmsg_users` (
  `user_id` int(10) NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__djmsg_templates` (`id`, `type`, `name`, `state`, `subject`, `body`, `created`, `created_by`, `checked_out`, `checked_out_time`, `params`) VALUES
(1, 'plain', 'Plain notification', 1, 'You have new message', '<p>Hello [[recipient_name]],</p>\r\n<p>You have a new message in your inbox from [[sender_name]].</p>\r\n<p><a href="[[messages_url]]">Check your inbox now</a></p>\r\n<p> </p>\r\n<div>[[message]]</div>', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', NULL),
(2, 'admin_message', 'Message from admin', 1, 'You have new message', '<p>Hello [[recipient_name]],</p>\r\n<p>You have a new message in your inbox from [[sender_name]].</p>\r\n<p> </p>\r\n<div>[[message]]</div>', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', NULL),
(3, 'reply', 'Reply from inbox', 1, 'There is a reply to your message', '<p>Hello [[recipient_name]],</p>\r\n<p>You have a new message in your inbox from [[sender_name]].</p>\r\n<p><a href="[[messages_url]]">Check your inbox now</a></p>\r\n<p> </p>\r\n<div>[[message]]</div>', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', NULL),
(4, 'admin_notification', 'Admin notification', 1, 'Admin notification', '<p>Hello,</p>\r\n<p>This is notification about the message sent from [[sender_name]] to [[recipient_name]].</p>\r\n<p>You will find the copy of the message below:</p>\r\n<hr />\r\n<div>[[message]]</div>', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', NULL);
