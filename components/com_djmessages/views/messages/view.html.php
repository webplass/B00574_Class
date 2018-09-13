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
		$this->pagination = $this->model->getPagination();
		$this->filters = $this->get('SourceFilters');
		
		$this->originItems = array('inbox' => array(), 'sent' => array(), 'archive'=>array(), 'trash'=>array());
		
		$this->originItems[$this->state->get('filter.origin')] = $this->items;
		
		/*$controller = JControllerLegacy::getInstance('DJMessages');
		$formView = $controller->getView('Form', 'html', 'DJMessagesView');
		$formModel = $controller->getModel('Form', 'DJMessagesModel');
		$formView->document = JFactory::getDocument();
		$formView->setModel($formModel, true);
		$this->formView = $formView;
		*/
		
		$this->prepareDocument();
		
		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function prepareDocument()
	{
		$app           = JFactory::getApplication();
		$menus         = $app->getMenu();
		$this->pathway = $app->getPathway();
		$title         = null;

		// Because the application sets a default page title, we need to get it from the menu item itself
		$this->menu = $menus->getActive();

		$heading = null;

		if ($this->menu)
		{
			$heading = $this->params->get('page_title', $this->menu->title);
		}
		else
		{
			$heading = JText::_($this->defaultPageTitle);
		}

		$this->params->set('page_heading', $heading);

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}