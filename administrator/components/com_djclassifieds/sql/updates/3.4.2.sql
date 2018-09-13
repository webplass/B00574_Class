ALTER TABLE `#__djcf_items`
	ADD `offer` int(11) NOT NULL;		

ALTER TABLE `#__djcf_categories`
	ADD `price_special` FLOAT( 12, 2 ) NOT NULL,
	ADD `rev_group_id` int(11) NOT NULL;
		
ALTER TABLE `#__djcf_days`
	ADD `price_special` FLOAT( 12, 2 ) NOT NULL,
	ADD `price_renew_special` FLOAT( 12, 2 ) NOT NULL;
	
ALTER TABLE `#__djcf_promotions`
	ADD `price_special` FLOAT( 12, 2 ) NOT NULL;

ALTER TABLE `#__djcf_points`
	ADD `price_special` FLOAT( 12, 2 ) NOT NULL,
	ADD `description` TEXT NOT NULL ;
	
ALTER TABLE `#__djcf_plans`
	ADD `price_special` FLOAT( 12, 2 ) NOT NULL;
		
	 
ALTER TABLE `#__djcf_types` 
	ADD `price` FLOAT( 12, 2 ) NOT NULL ,
	ADD `price_special` FLOAT( 12, 2 ) NOT NULL ,
	ADD `points` INT NOT NULL ;
 
CREATE TABLE IF NOT EXISTS `#__djcf_groups_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(55) NOT NULL,
  `item_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `price` float(12,2) NOT NULL,
  `price_special` float(12,2) NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djcf_offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` float(12,2) NOT NULL,
  `currency` varchar(55) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `status` int(11) NOT NULL,
  `paid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__djcf_items_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8 ;


INSERT IGNORE INTO `#__djcf_emails` (`id`, `label`, `title`, `content`) VALUES  
	(21, 'COM_DJCLASSIFIEDS_ET_NEW_ADVERT_USER_GUEST', 'New advert', '<p>Hello,</p>\r\n<p> </p>\r\n<p>Your new advert: ''''[[advert_title_link]]''''</p>\r\n<p> </p>\r\n<p>Category: [[advert_category]]</p>\r\n<p> </p>\r\n<p>Status: [[advert_status]]</p>\r\n<p> </p>\r\n<p>Intro description: [[advert_intro_desc]]</p>\r\n<p> </p>\r\n<p>Edition link: [[advert_edit]]</p>\r\n<p> </p>\r\n<p>Remove link: [[advert_delete]]</p>'),
	(22, 'COM_DJCLASSIFIEDS_ET_NEW_POINTS_USER_NOTICE', 'New points on your account.', '<p>Hello,</p>\r\n<p> </p>\r\n<p>New points on your account.</p>\r\n<p> </p>\r\n<p>Points: [[points_value]]</p>\r\n<p> </p>\r\n<p>Description: [[points_description]]</p>'),
	(23, 'COM_DJCLASSIFIEDS_ET_PAYMENTS_ADMIN_POINTS_PAYMENT', 'Points payment notification', '<p>Hello</p>\r\n<p>New points payment for:  [[payment_item_name]]<br /><br />Points value: [[payment_price]]<br /><br />Payment Information:<br /><br />[[payment_info]]<br /><br /></p>'),
	(24, 'COM_DJCLASSIFIEDS_ET_BUYNOW_OFFER_BUYER_EMAIL', 'You made offer to product', '<p>Hello [[user_name]],</p>\r\n<p>&nbsp;</p>\r\n<p>Your made the purchase on the page [[advert_title_link]].</p>\r\n<p>Quantity : [[offer_quantity]].</p>\r\n<p>Price per product: [[offer_price]].</p>\r\n<p>Total price: [[offer_price_total]].</p>\r\n<p>Offer message: [[offer_message]].</p>\r\n<p>Please contact with advert author:</p>\r\n<p>[[advert_author_name]]</p>\r\n<p>[[advert_author_email]]</p>'),
	(25, 'COM_DJCLASSIFIEDS_ET_BUYNOW_OFFER_AUTHOR_EMAIL', 'New offer on your advert', '<p>Hello [[user_name]],</p>\r\n<p>&nbsp;</p>\r\n<p>New offer on your advert [[advert_title_link]].</p>\r\n<p>Offerer [[offerer_name]]</p>\r\n<p>Offerer email [[offerer_email]]</p>\r\n<p>Quantity : [[offer_quantity]].</p>\r\n<p>Price per product: [[offer_price]].</p>\r\n<p>Total price: [[offer_price_total]].</p>\r\n<p>Offer message: [[offer_message]].</p>'),
	(26, 'COM_DJCLASSIFIEDS_ET_BUYNOW_OFFER_OFFERER_CONTACT', 'Message from product owner', '<p>Hello [[user_name]],</p>\r\n<p>&nbsp;</p>\r\n<p>Message from [[advert_author_name]]</p>\r\n<p>owner of advert [[advert_title_link]].</p>\r\n<p>Response to your offer</p>\r\n<p>Quantity : [[offer_quantity]].</p>\r\n<p>Price per product: [[offer_price]].</p>\r\n<p>Total price: [[offer_price_total]].</p>\r\n<p>&nbsp;</p>\r\n<p>Offer status: [[offer_status]]</p>\r\n<p>Message from owner:</p>\r\n<p>[[offer_response]]</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>'),
	(27, 'COM_DJCLASSIFIEDS_ET_ASK_FORM_NOTIFICATION', 'New enquiry about advert', '<p>Hello [[user_name]],</p>\r\n<p> </p>\r\n<p>New enquiry regarding to ''''[[advert_title_link]]''''</p>\r\n<p>From</p>\r\n<p>User name: [[contact_author_name]]</p>\r\n<p>User email: [[contact_author_email]]</p>\r\n<p>Please check message content on our website</p>');
	