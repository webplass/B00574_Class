<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
 
defined ('_JEXEC') or die('Restricted access');

class DJMessagesViewMessage extends JViewLegacy
{
	protected $viewName = 'message';
	protected $defaultPageTitle = 'COM_DJMESSAGES_TITLE_MESSAGE';
	protected $replyPageTitle = 'COM_DJMESSAGES_TITLE_NEW_MESSAGE';

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
		$user = JFactory::getUser();
		
		$this->params = $app->getParams();
		
		$model = JModelLegacy::getInstance('Form', 'DJMessagesModel');
		
		$this->item  = $model->getItem();
		
		if (!$this->item->id) {
			if ($user->authorise('djmsg.create', 'com_djmessages') == false) {
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);
				return false;
			}
		} else if ($this->item->user_from != $user->id && $this->item->user_to != $user->id) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);
			return false;
		}
		
		$secUser = ($this->item->id) ? $this->item->user_from : $app->input->getInt('send_to');
		
		$isBanned = DJMessagesHelperMessenger::isUserBanned($user->id, $secUser);
		
		$this->form = $isBanned ? false : $model->getForm();
		
		if (!$this->form && !$this->item->id) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);
			return false;
		}
		
		$this->state = $this->get('State');
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
			$heading = $this->item->id > 0 ? JText::_($this->defaultPageTitle) : JText::_($this->replyPageTitle);
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