<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.plugin.plugin' );

require_once (JPath::clean(JPATH_ROOT  . '/administrator/components/com_djclassifieds/lib/djnotify.php'));
require_once (JPath::clean(JPATH_ROOT  . '/administrator/components/com_djmessages/helpers/messenger.php'));
require_once (JPath::clean(JPATH_ROOT  . '/components/com_djmessages/helpers/route.php'));

class plgDJClassifiedsmessageDJMessages extends JPlugin {
	public function __construct(& $subject, $config) {
		parent::__construct ( $subject, $config );
		$this->loadLanguage ();
		
		$lang = JFactory::getLanguage();
		
		if (JFactory::getApplication()->isSite()) {
			$lang->load('com_djmessages', JPATH_ROOT, 'en-GB', false, false);
			$lang->load('com_djmessages', JPath::clean(JPATH_ROOT.'/components/com_djmessages'), 'en-GB', false, false);
			$lang->load('com_djmessages', JPATH_ROOT, null, true, false);
			$lang->load('com_djmessages', JPath::clean(JPATH_ROOT.'/components/com_djmessages'), null, true, false);
		}
		else {
			$lang->load('com_djmessages', JPATH_ADMINISTRATOR, 'en-GB', false, false);
			$lang->load('com_djmessages', JPath::clean(JPATH_ROOT.'/administrator/components/com_djmessages'), 'en-GB', false, false);
			$lang->load('com_djmessages', JPATH_ADMINISTRATOR, null, true, false);
			$lang->load('com_djmessages', JPath::clean(JPATH_ROOT.'/administrator/components/com_djmessages'), null, true, false);
		}
	}
	
	function onDJClassifiedsSendMessage($item, $author, $mailto, $mailfrom, $fromname, $replyto, $replytoname, $subject, $message, $files, $custom_fields_msg) {
		$app = JFactory::getApplication ();
		$user = JFactory::getUser ();
		
		$breaks = array (
			"<br />",
			"<br>",
			"<br/>"
		);
		$message = str_ireplace ( $breaks, "\r\n", $message );
		
		$messenger = new DJMessagesHelperMessenger();

		$user_from = $user->id;
		if (!$user->id) {
			$user_from = array($replyto, $replytoname);
		}
		
		$data = array(
			'user_from' => $user_from,
			'user_to' => $item->user_id,
			'message' => $message . "\r\n\r\n" . $custom_fields_msg,
			'subject' => JText::sprintf('PLG_DJCLASSIFIEDSMESSAGE_DJMESSAGES_SUBJECT', $item->name)
		);
		
		$attachments = array();
		if (is_array($files) && isset($files['ask_file'])) {
			$attachments[] = $files['ask_file'];
		}
		
		$options = array(
			'type' => 'plain',
			'source' => 'com_djclassifieds.item',
			'source_id' => $item->id
		);
		
		$success = $messenger->notify($data['message'], $data['subject'], $data['user_from'], $data['user_to'], $options, $attachments);
		
		DJClassifiedsNotify::messageAskFormNotification ( $item, $author, $message, $files, $replyto, $replytoname, $custom_fields_msg );
		
		return true;
	}
	function onAdminAfterParseEmailBody(&$message, $message_id, $item, $buyer) {
		$app = JFactory::getApplication ();
		$db = JFactory::getDbo();
		
		if ($message_id == 7 || $message_id == 27) {
			
			$link = null;
			$isSSL = JUri::getInstance()->isSSL();
			if (JFactory::getApplication()->isAdmin()) {
				$link = DJMessagesHelperSiteRoute::buildRoute('getMessagesRoute', array(), true);
			} else {
				$link = JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), true, $isSSL ? 1 : -1);
			}
			
			$message = str_ireplace ( '[[contact_message_inputbox]]', $link, $message );
		}
		return null;
	}
	
	function onDJClassifiedsNotification ($sender_data, $recipient_data, $message_data, $custom_data = array()) {
		$messenger = new DJMessagesHelperMessenger();
		
		
		// Preparing Sender data. Important: system notification may not contain user_id, but email has to be provided!
		$sender = null;
		$senderName = '';
		$isSenderUser = false;
		if (!empty($sender_data['id'])) {
			$sender = $sender_data['id'];
			$isSenderUser = true;
		} else if (!empty($sender_data['email'])) {
			$sender = $sender_data['email'];
		}
		if (!empty($sender_data['name'])) {
			$senderName = $sender_data['name'];
		}
		
		if (trim($sender) == '') {
			return false;
		}
		
		if (!$isSenderUser) {
			$sender = array($sender, $senderName);
		}
		
		// Preparing recipient information. In this case we need user_id or email address.
		$recipient = null;
		if (!empty($recipient_data['id'])) {
			$recipient = $recipient_data['id'];
		} else if (!empty($sender['email'])) {
			$recipient = $recipient_data['email'];
		}
		if (trim($recipient) == '') {
			return false;
		}
		
		// Preparing message data - subject and message body itself
		if (!isset($message_data['message'])) {
			return false;
		}
		$message = $message_data['message'];
		$subject = isset($message_data['subject']) ? $message_data['subject'] : null;
		
		$this->prepareMessage($message, $sender, $recipient, $custom_data);
		$this->prepareSubject($subject, $sender, $recipient, $custom_data);
		
		return $messenger->notify($message, $subject, $sender, $recipient, array('type' => 'djclassifieds'));
	}
	
	protected function prepareMessage(&$message, $sender, $recipient, $custom_data) {
		/*$breaks = array (
		 "<br />",
		 "<br>",
		 "<br/>"
		 );
		 $message = str_ireplace ( $breaks, "\r\n", $message );
		 $message = strip_tags($message);*/
		//$message = str_ireplace('href=', 'target="_blank" href=', $message);
	}
	protected function prepareSubject(&$subject, $sender, $recipient, $custom_data) {
	}
}