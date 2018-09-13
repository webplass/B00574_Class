ALTER TABLE `#__djcf_payments` 
		ADD `coupon` VARCHAR( 55 ) NOT NULL ,
		ADD `coupon_discount` FLOAT( 12, 2 ) NOT NULL ;
		
ALTER TABLE `#__djcf_items` 
		ADD `blocked` INT NOT NULL ;		

CREATE TABLE IF NOT EXISTS `#__djcf_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code` varchar(55) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `groups_restriction` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` int(11) NOT NULL,
  `usage_count` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;		
		
CREATE TABLE IF NOT EXISTS `#__djcf_coupons_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djcf_fields_values` 
	ADD `value_date_to` DATE NOT NULL ;
	
ALTER TABLE `#__djcf_fields_values_profile` 
	ADD `value_date_to` DATE NOT NULL ;
	
ALTER TABLE `#__djcf_fields`
	ADD `in_module` int(11) NOT NULL,
	ADD `in_search_on_start` int(11) NOT NULL;