CREATE TABLE IF NOT EXISTS `#__djmsg_users` ( 
  `user_id` INT(10) NOT NULL , 
  `state` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__djmsg_messages` 
  ADD `sender_name` VARCHAR(400) NOT NULL DEFAULT '' AFTER `user_from`, 
  ADD `sender_email` VARCHAR(100) NOT NULL DEFAULT '' AFTER `sender_name`,
  ADD `recipient_name` VARCHAR(400) NOT NULL DEFAULT '' AFTER `user_to`, 
  ADD `recipient_email` VARCHAR(100) NOT NULL DEFAULT '' AFTER `recipient_name`;
