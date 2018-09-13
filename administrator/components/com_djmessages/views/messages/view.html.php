<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

/**
 * View class for a list of messages.
 *
 * @since  1.6
 */
class DJMessagesViewMessages extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->sidebar = JHtmlSidebar::render();

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = JHelperContent::getActions('com_djmessages');
		JToolbarHelper::title(JText::_('COM_DJMESSAGES_MESSAGES'), 'envelope inbox');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('message.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			
			if ($state->get('filter.only_me', 0) == 1 || $state->get('filter.only_me', 0) == 3) {
				JToolbarHelper::archiveList('messages.archive');
			} else if ($state->get('filter.only_me', 0) == 2) {
				JToolbarHelper::publish('messages.publish', 'COM_DJMESSAGES_TOOLBAR_MARK_AS_READ', true);
				JToolbarHelper::unpublish('messages.unpublish', 'COM_DJMESSAGES_TOOLBAR_MARK_AS_UNREAD', true);
				JToolbarHelper::archiveList('messages.archive');
			}
		}

		JToolbarHelper::divider();

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'messages.delete', 'JTOOLBAR_DELETE');
		}
		elseif ($canDo->get('core.edit.state') && $state->get('filter.only_me', 0) > 0)
		{
			JToolbarHelper::divider();
			JToolbarHelper::trash('messages.trash');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_djmessages');
		}
	}
}
