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


class DJClassifiedsViewProfileedit extends JViewLegacy{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/profileedit');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$theme = $par->get('theme','default');
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/profileedit');
		}
	}
	
	function display($tpl = NULL){
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$user 		= JFactory::getUser();
		$app 		= JFactory::getApplication();		
		$dispatcher	= JDispatcher::getInstance();
		//echo $val;
		
		if($user->id=='0'){
			$uri = JFactory::getURI();
			$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
			$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
		}else{		 			
			$model 			 = $this->getModel();			
			$profile 		 = $model->getProfile();
			$custom_fields   = $model->getCustomFields($profile);
			$custom_values_c = $model->getCustomValuesCount();
			$profile_image   = $model->getProfileImage();
			
			$regions = $model->getRegions();
			$r_name = '';
			$reg_path = array();
			
			if($profile->region_id!=0){
				$id = Array();
				$name = Array();
				$rid = $profile->region_id;
				if($rid!=0){
					while($rid!=0){
						foreach($regions as $li){
							if($li->id==$rid){
								$rid=$li->parent_id;
								$id[]=$li->id;
								$name[]=$li->name;
								if(!count($reg_path)){
									$r_name = $li->name;
								}
								$reg_path[] = $li->parent_id.','.$li->id;
								break;
							}
						}
						if($rid==$profile->region_id){ break; }
					}
				}
			}
			
			
			$plugin_sections = $dispatcher->trigger('onProfileEditFormSections', array ($user, & $custom_fields, &$custom_values_c, &$profile_image, &$par));
			
			$this->assignRef('profile',$profile);
			$this->assignRef('custom_fields',$custom_fields);
			$this->assignRef('custom_values_c',$custom_values_c);
			$this->assignRef('avatar',$profile_image);
			$this->assignRef('plugin_sections',$plugin_sections);
			$this->assignRef('regions',$regions);
			$this->assignRef('reg_path',$reg_path);
			$this->assignRef('r_name',$r_name);
			
       		parent::display();			
			
		}
	}

}




