ALTER TABLE `#__djcf_offers` 
	ADD `confirmed` INT NOT NULL,
	ADD `request` INT NOT NULL,
	ADD `admin_paid` INT NOT NULL;	
	
	
CREATE TABLE `#__djcf_days_xref` (   
	`id` int(11) NOT NULL AUTO_INCREMENT,   
	`cat_id` int(11) NOT NULL,   
	`day_id` int(11) NOT NULL,   
	PRIMARY KEY (`id`) 
) DEFAULT CHARSET=utf8;	

ALTER TABLE `#__djcf_profiles` 
	ADD `verified` INT NOT NULL;	


INSERT IGNORE INTO `#__djcf_emails` (`id`, `label`, `title`, `content`) VALUES
(31, 'COM_DJCLASSIFIEDS_ET_BUYNOW_OFFER_AUTHOR_CONFIRMATION_EMAIL', 'Offer confirmation on your advert', '<p>Hello [[user_name]],</p>\r\n<p> </p>\r\n<p>Buyer confirmed service in offer from your advert [[advert_title_link]].</p>\r\n<p>Offerer [[offerer_name]]</p>\r\n<p>Offerer email [[offerer_email]]</p>\r\n<p>Total price: [[offer_price_total]].</p>\r\n<p>You can login to your account and request for payment.</p>'),
(32, 'COM_DJCLASSIFIEDS_ET_BUYNOW_OFFER_ADMIN_REQUEST_EMAIL', 'Offer Payment request', '<p>Hello,</p>\r\n<p> </p>\r\n<p>New request for offer from [[advert_title_link]].</p>\r\n<p>Author name [[advert_author_name]]</p>\r\n<p>Author email [[advert_author_email]]</p>\r\n<p>Total price: [[offer_price_total]].</p>');
