ALTER TABLE `#__djcf_categories` 
		ADD `schema_type` VARCHAR( 255 ) NOT NULL;

CREATE TABLE IF NOT EXISTS `#__djcf_search_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `search_url` text NOT NULL,
  `search_query` text NOT NULL,
  `created` datetime NOT NULL,
  `last_check` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;



CREATE TABLE IF NOT EXISTS `#__djcf_search_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  UNIQUE KEY `user_item` (`search_id`,`user_id`,`item_id`),
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=utf8 ;

INSERT IGNORE INTO `#__djcf_emails` (`id`, `label`, `title`, `content`) VALUES
(28, 'COM_DJCLASSIFIEDS_ET_SAVED_SEARCH_NOTIFICATION', 'New adverts', '<p>Hello [[user_name]],</p>\r\n<p> </p>\r\n<p>We found new [[search_total]] adverts that suits your "Saved Search" rules</p>\r\n<p>[[search_adverts]]</p>');