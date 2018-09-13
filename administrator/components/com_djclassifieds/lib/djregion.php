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

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djregion.php');

class DJRegOptionList{
var $text;
var $value;
var $disable;	

	function __construct(){
	$text=null;
	$value=null;			
	$disable=null;
	}
	
}

class RegionItem{
	var $id;
	var $name;
	var $parent_id;
	var $parent_name;
	var $city;
	var $country;
	var $country_iso;
	var $published;
	var $level;

	function __construct(){
		$id=null;
		$name=null;
		$parent_id=null;
		$parent_name=null;
		$city=null;
		$country=null;
		$country_iso=null;
		$published=null;
		$level=0;
		$items_count;
	}
	
}

class DJClassifiedsRegion {
	
var $parent_id;
var $id;
var $name;
var $childs = Array();
var $level;

function __construct(){
	$parent_id=null;
	$id=null;
	$name=null;
	$childs[]=null;
	$elem[]=null;
	$level=0;
	$items_count=0;
}

public static function getRegSelect(){
	//$regions = DJClassifiedsRegion::getRegions();
	$regions_main = DJClassifiedsRegion::getRegionsMain();
	$regions_sort = DJClassifiedsRegion::getRegionsSortParent();
	
	$sort_regions = DJClassifiedsRegion::getListSelect($regions_main,$regions_sort);
	//echo '<pre>';print_r($sort_regions);echo '</pre>';die();
	
	return $sort_regions;
	
}

public static function getRegAll($cach_enabled = false){
	$regions_main = DJClassifiedsRegion::getRegionsMain();
	$regions_sort = DJClassifiedsRegion::getRegionsSortParent();
	$par 		  = JComponentHelper::getParams( 'com_djclassifieds' );
	
	if($cach_enabled && $par->get('cache_lib_regs','0')=='1'){
		$cache = JFactory::getCache();
		$cache->setCaching( 1 );
		$sort_regs = $cache->call( array( 'DJClassifiedsRegion', 'getListAll' ),$regions_main,$regions_sort );	
	}else{
		$sort_regs = DJClassifiedsRegion::getListAll($regions_main,$regions_sort);
	}
	
	//echo '<pre>';print_r($regions_sort);echo '</pre>';die();
	
	return $sort_regs;
	
}

public static function getSubReg($id,$show_count='0'){
	$regions_main = DJClassifiedsRegion::getRegionsMain();
	$regions_sort = DJClassifiedsRegion::getRegionsSortParent();
	$par 		  = JComponentHelper::getParams( 'com_djclassifieds' );
	
	
	if($par->get('cache_lib_regs','0')=='1'){		
		$cache = JFactory::getCache();
		$cache->setCaching( 1 );
		$sort_regs = $cache->call( array( 'DJClassifiedsRegion', 'getListSubreg' ),$regions_main,$regions_sort,$id );
	}else{
		$sort_regs = DJClassifiedsRegion::getListSubreg($regions_main,$regions_sort,$id);		
	}
	
	$reg_path = DJClassifiedsRegion::getParentPath($id);
	$level_over = count($reg_path);
	foreach($sort_regs as &$reg){
		$reg->level = $reg->level-$level_over;
		if($reg->level<0){$reg->level = 0;}
	}

	//echo '<pre>';print_r($sort_regs);die();
	
	
	if($show_count){
		$max_level = '0';
		foreach ($sort_regs as $r){
			if($r->level>$max_level){
				$max_level = $r->level;
			}
		}
	
		for($level=$max_level;$level>-1;$level--){
			$parent_value=0;
			for($r=count($sort_regs);$r>0;$r--){
				if($parent_value>0 && $level>$sort_regs[$r-1]->level){
					$sort_regs[$r-1]->items_count = $sort_regs[$r-1]->items_count + $parent_value;
					$parent_value=0;
				}
				if($level==$sort_regs[$r-1]->level){
					$parent_value =$parent_value + $sort_regs[$r-1]->items_count;
				}
			}
		}
		//echo '<pre>';print_r($sort_regs);echo '</pre>';die();
	}
	return $sort_regs;
	
}
	
public static function getMenuRegions($rid='0',$show_count='0',$include_def='0',$pub='0'){
	$par   = JComponentHelper::getParams( 'com_djclassifieds' );
	$cache = JFactory::getCache();
	$cache->setCaching( 1 );	
		
	$regions_main = DJClassifiedsRegion::getRegionsMain($pub);	
	$regions_sort = DJClassifiedsRegion::getRegionsSortParent($pub);			
	$sort_regs = DJClassifiedsRegion::getListAll($regions_main,$regions_sort);
	//TO CHANGE
	//$regions_main = $cache->call( array( 'DJClassifiedsRegion', 'getRegionsMain' ));
	//$regions_sort = $cache->call( array( 'DJClassifiedsRegion', 'getRegionsSortParent' ));
	//$sort_regs = $cache->call( array( 'DJClassifiedsRegion', 'getListAll' ),$regions_main,$regions_sort );
	
	
	///todo 
	/*if($include_def){
	
		$inputCookie  = JFactory::getApplication()->input->cookie;
		$def_reg      = $inputCookie->get('djcf_regid', 0);
		//echo $def_reg;die();
		if($def_reg>0){
			return DJClassifiedsRegion::getSubReg($def_reg,1);
		}
	}*/
	//$sort_regs = DJClassifiedsRegion::getListAll($regs,$regs);
	if($show_count){			
		$max_level = '0';			
		foreach ($sort_regs as $r){		
			if($r->level>$max_level){
				$max_level = $r->level;
			}
		}		
		
		for($level=$max_level;$level>-1;$level--){
			$parent_value=0;			
			for($r=count($sort_regs);$r>0;$r--){
				if($parent_value>0 && $level>$sort_regs[$r-1]->level){
					$sort_regs[$r-1]->items_count = $sort_regs[$r-1]->items_count + $parent_value;
					$parent_value=0;		
				}					
				if($level==$sort_regs[$r-1]->level){		
					$parent_value =$parent_value + $sort_regs[$r-1]->items_count;
				}
			}		
		}
	}
	
	$reg_path=','.$rid.',';		
	if($rid>0){								
		$reg_id = $rid;
		while($reg_id!=0){	
			foreach($sort_regs as $r){
				if($r->id==$reg_id){
					$reg_id=$r->parent_id;
					$reg_path .= $reg_id.',';
					break;
				}
			}
		}			
	}
	
	$menu_regs = array();
	for($i=0;$i<count($sort_regs);$i++){		
		if(strstr($reg_path,','.$sort_regs[$i]->id.',') || strstr($reg_path,','.$sort_regs[$i]->parent_id.',')){
			$menu_regs[] = $sort_regs[$i]; 
		}
		
	}
	$ret = array();
	$ret[]= $menu_regs;
	$ret[]= $reg_path;
	$ret[]= $sort_regs;
	
	return $ret;
}	


public static function getRegAllItemsCount($pub='0',$hide_empty='0',$include_def='0'){

	$regs = DJClassifiedsRegion::getRegionsSortParent($pub);
	$par  = JComponentHelper::getParams( 'com_djclassifieds' );
	
	//echo '<pre>';
	//print_r($regs);
	if($include_def){		
		$def_reg      = DJClassifiedsRegion::getDefaultRegion();
		//echo $def_reg;die();
		if($def_reg>0){
			return DJClassifiedsRegion::getSubReg($def_reg,1);
		}		
	}
	


	if(isset($regs[0])){
		if($par->get('cache_lib_regs','0')=='1'){
			$cache = JFactory::getCache();
			$cache->setCaching( 1 );
			$sort_regs = $cache->call( array( 'DJClassifiedsRegion', 'getListAll' ),$regs[0],$regs );
		}else{
			$sort_regs = DJClassifiedsRegion::getListAll($regs[0],$regs);
		}
		
	}else{
		$sort_regs = array();
	}

	$max_level = '0';
	foreach ($sort_regs as $r){
		if($r->level>$max_level){
			$max_level = $r->level;
		}
	}

	for($level=$max_level;$level>-1;$level--){
		$parent_value=0;
		for($r=count($sort_regs);$r>0;$r--){
			if($parent_value>0 && $level>$sort_regs[$r-1]->level){
				$sort_regs[$r-1]->items_count = $sort_regs[$r-1]->items_count + $parent_value;
				$parent_value=0;
			}
			if($level==$sort_regs[$r-1]->level){
				$parent_value =$parent_value + $sort_regs[$r-1]->items_count;
			}
		}
	}

	//echo '<pre>';print_r($sort_cats);die();
	if($hide_empty){
		$reg_items = array();
		for($i=0;$i<count($sort_regs);$i++){
			if($sort_regs[$i]->items_count){
				$reg_items[]=$sort_regs[$i];
			}
		}
		return $reg_items;
	}else{
		return $sort_regs;
	}

}

public static function getParentPath($rid='0'){	
	$regs = DJClassifiedsRegion::getRegions();
	$reg_path=Array();
	if(count($regs)){		
		while($rid!=0){
			if(isset($regs[$rid])){
				$reg_path[] =  $regs[$rid];
				$rid = 	$regs[$rid]->parent_id;	
			}else{
				break;
			}	
			/*foreach($regs as $r){
				if($r->id==$rid){
					$rid=$r->parent_id;
					$reg_path[] = $r; 					
					break;
				}
			}*/
		}
	}		

	//echo '<pre>';print_r($regs);echo '</pre>';die();
	
	return $reg_path;
	
}

public static function getSEOParentPath($rid='0'){
	$regs = DJClassifiedsRegion::getRegions();
	$reg_path=Array();
		
	while($rid!=0){
		if(isset($regs[$rid])){
			$reg_path[] = $regs[$rid]->id.':'.$regs[$rid]->name;
			$rid=$regs[$rid]->parent_id;
		}else{
			break;
		}
	}

	//echo '<pre>';print_r($reg_path);echo '</pre>';die();
	//return array_reverse($reg_path);
	return $reg_path;

}


public static function getDefaultRegion(){
	$inputCookie  = JFactory::getApplication()->input->cookie;
	$def_reg      = $inputCookie->get('djcf_regid', 0);
	return $def_reg;
}

public static function getDefaultRegionItem(){
	$inputCookie  = JFactory::getApplication()->input->cookie;
	$def_reg      = $inputCookie->get('djcf_regid', 0);

	if($def_reg>0){
		$reg_all = self::getRegions();
		return $reg_all[$def_reg];
	}

	return null;
}

public static function getDefaultRegionsIds(){
	$reg_ids = '';
	$def_reg = DJClassifiedsRegion::getDefaultRegion();
	if($def_reg>0){
		$reg_ids = DJClassifiedsRegion::getDefaultSubRegions($def_reg,1);
	}
	return $reg_ids;
}

public static function getDefaultSubRegions($id, $type=0){
	$regions_main = DJClassifiedsRegion::getRegionsMain();
	$regions_sort = DJClassifiedsRegion::getRegionsSortParent();
	$par 		  = JComponentHelper::getParams( 'com_djclassifieds' );

	if($par->get('cache_lib_regs','0')=='1'){	
		$cache = JFactory::getCache();
		$cache->setCaching( 1 );
		$sort_regs = $cache->call( array( 'DJClassifiedsRegion', 'getListSubreg' ),$regions_main,$regions_sort,$id );
	}else{
		$sort_regs = DJClassifiedsRegion::getListSubreg($regions_main,$regions_sort,$id);
	}
	
	
	if($type==1){
		$reg_ids = $id;
		foreach($sort_regs as $reg){
			$reg_ids .= ','.$reg->id;
		}		
		//echo '<pre>';print_r($reg_ids);die();
		return $reg_ids;
	}
	
	return $sort_regs;

}


private static $_regions =null;
private static $_regions_sort_parent =null;

	public static function getRegions(){
		
		if(!self::$_regions){
			self::$_regions = array();
		}		
		if(self::$_regions){
			return self::$_regions;
		}								
		
			$db= JFactory::getDBO();	
			$date_now = date("Y-m-d H:i:s");
			
			//$start = array_sum(explode(' ', microtime()));    
			//$mem = memory_get_usage();

			$query = "SELECT r.* FROM #__djcf_regions r "					
					."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci" ; 
				$db->setQuery($query); 
				$allregions=$db->loadObjectList('id');
				
			$query = "SELECT i.region_id, count(i.id) as items_count "
					."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id";
			$db->setQuery($query); 
			$items_count=$db->loadObjectList('region_id');	
			
			foreach($allregions as &$reg){
				if(isset($allregions[$reg->parent_id])){
					$reg->parent_name = $allregions[$reg->parent_id]->name;
				}else{
					$reg->parent_name = '';
				}

				if(isset($items_count[$reg->id])){
					$reg->items_count = $items_count[$reg->id]->items_count;
				}else{
					$reg->items_count = 0;
				}	
			}
			
			/*$query = "SELECT r.*, rr.name as parent_name,IFNULL(i.items_count,0) items_count FROM #__djcf_regions r "
					."LEFT JOIN #__djcf_regions rr ON r.parent_id=rr.id "
					."LEFT JOIN (SELECT i.region_id, count(i.id) as items_count "
								."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id) i ON i.region_id=r.id "
					."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci";*/
			
				//$db->setQuery($query); 
				//$allregions=$db->loadObjectList('id');
				//echo '<pre>';print_r($db);print_r($allregions);die();
				
/*$stop = array_sum(explode(' ', microtime()));
$totalTime = $stop - $start;
$mem2 = memory_get_usage();
$m = ($mem2-$mem)/1024/1024;
echo round($m,2).'MB<br />';
echo $totalTime; die();*/
				
				self::$_regions = $allregions;
		return self::$_regions ;
	}	
	
	
	public static function getRegionsMain($p='0'){
		
		if($p){
			$pub = ' AND r.published=1 ';
		}else{
			$pub ='';
		}
		
		
		$db= JFactory::getDBO();
		$date_now = date("Y-m-d H:i:s");
		/*$query = "SELECT r.*, rr.name as parent_name,IFNULL(i.items_count,0) items_count FROM #__djcf_regions r "
				."LEFT JOIN #__djcf_regions rr ON r.parent_id=rr.id "
				."LEFT JOIN (SELECT i.region_id, count(i.id) as items_count "
							."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id) i ON i.region_id=r.id "						
				."WHERE r.parent_id=0 ".$pub." ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci";
			
		$db->setQuery($query);
		$allregions=$db->loadObjectList('id');*/
		
		
			$query = "SELECT r.* FROM #__djcf_regions r "					
					."WHERE r.parent_id=0 ".$pub." ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci"; 
				$db->setQuery($query); 
				$allregions=$db->loadObjectList('id');
				
			$query = "SELECT i.region_id, count(i.id) as items_count "
					."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id";
			$db->setQuery($query); 
			$items_count=$db->loadObjectList('region_id');	
			
			foreach($allregions as &$reg){
				if(isset($allregions[$reg->parent_id])){
					$reg->parent_name = $allregions[$reg->parent_id]->name;
				}else{
					$reg->parent_name = '';
				}
								
				if(isset($items_count[$reg->id])){
					$reg->items_count = $items_count[$reg->id]->items_count;
				}else{
					$reg->items_count = 0;
				}	
			}
		
		
		//echo '<pre>';print_r($db);print_r($this->_allregions);die();
		//echo '<pre>';print_r($this->_allregions);
	
		//echo '<pre>';print_r($regions);die();
	
		return $allregions;
	}
	
	public static function getRegionsSortParent($p='0'){
		if(!self::$_regions_sort_parent){
			self::$_regions_sort_parent = array();
		}
		if(self::$_regions_sort_parent){
			return self::$_regions_sort_parent;
		}
		
		if($p){
			$pub = 'WHERE r.published=1 ';
		}else{
			$pub ='';
		}
		
		$db= JFactory::getDBO();
		$date_now = date("Y-m-d H:i:s");
		/*$query = "SELECT r.*, rr.name as parent_name,IFNULL(i.items_count,0) items_count FROM #__djcf_regions r "
				."LEFT JOIN #__djcf_regions rr ON r.parent_id=rr.id "
				."LEFT JOIN (SELECT i.region_id, count(i.id) as items_count "
							."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id) i ON i.region_id=r.id "
				.$pub					
				."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci ";
			
		$db->setQuery($query);
		$allregions=$db->loadObjectList('id');*/
		
			$query = "SELECT r.* FROM #__djcf_regions r "					
					.$pub						
					."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci "; 
				$db->setQuery($query); 
				$allregions=$db->loadObjectList('id');
				
			$query = "SELECT i.region_id, count(i.id) as items_count "
					."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id";
			$db->setQuery($query); 
			$items_count=$db->loadObjectList('region_id');
			
			foreach($allregions as &$reg){
				if(isset($allregions[$reg->parent_id])){
					$reg->parent_name = $allregions[$reg->parent_id]->name;
				}else{
					$reg->parent_name = '';
				}
				
				if(isset($items_count[$reg->id])){
					$reg->items_count = $items_count[$reg->id]->items_count;
				}else{
					$reg->items_count = 0;
				}	
			}
		
		//echo '<pre>';print_r($db);print_r($this->_allregions);die();
		//echo '<pre>';print_r($allregions);die();
		$regions = array();
		foreach($allregions as $r){
			if(!isset($regions[$r->parent_id])){
				$regions[$r->parent_id] = array();
			}
			$regions[$r->parent_id][] = $r;
		}
		self::$_regions_sort_parent = $regions;
		//echo '<pre>';print_r($regions);die();
	
		return $regions;
	}	
	
	
	public static function getListSelect(& $lists,& $lists_const,& $option=Array()){
		
		foreach($lists as $list){
			$op= new DJRegOptionList;
				$op->text=$list->name;
				$op->value= $list->id;
				$option[]=$op;
			$childs=Array();
				
			if(isset($lists_const[$list->id])){
				for($i=0;$i<count($lists_const[$list->id]);$i++){
					$child=new RegionItem();
					$child->id=$lists_const[$list->id][$i]->id;
					$child->parent_id=$lists_const[$list->id][$i]->parent_id;
					if(isset($list->level)){
						$child->level=$list->level+1;	
					}else{
						$child->level=1;
					}
					
					$new_name=$lists_const[$list->id][$i]->name;
						for($lev=0;$lev<$child->level;$lev++){
							$new_name="- ".$new_name;
						}
					$child->name=$new_name;
					$childs[]=$child;
				}
				DJClassifiedsRegion::getListSelect($childs,$lists_const,$option);
				//echo count($lists_const).' ';
				unset($lists_const[$list->id]);
			}
				
		}
		
		return($option);		
	}

	public static function getListAll(& $lists,& $lists_const,& $option=Array()){
	
		foreach($lists as $list){			
				$cat_item =  new RegionItem();
				$cat_item->id=$list->id;
				$cat_item->name=$list->name;
				$cat_item->parent_id=$list->parent_id;
				$cat_item->parent_name=$list->parent_name;
				$cat_item->country=$list->country;
				$cat_item->country_iso=$list->country_iso;
				$cat_item->city=$list->city;
				$cat_item->published=$list->published;
				$cat_item->items_count= $list->items_count;
				
				if(isset($list->level)){
					$cat_item->level= $list->level;	
				}else{
					$cat_item->level= 0;
				}
				
						
				$option[]=$cat_item;				
				$childs=Array();
											
				if(isset($lists_const[$list->id])){					
					for($i=0;$i<count($lists_const[$list->id]);$i++){						
						$child=new RegionItem();
						$child->id=$lists_const[$list->id][$i]->id;
						$child->name=$lists_const[$list->id][$i]->name;						
						$child->parent_id=$lists_const[$list->id][$i]->parent_id;
						$child->parent_name=$lists_const[$list->id][$i]->parent_name;
						$child->country=$lists_const[$list->id][$i]->country;
						$child->country_iso=$lists_const[$list->id][$i]->country_iso;
						$child->city=$lists_const[$list->id][$i]->city;
						$child->published=$lists_const[$list->id][$i]->published;
						$child->items_count=$lists_const[$list->id][$i]->items_count;
						
						if(isset($list->level)){
							$child->level=$list->level+1;	
						}else{
							$child->level=1;
						}												
						$childs[]=$child;
					}
					DJClassifiedsRegion::getListAll($childs,$lists_const,$option);
					//echo count($lists_const).' ';
					unset($lists_const[$list->id]);
				}
									
		}
					   
		return($option);		
	}

	
	public static function getListSubreg(& $lists,& $lists_const, $main_id=0, $main_level=0,$main_f =0 , & $option=Array()){
	
		$liczba = count($lists_const);
		foreach($lists as $list){									
				if(isset($list->level)){
					$current_level= $list->level;	
				}else{
					$current_level= 0;
				}
								
				if($main_f==1 && ($main_level>$current_level || $current_level==$main_level)){
					break;
				}
				
				if($main_id==$list->id){
					$main_f=1;	
					$main_level = $current_level;
				}
				
		
				
				if($main_f==1 && $main_level<$current_level){
					$cat_item =  new CatItem;
					$cat_item->id=$list->id;
					$cat_item->name=$list->name;
					$cat_item->parent_id=$list->parent_id;
					$cat_item->parent_name=$list->parent_name;
					$cat_item->country=$list->country;
					$cat_item->country_iso=$list->country_iso;
					$cat_item->city=$list->city;
					$cat_item->published=$list->published;					
					$cat_item->level= $current_level;
					$cat_item->items_count= $list->items_count;
							
					$option[]=$cat_item;
				}
					$childs=Array();			

					if(isset($lists_const[$list->id])){
						for($i=0;$i<count($lists_const[$list->id]);$i++){
							$child=new RegionItem();
							$child->id=$lists_const[$list->id][$i]->id;
							$child->name=$lists_const[$list->id][$i]->name;
							$child->parent_id=$lists_const[$list->id][$i]->parent_id;
							$child->parent_name=$lists_const[$list->id][$i]->parent_name;
							$child->country=$lists_const[$list->id][$i]->country;
							$child->country_iso=$lists_const[$list->id][$i]->country_iso;
							$child->city=$lists_const[$list->id][$i]->city;
							$child->published=$lists_const[$list->id][$i]->published;
							$child->items_count=$lists_const[$list->id][$i]->items_count;
					
							if(isset($list->level)){
								$child->level=$list->level+1;
							}else{
								$child->level=1;
							}
							$childs[]=$child;
						}
						
						DJClassifiedsRegion::getListSubreg($childs,$lists_const,$main_id,$main_level,$main_f,$option);
						//echo count($lists_const).' ';
						unset($lists_const[$list->id]);
					}
																		
		}
		return($option);		
	}


// dj custom START

public static function getRegSelectNoCities(){
	//$regions = DJClassifiedsRegion::getRegions();
	$regions_main = DJClassifiedsRegion::getRegionsMain();
	$regions_sort = DJClassifiedsRegion::getRegionsSortParentNoCities();
	
	$sort_regions = DJClassifiedsRegion::getListSelectWithDisabled($regions_main,$regions_sort);
	//echo '<pre>';print_r($sort_regions);echo '</pre>';die();
	
	return $sort_regions;
	
}

public static function getRegionsSortParentNoCities(){
	if(!self::$_regions_sort_parent){
		self::$_regions_sort_parent = array();
	}
	if(self::$_regions_sort_parent){
		return self::$_regions_sort_parent;
	}
	
	$db= JFactory::getDBO();
	$date_now = date("Y-m-d H:i:s");
	/*$query = "SELECT r.*, rr.name as parent_name,IFNULL(i.items_count,0) items_count FROM #__djcf_regions r "
			."LEFT JOIN #__djcf_regions rr ON r.parent_id=rr.id "
			."LEFT JOIN (SELECT i.region_id, count(i.id) as items_count "
						."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id) i ON i.region_id=r.id "
			."WHERE r.country=1 OR r.parent_id=0 "		
			."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci ";
		
	$db->setQuery($query);
	$allregions=$db->loadObjectList('id');*/
	
			$query = "SELECT r.* FROM #__djcf_regions r "					
					."WHERE r.country=1 OR r.parent_id=0 "		
					."ORDER BY r.parent_id, r.name COLLATE utf8_polish_ci "; 
				$db->setQuery($query); 
				$allregions=$db->loadObjectList('id');
				
			$query = "SELECT i.region_id, count(i.id) as items_count "
					."FROM #__djcf_items i WHERE i.published=1 AND i.date_exp > '".$date_now."' GROUP BY i.region_id";
			$db->setQuery($query); 
			$items_count=$db->loadObjectList('region_id');	
			
			foreach($allregions as &$reg){
				if(isset($allregions[$reg->parent_id])){
					$reg->parent_name = $allregions[$reg->parent_id]->name;
				}else{
					$reg->parent_name = '';
				}
				
				if($items_count[$reg->id]){
					$reg->items_count = $items_count[$reg->id]->items_count;
				}else{
					$reg->items_count = 0;
				}	
			}
	
	$regions = array();
	foreach($allregions as $r){
		if(!isset($regions[$r->parent_id])){
			$regions[$r->parent_id] = array();
		}
		$regions[$r->parent_id][] = $r;
	}
	self::$_regions_sort_parent = $regions;

	return $regions;
}

public static function getListSelectWithDisabled(& $lists,& $lists_const,& $option=Array()){
	
	foreach($lists as $list){
		$op= new DJRegOptionList;
			$op->text=$list->name;
			$op->value= $list->id;
			// dj custom
			if(!$list->parent_id) {
				$op->disable=1;
				$len = (30 - strlen($list->name)) / 2;
				$op->text = str_repeat('_', $len).' '.strtoupper($list->name).' '.str_repeat('_', $len);
			}
			$option[]=$op;
		$childs=Array();
			
		if(isset($lists_const[$list->id])){
			for($i=0;$i<count($lists_const[$list->id]);$i++){
				$child=new RegionItem();
				$child->id=$lists_const[$list->id][$i]->id;
				$child->parent_id=$lists_const[$list->id][$i]->parent_id;
				if(isset($list->level)){
					$child->level=$list->level+1;	
				}else{
					$child->level=1;
				}
				
				$new_name=$lists_const[$list->id][$i]->name;
					for($lev=0;$lev<$child->level - 1;$lev++){
						$new_name="- ".$new_name;
					}
					
				$child->name=$new_name;
				$childs[]=$child;
			}
			DJClassifiedsRegion::getListSelect($childs,$lists_const,$option);
			//echo count($lists_const).' ';
			unset($lists_const[$list->id]);
		}
			
	}
	
	return($option);		
}

// dj custom END


}
