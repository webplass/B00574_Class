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


class plgDJClassifiedsAcymailing extends JPlugin {
	public function __construct(& $subject, $config) {
		parent::__construct ( $subject, $config );
		$this->loadLanguage ();
	}
	
	function onUserRegistrationForm(){
		$content = null;
		if (JComponentHelper::getComponent('com_acymailing', true)->enabled){
			$content = '<div class="djform_row">    
					<label class="label">&nbsp;</label>        		
		            	            	
	                <div class="djform_field">
	                    <input class="text_area " type="checkbox" name="acymailing" id="acymailing" maxlength="250" value="1" />
						<span id="acymailing-lbl" for="acymailing" class="label_checkbox">'.JText::_('PLG_DJCLASSIFIEDS_ACYMAILING_SIGNUP_NEWSLETTER').'</span>
	                </div>
	                <div class="clear_both"></div> 
	            </div> ';	
		}
		return $content;
	}
	
	
	function onAfterDJClassifiedsSaveUser($data, $user_id){
		$newsletter = JRequest::getVar('acymailing','');
		if($newsletter){
			$db = JFactory::getDBO();
			$list_id = $this->params->get('list_id', '1');
			
			$query = "SELECT subid FROM #__acymailing_subscriber WHERE email = '".$data['email1']."' ";
			$db->setQuery($query);
			$subid = $db->loadResult(); 
			
			if($subid){
				$query = "INSERT INTO #__acymailing_listsub (`listid`,`subid`,`subdate`,`status`) "
						."VALUES ('".$list_id."','".$subid."','".time()."','1') ";
				$db->setQuery($query);
				$db->query();
			}
		}
		/*
		echo $user_id;
		echo '<pre>';
		print_r($data);
		print_r($_POST);
		die();*/
		
		
		return null;
	}
}


