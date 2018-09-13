CREATE INDEX published ON `#__djcf_categories` (published);   
CREATE INDEX parent_id ON `#__djcf_categories` (parent_id);   
CREATE INDEX type_id ON `#__djcf_items` (type_id);
CREATE INDEX region_id ON `#__djcf_items` (region_id);
CREATE INDEX user_id ON `#__djcf_items` (user_id);
CREATE INDEX name ON `#__djcf_items` (name);
CREATE INDEX published ON `#__djcf_items` (published);
CREATE INDEX blocked ON `#__djcf_items` (blocked);
CREATE INDEX item_id ON `#__djcf_images` (item_id);
CREATE INDEX type ON `#__djcf_images` (type);
CREATE INDEX name ON `#__djcf_regions` (name);
CREATE INDEX parent_id ON `#__djcf_regions` (parent_id);
CREATE INDEX published ON `#__djcf_regions` (published);
CREATE INDEX item_id ON `#__djcf_items_categories` (item_id);
CREATE INDEX cat_id ON `#__djcf_items_categories` (cat_id);
CREATE INDEX item_id ON `#__djcf_items_promotions` (item_id);
CREATE INDEX prom_id ON `#__djcf_items_promotions` (prom_id);
CREATE INDEX date_exp ON `#__djcf_items_promotions` (date_exp);
CREATE INDEX name ON `#__djcf_fields` (name);
CREATE INDEX type ON `#__djcf_fields` (type);
CREATE INDEX published ON `#__djcf_fields` (published);
CREATE INDEX field_id ON `#__djcf_fields_values` (field_id);
CREATE INDEX item_id ON `#__djcf_fields_values` (item_id);	

CREATE TABLE IF NOT EXISTS `#__djcf_profiles` (
  `user_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `post_code` varchar(55) NOT NULL,
  `latitude` decimal(18,15) NOT NULL,
  `longitude` decimal(18,15) NOT NULL
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djcf_categories` 
	ADD `metarobots` text NOT NULL;
	
ALTER TABLE `#__djcf_items` 
	ADD `metarobots` text NOT NULL;	

INSERT IGNORE INTO `#__djcf_emails` (`id`, `label`, `title`, `content`) VALUES
(29, 'COM_DJCLASSIFIEDS_ET_ADVERT_RENEW_ADMINISTRATOR', 'Advert renew', '<p>Hello,</p>\r\n<p> </p>\r\n<p>Renewing of advert: \'\'[[advert_title_link]]\'\'</p>\r\n<p> </p>\r\n<p>Category: [[advert_category]]</p>\r\n<p> </p>\r\n<p>Author name: [[advert_author_name]]</p>\r\n<p>Author email: [[advert_author_email]]</p>\r\n<p> </p>\r\n<p>Intro description: [[advert_intro_desc]]</p>\r\n<p> </p>\r\n<p>Description:<br /> [[advert_desc]]</p>'),
(30, 'COM_DJCLASSIFIEDS_ET_NEW_ADVERT_BAD_WORD_ADMINISTRATOR', 'Bad word found', '<p>Hello,</p>\r\n<p> </p>\r\n<p>Bad word found in advert: ''''[[advert_title_link]]''''</p>\r\n<p></p>\r\n<p>Category: [[advert_category]]</p>\r\n<p></p>\r\n<p>Author name: [[advert_author_name]]</p>\r\n<p>Author email: [[advert_author_email]]</p>\r\n<p></p>\r\n<p>Intro description: [[advert_intro_desc]]</p>\r\n<p></p>\r\n<p>Description:<br /> [[advert_desc]]</p>');