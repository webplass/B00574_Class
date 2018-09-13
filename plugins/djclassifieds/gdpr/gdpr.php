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
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djseo.php');


class plgDJClassifiedsGdpr extends JPlugin {
	public function __construct(& $subject, $config) {
		parent::__construct ( $subject, $config );
		$this->loadLanguage ();
	}
	
	
	function onDJClassifiedsSendMessage($item,$author,$mailto,$mailfrom,$fromname,$replyto,$replytoname,$subject,$message,$files,$custom_fields_msg) {
		$db	     = JFactory::getDBO();
		$user    = JFactory::getUser();
		$app     = JFactory::getApplication();
		$menus	 = $app->getMenu('site');
		$cfpar 	 =  JComponentHelper::getParams( 'com_djclassifieds' );
		if($cfpar->get('gdpr_agreement',1)>0 && $user->id==0){
			
			if ( ! file_exists( JPATH_ROOT.'/components/com_gdpr/models/user.php' ) ) {
				echo 'GDPR not installed!';
				return;
			}
			
				$recordData = array();				
				$db = JFactory::getDBO();
				$user = JFactory::getUser();
				if($user->id) {
					// We have a logged in user, track it
					$recordData['user_id'] = $user->id;
				}
				
				$u = JURI::getInstance( JURI::root() );
				if($u->getScheme()){
					$link = $u->getScheme().'://';
				}else{
					$link = 'http://';
				}								
				$link .= $u->getHost().JRoute::_(DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias,$item->region_id.':'.$item->r_alias));
				$link = str_ireplace('administrator/', '', $link);
				
				
				$recordData['formid'] = 'AskSeller_'.$item->id;
				$recordData['formname'] = 'AskSeller';
				$recordData['url'] = $link;		
				$recordData['session_id'] = session_id();		
				$recordData['consent_date'] = JDate::getInstance()->toSql();
				$recordData['formfields'] = json_encode($author);
				
				
				// Check if we have a duplicated key AKA same url, same formid/or/formname and same user_id/or/session_id
				$where = array();
				// We have a logged in user
				if(isset($recordData['user_id'])) {
					$where[] = "\n " . $db->quoteName('user_id') . " = " . (int)($recordData['user_id']);
				} else {
					$where[] = "\n " . $db->quoteName('session_id') . " = " . $db->quote($recordData['session_id']);
				}
				
				// Identify the form in the page
				if(isset($recordData['formid'])) {
					$where[] = "\n " . $db->quoteName('formid') . " = " . $db->quote($recordData['formid']);
				} elseif(isset($recordData['formname'])) {
					$where[] = "\n " . $db->quoteName('formname') . " = " . $db->quote($recordData['formname']);
				}
				
				$query = "SELECT " . $db->quoteName('id') .
				"\n FROM " . $db->quoteName('#__gdpr_consent_registry') .
				"\n WHERE " . $db->quoteName('url') . " = " . $db->quote($recordData['url']) .
				"\n AND "  . implode(" AND ", $where);
				try {
					$existentId = $db->setQuery($query)->loadResult();
				} catch (Exception $e) {
					// No errors handling for user interface
				}
				
				// Go on with a new store if no duplicated key detected
				if(!$existentId) {
					$recordDataObject = (object)$recordData;
					try {
						$db->insertObject('#__gdpr_consent_registry', $recordDataObject);
						return $db->insertid();
					} catch(Exception $e) {
						// No errors handling for user interface
					}
				}
					
			
			
		}
	
		return null;
	
	}
	
	
	
	function onAfterDJClassifiedsSaveAdvert(&$row,$is_new){
		$db	     = JFactory::getDBO();
		$user    = JFactory::getUser();
		$app     = JFactory::getApplication();
		$menus	 = $app->getMenu('site');
		$cfpar 	 =  JComponentHelper::getParams( 'com_djclassifieds' );
		if($cfpar->get('gdpr_agreement',1)>0 && $user->id==0 && $is_new){
			
			if ( ! file_exists( JPATH_ROOT.'/components/com_gdpr/models/user.php' ) ) {
				echo 'GDPR not installed!';
				return;
			}			
			
				$recordData = array();				
				$db = JFactory::getDBO();
				$user = JFactory::getUser();
				if($user->id) {
					// We have a logged in user, track it
					$recordData['user_id'] = $user->id;
				}

				$query = "SELECT i.*, c.name as c_name, c.alias as c_alias, r.name as r_name FROM #__djcf_items i "
						."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
						."LEFT JOIN #__djcf_regions r ON r.id=i.region_id "
						."WHERE i.id = ".$row->id;
				$db->setQuery($query);
				$item = $db->loadObject();
				if(!$item->c_alias){
					$item->c_alias = DJClassifiedsSEO::getAliasName($item->c_name);
				}
				$item->r_alias = DJClassifiedsSEO::getAliasName($item->r_name);								
				
				$u = JURI::getInstance( JURI::root() );
				if($u->getScheme()){
					$link = $u->getScheme().'://';
				}else{
					$link = 'http://';
				}								
				$link .= $u->getHost().JRoute::_(DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias,$item->region_id.':'.$item->r_alias));
				$link = str_ireplace('administrator/', '', $link);
				
				
				$recordData['formid'] = 'NewAdvert_'.$item->id;
				$recordData['formname'] = 'NewAdvert';
				$recordData['url'] = $link;		
				$recordData['session_id'] = session_id();		
				$recordData['consent_date'] = JDate::getInstance()->toSql();
				$recordData['formfields'] = json_encode(array('Email'=>$item->email));
				
				
				// Check if we have a duplicated key AKA same url, same formid/or/formname and same user_id/or/session_id
				$where = array();
				// We have a logged in user
				if(isset($recordData['user_id'])) {
					$where[] = "\n " . $db->quoteName('user_id') . " = " . (int)($recordData['user_id']);
				} else {
					$where[] = "\n " . $db->quoteName('session_id') . " = " . $db->quote($recordData['session_id']);
				}
				
				// Identify the form in the page
				if(isset($recordData['formid'])) {
					$where[] = "\n " . $db->quoteName('formid') . " = " . $db->quote($recordData['formid']);
				} elseif(isset($recordData['formname'])) {
					$where[] = "\n " . $db->quoteName('formname') . " = " . $db->quote($recordData['formname']);
				}
				
				$query = "SELECT " . $db->quoteName('id') .
				"\n FROM " . $db->quoteName('#__gdpr_consent_registry') .
				"\n WHERE " . $db->quoteName('url') . " = " . $db->quote($recordData['url']) .
				"\n AND "  . implode(" AND ", $where);
				try {
					$existentId = $db->setQuery($query)->loadResult();
				} catch (Exception $e) {
					// No errors handling for user interface
				}
				
				// Go on with a new store if no duplicated key detected
				if(!$existentId) {
					$recordDataObject = (object)$recordData;
					try {
						$db->insertObject('#__gdpr_consent_registry', $recordDataObject);
						return $db->insertid();
					} catch(Exception $e) {
						// No errors handling for user interface
					}
				}
					
			
			
		}
	
		return null;
	
	}
	
	
}


