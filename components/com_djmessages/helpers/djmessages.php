<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 */
use Joomla\Registry\Registry;

defined ('_JEXEC') or die('Restricted access');

class DJMessagesHelper {
	
	protected static $users = array();
	
	static $languageLoaded = false;
	static $cssLoaded = false;
	
	public static function getUsers($ignore = null, $orderby = 'name-asc', $limit = 0, $start = 0) {
		$hash = md5($orderby.$limit.$start);
		
		if (isset(static::$users[$hash])) {
			return $users[$hash];
		}
		
		$plugin = JPluginHelper::getPlugin('system', 'djmessages');
		
		$defVisible = false;
		if (!empty($plugin)) {
			$plgParams = new Registry($plugin->params);
			$defVisible = $plgParams->get('default_visible', 0);
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('u.id, u.name, u.username, u.email, mu.visible AS mu_visible, mu.state AS mu_state');
		$query->from('#__users AS u');
		if ($defVisible) {
			$query->join('left', '#__djmsg_users AS mu ON mu.user_id = u.id');
			$query->where('( (mu.visible IS NULL AND mu.state IS NULL) OR (mu.visible=1 AND mu.state=1) )');
		} else {
			$query->join('inner', '#__djmsg_users AS mu ON mu.user_id = u.id');
			$query->where('mu.visible=1 AND mu.state=1');
		}
		$query->where('u.block=0');
		
		if (is_null($ignore) == false) {
			if (is_array($ignore)) {
				JArrayHelper::toInteger($ignore);
				$query->where('u.id NOT IN ('.implode(',', $ignore).')');
			} else if (is_scalar($ignore)) {
				$query->where('u.id != '.(int)$ignore);
			}
		}
		
		switch ($orderby) {
			case 'id-asc' : {
				$query->order('u.id ASC');
				break;
			}
			case 'id-desc' : {
				$query->order('u.id DESC');
				break;
			}
			case 'login-asc' : {
				$query->order('u.username ASC');
				break;
			}
			case 'login-desc' : {
				$query->order('u.username DESC');
				break;
			}
			case 'registration-asc' : {
				$query->order('u.registerDate ASC');
				break;
			}
			case 'registration-desc' : {
				$query->order('u.registerDate DESC');
				break;
			}
			case 'name-desc' : {
				$query->order('u.name DESC');
				break;
			}
			case 'name-asc' :
			default: {
				$query->order('u.name ASC');
				break;
			}
		}
		
		
		$db->setQuery($query, $start, $limit);
		$users = $db->loadObjectList();
		
		foreach($users as &$user) {
			$user->_msg_link = JRoute::_(DJMessagesHelperRoute::getMessageRoute().'&send_to=' . $user->id);
		}
		unset($user);
		
		$query->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');
		$db->setQuery($query);
		$total = $db->loadResult();
		
		static::$users[$hash] = array('total' => $total, 'users' => $users);
		
		return static::$users[$hash];
	}
	
	public static function loadComponentLanguage() {
		if (!self::$languageLoaded /*&& JFactory::getApplication()->input->getCmd('option') != 'com_djmessages'*/) {
			
			$lang = JFactory::getLanguage();
			
			if ($lang->getTag() != 'en-GB') {
				$lang->load('com_djmessages', JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmessages'), 'en-GB', false, false);
				$lang->load('com_djmessages', JPATH_ADMINISTRATOR, 'en-GB', false, false);
				$lang->load('com_djmessages', JPath::clean(JPATH_ROOT.'/components/com_djmessages'), 'en-GB', false, false);
				$lang->load('com_djmessages', JPATH_ROOT, 'en-GB', false, false);
			}
			
			$lang->load('com_djmessages', JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmessages'), null, true, false);
			$lang->load('com_djmessages', JPATH_ADMINISTRATOR, null, true, false);
			$lang->load('com_djmessages', JPath::clean(JPATH_ROOT.'/components/com_djmessages'), null, true, false);
			$lang->load('com_djmessages', JPATH_ROOT, null, true, false);
			
			self::$languageLoaded = true;
		}
	}
	
	public static function loadCss() {
		if (!self::$cssLoaded) {
			JFactory::getDocument()->addStyleSheet(JUri::root(true).'/components/com_djmessages/assets/css/style.css');
			if (JFile::exists(JPATH_ROOT.'/components/com_djmessages/assets/css/custom.css')) {
				JFactory::getDocument()->addStyleSheet(JUri::root(true).'/components/com_djmessages/assets/css/custom.css');
			}
			self::$cssLoaded = true;
		}
	}
}