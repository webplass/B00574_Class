<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage 	DJ Classifieds Component
* @copyright 	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license 		http://www.gnu.org/licenses GNU/GPL
* @author 		url: http://design-joomla.eu
* @author 		email contact@design-joomla.eu
* @developer 	Łukasz Ciastek - lukasz.ciastek@design-joomla.eu
*
*
* DJ Classifieds is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ Classifieds is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ Classifieds. If not, see <http://www.gnu.org/licenses/>.
*
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class DJClassifiedsViewPlan extends JViewLegacy {
	protected $state;
	protected $item;
	protected $form;
	
	public function display($tpl = null)
	{
		
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->groups_restriction = $this->get('GroupsRestriction');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		$document= JFactory::getDocument();
		$document->addScript(JURI::base() . '/components/com_djclassifieds/models/fields/cfcolor/jscolor.js');
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		//JRequest::setVar('hidemainmenu', true);
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);

		$text = $isNew ? JText::_( 'COM_DJCLASSIFIEDS_NEW' ) : JText::_( 'COM_DJCLASSIFIEDS_EDIT' );
		JToolBarHelper::title(   JText::_( 'COM_DJCLASSIFIEDS_PLAN' ).': <small><small>[ ' . $text.' ]</small></small>', 'generic.png' );
		
		JToolBarHelper::apply('plan.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('plan.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::custom('plan.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		JToolBarHelper::custom('plan.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		JToolBarHelper::cancel('plan.cancel', 'JTOOLBAR_CANCEL');
	}
}
?>