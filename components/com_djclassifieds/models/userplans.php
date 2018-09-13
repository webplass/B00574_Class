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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class DjclassifiedsModelUserPlans extends JModelLegacy{	
	
	function getActivePlans(){
		
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$limit		= JRequest::getVar('limit', $par->get('limit_djitem_show'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$user 		= JFactory::getUser();			 
		$order		= JRequest::getCmd('order', $par->get('items_ordering','date_e'));
		$ord_t 		= JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
		$db			= JFactory::getDBO();
		$date_now   = date("Y-m-d H:i:s");
			
			$ord="ps.date_start ";
			
			if($order=="points"){
				$ord="p.points ";
			}				
		
			if($ord_t == 'asc'){
				$ord .= 'ASC';
			}else{
				$ord .= 'DESC';
			}
			
			$expiration_query = " AND (ps.date_exp > '".$date_now."' OR ps.date_exp='0000-00-00 00:00:00') AND ps.adverts_available>0  ";
			
			$plans_plugin = JPluginHelper::getPlugin('djclassifieds', 'plans');
			$plugin_params = new JRegistry($plans_plugin->params);			
			$free_expiration =  $plugin_params->get('ps_free_plan_expiration','0');

			if($free_expiration){				
				$expiration_query = " AND ( ((ps.date_exp > '".$date_now."' OR ps.date_exp='0000-00-00 00:00:00') AND ps.adverts_available>0) " 
										 		."OR (p.price=0 AND (ps.date_exp > '".$date_now."' OR ps.date_exp='0000-00-00 00:00:00') AND ps.adverts_available=0) )  ";
			}
			
			$query = "SELECT ps.*, p.price, p.name as plan_name, p.description as plan_description FROM #__djcf_plans_subscr ps, #__djcf_plans p "
					."WHERE ps.user_id='".$user->id."' AND p.id=ps.plan_id "
					.$expiration_query
					."ORDER BY  ".$ord." ";
		
			//$points = $this->_getList($query, $limitstart, $limit);	
			
				$db->setQuery($query);
				$plans=$db->loadObjectList();
				//echo '<pre>';print_r($db);print_r($plans);echo '<pre>';die();
				
				if($plans){
					foreach($plans as $plan){
						$plan->items='';
						$query = "SELECT p.*, i.name as i_name,i.alias as i_alias, i.cat_id, i.c_alias, i.region_id, i.r_name FROM #__djcf_plans_subscr_items p "
								."LEFT JOIN (SELECT i.*,c.alias as c_alias, r.name as r_name FROM #__djcf_items i
												LEFT JOIN #__djcf_categories c ON c.id=i.cat_id
												LEFT JOIN #__djcf_regions r ON r.id=i.region_id) i ON i.id=p.item_id " 
								."WHERE p.subscr_id=".$plan->id." "
								."ORDER BY p.id DESC";
						$db->setQuery($query);
						$plan->items = $db->loadObjectList();
					}
				}
				
				
				//echo '<pre>';print_r($db);print_r($plans);echo '<pre>';die();	
			return $plans;
	}
	
	function getExpiredPlans(){
	
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$limit		= JRequest::getVar('limit', $par->get('limit_djitem_show'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$user 		= JFactory::getUser();
		$order		= JRequest::getCmd('order', $par->get('items_ordering','date_e'));
		$ord_t 		= JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
		$db			= JFactory::getDBO();
		$date_now   = date("Y-m-d H:i:s");
			
		$ord="ps.date_start ";
			
		if($order=="points"){
			$ord="p.points ";
		}
	
		if($ord_t == 'asc'){
			$ord .= 'ASC';
		}else{
			$ord .= 'DESC';
		}

		
		$expiration_query = " AND ((ps.date_exp < '".$date_now."' AND ps.date_exp!='0000-00-00 00:00:00') OR ps.adverts_available=0)  ";
			
		$plans_plugin = JPluginHelper::getPlugin('djclassifieds', 'plans');
		$plugin_params = new JRegistry($plans_plugin->params);
		$free_expiration =  $plugin_params->get('ps_free_plan_expiration','0');
		
		if($free_expiration){
			$expiration_query = " AND ( (ps.date_exp < '".$date_now."' AND ps.date_exp!='0000-00-00 00:00:00') OR ps.adverts_available=0 AND p.price>0) ";
		}
		
		$query = "SELECT ps.*, p.name as plan_name, p.description as plan_description FROM #__djcf_plans_subscr ps, #__djcf_plans p "
				."WHERE ps.user_id='".$user->id."' AND p.id=ps.plan_id "
				.$expiration_query
				."ORDER BY  ".$ord."";
	
		//$points = $this->_getList($query, $limitstart, $limit);
			
		$db->setQuery($query);
		$plans=$db->loadObjectList();
	
		if($plans){
			foreach($plans as $plan){
				$plan->items='';
				$query = "SELECT p.*, i.name as i_name,i.alias as i_alias, i.cat_id, i.c_alias, i.region_id, i.r_name FROM #__djcf_plans_subscr_items p "
						."LEFT JOIN (SELECT i.*,c.alias as c_alias, r.name as r_name FROM #__djcf_items i		
									 	LEFT JOIN #__djcf_categories c ON c.id=i.cat_id
										LEFT JOIN #__djcf_regions r ON r.id=i.region_id) i ON i.id=p.item_id "
								."WHERE p.subscr_id=".$plan->id." "
										."ORDER BY p.id DESC";
				$db->setQuery($query);
				$plan->items = $db->loadObjectList();
			}
		}
	
	
//		echo '<pre>';print_r($db);print_r($plans);echo '<pre>';die();
		return $plans;
	}
	
	
	function getCountPlans(){
					
			$user = JFactory::getUser();
			$query = "SELECT count(p.id)FROM #__djcf_plans_subscr p "
					."WHERE p.user_id='".$user->id."' ";				
						
				$db= JFactory::getDBO();
				$db->setQuery($query);
				$plans_count=$db->loadResult();
								
				//echo '<pre>';print_r($db);print_r($points_count);echo '<pre>';die();	
			return $plans_count;
	}	
	

	function getDurations(){
		$db= JFactory::getDBO();
		$user = JFactory::getUser();
			
		$query = "SELECT *  FROM #__djcf_days  "
				."WHERE published=1";
	
		$db->setQuery($query);
		$durations=$db->loadObjectList('id');
		//echo '<pre>';print_r($db);print_r($plans);die();
	
		return $durations;
	}
	
	function getPromotions(){
		$db= JFactory::getDBO();
		$user = JFactory::getUser();
			
		$query = "SELECT *  FROM #__djcf_promotions  "
				."WHERE published=1";
	
		$db->setQuery($query);
		$promotions=$db->loadObjectList('id');
		//echo '<pre>';print_r($db);print_r($plans);die();
	
		return $promotions;
	}	
	
	
}

