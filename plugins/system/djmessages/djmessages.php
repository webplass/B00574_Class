<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class plgSystemDJMessages extends JPlugin
{
	protected $autoloadLanguage = true;
	
	function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		
		if (!($form instanceof JForm))
		{
			return;
		}
		
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}
		
		if (JFactory::getUser()->get('requireReset', 0)) {
			return true;
		}
		
		$registration_enable = $this->params->get('registration_enable', 0);
		$frontend_enable = $this->params->get('frontend_enable', 0);
		
		if ($app->isSite()) {
			if ($frontend_enable == false) {
				return;
			}
			if ($name == 'com_users.registration' && $registration_enable == false) {
				return;
			}
		}
		
		JForm::addFormPath(JPath::clean(JPATH_ROOT.'/plugins/system/djmessages/forms/'));
		$form->loadFile('usersettings', true);
		
		$allow = $this->params->get('default_state', 0);
		$visible = $this->params->get('default_visible', 0);
		
		$userId = null;
		if (is_object($data)){
			$userId = isset($data->id) ? $data->id : 0;
		} else if (is_array($data)) {
			$userId = isset($data['id']) ? $data['id'] : 0;
		}
		
		if ($userId) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djmsg_users')->where('user_id='.$userId);
			$db->setQuery($query);
			
			$msgUser = $db->loadObject();
			
			if (!empty($msgUser)) {
				$allow = $msgUser->state;
				$visible = $msgUser->visible;
			}
		}
		
		$form->setFieldAttribute('djmsg_state', 'default', $allow, 'params');
		$form->setFieldAttribute('djmsg_visible', 'default', $visible, 'params');
		
		if (is_object($data) && isset($data->params) && is_array($data->params)) {
			$data->params['djmsg_state'] = $allow;
			$data->params['djmsg_visible'] = $visible;
		} else if (is_array($data) && isset($data['params'])&& is_array($data['params'])) {
			$data['params']['djmsg_state'] = $allow;
			$data['params']['djmsg_visible'] = $visible;
		}
		
		if (!JHtml::isRegistered('users.djmsg_state'))
		{
			JHtml::register('users.djmsg_state', array(__CLASS__, 'htmlBoolean'));
		}
		
		if (!JHtml::isRegistered('users.djmsg_visible'))
		{
			JHtml::register('users.djmsg_visible', array(__CLASS__, 'htmlBoolean'));
		}
	}
	
	public static function htmlBoolean($value) {
		if ($value == '') {
			return JHtml::_('users.value', $value);
		}
		
		return $value ? JText::_('JYES') : JText::_('JNO');
	}
	
	function onContentPrepareData($context, $data)
	{
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}
	}
	
	function onUserAfterSave($data, $isNew, $result, $error)
	{
		if (empty($data['params']) || empty($data['id'])) {
			return;
		}
		
		$userId = (int)$data['id'];
		
		$params = new Registry($data['params']);
		
		$allow = $params->get('djmsg_state', $this->params->get('default_state', 0));
		$visible = $params->get('djmsg_visible', $this->params->get('default_visible', 0));
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djmsg_users')->where('user_id='.$userId);
		$db->setQuery($query);
		
		$msgUser = $db->loadObject();
		
		$msgNew = $isNew;
		if (!empty($msgUser)) {
			$msgNew = false;
		} else {
			$msgNew = true;
			$msgUser = new stdClass();
			$msgUser->user_id = $userId;
		}
		
		$msgUser->state = $allow;
		$msgUser->visible = $allow ? $visible : 0;
		
		if ($msgNew) {
			$db->insertObject('#__djmsg_users', $msgUser);
		} else {
			$db->updateObject('#__djmsg_users', $msgUser, 'user_id', true);
		}
		
		return;
	}
	
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return;
		}
		
		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');
		$db = JFactory::getDbo();
		if ($userId)
		{
			try
			{
				$query = $db->getQuery(true);
				$query->delete('#__djmsg_users');
				$query->where('user_id='.$userId);
				$db->execute();
				
				$query = $db->getQuery(true);
				$query->delete('#__djmsg_banned');
				$query->where('user_id='.$userId);
				$db->execute();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				return false;
			}
		}
	}
	
	/*function onDJMessagesSourceFiltersPrepare(&$filters, $source = '', $source_id = 0) {
	 if (count($filters) < 1) return;
	 
	 $lang = JFactory::getLanguage();
	 
	 foreach($filters[0] as $key => &$option) {
	 if ($option->value == '') continue;
	 $constant = 'PLG_SYSTEM_DJMESSAGES_SOURCE_' . JString::strtoupper(str_replace(array('.', '-'), '_', $option->text));
	 if ($lang->hasKey($constant)) {
	 $option->text = JText::_($constant);
	 }
	 }
	 unset($option);
	 
	 if (isset($filters[1]) && $source != '') {
	 $sourceName =  'sourceOptions' . ucfirst(preg_replace('/[^A-Z0-9_]/i', '', $source));
	 if (method_exists($this, $sourceName)) {
	 $this->$sourceName($filters[1]);
	 }
	 }
	 }
	 
	 protected function sourceOptionsCom_djclassifiedsitem(&$options) {
	 $ids = array();
	 $lang = JFactory::getLanguage();
	 
	 foreach($options as &$option) {
	 if ($option->value > 0) {
	 $ids[] = (int)$option->value;
	 } else {
	 if ($lang->hasKey('PLG_SYSTEM_DJMESSAGES_SOURCE_COM_DJCLASSIFIEDS_ITEM_SELECT')) {
	 $option->text = JText::_('PLG_SYSTEM_DJMESSAGES_SOURCE_COM_DJCLASSIFIEDS_ITEM_SELECT');
	 }
	 }
	 }
	 unset($option);
	 
	 if (count($ids) > 0) {
	 $db = JFactory::getDbo();
	 $query = $db->getQuery(true);
	 $query->select('id, name')->from('#__djcf_items')->where('id IN ('.implode(',', $ids).')');
	 $db->setQuery($query);
	 $items = $db->loadObjectList('id');
	 
	 if (!empty($items)) {
	 foreach($options as &$option) {
	 if ($option->value && isset($items[$option->value])) {
	 $option->text = $items[$option->value]->name;
	 }
	 }
	 unset($option);
	 }
	 }
	 }*/
}