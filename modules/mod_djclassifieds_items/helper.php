<?php
/**
* @version 2.0
* @package DJ Classifieds Menu Module
* @subpackage DJ Classifieds Component
* @copyright Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer Ĺ�ukasz Ciastek - lukasz.ciastek@design-joomla.eu
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
require_once(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djimage.php');
require_once(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djseo.php');

class modDjClassifiedsItems{
	public static function getItems($params,$cfpar=null){
		
		if(!$cfpar){
			$cfpar = JComponentHelper::getParams( 'com_djclassifieds' );
		}
		
		$date_time 	= JFactory::getDate();
		$date_exp	= $date_time->toSQL();
		$date_now   = date("Y-m-d H:i:s");
		$db			= JFactory::getDBO();
		$user		= JFactory::getUser();
		$ord 		= "i.date_start DESC";
		$app		= JFactory::getApplication();
		$inputCookie  = $app->input->cookie;
		$prom_special_lj = "";
	
		if($params->get('items_ord')==1){
			$ord = "i.display DESC"; 
		}else if($params->get('items_ord')==2){
			$ord = "rand()";
		}else if($params->get('items_ord')==3){
			$ord = "i.name";
		}else if($params->get('items_ord')==4){
		    $prom_special_lj = "LEFT JOIN #__djcf_items_promotions ip ON i.id=ip.item_id AND ip.prom_id=5 ";
		    $ord = "ip.date_exp DESC";
		}
		
		$promoted='';
		$prom_list = array();
		if($params->get('only_p_special','0')==1){
			$prom_list[] = " i.promotions LIKE '%p_special%' "; 
		}
		if($params->get('only_p_first','0')==1){
			$prom_list[] = " i.promotions LIKE '%p_first%' "; 
		}
		if($params->get('only_p_bold','0')==1){
			$prom_list[] = " i.promotions LIKE '%p_bold%' "; 
		}
		if($params->get('only_p_border','0')==1){
			$prom_list[] = " i.promotions LIKE '%p_border%' "; 
		}
		if($params->get('only_p_bg','0')==1){
			$prom_list[] = " i.promotions LIKE '%p_bg%' "; 
		}
		
		if(count($prom_list)==1){
			$promoted=' AND '.$prom_list[0].' ';	
		}else if(count($prom_list)>1){
			$promoted=' AND ('.implode(' OR ', $prom_list).') ';
		}
		
		$item_ids = $params->get('items_ids','');
		if($item_ids){
			$item_ids = ' AND i.id IN ('.$item_ids.')';
		}else{
			$item_ids = '';
		}				
		
		$users_ids = $params->get('users_ids','');
		if($users_ids){
			$users_ids = ' AND i.user_id IN ('.$users_ids.')';
		}else{
			$users_ids = '';
		}

		$types_ids_v = $params->get('type_id','');
		$types_ids = '';
		if(is_array($types_ids_v)){
			if(count($types_ids_v)){			
			$types_ids = ' AND i.type_id IN ('.implode(',', $types_ids_v).')';
			}
		}		
		
		$cat_ids = $params->get('cat_id','0');
		$cid = JRequest::getInt('cid','0');
		$fallow_cat= '';
		$cat_list= '';			
		$mcat_lj = '';
		
		if($params->get('fallow_category')==1 && JRequest::getVar('option','')=='com_djclassifieds' && $cid>0){		
			$djcfcatlib = new DJClassifiedsCategory();
			$cats= $djcfcatlib->getSubCat($cid,1);				
			$catlist= $cid;			
			foreach($cats as $c){
				$catlist .= ','. $c->id;
			}
			$fallow_cat = ' AND  (i.cat_id IN ('.$catlist.') OR mc.mcat_c>0)';			
			$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".$catlist.") GROUP BY item_id ) mc ON i.id=mc.item_id ";
							
		}else if(is_array($cat_ids)){
			if(count($cat_ids)>1){
				$cat_list = ' AND (i.cat_id IN ('.implode(',', $cat_ids).') OR mc.mcat_c>0) ';
				$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".implode(',', $cat_ids).") GROUP BY item_id ) mc ON i.id=mc.item_id ";
			}else if(isset($cat_ids[0])){
				if($cat_ids[0]>0){
					$cat_list = ' AND (i.cat_id = '.$cat_ids[0].' OR mc.mcat_c>0) ';
					$mcat_lj = "LEFT JOIN ( SELECT item_id, count(id) as  mcat_c FROM #__djcf_items_categories WHERE cat_id IN (".$cat_ids[0].") GROUP BY item_id ) mc ON i.id=mc.item_id ";
				}
			}
		}						


		$reg_ids = $params->get('region_id','0');		
		$fallow_region= '';
		$region_list= '';				
		
		if($params->get('fallow_region','0')==1 && JRequest::getVar('option','')=='com_djclassifieds'){						
			$djcfreglib = new DJClassifiedsRegion();
			if(JRequest::getVar('view','')=='item'){			
				$id = JRequest::getInt('id','0');
								
				$query = "SELECT i.region_id FROM #__djcf_items i "
						."WHERE i.id=".$id." LIMIT 1";
				$db->setQuery($query);
				$region_id=$db->loadResult();
				
				if($region_id){
					$regs= $djcfreglib->getSubReg($region_id);				
					$reglist= $region_id;			
					foreach($regs as $r){
						$reglist .= ','. $r->id;
					}
					$fallow_region = ' AND i.region_id IN ('.$reglist.') ';	
				}
			}else if(JRequest::getVar('view','')=='items' && JRequest::getInt('se','')==1 && isset($_GET['se_regs'])){
											
				if(is_array($_GET['se_regs'])){
					$reg_id_se= end($_GET['se_regs']);
					if($reg_id_se=='' && count($_GET['se_regs'])>2){
						$reg_id_se =$_GET['se_regs'][count($_GET['se_regs'])-2];
					}
				}else{
					$reg_ids_se = explode(',', JRequest::getVar('se_regs'));
					$reg_id_se = end($reg_ids_se);
				}
				$reg_id_se=(int)$reg_id_se;
											
				if($reg_id_se){
					$regs= $djcfreglib->getSubReg($reg_id_se);
					$reglist= $reg_id_se;
					foreach($regs as $r){
						$reglist .= ','. $r->id;
					}
					$fallow_region = ' AND i.region_id IN ('.$reglist.') ';
				}
			}
							
		}
		if(is_array($reg_ids) && $fallow_region==''){
			if(count($reg_ids)>1){
				$region_list = ' AND i.region_id IN ('.implode(',', $reg_ids).') ';				
			}else if(isset($reg_ids[0])){
				if($reg_ids[0]>0){
					$region_list = ' AND i.region_id = '.$reg_ids[0].' ';
				}
			}
		}else if(!is_array($reg_ids) && $fallow_region==''){		
			$reglist = DJClassifiedsRegion::getDefaultRegionsIds();
			if($reglist){
				$fallow_region = ' AND i.region_id IN ('.$reglist.') ';
			}
		}
		
						
		$follow_user = '';
		$item_id = JRequest::getInt('id',0);
		if($params->get('follow_user')==1 && JRequest::getVar('option','')=='com_djclassifieds' && JRequest::getVar('view','')=='item' && $item_id>0){
			$query_item = "SELECT * FROM #__djcf_items WHERE id=".$item_id." AND published=1 AND blocked=0 LIMIT 1";
			$db->setQuery($query_item);
			$active_item =  $db->loadObject();

			if($active_item->user_id>0){
				$follow_user = " AND i.user_id=".$active_item->user_id." ";
			}else if($active_item->email!=''){
				$follow_user = " AND i.email= '".$active_item->email."' ";
			}
		}		
		
		
		$only_img='';
		$search_img_count_lj = '';
		if($params->get('only_with_img','0')==1){
			//$only_img = " AND img.name !='' ";
			$only_img .= " AND img.img_c>0 ";
			$search_img_count_lj = "LEFT JOIN ( SELECT COUNT(img.id) as img_c, img.item_id FROM #__djcf_images img
									WHERE img.type='item' GROUP BY item_id ) img ON i.id=img.item_id ";
		}
		
		$current_ad = '';
		if(JRequest::getVar('option','')=='com_djclassifieds' && JRequest::getVar('view','')=='item' && JRequest::getInt('id',0)>0){
			$current_ad = ' AND i.id!='.JRequest::getInt('id',0).' ';
		}
		
		$source = '';
		$fav_lj = '';
		$fav_s = '';
		if($user->id){
			if($params->get('items_source','0')==1){
				$source = ' AND i.user_id='.$user->id.' ';
			}else if($params->get('items_source','0')==2){
				$fav_lj = "LEFT JOIN ( SELECT * FROM #__djcf_favourites WHERE user_id=".$user->id.") f ON i.id=f.item_id ";
				$fav_s = ',f.id as f_id ';
				$source =  " AND f.id IS NOT NULL ";				
			}	
			
			if($cfpar->get('favourite','1') && $params->get('show_fav_icon','0')==1 && !$fav_lj && !$fav_s){
				$fav_lj = "LEFT JOIN ( SELECT * FROM #__djcf_favourites WHERE user_id=".$user->id.") f ON i.id=f.item_id ";
				$fav_s = ',f.id as f_id ';
			}
		}
		if($params->get('items_source','0')==3){
			$latest_items = $inputCookie->get('djcf_lastitems', '');
			if($latest_items){
				$items_ids = '';
				$latest_items_ids = explode('_', $latest_items);
				foreach($latest_items_ids as $latest_id){
					if($items_ids){
						$items_ids .=',';
					}
					$items_ids .= intval($latest_id);
				}
			}else{
				$items_ids = 0;
			}
			$current_ad = '';
			$source = ' AND i.id IN ('.$items_ids.') ';
		}else if($params->get('items_source','0')==4 ){
			$citem_id = $app->input->getInt('id',0);			
			if($app->input->get('option','')=='com_djclassifieds' && $app->input->getVar('view','')=='item' && $citem_id>0){				
				$query_u = "SELECT i.* FROM #__djcf_items i WHERE i.id=".$citem_id." LIMIT 1";
				$db->setQuery($query_u);
				$citem=$db->loadObject();
				if($citem->user_id>0){
					$source = ' AND i.user_id = '.$citem->user_id.' AND i.id != '.$citem_id.' ';
				}
			}
		} 
		
		$groups_acl = '0,'.implode(',', $user->getAuthorisedViewLevels());
		$access_view = " AND c.access_view IN (" . $groups_acl . ") ";
		
		$adult_restriction = '';
		if(!isset($_COOKIE["djcf_warning18"])){
			$adult_restriction .= " AND c.restriction_18=0 ";
		}
		
		$auctions_restriction = '';
		if($params->get('only_auctions','')){
			$auctions_restriction = ' AND i.auction=1 ';
		}
		
		if($cfpar->get('show_archived',0)==2){
			$published .= " (( i.published=1 AND i.date_exp > '".$date_now."') OR i.published=2) ";
		}else{
			$published .= " i.published=1 AND i.date_exp > '".$date_now."' ";
		}
		
		$query = "SELECT i.*,c.id as c_id, c.name as c_name,c.alias as c_alias,c.icon_url as c_icon_url, r.id as r_id, r.name as r_name "
						//." img.path as img_path, img.name as img_name, img.ext as img_ext,img.caption as img_caption ".$fav_s
						.$fav_s
				."FROM #__djcf_categories c, #__djcf_items i "
				.$search_img_count_lj	
				.$mcat_lj	
				.$prom_special_lj
				."LEFT JOIN #__djcf_regions r ON r.id=i.region_id ".$fav_lj
				//."LEFT JOIN ( SELECT img.id, img.item_id, img.name, img.path, img.ext, img.ordering, img.caption 
		 		//			  FROM (SELECT * FROM #__djcf_images WHERE type='item' ORDER BY ordering) img GROUP BY img.item_id ) AS img ON img.item_id=i.id "
				."WHERE ".$published." AND i.blocked=0 AND c.published = 1 AND i.cat_id=c.id "
				.$promoted.$item_ids.$users_ids.$fallow_cat.$cat_list.$fallow_region.$region_list.$follow_user.$types_ids.$only_img.$source.$access_view.$current_ad.$adult_restriction.$auctions_restriction
				."ORDER BY ".$ord." limit ".$params->get('items_nr');
		$db->setQuery($query);
		$items=$db->loadObjectList();
		//echo '<pre>';print_r($db);print_r($items);die();
		
		if(count($items)){
			$id_list= '';
			foreach($items as $item){
				if($id_list){
					$id_list .= ','.$item->id;
				}else{
					$id_list .= $item->id;
				}
			}
		
			$items_img = DJClassifiedsImage::getAdsImages($id_list);
		
			for($i=0;$i<count($items);$i++){
				$img_found =0;
				$items[$i]->images = array();
				foreach($items_img as $img){
					if($items[$i]->id==$img->item_id){
						$img_found =1;
						if(count($items[$i]->images)==0){
							$items[$i]->img_path = $img->path;
							$items[$i]->img_name = $img->name;
							$items[$i]->img_ext = $img->ext;
							$items[$i]->img_caption = $img->caption;
						}
						$items[$i]->images[]=$img;
					}else if($img_found){
						break;
					}
				}
			}
		}
		
		return $items;
	}
	
	static function getCatImages(){
		$db= JFactory::getDBO();
		$query = "SELECT * FROM #__djcf_images WHERE type='category' ORDER BY item_id ";
		$db->setQuery($query);
		$cat_images=$db->loadObjectList('item_id');
		
		//echo '<pre>';print_r($cat_images);die();
		return $cat_images;
	}
	
	static function getTypes(){						
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
	
	static function getFields($params,$items){
		$id= JRequest::getInt('id',0);
		$db= JFactory::getDBO();
		
		if(count($items)==0){
			return null;
		}else{
			$item_ids = '';
			foreach($items as $item){
				if($item_ids){$item_ids .= ',';}
				$item_ids .= $item->id; 
			}
		}
		
		$source = 'AND f.source IS NOT NULL';
		if($params->get('custom_fields',0) == 1) {
			$source = 'AND f.source=0';
		} elseif($params->get('custom_fields',0) == 2) {
			$source = 'AND f.source=1';
		}
	
		$query ="SELECT f.*, v.value,v.value_date,v.value_date_to,v.item_id FROM #__djcf_fields f "
				."LEFT JOIN #__djcf_fields_values v ON f.id=v.field_id "
				."WHERE f.published=1 AND f.access=0 AND f.in_module=1 ".$source." AND v.item_id IN (".$item_ids.") AND f.name!='price' AND f.name!='contact' ORDER BY f.ordering ";
		
		$db->setQuery($query);
		$fields=$db->loadObjectList();
				//echo '<pre>';print_r($fields);die();
		return $fields;
	}

}
?>
