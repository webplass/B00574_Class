<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
 
defined ('_JEXEC') or die('Restricted access');

class DJMessagesViewMessages extends JViewLegacy
{
	protected $viewName = 'messages';
	protected $defaultPageTitle = 'COM_DJMESSAGES_TITLE_MESSAGES';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		
		$this->model = $this->getModel();
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->filters = $this->get('SourceFilters');
		
		$this->pagination = $this->model->getPagination();
		$this->pagination->setAdditionalUrlParam('format', '');
		
		$this->originItems = array('inbox' => array(), 'sent' => array(), 'archive'=>array(), 'trash'=>array());
		
		$this->origin = $this->state->get('filter.origin');
		$this->originItems[$this->origin] = $this->items;
		
		ob_start();
		parent::display('messages');
		$html = ob_get_contents();
		ob_end_clean();
		$js_state= array();
		$out = array('state'=> $js_state, 'html' => $html);
		
		echo json_encode($out);
		
	}
}