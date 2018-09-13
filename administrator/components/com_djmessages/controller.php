<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */
 
defined ('_JEXEC') or die('Restricted access');

class DJMessagesController extends JControllerLegacy
{
	protected $default_view = 'cpanel';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/djmessages.php';
		JLoader::register('DJMessagesHelper', JPATH_ADMINISTRATOR . '/components/com_djmessages/helpers/djmessages.php');
		
		DJMessagesHelper::addSubmenu(JFactory::getApplication()->input->getCmd('view', 'cpanel'));
		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_djmessages/assets/css/adminstyle.css');

		parent::display($cachable, $urlparams);
		return $this;
	}
	
	function download_attachment() {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		if ($user->guest) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$msgId = $app->input->getInt('id');
		$fileName = $app->input->getBase64('file');
		
		if (!$msgId || !$fileName) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$model = $this->getModel('Message');
		$message = $model->getItem($msgId);
		
		if (empty($message) || empty($message->id)) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		if ($user->id != $message->user_to && $user->id != $message->user_from && !$user->authorise('core.admin', 'com_djmessages')) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		if (!DJMessagesHelperAttachment::getFile($message, base64_decode($fileName))){
			throw new Exception(JText::_('COM_DJMESSAGES_ATTACHMENT_NOT_FOUND'), 404);
		}
		
		$app->close();
	}
	
	public function multiupload() {
		$app = JFactory::getApplication();
		// todo: secure upload from injections
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_djmessages')
			&& !$user->authorise('djmsg.upload', 'com_djmessages') )
		{
			$app = JFactory::getApplication();
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			
			echo '{"jsonrpc" : "2.0", "error" : {"code": 403, "message": "'.JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN').'"}}';
			
			$app->close();
		}
		
		DJMessagesHelperAttachment::upload();
		
		return true;
	}
}