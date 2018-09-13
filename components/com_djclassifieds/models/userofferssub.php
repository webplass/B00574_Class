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

class DjclassifiedsModelUserOffersSub extends JModelLegacy{	
	
	function getOffers(){
		
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
			
			$query = "SELECT o.*, i.name as i_name, i.alias as i_alias, i.cat_id, i.c_alias, i.region_id, i.r_name, i.user_id as i_user_id, i.username, i.u_email as email, i.currency   FROM #__djcf_offers o "
						."LEFT JOIN (SELECT i.*, c.alias as c_alias, ".$u_name.", u.email as u_email, r.name as r_name FROM  #__djcf_categories c, #__djcf_items i
								LEFT JOIN #__users u ON u.id=i.user_id
								LEFT JOIN #__djcf_regions r ON r.id=i.region_id
								WHERE i.cat_id=c.id ) i ON i.id=o.item_id "
					."WHERE o.user_id='".$user->id."'  "
					."ORDER BY  ".$ord." ";
		
			$offers = $this->_getList($query, $limitstart, $limit);	
			
				//$db->setQuery($query);
				//$plans=$db->loadObjectList();

				if(count($offers)){
					$users_id = array();
					$id_list= '';
					foreach($offers as $offer){
						if($id_list){
							$id_list .= ','.$offer->item_id;
						}else{
							$id_list .= $offer->item_id;
						}
						if($offer->i_user_id){
							$users_id[$offer->i_user_id] = $offer->i_user_id; 
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
					}
					
					
					for($i=0;$i<count($offers);$i++){		
						$offers[$i]->i_user_items_count = '';
						if(isset($user_items_c[$offers[$i]->i_user_id])){
							$offers[$i]->i_user_items_count= $user_items_c[$offers[$i]->i_user_id]->user_items_c;
						}						
						$img_found =0;
						$offers[$i]->images = array();
						foreach($items_img as $img){
							if($offers[$i]->item_id==$img->item_id){
								$img_found =1;
								$offers[$i]->images[]=$img;
							}else if($img_found){
								break;
							}
						}																		
					}
					
				}				
				
				//echo '<pre>';print_r($db);print_r($offers);echo '<pre>';die();	
			return $offers;
	}
	
	function getCountOffers(){
					
			$user = JFactory::getUser();
			$query = "SELECT count(o.id) FROM #__djcf_offers o "
					."WHERE o.user_id='".$user->id."' ";				
						
				$db= JFactory::getDBO();
				$db->setQuery($query);
				$offers_count=$db->loadResult();
								
				//echo '<pre>';print_r($db);print_r($offers_count);echo '<pre>';die();	
			return $offers_count;
	}	
	
	
}

