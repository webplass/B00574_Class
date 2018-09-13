<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');

class DJMessagesController extends JControllerLegacy
{
	
	protected $default_view = 'messages';
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		$this->input = JFactory::getApplication()->input;

		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = array())
	{
		$cachable = true;
		
		$viewName = JFactory::getApplication()->input->getCmd('view', 'messages');
		$this->input->set('view', $viewName);
		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		if ($viewName == 'messages' || $viewName == 'message')
		{
			$cachable = false;
		}
		
		$urlparams = array(
				'id' => 'INT',
				'limit' => 'UINT',
				'limitstart' => 'UINT',
				'filter_search' => 'STRING',
				'filter_author' => 'UINT',
				'filter_status' => 'STRING',
				'filter_order' => 'STRING',
				'filter_order_Dir' => 'STRING',
				'task' => 'CMD',
				'start' => 'UINT',
				'send_to' => 'UINT',
				'return' => 'BASE64',
				'file' => 'BASE64',
				'print' => 'BOOLEAN',
				'Itemid' => 'INT');
		
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$id = $app->input->getInt('id');
	
		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
	
		// Get/Create the model
		
		$modelName = $viewName;
		
		if ($viewName == 'messages' || $viewName == 'form' || $viewName == 'message') {
			if ($user->guest) {
				$returnUrl = (string)JUri::getInstance();
				$msg = JText::_('COM_DJMESSAGES_PLEASE_LOGIN_FIRST');
				
				$tmpl = $this->input->getCmd('tmpl');
				
				if (($viewName == 'form' || $viewName == 'message') && $tmpl == 'component') {
					$html = '<html><head>';
					$html .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
					$html .= '<script>
					if (window.self !== window.top ) {
						window.top.location.href = "'.JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false)), false).'";
					}
					</script>';
					$html .= '</head><body></body></html>';
						
					echo $html;
					$app->close();
				} else {
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($returnUrl), false), $msg, 'notice');
					return true;
				}
			}
		}
		
		if ($model = $this->getModel($modelName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
	
		$view->document = $document;
		
		DJMessagesHelper::loadCss();
	
		$conf = JFactory::getConfig();
	
		// Display the view
		if ($cachable && $viewType != 'feed' && $conf->get('caching') >= 1)
		{
			$option = $this->input->get('option');
			$cache = JFactory::getCache($option, 'view');
	
			if (is_array($urlparams))
			{
				$app = JFactory::getApplication();
	
				if (!empty($app->registeredurlparams))
				{
					$registeredurlparams = $app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new stdClass;
				}
	
				foreach ($urlparams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}
	
				$app->registeredurlparams = $registeredurlparams;
			}
	
			$cache->get($view, 'display');
		}
		else
		{
			$view->display();
		}
	
		return $this;
	}
	
	function getUsers() {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		if ($user->guest) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$order = $app->input->getCmd('sort', 'name-asc');
		$limit = $app->input->getInt('limit', 10);
		$offset = $app->input->getInt('offset', 0);
		
		$limit = min(100, $limit);
		
		$data = DJMessagesHelper::getUsers($user->id, $order, $limit, $offset);
		
		echo json_encode($data);
		
		$app->close();
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
		
		$model = $this->getModel('Form');
		$message = $model->getItem($msgId);
		
		if (empty($message) || empty($message->id)) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		if ($user->id != $message->user_to && $user->id != $message->user_from && !$user->authorise('core.admin', 'com_djmessages')) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$attachments = DJMessagesHelperAttachment::getFiles($message);
		if (empty($attachments)) {
			throw new Exception(JText::_('COM_DJMESSAGES_ATTACHMENT_NOT_FOUND'), 404);
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
