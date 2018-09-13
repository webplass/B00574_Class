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

class DjclassifiedsModelItems extends JModelLegacy{	
	
	
	function getItems($catlist=''){
		JPluginHelper::importPlugin('djclassifieds');
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$limit		= JRequest::getVar('limit', $par->get('limit_djitem_show'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$user 		= JFactory::getUser();
		$db			= JFactory::getDBO();
		$app		= JFactory::getApplication();
		$cid		= JRequest::getInt('cid',0);
		$dispatcher = JDispatcher::getInstance();
			
			$where = '';
			$mcat_lj = '';
			if($catlist){
				$where = ' AND (i.cat_id IN ('.$catlist.') OR mc.mcat_c>0) ';
				$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".$catlist.") GROUP BY item_id ) mc ON i.id=mc.item_id ";
			}
			
			$uid=JRequest::getVar('uid','0','','int'); 
			if($uid>0){
				$where .= " AND i.user_id=".$uid." ";
			}
			
			$fav_s='';
			$fav_lj='';
			if($par->get('favourite','1') && $user->id>0){
				$fav_lj = "LEFT JOIN ( SELECT * FROM #__djcf_favourites WHERE user_id=".$user->id.") f ON i.id=f.item_id ";
				$fav_s = ',f.id as f_id ';
				$fav=JRequest::getVar('fav','0','','int');				
				if($fav>0){
					$where .= " AND f.id IS NOT NULL ";
				}
			}
		
				$search ='';
				$search_fields='';
				$cat_id = 0;
				$reg_id = 0;
				$search_radius_v = '';
				$search_radius_h = '';
				$search_img_count_v = '';
				$search_img_count_lj = '';
				if(JRequest::getVar('se','0','','string')!='0'){			
					if(JRequest::getVar('search',JText::_('COM_DJCLASSIFIEDS_SEARCH'),'','string')!=JText::_('COM_DJCLASSIFIEDS_SEARCH') && JRequest::getVar('search','','','string')){
						if($par->get('authorname','name')=='name'){
							$u_name_search = 'u.name';
						}else{
							$u_name_search = 'u.username';
						}
						if($par->get('search_type_phrase',0)==1){
							$se_words = explode(' ',$db->escape(JRequest::getVar('search','','','string'), true));
							foreach($se_words as $se_w){
								if(strlen($se_w)>2){
									$search .= " AND (CONCAT_WS(i.name,i.intro_desc,i.description,i.contact) LIKE ".$db->Quote('%'.$se_w.'%')." OR c.name LIKE ".$db->Quote('%'.$se_w.'%')." OR r.name LIKE ".$db->Quote('%'.$se_w.'%')." OR i.id=".$db->Quote($se_w)." OR ".$u_name_search." LIKE ".$db->Quote($se_w)." ) ";
								}
							}	
						}else{
							$search_word = $db->Quote('%'.$db->escape(JRequest::getVar('search','','','string'), true).'%');
							$search_word2 = $db->Quote($db->escape(JRequest::getVar('search','','','string'), true));
							$search = " AND (CONCAT_WS(i.name,i.intro_desc,i.description,i.contact) LIKE ".$search_word." OR c.name LIKE ".$search_word." OR r.name LIKE ".$search_word." OR i.id=".$search_word2." OR ".$u_name_search."=".$search_word2." ) ";
						}
					}
					if(isset($_GET['se_cats'])){
						if(is_array($_GET['se_cats'])){
							$cat_id= end($_GET['se_cats']);
							if($cat_id=='' && count($_GET['se_cats'])>2){
								$cat_id =$_GET['se_cats'][count($_GET['se_cats'])-2];
							}	
						}else{
							$cat_ids = explode(',', JRequest::getVar('se_cats'));
							$cat_id = end($cat_ids);
						}											
					}
					
					$cat_id = str_ireplace('p', '', $cat_id);
					$cat_id=(int)$cat_id;
											
					if($cat_id>0){
						$cats= DJClassifiedsCategory::getSubCat($cat_id,1);					
						$catlist= $cat_id;			
						foreach($cats as $c){
							$catlist .= ','. $c->id;
						}
						$search .= ' AND (i.cat_id IN ('.$catlist.') OR mc.mcat_c>0) ';		
						$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".$catlist.") GROUP BY item_id ) mc ON i.id=mc.item_id ";
						
					}
					
					$se_ef = JRequest::getInt('ef',0);					
					//if($cat_id>0 || $se_ef){
						$search_fields = $this->getSearchFields($cat_id,$se_ef);
					//}
					
					if(isset($_GET['se_regs'])){
						if(is_array($_GET['se_regs'])){
							$reg_id= end($_GET['se_regs']);
							if($reg_id=='' && count($_GET['se_regs'])>2){
								$reg_id =$_GET['se_regs'][count($_GET['se_regs'])-2];
							}								
						}else{
							$reg_ids = explode(',', JRequest::getVar('se_regs'));
							$reg_id = end($reg_ids);
						}
						$reg_id=(int)$reg_id;
					}


					if($reg_id>0){
						$regs= DJClassifiedsRegion::getSubReg($reg_id,1);
																
						$reglist= $reg_id;			
						foreach($regs as $r){
							$reglist .= ','. $r->id;
						}
						$search .= ' AND i.region_id IN ('.$reglist.') ';						
					}else{
						$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
						if($reglist){
							$search .= ' AND i.region_id IN ('.$reglist.') ';
						}
					}

					
					$se_price_from = JRequest::getInt('se_price_f','');
					$se_price_to = JRequest::getInt('se_price_t','');
					if($se_price_from){
						$search .= " AND ABS(i.price) >= ".$se_price_from." ";
					}
					
					if($se_price_to && $se_price_to>=$se_price_from){
						$search .= " AND ABS(i.price) <= ".$se_price_to." ";
					}
					
					$type_id=JRequest::getInt('se_type_id','0'); 
					if($type_id>0){
						$where .= " AND i.type_id=".$type_id." ";
					}
					
					$days_l=JRequest::getInt('days_l','0');
					if($days_l>0){
						$date_limit = date("Y-m-d H:i:s",mktime(date("H"), date("i"), date("s"), date("m"), date("d")-$days_l, date("Y")));
						$where .= " AND i.date_start >= '".$date_limit."' ";
					}
					
					$only_img=JRequest::getInt('se_only_img',0); 
					if($only_img==1){
						$search .= " AND img.img_c>0 ";
						$search_img_count_v = ', img.img_c';
						$search_img_count_lj = "LEFT JOIN ( SELECT COUNT(img.id) as img_c, img.item_id FROM #__djcf_images img
								 				WHERE img.type='item' GROUP BY item_id ) img ON i.id=img.item_id ";
					}
					
					$only_video=JRequest::getInt('se_only_video',0); 
					if($only_video==1){
						$search .= " AND i.video!='' ";
					}
					
					$only_auctions=JRequest::getInt('se_only_auctions',0);
					if($only_auctions==1){
						$search .= " AND i.auction = 1 ";
					}
					
					$only_buynow=JRequest::getInt('se_only_buynow',0);
					if($only_buynow==1){
						$search .= " AND i.buynow = 1 ";
					}
					
					$only_price_negotiable=JRequest::getInt('se_only_price_neg',0);
					if($only_price_negotiable==1){
						$search .= " AND i.price_negotiable = 1 ";
					}
					
					$postcode=JRequest::getVar('se_postcode','');
					$radius=JRequest::getFloat('se_radius',0);
					$se_address=JRequest::getVar('se_address','');
					$se_geoloc=JRequest::getVar('se_geoloc','');
					
					if($radius){
						$radius_unit=JRequest::getCmd('se_radius_unit','km');
						if($radius_unit=='mile'){
							$radius_unit_v = 3959;
						}else{
							$radius_unit_v = 6371;
						}
						
						if($se_geoloc){
							$user_latlog = explode('_', $_COOKIE["djcf_latlon"]);							
							$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$user_latlog[0].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$user_latlog[1].') ) + sin( radians('.$user_latlog[0].') ) * sin( radians( i.latitude ) ) ) ) AS distance ';
							$search_radius_h = 'HAVING distance < '.$radius.' ';
						}else if($postcode!='' && $postcode != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_POSTCODE')){
							$postcode_country=JRequest::getVar('se_postcode_c','');
							$post_coord = DJClassifiedsGeocode::getLocationPostCode($postcode,$postcode_country);																			
							if($post_coord){
								$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$post_coord['lat'].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$post_coord['lng'].') ) + sin( radians('.$post_coord['lat'].') ) * sin( radians( i.latitude ) ) ) ) AS distance ';
								$search_radius_h = 'HAVING distance < '.$radius.' ';
							}else{							
								$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_POSTCODE_WE_OMIITED_RANGE_RESTRICTION'),'notice');
							}
						}else if($se_address!='' && $se_address != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_ADDRESS')){
							$se_address_coord = DJClassifiedsGeocode::getLocation($se_address);						
							if($se_address_coord){
								$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$se_address_coord['lat'].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$se_address_coord['lng'].') ) + sin( radians('.$se_address_coord['lat'].') ) * sin( radians( i.latitude ) ) ) ) AS distance ';
								$search_radius_h = 'HAVING distance < '.$radius.' ';
							}else{							
								$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_ADDRESS_WE_OMIITED_RANGE_RESTRICTION'),'notice');
							}
						}
					}
					
					if(JRequest::getInt('se_also_18',0)==0){
						$search .= " AND c.restriction_18=0 ";
					}
						
				}else{
					if($par->get('restriction_18_allads',0)==1 && !isset($_COOKIE["djcf_warning18"])){
						$where .= " AND c.restriction_18=0 ";
					}
					
					$reg_id = JRequest::getInt('rid',0);
					 
					if($reg_id>0){
						$regs= DJClassifiedsRegion::getSubReg($reg_id,1);
						$reglist= $reg_id;
						foreach($regs as $r){
							$reglist .= ','. $r->id;
						}
						$where .= ' AND i.region_id IN ('.$reglist.') ';
					}else{
						$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
						if($reglist){
							$where .= ' AND i.region_id IN ('.$reglist.') ';
						}
					}					
				}
				
				
			$groups_acl = '0,'.implode(',', $user->getAuthorisedViewLevels());
			$where .= " AND c.access_view IN (" . $groups_acl . ") ";

			$distance_v = '';	
			if($par->get('column_distance','0') && isset($_COOKIE["djcf_latlon"])){
				$distance_unit=JRequest::getCmd('column_distance_unit','km');
				$user_latlog = explode('_', $_COOKIE["djcf_latlon"]);									
				if($distance_unit=='mile'){										
					$distance_unit_v = 3959;
				}else{
					$distance_unit_v = 6371;
				}
				$distance_v = ', ( '.$distance_unit_v.' * acos( cos( radians('.$user_latlog[0].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$user_latlog[1].') ) + sin( radians('.$user_latlog[0].') ) * sin( radians( i.latitude ) ) ) ) AS distance_latlon ';
			}	
			
			 $rev_lj = '';
			 $rev_s = '';
			 
			 $order = JRequest::getCmd('order', $par->get('items_ordering','date_e'));
			 $ord_t = JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
			
			$ord="i.date_exp ";
			
			if($order=="title"){
				$ord="i.name ";
			}elseif($order=="cat"){
				$ord="c.name ";
			}elseif($order=="loc"){
				$ord="r.name ";
			}elseif($order=="price"){
				$ord="ABS(i.price) ";
			}elseif($order=="display"){
				$ord="i.display ";
			}elseif($order=="date_a"){
				$ord="i.date_start ";
			}elseif($order=="date_e"){
				$ord="i.date_exp ";
			}elseif($order=="date_sort"){
				$ord="i.date_sort ";
			}elseif($order=="distance"){
				$ord = ($distance_v ? "distance_latlon " : "i.date_exp ");
			}elseif($order=="random"){
				$ord="RAND() ";
			}elseif($order=="reviews"){
				$ord = 'rev.avg_rate ';
				$rev_s = ', rev.avg_rate as rev_avg_rate ';
				$rev_lj = "LEFT JOIN #__djrevs_objects rev ON i.id = rev.entry_id AND rev.object_type='com_djclassifieds.item' ";	
			}
		
			if($ord_t == 'desc'){
				$ord .= 'DESC';
			}else{
				$ord .= 'ASC';
			}
			
			if($par->get('authorname','name')=='name'){
				$u_name = ', u.name as username';
			}else{
				$u_name = ', u.username';
			}
			 

			$date_time = JFactory::getDate();
			$date_exp=$date_time->toSQL();
			$date_now = date("Y-m-d H:i:s");			
			
			if($par->get('show_archived',0)==2){
				$where .= " AND (( i.published=1 AND i.date_exp > '".$date_now."') OR i.published=2) ";				
			}else{
				$where .= " AND i.published=1 AND i.date_exp > '".$date_now."' ";
			}
			
			$dispatcher->trigger('onPrepareDJClassifiedsListingQueryWhere', array (&$where, &$par));			

			$query = "SELECT i.*, c.name AS c_name,c.alias AS c_alias, c.id as c_id, c.rev_group_id, r.name as r_name, r.id as r_id ".$search_img_count_v.$fav_s.$search_radius_v.$distance_v.$rev_s.$u_name." FROM ".$search_fields." #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON i.cat_id = c.id "
					."LEFT JOIN #__djcf_regions r ON i.region_id = r.id "
					."LEFT JOIN #__users u ON i.user_id = u.id "
					.$search_img_count_lj
					.$fav_lj
					.$rev_lj
					.$mcat_lj
					."WHERE i.blocked=0 AND c.published=1 "
					.$where;
					
				if($search_fields){
					$query .=" AND sf.item_id=i.id ";			
				}			

			if(JRequest::getVar('format','')=='feed'){
				$query .= $search.$search_radius_h." ORDER BY i.date_start DESC ";
				$items = $this->_getList($query, 0, 100);
			}else{
				$query .= $search.$search_radius_h." ORDER BY i.special DESC, ".$ord."";
				$items = $this->_getList($query, $limitstart, $limit);
			}													
						
			//echo '<pre>';print_r($query);print_r($items);echo '<pre>';die();
			
			if(count($items)){
				$id_list= '';
				$uid_list= '';
				foreach($items as $item){
					if($id_list){$id_list .= ',';}
					$id_list .= $item->id;
					
					if($item->user_id){
						if($uid_list){$uid_list .= ',';}
						$uid_list .= $item->user_id;
					}
					
				}
					
				
				$query = "SELECT fv.item_id, fv.field_id, fv.value, fv.value_date, fv.value_date_to, f.label, f.type FROM #__djcf_fields_values fv,#__djcf_fields f "
						."WHERE fv.item_id IN (".$id_list.") AND f.id=fv.field_id AND (f.in_table>0 OR f.in_blog=1) AND f.access=0 AND f.published=1 "
						."ORDER BY fv.item_id,f.ordering, f.label";
				$db->setQuery($query);
				$custom_fields=$db->loadObjectList();
				//echo '<pre>';print_r($custom_fields);die();
								
				$items_img = DJClassifiedsImage::getAdsImages($id_list);
				
				$icats = '';
				if($mcat_lj){
					$query = "SELECT ic.item_id, ic.cat_id, c.name, c.alias FROM #__djcf_items_categories ic, #__djcf_categories c "
							."WHERE c.id=ic.cat_id AND ic.item_id IN (".$id_list.")  "
							."ORDER BY ic.item_id,ic.ordering";
					$db->setQuery($query);
					$icats=$db->loadObjectList();
				}
				
				if($uid_list){					
					if(!$par->get('profile_avatar_source','')){
						$query ="SELECT * FROM #__djcf_images WHERE item_id IN (".$uid_list.") AND type='profile' ";
						$db->setQuery($query);
						$profiles_img = $db->loadObjectList('item_id');
					}
					$query ="SELECT * FROM #__djcf_profiles WHERE user_id IN (".$uid_list.") ";
					$db->setQuery($query);
					$profiles_details = $db->loadObjectList('user_id');
					
				}
				
				
				for($i=0;$i<count($items);$i++){
					$cf_found =0;
					$items[$i]->fields = array();
					foreach($custom_fields as $cf){
						if($items[$i]->id==$cf->item_id){
							$cf_found =1;
							if($cf->type=='date'){
								if($cf->value_date!='0000-00-00'){
									$items[$i]->fields[$cf->field_id] = $cf->value_date;
								}							
							}elseif($cf->type=='date_from_to'){
								 // $date_start = $cf->value_date && $cf->value_date != '0000-00-00' ? DJClassifiedsTheme::formatDate(strtotime($cf->value_date)) : '';
								 // $date_end = $cf->value_date_to && $cf->value_date_to != '0000-00-00' ? DJClassifiedsTheme::formatDate(strtotime($cf->value_date_to)) : '';
								 $date_start = $cf->value_date && $cf->value_date != '0000-00-00' ? JHtml::_('date', strtotime($cf->value_date), 'Y-m-d') : '';
								 $date_end = $cf->value_date_to && $cf->value_date_to != '0000-00-00' ? JHtml::_('date', strtotime($cf->value_date_to), 'Y-m-d') : '';
								 $items[$i]->fields[$cf->field_id] = $date_start.($date_end ? ' - '.$date_end : '');
							}elseif($cf->type=='date_min_max'){
								$datetime_start = $cf->value_date_start == '0000-00-00 00:00:00' ? '' : $cf->value_date_start;
								$datetime_end = $cf->value_date_end == '0000-00-00 00:00:00' ? '' : $cf->value_date_end;
								$date_output = ''; $date_start = ''; $time_start = ''; $date_end = ''; $time_end = '';
								
								if($datetime_start){
									$date_start = new JDate($datetime_start);
									$date_start = $date_start->format('j M, Y');
									if($cf->date_use_time){
										$time_start = new JDate($datetime_start);
										$time_start = $time_start->format('H:i');
									}
								}
								if($datetime_end){
									$date_end = new JDate($datetime_end);
									$date_end = $date_end->format('j M, Y');
									if($cf->date_use_time){
										$time_end = new JDate($datetime_end);
										$time_end = $time_end->format('H:i');
									}
								}
								
								$delimiter = $date_end ? ' - ' : '';
	
								if(!$cf->all_day && $date_start==$date_end){
									$date_output = $date_start.' '.$time_start.$delimiter.$time_end;
								}elseif($cf->all_day && $date_start==$date_end){
									$date_output = $date_start;
								}elseif($cf->all_day && $date_start!=$date_end){
									$date_output = $date_start.$delimiter.$date_end;
								}else{
									$date_output = $date_start.' '.$time_start.$delimiter.$date_end.' '.$time_end;
								}
								
								$items[$i]->fields[$cf->field_id] = $date_output;							
							}else{
								$items[$i]->fields[$cf->field_id] = $cf->value;
							}
							
						}else if($cf_found){
							break;
						}
					}
					
					$img_found =0;
					$items[$i]->images = array();
					foreach($items_img as $img){
						if($items[$i]->id==$img->item_id){
							$img_found =1;							
							$items[$i]->images[]=$img;								
						}else if($img_found){
							break;
						}
					}	

					$items[$i]->extra_cats = array();
					if($icats){
						$ic_found =0;						
						foreach($icats as $ic){
							if($items[$i]->id==$ic->item_id){
								$ic_found =1;								
								$items[$i]->extra_cats[] = $ic;																	
							}else if($ic_found){
								break;
							}
						}
					}
					
					$items[$i]->profile = array();
					if($items[$i]->user_id){
						$items[$i]->profile['img'] = '';
						if(isset($profiles_img[$items[$i]->user_id])){
							$items[$i]->profile['img'] = $profiles_img[$items[$i]->user_id];
						}
						if(isset($profiles_details[$items[$i]->user_id])){
							$items[$i]->profile['details'] = $profiles_details[$items[$i]->user_id];
						}
					}
					
				}
			}
			
			//$db= &JFactory::getDBO();$db->setQuery($query);$items=$db->loadObjectList();
			//echo '<pre>';print_r($db);print_r($items);echo '<pre>';die();				
		return $items;
	}
	
	function getCountItems($catlist=''){
			JPluginHelper::importPlugin('djclassifieds');
			$par 	=	JComponentHelper::getParams( 'com_djclassifieds' );
			$user 	=	JFactory::getUser();
			$db 	= 	JFactory::getDBO();
			$cid	= 	JRequest::getInt('cid',0);
			$app	=   JFactory::getApplication();
			$dispatcher = JDispatcher::getInstance();
			
			$where = '';
			$mcat_lj = '';
			
			if($catlist){
				$where = ' AND (i.cat_id IN ('.$catlist.') OR mc.mcat_c>0) ';
				$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".$catlist.") GROUP BY item_id ) mc ON i.id=mc.item_id ";
			}
			$uid=JRequest::getVar('uid','0','','int'); 
			if($uid>0){
				$where .= " AND i.user_id=".$uid." ";
			}

			$fav_lj='';
			if($par->get('favourite','1') && $user->id>0){
				$fav_lj = "LEFT JOIN ( SELECT * FROM #__djcf_favourites WHERE user_id=".$user->id.") f ON i.id=f.item_id ";
				$fav=JRequest::getVar('fav','0','','int');				
				if($fav>0){
					$where .= " AND f.id IS NOT NULL ";
				}
			}
				$search ='';
				$search_fields='';
				$cat_id = 0;
				$reg_id = 0;
				$search_radius_v = '';
				$search_radius_h = '';
				$search_img_count_lj = '';
				if(JRequest::getVar('se','0','','string')!='0'){			
					if(JRequest::getVar('search',JText::_('COM_DJCLASSIFIEDS_SEARCH'),'','string')!=JText::_('COM_DJCLASSIFIEDS_SEARCH') && JRequest::getVar('search','','','string')){						
						if($par->get('authorname','name')=='name'){
							$u_name_search = 'u.name';
						}else{
							$u_name_search = 'u.username';
						}
						if($par->get('search_type_phrase',0)==1){
							$se_words = explode(' ',$db->escape(JRequest::getVar('search','','','string'), true));
							foreach($se_words as $se_w){
								if(strlen($se_w)>2){
									$search .= " AND (CONCAT_WS(i.name,i.intro_desc,i.description,i.contact) LIKE ".$db->Quote('%'.$se_w.'%')." OR c.name LIKE ".$db->Quote('%'.$se_w.'%')." OR r.name LIKE ".$db->Quote('%'.$se_w.'%')." OR i.id=".$db->Quote($se_w)." OR ".$u_name_search." LIKE ".$db->Quote($se_w)." ) ";
								}
							}
						}else{
							$search_word = $db->Quote('%'.$db->escape(JRequest::getVar('search','','','string'), true).'%');
							$search_word2 = $db->Quote($db->escape(JRequest::getVar('search','','','string'), true));
							$search = " AND (CONCAT_WS(i.name,i.intro_desc,i.description,i.contact) LIKE ".$search_word." OR c.name LIKE ".$search_word." OR r.name LIKE ".$search_word." OR i.id=".$search_word2." OR ".$u_name_search."=".$search_word2." ) ";
						}
						
					}
					
					if(isset($_GET['se_cats'])){
						if(is_array($_GET['se_cats'])){
							$cat_id= end($_GET['se_cats']);
							if($cat_id=='' && count($_GET['se_cats'])>2){
								$cat_id =$_GET['se_cats'][count($_GET['se_cats'])-2];
							}	
						}else{
							$cat_ids = explode(',', JRequest::getVar('se_cats'));
							$cat_id = end($cat_ids);
						}											
					}
					
					$cat_id = str_ireplace('p', '', $cat_id);
					$cat_id=(int)$cat_id;
											
					if($cat_id>0){
						$cats= DJClassifiedsCategory::getSubCat($cat_id,1);					
						$catlist= $cat_id;			
						foreach($cats as $c){
							$catlist .= ','. $c->id;
						}
						$search .= ' AND (i.cat_id IN ('.$catlist.') OR mc.mcat_c>0) ';
						$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".$catlist.") GROUP BY item_id ) mc ON i.id=mc.item_id ";
					}
					
					$se_ef = JRequest::getInt('ef',0);
					//if($cat_id>0 || $se_ef){
						$search_fields = $this->getSearchFields($cat_id,$se_ef);
					//}
					
					if(isset($_GET['se_regs'])){
						if(is_array($_GET['se_regs'])){
							$reg_id= end($_GET['se_regs']);
							if($reg_id=='' && count($_GET['se_regs'])>2){
								$reg_id =$_GET['se_regs'][count($_GET['se_regs'])-2];
							}								
						}else{
							$reg_ids = explode(',', JRequest::getVar('se_regs'));
							$reg_id = end($reg_ids);
						}
						$reg_id=(int)$reg_id;
					}


					if($reg_id>0){
						$regs= DJClassifiedsRegion::getSubReg($reg_id,1);										
						$reglist= $reg_id;			
						foreach($regs as $r){
							$reglist .= ','. $r->id;
						}
						$search .= ' AND i.region_id IN ('.$reglist.') ';						
					}else{
						$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
						if($reglist){
							$search .= ' AND i.region_id IN ('.$reglist.') ';
						}
					}
					
					
					$se_price_from = JRequest::getInt('se_price_f','');
					$se_price_to = JRequest::getInt('se_price_t','');
					if($se_price_from){
						$search .= " AND ABS(i.price) >= '".$se_price_from."' ";
					}
					
					if($se_price_to && $se_price_to>=$se_price_from){
						$search .= " AND ABS(i.price) <= '".$se_price_to."' ";
					}	
					
					$type_id=JRequest::getInt('se_type_id','0'); 
					if($type_id>0){
						$where .= " AND i.type_id=".$type_id." ";
					}				
					
					$days_l=JRequest::getInt('days_l','0');
					if($days_l>0){
						$date_limit = date("Y-m-d H:i:s",mktime(date("H"), date("i"), date("s"), date("m"), date("d")-$days_l, date("Y")));
						$where .= " AND i.date_start >= '".$date_limit."' ";
					}
				
					$only_img=JRequest::getInt('se_only_img',0); 
					if($only_img==1){
						$search .= " AND img.img_c>0 ";
						$search_img_count_lj = "LEFT JOIN ( SELECT COUNT(img.id) as img_c, img.item_id FROM #__djcf_images img
												WHERE img.type='item' GROUP BY item_id ) img ON i.id=img.item_id ";
					}
					
					$only_video=JRequest::getInt('se_only_video',0); 
					if($only_video==1){
						$search .= " AND i.video!='' ";
					}
					
					$only_auctions=JRequest::getInt('se_only_auctions',0);
					if($only_auctions==1){
						$search .= " AND i.auction = 1 ";
					}
					
					$only_buynow=JRequest::getInt('se_only_buynow',0);
					if($only_buynow==1){
						$search .= " AND i.buynow = 1 ";
					}
					
					$only_price_negotiable=JRequest::getInt('se_only_price_neg',0);
					if($only_price_negotiable==1){
						$search .= " AND i.price_negotiable = 1 ";
					}
					
					$postcode=JRequest::getVar('se_postcode','');
					$radius=JRequest::getFloat('se_radius',0);
					$se_address=JRequest::getVar('se_address','');
					$se_geoloc=JRequest::getVar('se_geoloc','');
					if($radius){
						$radius_unit=JRequest::getCmd('se_radius_unit','km');
						if($radius_unit=='mile'){
							$radius_unit_v = 3959;
						}else{
							$radius_unit_v = 6371;
						}
						
						if($se_geoloc){
							$user_latlog = explode('_', $_COOKIE["djcf_latlon"]);							
							$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$user_latlog[0].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$user_latlog[1].') ) + sin( radians('.$user_latlog[0].') ) * sin( radians( i.latitude ) ) ) ) AS distance ';
							$search_radius_h = 'HAVING distance < '.$radius.' ';
						}else if($postcode!='' && $postcode != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_POSTCODE')){
							$postcode_country=JRequest::getVar('se_postcode_c','');
							$post_coord = DJClassifiedsGeocode::getLocationPostCode($postcode,$postcode_country);																			
							if($post_coord){
								$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$post_coord['lat'].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$post_coord['lng'].') ) + sin( radians('.$post_coord['lat'].') ) * sin( radians( i.latitude ) ) ) ) AS distance ';
								$search_radius_h = 'HAVING distance < '.$radius.' ';
							}else{							
								//$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_POSTCODE_WE_OMIITED_RANGE_RESTRICTION'),'notice');
							}
						}else if($se_address!='' && $se_address != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_ADDRESS')){
							$se_address_coord = DJClassifiedsGeocode::getLocation($se_address);						
							if($se_address_coord){
								$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$se_address_coord['lat'].') ) * cos( radians( i.latitude ) ) * cos( radians( i.longitude ) - radians('.$se_address_coord['lng'].') ) + sin( radians('.$se_address_coord['lat'].') ) * sin( radians( i.latitude ) ) ) ) AS distance ';
								$search_radius_h = 'HAVING distance < '.$radius.' ';
							}else{							
								//$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_ADDRESS_WE_OMIITED_RANGE_RESTRICTION'),'notice');
							}
						}
					}
					
					
					if(JRequest::getInt('se_also_18',0)==0){
						$search .= " AND c.restriction_18=0 ";
					}
						
				}else{
					if($par->get('restriction_18_allads',0)==1 && !isset($_COOKIE["djcf_warning18"])){
						$where .= " AND c.restriction_18=0 ";
					}
					
					$reg_id = JRequest::getInt('rid',0);
					if($reg_id>0){
						$regs= DJClassifiedsRegion::getSubReg($reg_id,1);
						$reglist= $reg_id;
						foreach($regs as $r){
							$reglist .= ','. $r->id;
						}
						$where .= ' AND i.region_id IN ('.$reglist.') ';
					}else{
						$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
						if($reglist){
							$where .= ' AND i.region_id IN ('.$reglist.') ';
						}
					}
				}
			

			$date_time = JFactory::getDate();
			$date_exp=$date_time->toSQL();
			$date_now = date("Y-m-d H:i:s");
			
			if($par->get('show_archived',0)==2){
				$where .= " AND (( i.published=1 AND i.date_exp > '".$date_now."') OR i.published=2) ";				
			}else{
				$where .= " AND i.published=1 AND i.date_exp > '".$date_now."' ";
			}
				
			$groups_acl = '0,'.implode(',', $user->getAuthorisedViewLevels());
			$where .= " AND c.access_view IN (" . $groups_acl . ") ";				
						
			$dispatcher->trigger('onPrepareDJClassifiedsListingQueryWhere', array (&$where, &$par));
			
			$query = "SELECT count(i.id) FROM (SELECT i.id ".$search_radius_v." FROM ".$search_fields." #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON i.cat_id = c.id "
					."LEFT JOIN #__djcf_regions r ON i.region_id = r.id "
					."LEFT JOIN #__users u ON i.user_id = u.id "
					.$search_img_count_lj
					.$fav_lj
					.$mcat_lj
					."WHERE i.blocked=0 AND c.published=1 "
					.$where.$search;		
					
					if($search_fields){
						$query .=" AND sf.item_id=i.id ";			
					}
					$query .= $search_radius_h;
					$query .=" ) as i ";
					
						
				
				$db->setQuery($query);
				$items_count=$db->loadResult();
				
			//	echo '<pre>';print_r($db);print_r($items_count);echo '<pre>';die();	
			return $items_count;
	}	
	
	function getUserName($uid){
			$par = JComponentHelper::getParams( 'com_djclassifieds' );
			
			if($par->get('authorname','name')=='name'){
				$u_name = 'name ';
			}else{
				$u_name = 'username as name';
			}
									
			$db= JFactory::getDBO();			
			$query = "SELECT ".$u_name." FROM #__users WHERE id=".$uid." LIMIT 1";
			$db->setQuery($query);
			$username=$db->loadResult();
			return $username;
	}
	
	
	function getMainCat($cat_id){						
			$db			= JFactory::getDBO();
			$user 		=	JFactory::getUser();
			$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
			$groups_acl = implode(',', $user->getAuthorisedViewLevels());
			
			$query = "SELECT * FROM #__djcf_categories "
					."WHERE id=".$cat_id." AND access_view IN (" . $groups_acl . ")  LIMIT 1";
			$db->setQuery($query);
			$cat_name=$db->loadObject();
			
			if(!$cat_name){
				if($par->get('404_cat_redirect','0')==1){
					throw new Exception(JText::_('COM_DJCLASSIFIEDS_CATEGORY_NOT_AVAILABLE'), 404);
				}
			}
			
			return $cat_name;
	}	
	
	function getMainRegions($reg_id){						
			$db= JFactory::getDBO();
			$query = "SELECT * FROM #__djcf_regions WHERE id=".$reg_id." LIMIT 1";
			$db->setQuery($query);
			$reg_name=$db->loadObject();
			return $reg_name;
	}		
		
	
	function getSearchFields($cat_id, $se_ef){
		$search_fields = '';
			$session = JFactory::getSession();							
			/*$cat_id= end($_GET['se_cats']);
			if(!$cat_id){
				$cat_id =$_GET['se_cats'][count($_GET['se_cats'])-2];
			}	
			$cat_id = str_ireplace('p', '', $cat_id); */
			
			$fields = array();
			
			$db=JFactory::getDBO();
			if($cat_id){
				$query = "SELECT f.* FROM #__djcf_fields f, #__djcf_fields_xref fx "
						."WHERE fx.field_id=f.id AND fx.cat_id=".$cat_id."";
				$db->setQuery($query);
				$fields=$db->loadObjectList();
			}else if($se_ef){
				$query = "SELECT f.* FROM #__djcf_fields f "
						."WHERE f.published = 1 ";
				$db->setQuery($query);
				$fields_all=$db->loadObjectList();
				
				foreach($fields_all as $field){
					if(isset($_GET['se_'.$field->id])){
						$fields[] = $field;
						break;
					}
				}				
			}else{
				$query ="SELECT COUNT(id) FROM #__djcf_categories ";
				$db->setQuery($query);
				$cats_c =$db->loadResult();
				 
				$query ="SELECT f.*, count(fx.id) as cat_a FROM #__djcf_fields f, #__djcf_fields_xref fx  "
						."WHERE f.id=fx.field_id AND f.published=1 AND f.access=0 AND f.search_type!=''
	     					AND f.in_search=1 AND f.source<2 AND f.in_search_on_start=1 "
						."GROUP BY f.id ORDER BY f.ordering";
				$db->setQuery($query);
				$fields_list_tmp =$db->loadObjectList();
				if(count($fields_list_tmp)){
					foreach($fields_list_tmp as $field){
						if($field->cat_a>=$cats_c){
							$fields[]=$field;
						}
					}
				}
			} 
			
			if(count($fields)==0){
				return null;
			}
			
				
				$search_fields = 'SELECT * FROM (SELECT COUNT( * ) AS c, item_id FROM (';
				$sf_count = 0;
			
				foreach($fields as $f){
					if($f->type=='date'){						
						if($f->search_type=='inputbox_min_max'){
							if(isset($_GET['se_'.$f->id.'_min']) && isset($_GET['se_'.$f->id.'_max'])){	
								$f_v1 =  $db->escape($_GET['se_'.$f->id.'_min']);
								$f_v2 =  $db->escape($_GET['se_'.$f->id.'_max']);
								if($f_v1!='' || $f_v1!=''){
									$sf_count ++;	
									$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
										if($f_v1!=''){
											$search_fields .= " AND f.value_date >= '".$f_v1."'";
										}
										if($f_v2!=''){
											$search_fields .= " AND f.value_date <= '".$f_v2."' ";
										}
									
									$search_fields .= " ) UNION ";
									$session->set('se_'.$f->id.'_min',$f_v1);
									$session->set('se_'.$f->id.'_max',$f_v2);							
								}
							}
							if(!isset($_GET['se_'.$f->id.'_min'])){
								$session->set('se_'.$f->id.'_min','');
							}
							if(!isset($_GET['se_'.$f->id.'_max'])){
								$session->set('se_'.$f->id.'_max','');
							} 								
						}else{
							if(isset($_GET['se_'.$f->id])){							
								$f_v1 =  $db->escape($_GET['se_'.$f->id]);
								
								if($f_v1!=''){
									$sf_count ++;
									$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." AND f.value_date = '".$f_v1."' ) UNION ";
									$session->set('se_'.$f->id,$f_v1);								
								}else{
									$session->set('se_'.$f->id,'');	
								}
							}else{
								$session->set('se_'.$f->id,'');	
							}
						}
					}else if($f->type=='date_from_to'){						
						if($f->search_type=='inputbox_min_max'){
							if(isset($_GET['se_'.$f->id.'_min']) && isset($_GET['se_'.$f->id.'_max'])){	
								$f_v1 =  $db->escape($_GET['se_'.$f->id.'_min']);
								$f_v2 =  $db->escape($_GET['se_'.$f->id.'_max']);
								if($f_v1!='' || $f_v1!=''){
									$sf_count ++;	
									$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
										if($f_v1!=''){
											$search_fields .= " AND f.value_date_to >= '".$f_v1."'";
										}
										if($f_v2!=''){
											$search_fields .= " AND f.value_date <= '".$f_v2."' ";
										}
									
									$search_fields .= " ) UNION ";
									$session->set('se_'.$f->id.'_min',$f_v1);
									$session->set('se_'.$f->id.'_max',$f_v2);							
								}
							}
							if(!isset($_GET['se_'.$f->id.'_min'])){
								$session->set('se_'.$f->id.'_min','');
							}
							if(!isset($_GET['se_'.$f->id.'_max'])){
								$session->set('se_'.$f->id.'_max','');
							} 								
						}else{
							if(isset($_GET['se_'.$f->id])){							
								$f_v1 =  $db->escape($_GET['se_'.$f->id]);
								
								if($f_v1!=''){
									$sf_count ++;
									$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." AND f.value_date = '".$f_v1."' ) UNION ";
									$session->set('se_'.$f->id,$f_v1);								
								}else{
									$session->set('se_'.$f->id,'');	
								}
							}else{
								$session->set('se_'.$f->id,'');	
							}
						}
					}else{	
						if($f->search_type=='select_min_max' || $f->search_type=='inputbox_min_max'){
							if(isset($_GET['se_'.$f->id.'_min']) || isset($_GET['se_'.$f->id.'_max'])){	
								$f_v1 =  $db->escape($_GET['se_'.$f->id.'_min']);
								$f_v2 =  $db->escape($_GET['se_'.$f->id.'_max']);
								if($f_v1!='' || $f_v2!=''){
									$sf_count ++;	
									if(is_numeric($f_v1) || is_numeric($f_v2)){
										$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
											if(is_numeric($f_v1)){
												$search_fields .= " AND f.value >= ".$f_v1." ";
											}
											if(is_numeric($f_v2)){
												$search_fields .= " AND f.value <= ".$f_v2." ";
											}										
										$search_fields .= "  ) UNION ";	
									}else{
										$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
											if($f_v1){
												$search_fields .= " AND f.value >= '".$f_v1."'";
											}
											if($f_v2){
												$search_fields .= " AND f.value <= '".$f_v2."' ";
											}
										 	
										$search_fields .= " ) UNION ";
									}
									$session->set('se_'.$f->id.'_min',$f_v1);
									$session->set('se_'.$f->id.'_max',$f_v2);
								}
							}
							if(!isset($_GET['se_'.$f->id.'_min'])){
								$session->set('se_'.$f->id.'_min','');
							}
							if(!isset($_GET['se_'.$f->id.'_max'])){
								$session->set('se_'.$f->id.'_max','');
							} 
								
						}elseif($f->search_type=='checkbox' || $f->search_type=='checkbox_accordion_o' || $f->search_type=='checkbox_accordion_c'){
							if(isset($_GET['se_'.$f->id])){
								$v_chec = array();
								$v_chec =  explode(',', $_GET['se_'.$f->id]);
								
								$f_v1 =';';
								//print_R($f);die();
								if(count($v_chec)>0){
									$sf_count ++;
									if($f->type=='checkbox'){
										$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
										if(count($v_chec)){
											$search_fields .= " AND (";											
											for($ch=0;$ch<count($v_chec);$ch++){
												if(!$ch){
												   $search_fields .= " f.value LIKE '%;".$db->escape($v_chec[$ch]).";%' OR f.value LIKE '".$db->escape($v_chec[$ch]).";%' ";
												}else{
												   $search_fields .= " OR f.value LIKE '%;".$db->escape($v_chec[$ch]).";%' OR f.value LIKE '".$db->escape($v_chec[$ch]).";%'  ";
												}
												$f_v1 .= $v_chec[$ch].';';
											}	
											$search_fields .=") ";
										}									
									}else{								
										$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
										for($ch=0;$ch<count($v_chec);$ch++){
											if($ch==0){
												$search_fields .= " AND ( f.value = '".$db->escape($v_chec[$ch])."' ";	
											}else{
												$search_fields .= " OR f.value = '".$db->escape($v_chec[$ch])."' ";
											}																				
											$f_v1 .= $v_chec[$ch].';';
										}
										$search_fields .= ') ';									
									}
										$search_fields .= " ) UNION ";
										$session->set('se_'.$f->id,$f_v1);
								}
								
							}else{
								$session->set('se_'.$f->id,'');	
							}						
						}
						elseif($f->search_type=='date_min_max'){
							$f_v1 =  isset($_GET['se_'.$f->id.'_min']) ? $db->escape($_GET['se_'.$f->id.'_min']) : '';
							$f_v2 =  isset($_GET['se_'.$f->id.'_max']) ? $db->escape($_GET['se_'.$f->id.'_max']) : '';
							if($f_v1!='' || $f_v2!=''){
								$sf_count ++;
								$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." ";
								if($f_v1){
									$search_fields .= " AND (CASE WHEN f.value_date_end='0000-00-00 00:00:00' THEN DATE_FORMAT(f.value_date_start,'%Y-%m-%d') ELSE DATE_FORMAT(f.value_date_end,'%Y-%m-%d') END) >= '".$f_v1."'";
								}
								if($f_v2){
									$search_fields .= " AND DATE_FORMAT(f.value_date_start,'%Y-%m-%d') <= '".$f_v2."' ";
								}
								$search_fields .= " ) UNION ";
								$session->set('se_'.$f->id.'_min',$f_v1);
								$session->set('se_'.$f->id.'_max',$f_v2);
							}
							if(!isset($_GET['se_'.$f->id.'_min'])){
								$session->set('se_'.$f->id.'_min','');
							}
							if(!isset($_GET['se_'.$f->id.'_max'])){
								$session->set('se_'.$f->id.'_max','');
							}
						}
						else{
							if(isset($_GET['se_'.$f->id])){							
								$f_v1 =  $db->escape($_GET['se_'.$f->id]);
								
								if($f_v1!=''){
									$sf_count ++;
									$search_fields .= " (SELECT * FROM #__djcf_fields_values f WHERE f.field_id=".$f->id." AND f.value LIKE '%".$f_v1."%' ) UNION ";
									$session->set('se_'.$f->id,$f_v1);								
								}else{
									$session->set('se_'.$f->id,'');	
								}
							}else{
								$session->set('se_'.$f->id,'');	
							}						
						}
					}						
				}

					if($sf_count>0){
						$search_fields = '('.substr($search_fields, 0, -6);
						$search_fields .= ' ) AS f GROUP BY f.item_id ) f WHERE f.c = '.$sf_count.' ) sf, ';	
					}else{
						$search_fields = '';
					}
					
					//print_r($search_fields);die();

					
					return $search_fields;
				
	}

	function resetSearchFilters(){
		$session = JFactory::getSession();								
			
		$db= JFactory::getDBO();
		$query = "SELECT f.* FROM #__djcf_fields f "
				."WHERE f.published=1";
		$db->setQuery($query);
		$fields=$db->loadObjectList();
		
			foreach($fields as $f){
				if($f->search_type=='select_min_max' || $f->search_type=='inputbox_min_max' || $f->search_type=='date_min_max'){
					$session->set('se_'.$f->id.'_min','');
					$session->set('se_'.$f->id.'_max','');
				}else{
					$session->set('se_'.$f->id,'');															
				}									
			}

		return null; 
		
	} 
	
	function getTypes(){						
		$db= JFactory::getDBO();
		$query = "SELECT * FROM #__djcf_types WHERE published=1";
		$db->setQuery($query);
		$types=$db->loadObjectList('id');			
			foreach($types as $type){
				$registry = new JRegistry();		
				$registry->loadString($type->params);
				$type->params = $registry->toObject();
			}
		//echo '<pre>';print_r($types);die();		
		return $types;
	}	
	
	function getCustomFields(){
		$db= JFactory::getDBO();
		$query = "SELECT * FROM #__djcf_fields WHERE published=1 AND (in_table>0 OR in_blog=1) ORDER BY name";
		$db->setQuery($query);
		$cf=$db->loadObjectList('id');
		//echo '<pre>';print_r($cf);die();
		return $cf;
	}

	function getCatImages($cats_id){
		$db= JFactory::getDBO();
		$query = "SELECT * FROM #__djcf_images WHERE type='category' AND item_id IN (".$cats_id.") GROUP BY item_id ";
		$db->setQuery($query);
		$cat_img=$db->loadObjectList('item_id');
		//echo '<pre>';print_r($db);print_r($cat_img);die();
		return $cat_img;
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
	
}

