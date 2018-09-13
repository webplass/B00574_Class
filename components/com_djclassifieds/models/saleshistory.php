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

class DjclassifiedsModelSalesHistory extends JModelLegacy{	
	
	function getOrders(){
		
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$limit		= JRequest::getVar('limit', $par->get('limit_djitem_show'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$user 		= JFactory::getUser();			 
		$order		= JRequest::getCmd('order', $par->get('items_ordering','date_e'));
		$ord_t 		= JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
		$db			= JFactory::getDBO();
			
			$ord="o.date ";
			
			/*if($order=="points"){
				$ord="p.points ";
			}*/				
		
			if($ord_t == 'asc'){
				$ord .= 'ASC';
			}else{
				$ord .= 'DESC';
			}
			
			if($par->get('authorname','name')=='name'){
				$u_name = 'u.name as username';
			}else{
				$u_name = 'u.username';
			}
			
			$query = "SELECT o.*, i.name as i_name, i.alias as i_alias, i.cat_id, i.currency, c.alias as c_alias, r.name as r_name, ".$u_name.", u.email FROM #__djcf_orders o, #__users u, #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "					
					."LEFT JOIN #__djcf_regions r ON r.id=i.region_id "
					."WHERE o.item_id=i.id AND u.id=o.user_id AND i.user_id='".$user->id."'  "
					."ORDER BY  ".$ord." ";
		
			$orders = $this->_getList($query, $limitstart, $limit);	
			
				//$db->setQuery($query);
				//$plans=$db->loadObjectList();

				if(count($orders)){
					$users_id = array();
					$id_list= '';
					foreach($orders as $order){
						if($id_list){
							$id_list .= ','.$order->item_id;
						}else{
							$id_list .= $order->item_id;
						}
						if($order->user_id){
							$users_id[$order->user_id] = $order->user_id; 
						}						
					}
					
					$items_img = DJClassifiedsImage::getAdsImages($id_list);
					$users_ids = implode(',', $users_id);
					$user_items_c = '';
					if($users_ids){
						$date_now = date("Y-m-d H:i:s");
						$query = "SELECT user_id , COUNT(i.id) as user_items_c FROM #__djcf_items i "
								."WHERE i.published=1 AND i.date_exp>'".$date_now."' AND i.user_id  IN (".$users_ids.") "
								."GROUP BY user_id";
						$db->setQuery($query);
						$user_items_c=$db->loadObjectList('user_id');
						
						$query = "SELECT id FROM #__djcf_fields f "
								."WHERE f.published=1 AND f.name='purchase_details' AND f.source=2 LIMIT 1";
						$db->setQuery($query);
						$pd_field_id_c=$db->loadResult();
						
						if($pd_field_id_c){
							$query = "SELECT p.* FROM #__djcf_fields_values_profile p "
									."WHERE p.field_id = '".$pd_field_id_c."' AND p.user_id  IN (".$users_ids.") ";
							$db->setQuery($query);
							$user_pd=$db->loadObjectList('user_id');
								
						}
					}
					
					
					for($i=0;$i<count($orders);$i++){		
						$orders[$i]->i_user_items_count = '';
						if(isset($user_items_c[$orders[$i]->user_id])){
							$orders[$i]->i_user_items_count= $user_items_c[$orders[$i]->user_id]->user_items_c;
						}		

						if(isset($user_pd[$orders[$i]->user_id])){
							$orders[$i]->i_user_pd= $user_pd[$orders[$i]->user_id];
						}
						
						$img_found =0;
						$orders[$i]->images = array();
						foreach($items_img as $img){
							if($orders[$i]->item_id==$img->item_id){
								$img_found =1;
								$orders[$i]->images[]=$img;
							}else if($img_found){
								break;
							}
						}																		
					}
					
				}				
				
				//echo '<pre>';print_r($db);print_r($orders);echo '<pre>';die();	
			return $orders;
	}
	
	function getCountOrders(){
					
			$user = JFactory::getUser();
			$query = "SELECT count(o.id) FROM #__djcf_orders o, #__djcf_items i "
					."WHERE i.id=o.item_id AND i.user_id='".$user->id."' ";				
						
				$db= JFactory::getDBO();
				$db->setQuery($query);
				$orders_count=$db->loadResult();
								
				//echo '<pre>';print_r($db);print_r($orders_count);echo '<pre>';die();	
			return $orders_count;
	}	
	
	
}

