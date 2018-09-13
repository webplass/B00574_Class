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

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class DJClassifiedsModelProfile extends JModelAdmin
{

	public function getTable($type = 'Profile', $prefix = 'DJClassifiedsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		return $form;
	}
   
    function getProfileFields(){
   		$id = JRequest::getVar('id', '0', '', 'int');
   		if($id==0){
   			$cid = JRequest::getVar('cid', array(0), '', 'array' );
   			$id = $cid[0];
   		}
   		
    	$db = JFactory::getDBO();
    	$query ="SELECT f.*, v.value, v.value_date, v.value_date_to FROM #__djcf_fields f "
    			."LEFT JOIN (SELECT * FROM #__djcf_fields_values_profile WHERE user_id=".$id.") v "
    					."ON v.field_id=f.id "
    			."WHERE f.source=2 AND f.published=1 ORDER BY f.ordering";
    	$db->setQuery($query);
    	$fields_list =$db->loadObjectList();
    	//echo '<pre>'; print_r($db);print_r($fields_list);die();
    	return $fields_list;
    }
    
    function getProfile(){
    	$id = JRequest::getVar('id', '0', '', 'int');
    	if($id==0){
    		$cid = JRequest::getVar('cid', array(0), '', 'array' );
    		$id = $cid[0];
    	}
    	 
    	JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
    	jimport( 'joomla.database.table' );
    	$row = JTable::getInstance('Profiles', 'DJClassifiedsTable');
    	
    	$row->load($id);

    	return $row;
    }
    
    function getImages(){
    	$id = JRequest::getVar('id', '', '0', 'int');
    	if($id==0){
    		$cid = JRequest::getVar('cid', array(0), '', 'array' );
    		$id = $cid[0];
    	}
    	$db= JFactory::getDBO();
    	$query = "SELECT * FROM #__djcf_images WHERE item_id=".$id." AND type='profile' ORDER BY ordering";
    	$db->setQuery($query);
    	$images=$db->loadObjectList();
    
    	return $images;
    }
    
    function getRegions(){
    	$db= JFactory::getDBO();
    	$query = "SELECT r.* FROM #__djcf_regions r "
    			."ORDER BY r.parent_id, r.name ";
    
    			$db->setQuery($query);
    			$regions=$db->loadObjectList();
    
    			return $regions;
    }    
    
    public function getProfileGroups(){    	
    		$db = JFactory::getDbo();
    		$query = $db->getQuery(true);
    		$query->select('id as value, name as text')->from('#__djcf_fields_groups')->where('source=2')->order('ordering asc');
    		$db->setQuery($query);
    		$groups = $db->loadObjectList();
    	
    		return $groups;    	
    	}
    
}