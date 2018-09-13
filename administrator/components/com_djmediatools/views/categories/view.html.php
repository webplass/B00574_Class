<?php
/**
 * @version $Id: view.html.php 99 2017-08-04 10:55:30Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

class DJMediatoolsViewCategories extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->ordering		= $this->get('Ordering');
		
		$categories = new DJMediatoolsModelCategories();
		$this->category_options	= $categories->getSelectOptions();
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		foreach($this->items as $item) {
			if(!$item->thumb = DJImageResizer::createThumbnail($item->image, 'media/djmediatools/cache', 60, 40, 'crop', 80)) {
				$item->thumb = 'administrator/components/com_djmediatools/assets/icon-48-category.png';
			}
			if(strcasecmp(substr($item->image, 0, 4), 'http') != 0 && !empty($item->image)) {
				$item->image = JURI::root(true).'/'.$item->image;
			}
			if(strcasecmp(substr($item->thumb, 0, 4), 'http') != 0 && !empty($item->thumb)) {
				$item->thumb = JURI::root(true).'/'.$item->thumb;
			}
		}
		
		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_DJMEDIATOOLS').' ›› '.JText::_('COM_DJMEDIATOOLS_CATEGORIES'), 'category');
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-category { background-image: url(components/com_djmediatools/assets/icon-48-category.png); }');
		
		if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_djimageslider/djimageslider.php')){
			$doc->addStyleDeclaration('.icon-32-import { background-image: url(components/com_djmediatools/assets/icon-32-import.png); }');
			JToolBarHelper::custom('items.import', 'import', '','COM_DJMEDIATOOLS_IMPORT_SLIDES', false);
			JToolBarHelper::divider();
		}
		
		JToolBarHelper::addNew('category.add','JTOOLBAR_NEW');
		JToolBarHelper::editList('category.edit','JTOOLBAR_EDIT');
		JToolBarHelper::deleteList(JText::_('COM_DJMEDIATOOLS_DELETE_ALBUM_CONFIRMATION'), 'categories.delete','JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_djmediatools', 550, 900);
		
	}
}