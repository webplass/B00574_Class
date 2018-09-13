ALTER TABLE `#__djmsg_messages`  
ADD `msg_source` VARCHAR(50) NULL  AFTER `recipient_state`,  
ADD `msg_source_id` INT NOT NULL DEFAULT '0'  AFTER `msg_source`,  
ADD `additional_data` MEDIUMTEXT NULL  AFTER `msg_source_id`,  
ADD `attachments` MEDIUMTEXT NULL  AFTER `additional_data`;

ALTER TABLE `#__djmsg_messages` ADD `parent_id` INT NOT NULL DEFAULT '0' AFTER `id`, ADD `reply_to_id` INT NOT NULL DEFAULT '0' AFTER `parent_id`;

ALTER TABLE `#__djmsg_messages`
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_reply_to` (`reply_to_id`);

INSERT IGNORE INTO `#__djmsg_templates` (`id`, `type`, `name`, `state`, `subject`, `body`, `created`, `created_by`, `checked_out`, `checked_out_time`, `params`) VALUES
(4, 'admin_notification', 'Admin notification', 1, 'Admin notification', '<p>Hello,</p>\r\n<p>This is notification about the message sent from [[sender_name]] to [[recipient_name]].</p>\r\n<p>You will find the copy of the message below:</p>\r\n<hr />\r\n<div>[[message]]</div>', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', NULL);