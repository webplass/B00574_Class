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
defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class DjclassifiedsModelAddItem extends JModelLegacy{	

	function getItem()
	{
		$app	= JFactory::getApplication();
		$id 	= JRequest::getVar('id', 0, '', 'int' );
		$copy 	= JRequest::getVar('copy', 0, '', 'int' );
		$token 	= JRequest::getCMD('token', '');
        $row 	= JTable::getInstance('Items', 'DJClassifiedsTable');
        $db		= JFactory::getDBO();
        $par 	= JComponentHelper::getParams( 'com_djclassifieds' );
        
		if($id>0 || $copy>0){				
			$user=JFactory::getUser();
			
			if($id){
				$row->load($id);	
			}else{
				$row->load($copy);
				$row->id=0;
			}
			
			$wrong_ad = 0;			
			if($user->id==0){
				$wrong_ad = 1;
			}else if ($user->id!=$row->user_id){
				$wrong_ad = 1;
				if($par->get('admin_can_edit_delete','0') && $user->authorise('core.admin')){
					$wrong_ad = 0;
				}
			}
			
			
			if($wrong_ad){
				$message = JText::_("COM_DJCLASSIFIEDS_WRONG_AD");
				$redirect= 'index.php?option=com_djclassifieds&view=additem' ;
				$app->redirect($redirect,$message,'error');		
			}
		}else if($token){
			$query = "SELECT i.id FROM #__djcf_items i "
					."WHERE i.user_id=0 AND i.token=".$db->Quote($db->escape($token));			
			$db->setQuery($query);
			$id=$db->loadResult();
			if($id){
				$row->load($id);
			}			
		}
	  	
        return $row;
	}
	
		function getCategories(){
			$db		= JFactory::getDBO();
			$user 	= JFactory::getUser();
			
			$lj = '';
			$ls = '';						
			$g_list = '0';
			if($user->groups){
				$g_list = implode(',',$user->groups);	
			}									
			if (!$g_list){
				$g_list = '0';
			}
			if($user->id){
				$ls=',g.g_active';
				$lj="LEFT JOIN (SELECT COUNT(id) as g_active, cat_id FROM #__djcf_categories_groups " 
				   ."WHERE group_id in(".$g_list.") GROUP BY cat_id ) g ON g.cat_id=c.id ";
				$lj_where = ' AND (c.access=0 OR (c.access=1 AND g.g_active>0 ))';
			}else{
				$lj_where = ' AND c.access=0 ';	
			}
			$query = "SELECT c.* ".$ls." FROM #__djcf_categories c "
					.$lj
					."WHERE c.published=1 ".$lj_where
					."ORDER BY c.parent_id, c.ordering ";
	
			$db->setQuery($query);
			$cats=$db->loadObjectList();
			//echo '<pre>';print_r($db);print_r($cats);die();
	
			return $cats;
	}
	
	function getRegions(){
			$db	= JFactory::getDBO();
			$query = "SELECT r.* FROM #__djcf_regions r "
					."WHERE r.published=1 "
					."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci ";
	
			$db->setQuery($query);
			$regions=$db->loadObjectList();
	
			return $regions;
	}
	
	function getTermsLink($id){
			$db= JFactory::getDBO();
			$query = "SELECT a.id, a.alias, a.catid, c.alias as c_alias FROM #__content a "
					."LEFT JOIN #__categories c ON c.id=a.catid "
					."WHERE a.state=1 AND a.id=".$id;
			
			$db->setQuery($query);
			$article=$db->loadObject();
			
			return $article;	
	}
	

	function getDays(){
			$db= JFactory::getDBO();
			
			$query = "SELECT COUNT(c.id) FROM #__djcf_categories c  ";
			$db->setQuery($query);
			$cats_total=$db->loadResult();
						
			$query = "SELECT d.*, IFNULL(c.cat_c,0) AS cat_c FROM #__djcf_days d "
					."LEFT JOIN (SELECT COUNT(id) as cat_c, day_id FROM #__djcf_days_xref GROUP BY day_id) c ON c.day_id=d.id "			
					."WHERE d.published=1 AND (cat_c IS NULL OR cat_c=".$cats_total."  )"
					."ORDER BY d.days "; 
	
			$db->setQuery($query);
			$days=$db->loadObjectList('days');	

			if(isset($days[0])){
				$day_0 = $days[0];
				unset($days[0]);
				$days[0] = $day_0;
			}
			
			return $days;
	}	

	function getPromotions(){
			$db= JFactory::getDBO();
			$query = "SELECT p.*, '' as prices FROM #__djcf_promotions p "
					."WHERE p.published=1 "
					."ORDER BY p.ordering,p.id ";
	
			$db->setQuery($query);
			$promotions=$db->loadObjectList('id');
						
			$query = "SELECT p.* FROM #__djcf_promotions_prices p "
					."ORDER BY p.days ";
			$db->setQuery($query);
			$prom_prices=$db->loadObjectList();		
			
				foreach($prom_prices as $pp){
					if(isset($promotions[$pp->prom_id])){
						if(!is_array($promotions[$pp->prom_id]->prices)){
							$promotions[$pp->prom_id]->prices = array();
						}	
						$promotions[$pp->prom_id]->prices[$pp->days]=$pp;
					}
				}
			
			//echo '<pre>';print_r($promotions);die();			
	
			return $promotions;
	}		
	
	function getItemPromotions($id){
		$promotions = '';
		if($id){
			$db= JFactory::getDBO();
			$query = "SELECT p.* FROM #__djcf_items_promotions p "
					."WHERE item_id=".$id;
	
					$db->setQuery($query);
					$promotions=$db->loadObjectList('prom_id');
					//echo '<pre>';print_r($promotions);die();
		}
		return $promotions;
	}
	
	function getCustomContactFields(){
		global $mainframe;
		$id 	= JRequest::getInt('id', '0');
		$id_copy 	= JRequest::getInt('copy', '0');
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$token  = JRequest::getCMD('token', '' );
		
		if($id==0){
			$id=$id_copy;
		}
		
		if($user->id==0){
			$id=0;
		}
		
		$item='';
		if($id>0){
			$query = "SELECT * FROM #__djcf_items WHERE id='".$id."' LIMIT 1";
			$db->setQuery($query);
			$item =$db->loadObject();
			if($item->user_id!=$user->id){
				if($par->get('admin_can_edit_delete','0')==0 || !$user->authorise('core.admin')){
					$id=0;
				}
			}
		}else if($token){
		 	$query = "SELECT * FROM #__djcf_items WHERE token='".addslashes($token)."' AND user_id=0 LIMIT 1";
		 	$db->setQuery($query);
		 	$item =$db->loadObject();	
			if($item){
				$id=$item->id;
			}
			
		 }
		$query ="SELECT f.*, v.value, v.value_date, v.value_date_to FROM #__djcf_fields f "
				."LEFT JOIN (SELECT * FROM #__djcf_fields_values WHERE item_id=".$id.") v "
						."ON v.field_id=f.id "
				."WHERE f.published=1 AND f.source=1 AND f.edition_blocked=0 ORDER BY f.ordering";
		$db->setQuery($query);
		$fields_list =$db->loadObjectList();
		//echo '<pre>'; print_r($db);print_r($fields_list);die();
		
		if($user->id && $id==0){
			foreach($fields_list as $fl){
				if($fl->profile_source){
					$query ="SELECT value FROM #__djcf_fields_values_profile WHERE field_id=".$fl->profile_source." AND user_id=".$user->id;
					$db->setQuery($query);
					$profile_value =$db->loadResult();
					$fl->default_value = $profile_value;
				}
			}
		}
		
		return $fields_list;
		
	}	
	
	function getUserItemsCount(){
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$query = "SELECT COUNT(id) FROM #__djcf_items WHERE user_id='".$user->id."' ";
			$db->setQuery($query);
			$user_itesms_c =$db->loadResult();
		
		return $user_itesms_c;
	}
	
	function getItemImages($item_id)
	{
		$db 	= JFactory::getDBO();
		$images = array();
		$id_copy = JRequest::getInt('copy',0);
		
		if($item_id){			
			$query  = "SELECT * FROM #__djcf_images i "
					."WHERE i.type='item' AND i.item_id=".$item_id." ORDER BY ordering";
			$db->setQuery($query);
			$images=$db->loadObjectList();
		}else if($id_copy){
			$query  = "SELECT * FROM #__djcf_images i "
					."WHERE i.type='item' AND i.item_id=".$id_copy." ORDER BY ordering";
			$db->setQuery($query);
			$images=$db->loadObjectList();
			
			$t = time();
			foreach($images as &$image){
				$img_from = JPATH_BASE.$image->path.$image->name;
				$img_to = JPATH_BASE.'/tmp/djupload/'.$image->name;
				copy($img_from.'.'.$image->ext,$img_to.'_'.$t.'.'.$image->ext);
				$image->name = $image->name.'_'.$t;
				$image->path = '/tmp/djupload/';
				//echo '<pre>';print_r($image);die();
			}
			//echo '<pre>';print_r($images);die();
		}								
		
		return $images;
	}
	
	function getItemsUnits(){
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$query = "SELECT id, name FROM #__djcf_items_units WHERE published= 1 ORDER BY ordering  ";
		$db->setQuery($query);
		$units =$db->loadObjectList();
		
		return $units;
	}
	
	function getItemSubscriptionID($id){
		$db 	= JFactory::getDBO();
		$query = "SELECT subscr_id FROM #__djcf_plans_subscr_items WHERE item_id=".$id." LIMIT 1 ";
		$db->setQuery($query);
		$subscr_id =$db->loadResult();
		return $subscr_id;
	}
	
	function getProfileDefaultValues($item){
		$db 	= JFactory::getDBO();
		$user	= JFactory::getUser();
		if($user->id>0){
			$query ="SELECT f.*, v.value, v.value_date, v.value_date_to FROM #__djcf_fields f "
					."LEFT JOIN (SELECT * FROM #__djcf_fields_values_profile WHERE user_id=".$user->id.") v "
					."ON v.field_id=f.id "
					."WHERE f.published=1 AND f.source=2 AND f.access=0 AND f.core_source != '' ORDER BY f.ordering";
			$db->setQuery($query);
			$fields = $db->loadObjectList();
			
			if(count($fields)){
				foreach($fields as $field){
					if($field->core_source=='contact'){
						if($field->type=='date' || $field->type=='date_from_to'){
							$item->contact = $field->value_date;
						}else{
							$item->contact = $field->value;
						}
						 
					}else if($field->core_source=='address'){
						$item->address = $field->value;						 
					}else if($field->core_source=='video'){
						$item->video = $field->value;						 
					}
				}
			}
			
			$query ="SELECT * FROM #__djcf_profiles WHERE user_id=".$user->id." LIMIT 1 ";
			$db->setQuery($query);
			$profile = $db->loadObject();
			
			if(isset($profile->user_id)){
				if($profile->address){
					$item->address = $profile->address;
				}
				$item->region_id = $profile->region_id;
				$item->post_code = $profile->post_code;
				$item->latitude = $profile->latitude;
				$item->longitude = $profile->longitude;
			}
		}
		return $item;
	}
	
}

