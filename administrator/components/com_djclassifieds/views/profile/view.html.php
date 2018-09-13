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


class DJClassifiedsViewProfile extends JViewLegacy
{

	function display($tpl = null)
	{				
		$id = JRequest::getVar('id', '0', '', 'int');
		if($id==0){
			$cid = JRequest::getVar('cid', array(0), '', 'array' );
			$id = $cid[0];
		}
		$this->profile_fields = $this->get('ProfileFields');
		$this->profile = $this->get('Profile');
		$this->images = $this->get('Images');
		$this->jprofile = JFactory::getUser($id);
		$this->regions = $this->get('Regions');
		$this->profile_groups = $this->get('ProfileGroups');
		
		$country='';
		$city='';
		
		$reg_path='';
		if($this->profile->region_id!=0){
			$id = Array();
			$name = Array();
			$rid = $this->profile->region_id;
			if($rid!=0){
				while($rid!=0){
					$parent_f = 0;
					foreach($this->regions as $li){
						if($li->id==$rid){
							$rid=$li->parent_id;
							$id[]=$li->id;
							$name[]=$li->name;
							$reg_path = 'new_reg('.$li->parent_id.','.$li->id.');'.$reg_path;
							if($li->country){
								$country =$li->name;
							}
							if($li->city){
								$city =$li->name;
							}
							$parent_f = 1;
							break;
						}
					}
					if($rid==$this->profile->region_id){break;}
					if(!$parent_f){break;}
				}
			}
		}
		
		$this->country = $country;
		$this->city = $city;
		
		$this->reg_path = $reg_path;
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu',1);
		
		$user		= JFactory::getUser();
		JToolBarHelper::title(   JText::_( 'COM_DJCLASSIFIEDS_USER_PROFILE' ).': <small><small>[ ' . JText::_( 'COM_DJCLASSIFIEDS_EDIT' ).' ]</small></small>', 'generic.png' );
		JToolBarHelper::apply('profile.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('profile.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::cancel('profile.cancel', 'JTOOLBAR_CANCEL');
	}

}