ALTER TABLE `#__djcf_categories` 
	ADD `metatitle` text NOT NULL;

ALTER TABLE `#__djcf_profiles`
	ADD `group_id` INT NOT NULL default '0';	
	
ALTER TABLE `#__djcf_fields`
	ADD `group_id` INT NOT NULL default '0';
	
CREATE TABLE IF NOT EXISTS `#__djcf_fields_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `groups_assignment` text NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`) 
) DEFAULT CHARSET=utf8;	
		
	