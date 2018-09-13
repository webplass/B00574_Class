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
class DJMessagesViewUsers extends JViewLegacy
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
		JToolbarHelper::title(JText::_('COM_DJMESSAGES_USERS'), 'users user');

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publish('users.publish', 'COM_DJMESSAGES_STATE_UNBAN', true);
			JToolbarHelper::unpublish('users.unpublish', 'COM_DJMESSAGES_STATE_BAN', true);
			JToolbarHelper::publish('users.visible', 'COM_DJMESSAGES_STATE_VISIBLE', true);
			JToolbarHelper::unpublish('users.invisible', 'COM_DJMESSAGES_STATE_INVISIBLE', true);
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_djmessages');
		}
	}
}
