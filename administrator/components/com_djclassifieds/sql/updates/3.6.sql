CREATE TABLE IF NOT EXISTS `#__djcf_items_promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `prom_id` int(11) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_exp` datetime NOT NULL,
  `days` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djcf_promotions_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prom_id` int(11) NOT NULL,
  `days` int(11) NOT NULL,
  `price` float(12,2) NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djcf_fields` 
	ADD `date_format` VARCHAR( 55 ) NOT NULL,
	ADD `core_source` VARCHAR( 255 ) NOT NULL,
	ADD `edition_blocked` INT NOT NULL;
	

INSERT IGNORE INTO `#__djcf_emails` (`id`, `label`, `title`, `content`) VALUES 
	(20, 'COM_DJCLASSIFIEDS_ET_PAYMENTS_BANKTRANSFER_PAYMENT_INFO', 'Bank Transfer Payment informations', '<p>Hello</p>\r\n<p>Payment for:  [[payment_item_name]]<br /><br />Price: [[payment_price]]<br /><br />Payment Information:<br /><br />[[payment_info]]<br /><br /><br />Payment ID: [[payment_id]]</p>');
	