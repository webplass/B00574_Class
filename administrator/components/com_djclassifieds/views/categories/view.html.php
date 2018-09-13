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
defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


class DjClassifiedsViewCategories extends JViewLegacy
{
	protected $pagination;
	
	function display($tpl = null)
	{
		$this->state			= $this->get('State');
		$this->categories		= $this->get('Categories');		
		$this->countCategories	= $this->get('CountCategories');
		
		
		$model = $this->getModel();
		$this->categories = $model->getCategories(); 
		
		jimport('joomla.html.pagination');		
		//$limit = JRequest::getVar('limit', '25', '', 'int');
		//$limitstart = JRequest::getVar('limitstart', '0', '', 'int');		
		//$pagination = new JPagination($this->countCategories, $limitstart, $limit);
		$pagination = new JPagination($this->countCategories, 0, 0);
		$this->pagination  = $pagination;
				


		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();
		if (class_exists('JHtmlSidebar')){
			$this->sidebar = JHtmlSidebar::render();
		}
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		$user = JFactory::getUser();
		JToolBarHelper::title(JText::_('COM_DJCLASSIFIEDS_CATEGORIES'), 'generic.png');
		if ($user->authorise('core.create', 'com_djclassifieds')) {
			JToolBarHelper::addNew('category.add','JTOOLBAR_NEW');
		}
		if ($user->authorise('core.edit', 'com_djclassifieds')) {
			JToolBarHelper::editList('category.edit','JTOOLBAR_EDIT');
		}
		if ($user->authorise('core.admin', 'com_djclassifieds')) {
			JToolBarHelper::custom('categories.recreateThumbnails','move','move',JText::_('COM_DJCLASSIFIEDS_RECREATE_THUMBNAILS'),true,true);
			JToolBarHelper::custom('categories.regenerateAliases','refresh','refresh',JText::_('COM_DJCLASSIFIEDS_GENERATE_ALIASES'),false,true);
		}
		if ($user->authorise('core.delete', 'com_djclassifieds')) {
			JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_DELETE');
		}		
		JToolBarHelper::divider();
		if ($user->authorise('core.admin', 'com_djclassifieds')) {
			JToolBarHelper::preferences('com_djclassifieds', 450, 800);
			JToolBarHelper::divider();
		}
	}
}
?>