<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Component
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
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


class DJclassifiedsViewGhostad extends JViewLegacy{
		
	public function __construct($config = array())
	{
		parent::__construct($config);				
		
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/ghostad');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$theme = $par->get('theme','default');
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/ghostad');
		}
	}	
	
	function display($tpl = null){
		
		$par 	    = JComponentHelper::getParams( 'com_djclassifieds' );
		$document   = JFactory::getDocument();
		$app	    = JFactory::getApplication();
		$theme 		= $par->get('theme','default');
		$user 		= JFactory::getUser();
		
		JTable::addIncludePath(JPATH_ROOT.'/administrator/components/com_djclassifieds/tables');
		$item = JTable::getInstance('GhostAds', 'DJClassifiedsTable');
		
		$item_id = $app->input->getInt('id');
		$item->load(array('item_id'=>$item_id));
		
		if($item->user_id!=$user->id && $item->access_view){		
			$groups_acl = ','.implode(',', $user->getAuthorisedViewLevels()).',';
			if(!strstr($groups_acl,','.$item->access_view.',')){
				DJClassifiedsTheme::djAccessRestriction();
			}
		}
		
		$document->setTitle($item->name);										
		
		$pathway = $app->getPathway();
		$pathway->addItem($item->name);
		
		$this->assignRef('item',$item);
		$this->assignRef('theme',$theme);
		$this->assignRef('params',$par);
		
		parent::display($tpl);
	}

}




