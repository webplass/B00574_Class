<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 

defined('_JEXEC') or die;

class DJMessagesViewTemplate extends JViewLegacy
{
	protected $form;

	protected $item;

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
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
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
		if ($this->getLayout() == 'edit')
		{
			JFactory::getApplication()->input->set('hidemainmenu', true);
			JToolbarHelper::title(JText::_('COM_DJMESSAGES_CREATE_TEMPLATE'), 'envelope-opened new-privatemessage');
			JToolbarHelper::apply('template.apply');
			JToolbarHelper::save('template.save');
			JToolbarHelper::save2new('template.save2new');
			JToolbarHelper::save2copy('template.save2copy');
			JToolbarHelper::cancel('template.cancel');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_DJMESSAGES_VIEW_PRIVATE_MESSAGE'), 'envelope inbox');
			JToolbarHelper::cancel('template.cancel');
			JToolbarHelper::help('JHELP_COMPONENTS_MESSAGING_READ');
		}
	}
}
