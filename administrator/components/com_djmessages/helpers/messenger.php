<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');

JTable::addIncludePath(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmessages/tables'));
require_once JPATH_ROOT.'/administrator/components/com_djmessages/helpers/route.php';
require_once JPATH_ROOT.'/administrator/components/com_djmessages/helpers/attachment.php';

jimport('joomla.mail.helper');

class DJMessagesHelperMessenger extends JObject
{
	
	protected static $users = array();
	
	protected static $templates = array();
	
	public static $classifieds_avatars = array();
	
	/**
	 * 
	 * @param string 	$message:   HTML body of the mesage
	 * @param string 	$subject:   optional message subject. If empty then default subject of the template will be used
	 * @param mixed 	$sender:    user's ID (int) or user's email (string) or array 
	 * @param mixed		$recipient: user's ID (int) or user's email (string)
	 * @param array 	$options:   associative array containing additional options such as type of notification (template)
	 *                              'type' => type of message / template
	 *                              'notify_recipient' => true|false, default: true
	 *                          
	 */
	public function notify($message, $subject, $sender, $recipient, $options = array(), $attachments = array()) {
		if (trim($message) == '') {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_EMPTY_MESSAGE'));
			return false;
		}
		
		$params = JComponentHelper::getParams('com_djmessages');
		
		$sender_user = (is_array($sender) && count($sender) == 2) ? static::getUser($sender[0]) : static::getUser($sender);
		$recipient_user = static::getUser($recipient);

		if (empty($sender_user) || !$sender_user->id) {
			$sender_name = '';
			$sender_email = '';
			
			if (is_array($sender) && count($sender) == 2) {
				$sender_email = $sender[0];
				$sender_name = $sender[1];
			} else {
				$sender_email = $sender;
			}
			
			if (JMailHelper::isEmailAddress($sender_email) == false) {
				$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_MISSING_SENDER'));
				return false;
			}
			
			if ($sender_name == '') {
				$tmp = explode('@', $sender_email, 2);
				$sender_name = $tmp[0];
			}
			
			$sender_user = new JUser();
			$sender_user->id = 0;
			$sender_user->sendEmail = 0;
			$sender_user->aid = 0;
			$sender_user->guest = 1;
			$sender_user->email = $sender_email;
			$sender_user->name = $sender_name;
		}
		
		if (!$recipient_user) {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_MISSING_RECIPIENT'));
			return false;
		}
		
		if (static::isUserBanned($sender_user->id)) {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_USER_IS_BANNED'));
			return false;
		}
		
		if (static::isUserBannedByUser($sender_user->id, $recipient_user->id) && $sender_user->authorise('core.admin', 'com_djmessages') == false) {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_USER_IS_BANNED_BY_USER'));
			return false;
		}

		if (static::isUserBanned($recipient_user->id) && $sender_user->authorise('core.admin', 'com_djmessages') == false) {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_RECIPIENT_IS_BANNED'));
			return false;
		}
		
		$template_type = isset($options['type']) ? $options['type'] : 'plain';
		$template = static::getTemplate($template_type);
		
		if (trim($subject) == '') {
			$subject = $template->subject;
		}
		
		$msg_source = (isset($options['source'])) ? $options['source'] : null;
		$msg_source_id = ($msg_source && isset($options['source_id'])) ? $options['source_id'] : 0;
		
		$parent_id = (!empty($options['parent'])) ? (int)$options['parent'] : 0;
		$reply_to_id = (!empty($options['reply_to'])) ? (int)$options['reply_to'] : 0;
		
		$table = JTable::getInstance('Message', 'DJMessagesTable', array());
		$data = array(
				'id' 				=> null,
				'parent_id'			=> $parent_id,
				'reply_to_id'		=> $reply_to_id,
				'subject' 			=> $subject,
				'message'			=> $message,
				'user_to'			=> $recipient_user->id,
				'recipient_name'	=> $recipient_user->name,
				'recipient_email'	=> $recipient_user->email,
				'user_from'			=> $sender_user->id,
				'sender_name'		=> $sender_user->name,
				'sender_email'		=> $sender_user->email,
				'msg_source' 		=> $msg_source,
				'msg_source_id'		=> $msg_source_id
		);
		
		if (is_array($attachments) && count($attachments) > 0) {
			$this->processAttachments($attachments, $data);
		}
		
		try {
			$this->saveMessage($table, $data);
		} catch (Exception $e) {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_COULDNT_SAVE_MESSAGE').' '.$e->getMessage());
			return false;
		}
		
		require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmessages/helpers/mailer.php');
		
		$link = null;
		$isSSL = JUri::getInstance()->isSSL();
		if (JFactory::getApplication()->isAdmin()) {
			//$link = DJMessagesHelperSiteRoute::buildRoute('getMessageRoute', array($table->id), true);
			$link = DJMessagesHelperSiteRoute::buildRoute('getMessagesRoute', array(), true);
		} else {
			//$link = JRoute::_(DJMessagesHelperRoute::getMessageRoute($table->id), true, $isSSL ? 1 : -1);
			$link = JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), true, $isSSL ? 1 : -1);
		}

		$mail_data = array(
				'notification_id' => $table->id,
				'subject' => $subject,
				'message' => $message,
				'sender_email' => $sender_user->email,
				'sender_name' => $sender_user->name,
				'recipient_email' => $recipient_user->email,
				'recipient_name' => $recipient_user->name,
				'sent_time' => $table->sent_time,
				'messages_url' => $link,
				'attachments' => $table->attachments
		);
		
		$notify_recipient = true;
		if (isset($options['notify_recipient'])) {
			$notify_recipient = (bool)$options['notify_recipient'];
		}
		$notified = ($notify_recipient) ? DJMessagesHelperMailer::send($mail_data, $template) : true;
		
		$notify_administrator = true;
		if (isset($options['notify_administrator'])) {
			$notify_administrator = (bool)$options['notify_administrator'];
		}
		
		if ($params->get('admin_notifications', 0) && $params->get('admin_notifications_user') && $notify_administrator) {
			$adminUser = static::getUserById((int)$params->get('admin_notifications_user'));
			if ($adminUser) {
				$template = static::getTemplate('admin_notification');
				$admin_data = $mail_data;
				$admin_data['recipient_email'] = $adminUser->email;
				$admin_data['subject'] = $template->subject.' - '.JText::sprintf('COM_DJMESSAGES_SUBJECT_COPY_OF', $mail_data['subject']);
				
				DJMessagesHelperMailer::send($admin_data, $template);
			}
		}
		
		if (!$notified) {
			$this->setError(JText::_('COM_DJMESSAGES_MESSENGER_ERROR_COULDNT_SEND_MAIL'));
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * 
	 * @param DJMessagesTableMessage $table
	 * @param array $data
	 */
	protected function saveMessage(&$table, &$data) {
		
		// Bind the data.
		if (!$table->bind($data))
		{
			throw new Exception($table->getError());
		}
		
		if ((int) $table->sent_time == 0)
		{
			$table->sent_time = JFactory::getDate()->toSql();
		}
		
		// Check the data.
		if (!$table->check())
		{
			throw new Exception($table->getError());
		}
		
		// Store the data.
		if (!$table->store())
		{
			throw new Exception($table->getError());
		}
		
		return true;
	}
	
	/**
	 * 
	 * @param int|string $data user ID or user email
	 * @return JUser
	 */
	public static function getUser($data) {
		$user = false;
		
		if (is_numeric($data)) {
			if ((int)$data > 0 ) {
				$user = static::getUserById($data);
			} else {
				return false;
			}
		} else if (is_string($data)) {
			$user = static::getUserByEmail(trim($data));
		}
		
		return $user;
	}
	
	/**
	 * 
	 * @param int $user_id
	 * @return JUser
	 */
	public static function getUserById($user_id, $skipChecks = false) {
		if (!$user_id) {
			return false;
		}
		if (!isset(static::$users[$user_id])) {
			
			$user = false;
			if ($skipChecks) {
				$user = new JUser($user_id);
			} else {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)->select('count(*)')->from('#__users')->where('id='. (int)$user_id);
				$db->setQuery($query);
				$result = $db->loadResult();
				if ($result) {
					$user = new JUser($user_id);
				}
			}
			
			static::$users[$user_id] = $user;
		}
		
		return static::$users[$user_id];
	}
	
	/**
	 *
	 * @param int $sender_id user ID
	 * @param int  $recipient_id user ID
	 * @return bool|string
	 */
	public static function isUserBanned($sender_id, $recipient_id = null) {
		
		if (!$sender_id) {
			$guest = new JUser();
			return $guest->authorise('djmsg.create', 'com_djmessages');
		}
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djmsg_users')->where('user_id=' . $sender_id);
		$db->setQuery($query);
		$data = $db->loadObject();
		
		if (!empty($data) && !$data->state) {
			// user banned globally
			return true;
		}
		
		if ($recipient_id === null) {
			// if recipient is not provided then we assume 
			// that this method is used only to check global ban
			return false;
		}
		
		return static::isUserBannedByUser($sender_id, $recipient_id);
	}
	
	/**
	 *
	 * @param int $sender_id user ID
	 * @param int  $recipient_id user ID
	 * @return bool|string
	 */
	public static function isUserBannedByUser($sender_id, $recipient_id) {
		$db = JFactory::getDbo();
	
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djmsg_banned')->where('user_id='.(int)$sender_id.' AND by_user='.(int)$recipient_id);
		$db->setQuery($query);
		$banInfo = $db->loadObject();
	
		if (empty($banInfo)) {
			return false;
		}
	
		return (trim($banInfo->reason)) == '' ? true : $banInfo->reason;
	}
	
	/**
	 * Allows to ban user by another user
	 * 
	 * @param int $user_id
	 * @param int $by_id
	 * @param string $reason
	 */
	public static function banUser($user_id, $by_id, $reason = '') {
		if ((int)$user_id == 0 || (int)$by_id == 0) {
			return false;
		}
		
		if (static::isUserBannedByUser($user_id, $by_id)) {
			// already banned
			return true;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('#__djmsg_banned')->columns(array('user_id', 'by_user', 'reason'));
		$query->values((int)$user_id.', '.(int)$by_id.', '.$db->quote($db->escape($reason)));
		$db->setQuery($query);
		
		return $db->execute();
	}
	
	/**
	 * Allows to unban user by another user
	 *
	 * @param int $user_id
	 * @param int $by_id
	 * @param string $reason
	 */
	public static function unBanUser($user_id, $by_id) {
		if ((int)$user_id == 0 || (int)$by_id == 0) {
			return false;
		}
	
		if (false === static::isUserBannedByUser($user_id, $by_id)) {
			// already banned
			return true;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__djmsg_banned')->where('user_id='.(int)$user_id.' AND by_user='.(int)$by_id);
		$db->setQuery($query);
	
		return $db->execute();
	}
	
	/**
	 * 
	 * @param string $email
	 * @return JUser
	 */
	public static function getUserByEmail($email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__users')->where('email LIKE '.$db->quote($db->escape(trim($email))));
		$db->setQuery($query);
		$user_id = $db->loadResult();
		
		return $user_id > 0 ? static::getUserById($user_id, true) : false;
	}
	
	/**
	 * 
	 * @param string $type
	 * @return object
	 */
	public static function getTemplate($type = null) {
		$type = trim($type);
	
		if ($type == '') {
			$type = 'plain';
		}
	
		$template = static::findTemplate($type);
		$plain_template = static::findTemplate('plain');
	
		if (!$template) {
			if ($type == 'plain') {
				$template = new stdClass();
				$template->body = '[[message]]';
				$template->subject = JText::_('COM_DJMESSAGES_NEW_MESSAGE_ARRIVED');
				$template->type = 'plain';
			} else {
				$template = clone $plain_template;
			}
		} else {
			if (is_object($template)) {
				$template = clone $template;
			} else {
				$template = clone $plain_template;
			}
		}
	
		return $template;
	}
	
	/**
	 * 
	 * @param string $type
	 * @return object|false $template or false
	 */
	protected static function findTemplate($type) {
		if (!isset(static::$templates[$type])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djmsg_templates')->where('type='.$db->quote($type));
			$db->setQuery($query);
			$template = $db->loadObject();
				
			if (empty($template) || !$template->state) {
				return false;
			}
				
			static::$templates[$type] = $template;
		}
	
		return static::$templates[$type];
	}
	
	public static function validateDate($date, $format = 'Y-m-d')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}
	
	public static function getUserAvatar($user_id) {
		if (!isset(static::$classifieds_avatars[$user_id])) {
			$has_avatar = false;
			$params = JComponentHelper::getParams('com_djclassifieds');
			if ((int)$user_id) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('*')->from('#__djcf_images')->where('item_id='.(int)$user_id.' AND type='.$db->quote('profile'));
				$db->setQuery($query);
				
				$profile = $db->loadObject();
				if ($profile) {
					static::$classifieds_avatars[$user_id] = JUri::root().$profile->path.$profile->name.'_ths.'.$profile->ext;
					$has_avatar = true;
				}
			}
			
			if (!$has_avatar) {
				static::$classifieds_avatars[$user_id] = JUri::root().'components/com_djclassifieds/assets/images/default_profile_s.png';
			}
		}
		
		return static::$classifieds_avatars[$user_id];
	}
	
	protected function processAttachments($attachments, &$data) {
		$fileInfos = array();
		$userTo = (!empty($data['user_to'])) ? $data['user_to'] : 0;
		
		foreach($attachments as $file) {
			$fileInfo = DJMessagesHelperAttachment::processFile($file, $userTo);
			if (is_array($fileInfo)) {
				$fileInfos[] = $fileInfo;
			}
		}
		
		$data['attachments'] = json_encode($fileInfos);
	}
}
