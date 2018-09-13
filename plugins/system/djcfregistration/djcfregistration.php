<?php
/**
* @version 2.0
* @package DJ Classifieds
* @subpackage DJ Classifieds Component
* @copyright Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer Łukasz Ciastek - lukasz.ciastek@design-joomla.eu
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
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.plugin.plugin' );
jimport ( 'joomla.utilities.utility' );
if(!defined("DS")){ define('DS',DIRECTORY_SEPARATOR);}
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djtheme.php');


class plgSystemDjcfregistration extends JPlugin {
	public function __construct(& $subject, $config) {
		parent::__construct ( $subject, $config );
		$this->loadLanguage ();
	}
	
	
	function onAfterRoute(){
		if(!strstr(JURI::base(), '/administrator')){
			$user = JFactory::getUser();
			if(JRequest::getVar('option') == 'com_users' && JRequest::getVar('view') == 'registration' && $user->id == 0 && JRequest::getVar('task') != 'user.login' && JRequest::getVar('task') != 'remind.remind' && JRequest::getVar('task') != 'reset.request' && JRequest::getVar('task') != 'reset.confirm' && JRequest::getVar('task') != 'reset.complete'){
				$app = JFactory::getApplication();
				$menus	= $app->getMenu('site');
				$menu_register = $menus->getItems('link','index.php?option=com_djclassifieds&view=registration',1);
				$registration_link='index.php?option=com_djclassifieds&view=registration';
				if($menu_register){
					$registration_link .= '&Itemid='.$menu_register->id;
				}
				$app->redirect($registration_link);
			}					
		}
		
	}
	
}


