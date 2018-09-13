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

class DjclassifiedsModelProfiles extends JModelLegacy{	
	
	function getItems(){
		//$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$app = JFactory::getApplication();
		$par = DJClassifiedsParams::getParams();
		$limit		= JRequest::getVar('limit', $par->get('limit_djitem_show'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$db			= JFactory::getDBO();
		$cat_id = 0;
		$reg_id = 0;

		$menuitem = $app->getMenu()->getActive();
		$user_type = $menuitem ? $menuitem->params->get('user_type','') : '';
		
		$search = '';
		$search_fields='';
		if(JRequest::getVar('p_search','','','string')){							
			$search_word = $db->Quote('%'.$db->escape(JRequest::getVar('p_search','','','string'), true).'%');
			$search .= "AND CONCAT_WS(u.name,u.username,p.address, r.name) LIKE ".$search_word." ";
		}

			if(isset($_GET['p_se_regs'])){
				if(is_array($_GET['p_se_regs'])){
					$reg_id= end($_GET['p_se_regs']);
					if($reg_id=='' && count($_GET['p_se_regs'])>=2){
						$reg_id =$_GET['p_se_regs'][count($_GET['p_se_regs'])-2];
					}								
				}else{
					$reg_ids = explode(',', JRequest::getVar('p_se_regs'));
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
				$search .= ' AND p.region_id IN ('.$reglist.') ';						
			}else{
				$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
				if($reglist){
					$search .= ' AND p.region_id IN ('.$reglist.') ';
				}
			}
			
			
			$postcode=JRequest::getVar('p_se_postcode','');
			$radius=JRequest::getFloat('p_se_radius',0);
			$se_address=JRequest::getVar('p_se_address','');
			$se_geoloc=JRequest::getVar('p_se_geoloc','');
				
			if($radius){
				$radius_unit=JRequest::getCmd('p_se_radius_unit','km');
				if($radius_unit=='mile'){
					$radius_unit_v = 3959;
				}else{
					$radius_unit_v = 6371;
				}
			
				if($se_geoloc){
					$user_latlog = explode('_', $_COOKIE["djcf_latlon"]);
					$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$user_latlog[0].') ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians('.$user_latlog[1].') ) + sin( radians('.$user_latlog[0].') ) * sin( radians( p.latitude ) ) ) ) AS distance ';
					$search_radius_h = 'HAVING distance < '.$radius.' ';
				}else if($postcode!='' && $postcode != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_POSTCODE')){
					$postcode_country=JRequest::getVar('p_se_postcode_c','');
					$post_coord = DJClassifiedsGeocode::getLocationPostCode($postcode,$postcode_country);
					if($post_coord){
						$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$post_coord['lat'].') ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians('.$post_coord['lng'].') ) + sin( radians('.$post_coord['lat'].') ) * sin( radians( p.latitude ) ) ) ) AS distance ';
						$search_radius_h = 'HAVING distance < '.$radius.' ';
					}else{
						$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_POSTCODE_WE_OMIITED_RANGE_RESTRICTION'),'notice');
					}
				}else if($se_address!='' && $se_address != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_ADDRESS')){
					$se_address_coord = DJClassifiedsGeocode::getLocation($se_address);
					if($se_address_coord){
						$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$se_address_coord['lat'].') ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians('.$se_address_coord['lng'].') ) + sin( radians('.$se_address_coord['lat'].') ) * sin( radians( p.latitude ) ) ) ) AS distance ';
						$search_radius_h = 'HAVING distance < '.$radius.' ';
					}else{
						$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_ADDRESS_WE_OMIITED_RANGE_RESTRICTION'),'notice');
					}
				}
			}			
			
			
			if(isset($_GET['p_se_cats'])){
				if(is_array($_GET['p_se_cats'])){
					$cat_id= end($_GET['p_se_cats']);
					if($cat_id=='' && count($_GET['p_se_cats'])>2){
						$cat_id =$_GET['p_se_cats'][count($_GET['p_se_cats'])-2];
					}	
				}else{
					$cat_ids = explode(',', JRequest::getVar('p_se_cats'));
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
				$search .= ' AND (p.cat_id IN ('.$catlist.')) ';
			}	
			
			$search_fields = $this->getSearchFields();
			if($search_fields){
				$search .=" AND sf.user_id=u.id ";
			}
			
		$order = $app->input->getCmd('order', 'activity');
		$ord_t = $app->input->getCmd('ord_t', 'desc');
		
		
		$ord="u.lastvisitDate ";
			
			if($order=="cat"){
				$ord="c.name ";
			}else if($order=="loc"){
				$ord="r.name ";
			} 
			
			if($ord_t == 'desc'){
				$ord .= 'DESC';
			}else{
				$ord .= 'ASC';
			}
		
			
		
		$query = "SELECT u.id as u_id, u.name, u.username, u.email, p.*, img.path, r.name as r_name ".$search_radius_v." "
				."FROM ".$search_fields." #__users u, #__djcf_profiles p "				 
				."LEFT JOIN (SELECT item_id, CONCAT(path,name,'.',ext) path FROM #__djcf_images WHERE type='profile') img ON p.user_id=img.item_id "
				."LEFT JOIN #__djcf_regions r ON p.region_id=r.id "
				.($user_type ? "INNER JOIN (SELECT user_id FROM #__user_usergroup_map WHERE group_id=".$user_type.") v ON v.user_id=p.user_id " : "")
				//."WHERE p.user_id=u.id AND u.lastvisitDate !='0000-00-00 00:00:00' ".$search
				."WHERE p.user_id=u.id ".$search.$search_radius_h
				."ORDER BY ".$ord;
	//print_r($query);//die();
		$items = $this->_getList($query, $limitstart, $limit);
		
		//print_r($items);die();
		


		$profile_fields = self::getProfileFields();
		
		foreach($items as $item){	
			$item->profile_fields = array();
			foreach($profile_fields as $pf){		
				if($item->u_id==$pf->user_id){
					$item->profile_fields[$pf->field_id] = $pf;
				}
			}
		}

		return $items;
	}
	
	function getCountItems(){
			$app = JFactory::getApplication();
			$db= JFactory::getDBO();
			$user = JFactory::getUser();
			$cat_id = 0;
			$reg_id = 0;
		
			$menuitem = $app->getMenu()->getActive();
			$user_type = $menuitem ? $menuitem->params->get('user_type','11') : '11';
			
			$search = '';
			if(JRequest::getVar('p_search','','','string')){							
				$search_word = $db->Quote('%'.$db->escape(JRequest::getVar('p_search','','','string'), true).'%');
				$search .= "AND CONCAT_WS(u.name,u.username,p.address) LIKE ".$search_word." ";
			}
			if(isset($_GET['p_se_regs'])){
				if(is_array($_GET['p_se_regs'])){
					$reg_id= end($_GET['p_se_regs']);
					if($reg_id=='' && count($_GET['p_se_regs'])>=2){
						$reg_id =$_GET['p_se_regs'][count($_GET['p_se_regs'])-2];
					}								
				}else{
					$reg_ids = explode(',', JRequest::getVar('p_se_regs'));
					$reg_id = end($reg_ids);
				}
				$reg_id=(int)$reg_id;
			}

			if(isset($_GET['p_se_cats'])){
				if(is_array($_GET['p_se_cats'])){
					$cat_id= end($_GET['p_se_cats']);
					if($cat_id=='' && count($_GET['p_se_cats'])>2){
						$cat_id =$_GET['p_se_cats'][count($_GET['p_se_cats'])-2];
					}	
				}else{
					$cat_ids = explode(',', JRequest::getVar('p_se_cats'));
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
				$search .= ' AND (p.cat_id IN ('.$catlist.')) ';
			}
			
			$reg_id = 0;
			if($reg_id>0){
				$regs= DJClassifiedsRegion::getSubReg($reg_id,1);
														
				$reglist= $reg_id;			
				foreach($regs as $r){
					$reglist .= ','. $r->id;
				}
				$search .= ' AND p.region_id IN ('.$reglist.') ';						
			}else{
				$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
				if($reglist){
					$search .= ' AND p.region_id IN ('.$reglist.') ';
				}
			}
			
			$postcode=JRequest::getVar('p_se_postcode','');
			$radius=JRequest::getFloat('p_se_radius',0);
			$se_address=JRequest::getVar('p_se_address','');
			$se_geoloc=JRequest::getVar('p_se_geoloc','');
				
			if($radius){
				$radius_unit=JRequest::getCmd('p_se_radius_unit','km');
				if($radius_unit=='mile'){
					$radius_unit_v = 3959;
				}else{
					$radius_unit_v = 6371;
				}
			
				if($se_geoloc){
					$user_latlog = explode('_', $_COOKIE["djcf_latlon"]);
					$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$user_latlog[0].') ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians('.$user_latlog[1].') ) + sin( radians('.$user_latlog[0].') ) * sin( radians( p.latitude ) ) ) ) AS distance ';
					$search_radius_h = 'HAVING distance < '.$radius.' ';
				}else if($postcode!='' && $postcode != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_POSTCODE')){
					$postcode_country=JRequest::getVar('p_se_postcode_c','');
					$post_coord = DJClassifiedsGeocode::getLocationPostCode($postcode,$postcode_country);
					if($post_coord){
						$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$post_coord['lat'].') ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians('.$post_coord['lng'].') ) + sin( radians('.$post_coord['lat'].') ) * sin( radians( p.latitude ) ) ) ) AS distance ';
						$search_radius_h = 'HAVING distance < '.$radius.' ';
					}else{
						$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_POSTCODE_WE_OMIITED_RANGE_RESTRICTION'),'notice');
					}
				}else if($se_address!='' && $se_address != JText::_('COM_DJCLASSIFIEDS_SEARCH_MODULE_ADDRESS')){
					$se_address_coord = DJClassifiedsGeocode::getLocation($se_address);
					if($se_address_coord){
						$search_radius_v = ', ( '.$radius_unit_v.' * acos( cos( radians('.$se_address_coord['lat'].') ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians('.$se_address_coord['lng'].') ) + sin( radians('.$se_address_coord['lat'].') ) * sin( radians( p.latitude ) ) ) ) AS distance ';
						$search_radius_h = 'HAVING distance < '.$radius.' ';
					}else{
						$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_SORRY_WE_CANT_FIND_COORDINATES_FROM_ADDRESS_WE_OMIITED_RANGE_RESTRICTION'),'notice');
					}
				}
			}
			
			$search_fields = $this->getSearchFields();
			if($search_fields){
				$search .=" AND sf.user_id=u.id ";
			}
			
			$query = "SELECT count(u.id) ".$search_radius_v." "
					."FROM ".$search_fields." #__djcf_profiles p, #__users u "
					."WHERE p.user_id=u.id  ".$search.$search_radius_h;						

				$db->setQuery($query);
				$items_count=$db->loadResult();
				

			return $items_count;
	}
	
	static function getProfileFields(){
			$db= JFactory::getDBO();

			$query = "SELECT u.id user_id, pf.id field_id, pf.name field_name,pf.label, pf.type, pfv.value, pfv.value_date "
			."FROM #__users u "
			."LEFT JOIN #__djcf_fields_values_profile pfv ON u.id=pfv.user_id "
			."LEFT JOIN #__djcf_fields pf ON pfv.field_id=pf.id "
			."WHERE value!='' AND pf.in_blog=1 ";
						
			$db->setQuery($query);
			$profile_fields=$db->loadObjectList();

			return $profile_fields;
	}
	
	function getSearchFields(){
		$search_fields = '';
		$session = JFactory::getSession();
		/*$cat_id= end($_GET['p_se_cats']);
		 if(!$cat_id){
		 $cat_id =$_GET['p_se_cats'][count($_GET['p_se_cats'])-2];
		 }
		 $cat_id = str_ireplace('p', '', $cat_id); */				
			
		$db=JFactory::getDBO();
		
		$query ="SELECT f.* FROM #__djcf_fields f "
				."WHERE f.published=1 AND f.access=0 AND f.search_type!=''
	     				AND f.in_search=1 AND f.source=2 "
				."GROUP BY f.id ORDER BY f.ordering";
		$db->setQuery($query);
		$fields =$db->loadObjectList();
		
		//print_r($fields);die();
			
		if(count($fields)==0){
			return null;
		}
			
	
		$search_fields = 'SELECT * FROM (SELECT COUNT( * ) AS c, user_id FROM (';
		$sf_count = 0;
			
		foreach($fields as $f){
			if($f->type=='date'){
				if($f->search_type=='inputbox_min_max'){
					if(isset($_GET['p_se_'.$f->id.'_min']) && isset($_GET['p_se_'.$f->id.'_max'])){
						$f_v1 =  $db->escape($_GET['p_se_'.$f->id.'_min']);
						$f_v2 =  $db->escape($_GET['p_se_'.$f->id.'_max']);
						if($f_v1!='' || $f_v1!=''){
							$sf_count ++;
							$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
							if($f_v1!=''){
								$search_fields .= " AND f.value_date >= '".$f_v1."'";
							}
							if($f_v2!=''){
								$search_fields .= " AND f.value_date <= '".$f_v2."' ";
							}
								
							$search_fields .= " ) UNION ";
							$session->set('p_se_'.$f->id.'_min',$f_v1);
							$session->set('p_se_'.$f->id.'_max',$f_v2);
						}
					}
					if(!isset($_GET['p_se_'.$f->id.'_min'])){
						$session->set('p_se_'.$f->id.'_min','');
					}
					if(!isset($_GET['p_se_'.$f->id.'_max'])){
						$session->set('p_se_'.$f->id.'_max','');
					}
				}else{
					if(isset($_GET['p_se_'.$f->id])){
						$f_v1 =  $db->escape($_GET['p_se_'.$f->id]);
	
						if($f_v1!=''){
							$sf_count ++;
							$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." AND f.value_date = '".$f_v1."' ) UNION ";
							$session->set('p_se_'.$f->id,$f_v1);
						}else{
							$session->set('p_se_'.$f->id,'');
						}
					}else{
						$session->set('p_se_'.$f->id,'');
					}
				}
			}else if($f->type=='date_from_to'){
				if($f->search_type=='inputbox_min_max'){
					if(isset($_GET['p_se_'.$f->id.'_min']) && isset($_GET['p_se_'.$f->id.'_max'])){
						$f_v1 =  $db->escape($_GET['p_se_'.$f->id.'_min']);
						$f_v2 =  $db->escape($_GET['p_se_'.$f->id.'_max']);
						if($f_v1!='' || $f_v1!=''){
							$sf_count ++;
							$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
							if($f_v1!=''){
								$search_fields .= " AND f.value_date_to >= '".$f_v1."'";
							}
							if($f_v2!=''){
								$search_fields .= " AND f.value_date <= '".$f_v2."' ";
							}
								
							$search_fields .= " ) UNION ";
							$session->set('p_se_'.$f->id.'_min',$f_v1);
							$session->set('p_se_'.$f->id.'_max',$f_v2);
						}
					}
					if(!isset($_GET['p_se_'.$f->id.'_min'])){
						$session->set('p_se_'.$f->id.'_min','');
					}
					if(!isset($_GET['p_se_'.$f->id.'_max'])){
						$session->set('p_se_'.$f->id.'_max','');
					}
				}else{
					if(isset($_GET['p_se_'.$f->id])){
						$f_v1 =  $db->escape($_GET['p_se_'.$f->id]);
	
						if($f_v1!=''){
							$sf_count ++;
							$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." AND f.value_date = '".$f_v1."' ) UNION ";
							$session->set('p_se_'.$f->id,$f_v1);
						}else{
							$session->set('p_se_'.$f->id,'');
						}
					}else{
						$session->set('p_se_'.$f->id,'');
					}
				}
			}else{
				if($f->search_type=='select_min_max' || $f->search_type=='inputbox_min_max'){
					if(isset($_GET['p_se_'.$f->id.'_min']) || isset($_GET['p_se_'.$f->id.'_max'])){
						$f_v1 =  $db->escape($_GET['p_se_'.$f->id.'_min']);
						$f_v2 =  $db->escape($_GET['p_se_'.$f->id.'_max']);
						if($f_v1!='' || $f_v2!=''){
							$sf_count ++;
							if(is_numeric($f_v1) || is_numeric($f_v2)){
								$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
								if(is_numeric($f_v1)){
									$search_fields .= " AND f.value >= ".$f_v1." ";
								}
								if(is_numeric($f_v2)){
									$search_fields .= " AND f.value <= ".$f_v2." ";
								}
								$search_fields .= "  ) UNION ";
							}else{
								$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
								if($f_v1){
									$search_fields .= " AND f.value >= '".$f_v1."'";
								}
								if($f_v2){
									$search_fields .= " AND f.value <= '".$f_v2."' ";
								}
	
								$search_fields .= " ) UNION ";
							}
							$session->set('p_se_'.$f->id.'_min',$f_v1);
							$session->set('p_se_'.$f->id.'_max',$f_v2);
						}
					}
					if(!isset($_GET['p_se_'.$f->id.'_min'])){
						$session->set('p_se_'.$f->id.'_min','');
					}
					if(!isset($_GET['p_se_'.$f->id.'_max'])){
						$session->set('p_se_'.$f->id.'_max','');
					}
	
				}elseif($f->search_type=='checkbox' || $f->search_type=='checkbox_accordion_o' || $f->search_type=='checkbox_accordion_c'){
					if(isset($_GET['p_se_'.$f->id])){
						$v_chec = array();
						$v_chec =  explode(',', $_GET['p_se_'.$f->id]);
	
						$f_v1 =';';
						//print_R($f);die();
						if(count($v_chec)>0){
							$sf_count ++;
							if($f->type=='checkbox'){
								$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
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
								$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
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
							$session->set('p_se_'.$f->id,$f_v1);
						}
	
					}else{
						$session->set('p_se_'.$f->id,'');
					}
				}
				elseif($f->search_type=='date_min_max'){
					$f_v1 =  isset($_GET['p_se_'.$f->id.'_min']) ? $db->escape($_GET['p_se_'.$f->id.'_min']) : '';
					$f_v2 =  isset($_GET['p_se_'.$f->id.'_max']) ? $db->escape($_GET['p_se_'.$f->id.'_max']) : '';
					if($f_v1!='' || $f_v2!=''){
						$sf_count ++;
						$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." ";
						if($f_v1){
							$search_fields .= " AND (CASE WHEN f.value_date_end='0000-00-00 00:00:00' THEN DATE_FORMAT(f.value_date_start,'%Y-%m-%d') ELSE DATE_FORMAT(f.value_date_end,'%Y-%m-%d') END) >= '".$f_v1."'";
						}
						if($f_v2){
							$search_fields .= " AND DATE_FORMAT(f.value_date_start,'%Y-%m-%d') <= '".$f_v2."' ";
						}
						$search_fields .= " ) UNION ";
						$session->set('p_se_'.$f->id.'_min',$f_v1);
						$session->set('p_se_'.$f->id.'_max',$f_v2);
					}
					if(!isset($_GET['p_se_'.$f->id.'_min'])){
						$session->set('p_se_'.$f->id.'_min','');
					}
					if(!isset($_GET['p_se_'.$f->id.'_max'])){
						$session->set('p_se_'.$f->id.'_max','');
					}
				}
				else{
					if(isset($_GET['p_se_'.$f->id])){
						$f_v1 =  $db->escape($_GET['p_se_'.$f->id]);
	
						if($f_v1!=''){
							$sf_count ++;
							$search_fields .= " (SELECT * FROM #__djcf_fields_values_profile f WHERE f.field_id=".$f->id." AND f.value LIKE '%".$f_v1."%' ) UNION ";
							$session->set('p_se_'.$f->id,$f_v1);
						}else{
							$session->set('p_se_'.$f->id,'');
						}
					}else{
						$session->set('p_se_'.$f->id,'');
					}
				}
			}
		}
	
		if($sf_count>0){
			$search_fields = '('.substr($search_fields, 0, -6);
			$search_fields .= ' ) AS f GROUP BY f.user_id ) f WHERE f.c = '.$sf_count.' ) sf, ';
		}else{
			$search_fields = '';
		}
			
		//print_r($search_fields);die();
	
			
		return $search_fields;
	
	}	
	
}

