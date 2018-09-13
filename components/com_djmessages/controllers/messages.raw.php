<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 

defined('_JEXEC') or die;

class DJMessagesControllerMessages extends JControllerLegacy
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Define standard task mappings.
		
		$this->registerTask('read', 'publish');
		
		$this->registerTask('unread', 'publish');

		// Value = 0
		$this->registerTask('unpublish', 'publish');

		// Value = 2
		$this->registerTask('archive', 'publish');

		// Value = -2
		$this->registerTask('trash', 'publish');
		
		$this->registerTask('unban', 'ban');

	}
	
	public function getModel($name = 'Form', $prefix = 'DJMessagesModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	
	public function publish()
	{
		$app = JFactory::getApplication();
		// Get items to publish from the request.
		$cid = $app->input->get('msg_id', array(), 'array');
		$data = array('read' => 1, 'unread' => 0, 'archive' => 2, 'trash' => -2);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');
	
		if (empty($cid))
		{
			$this->setMessage('COM_DJMESSAGES_UI_NO_ITEM_SELECTED', 'error');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
	
			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
	
			// Publish the items.
			try
			{
				$model->publish($cid, $value);
				$errors = $model->getErrors();
				
				if ($errors)
				{
					$this->setMessage(JText::plural('COM_DJMESSAGES_UI_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
				} else {
					if ($value == 1)
					{
						$ntext = 'COM_DJMESSAGES_UI_N_ITEMS_PUBLISHED';
					}
					elseif ($value == 0)
					{
						$ntext = 'COM_DJMESSAGES_UI_N_ITEMS_UNPUBLISHED';
					}
					elseif ($value == 2)
					{
						$ntext = 'COM_DJMESSAGES_UI_N_ITEMS_ARCHIVED';
					}
					else
					{
						$ntext = 'COM_DJMESSAGES_UI_N_ITEMS_TRASHED';
					}
					
					$this->setMessage(JText::plural($ntext, count($cid)));
				}
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}
		
		$response = array();
		if ($this->messageType == 'error') {
			$response['error'] = true;
			$response['message'] = $this->message;
		} else {
			$response['error'] = false;
			$response['message'] = $this->message;
		}
		
		echo json_encode($response);
		$app->close();
		
	}
	
	public function ban()
	{
		$app = JFactory::getApplication();
		// Get items to publish from the request.
		$user_id = $app->input->getInt('user_id');
		$by_id = JFactory::getUser()->id;
		$tmpl = $app->input->getCmd('tmpl');
		$tmplSfx = '';
		if ($tmpl == 'component') {
			$tmplSfx = '&tmpl=component';
		}
		
		$data = array('ban' => 1, 'unban' => 0);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 1, 'int');
	
		if (!$user_id || !$by_id)
		{
			$this->setMessage('COM_DJMESSAGES_UI_NO_USER_SELECTED', 'error');
		}
		else
		{
			$success = false;
			if ($value) {
				$success = DJMessagesHelperMessenger::banUser($user_id, $by_id);
			} else {
				$success = DJMessagesHelperMessenger::unBanUser($user_id, $by_id);
			}
	
			if (!$success)
			{
				if ($value == 1) {
					$this->setMessage(JText::_('COM_DJMESSAGES_UI_USER_FAILED_BANNING'), 'error');
				} else {
					$this->setMessage(JText::_('COM_DJMESSAGES_UI_USER_FAILED_UNBANNING'), 'error');
				}
			} else {
				if ($value == 1)
				{
					$this->setMessage(JText::_('COM_DJMESSAGES_UI_USER_BANNED_SUCCESSFULLY'));
				}
				else
				{
					$this->setMessage(JText::_('COM_DJMESSAGES_UI_USER_UNBANNED_SUCCESSFULLY'));
				}
					
			}
		}
		
		
		if ($tmpl == 'component') {
			$html = '<html><head>';
			$html .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
			$html .= '<script>
					if (window.self !== window.top ) {
						if (typeof window.top.DJMessagesUI != "undefined") {
							//window.top.DJMessagesUI.closeForm();
							window.top.DJMessagesUI.pushMessage("'.$this->message.'");
							window.top.DJMessagesUI.closeMessage();
						} else {
							window.top.location.href = "'.JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false).'";
						}
					} else {
						window.top.location.href = "'.JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false).'";
					}
					</script>';
			$html .= '</head><body></body></html>';
				
			echo $html;
			$app->close();
		}
		
		$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false));
		return true;
	
	}
	
	protected function allowReply($data) {
		if (empty($data['reply_to'])) {
			return false;
		}
		$user = JFactory::getUser();
		
		if ($user->guest) {
			return false;
		}
		
		if ($user->authorise('djmsg.reply', 'com_djmessages') == false) {
			return false;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djmsg_messages')->where('id='.(int)$data['reply_to']);
		$db->setQuery($query);
		$message = $db->loadObject();
		if (empty($message)) {
			return false;
		}
		if ($message->user_from != $user->id && $message->user_to != $user->id) {
			return false;
		}
		
		$recipient = DJMessagesHelperMessenger::getUserById($message->user_from);
		
		if (!$recipient) {
			return false;
		}
		
		return $message;
	}
	
	protected function allowSend($data) {
		if (empty($data['send_to_id'])) {
			return false;
		}
		
		$user = JFactory::getUser();
		
		if ($user->guest) {
			return false;
		}
		
		if ($user->authorise('djmsg.create', 'com_djmessages') == false) {
			return false;
		}
		
		$recipient = DJMessagesHelperMessenger::getUserById($data['send_to_id']);
		
		if (!$recipient) {
			return false;
		}
		
		return $recipient->id;
	}
	
	public function reply() {
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$context = 'com_djmessages.edit.message';
		$model = $this->getModel();
		
		$source = $app->input->getCmd('source');
		
		$tmpl = $app->input->getCmd('tmpl');
		$tmplSfx = '';
		if ($tmpl == 'component') {
			$tmplSfx = '&tmpl=component';
		}
		
		$data = $app->input->get('jform', array(), 'array');
		$data['id'] = null;
		
		$reply_to = $data['reply_to'];
		
		$replyToMsg = $this->allowReply($data);
		
		if (empty($replyToMsg))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute($reply_to) . $tmplSfx, false));
			return false;
		}
		
		$recipient_id = $replyToMsg->user_from;
		
		$data['reply_to_id'] = $replyToMsg->id;
		$data['parent_id'] = $replyToMsg->parent_id ? $replyToMsg->parent_id : $replyToMsg->id;
		
		$data['msg_source'] = $replyToMsg->msg_source;
		$data['msg_source_id'] = $replyToMsg->msg_source_id;
		
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute($reply_to) . $tmplSfx, false));
			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			
			$msgErrors = array();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					
					$msgErrors[] = ($errors[$i]->getMessage());
				}
				else
				{
					$msgErrors[] = ($errors[$i]);
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			$this->setMessage(implode('<br />', $msgErrors), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute($reply_to) . $tmplSfx, false));
			return false;
		}
		
		$validData['user_to'] = $recipient_id;
		
		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);
		
			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute($reply_to) . $tmplSfx, false));
		
			return false;
		}
		
		$app->setUserState($context . '.data', null);
		
		// IF OK
		if ($source == 'frame') {
			$html = '<html><head>';
			$html .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
			$html .= '<script>
					if (window.self !== window.top ) { 
						if (typeof window.top.DJMessagesUI != "undefined") {
							//window.top.DJMessagesUI.closeForm();
							window.top.DJMessagesUI.closeMessage();
						} else {
							window.top.location.href = "'.JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false).'";
						}
					} else {
						window.top.location.href = "'.JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false).'";
					}
					</script>';
			$html .= '</head><body></body></html>';
			
			echo $html;
			$app->close();
		} else {
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false));
		}
		
		return true;
	}
	
	public function create() {
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$context = 'com_djmessages.edit.message';
		$model = $this->getModel();
		
		$source = $app->input->getCmd('source');
		
		$tmpl = $app->input->getCmd('tmpl');
		$tmplSfx = '';
		if ($tmpl == 'component') {
			$tmplSfx = '&tmpl=component';
		}
		
		$data = $app->input->get('jform', array(), 'array');
		$data['id'] = null;
		
		$send_to = $data['send_to_id'];
		
		$recipient_id = $this->allowSend($data);
		
		if (!$recipient_id || !$send_to)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessagesRoute() . $tmplSfx, false));
			return false;
		}
		
		$form = $model->getForm($data, false);
		
		if (!$form)
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute(0).'&send_to=' .$send_to . $tmplSfx, false));
			return false;
		}
		
		// Test whether the data is valid.
		$validData = $model->validate($form, $data);
		
		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			
			$msgErrors = array();
			
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					
					$msgErrors[] = ($errors[$i]->getMessage());
				}
				else
				{
					$msgErrors[] = ($errors[$i]);
				}
			}
			
			// Save the data in the session.
			$app->setUserState($context . '.data', $data);
			
			$this->setMessage(implode('<br />', $msgErrors), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute(0).'&send_to=' .$send_to . $tmplSfx, false));
			return false;
		}
		
		$validData['user_to'] = $recipient_id;
		
		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);
			
			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessageRoute(0).'&send_to=' .$send_to . $tmplSfx, false));
			
			return false;
		}
		
		$app->setUserState($context . '.data', null);
		
		// IF OK
		if ($source == 'frame') {
			$html = '<html><head>';
			$html .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
			$html .= '<script>
					if (window.self !== window.top ) {
						if (typeof window.top.DJMessagesUI != "undefined") {
							//window.top.DJMessagesUI.closeForm();
							window.top.DJMessagesUI.closeMessage();
						} else {
							window.top.location.href = "'.JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false).'";
						}
					} else {
						window.top.location.href = "'.JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false).'";
					}
					</script>';
			$html .= '</head><body></body></html>';
			
			echo $html;
			$app->close();
		} else {
			$this->setRedirect(JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false));
		}
		
		return true;
	}
}