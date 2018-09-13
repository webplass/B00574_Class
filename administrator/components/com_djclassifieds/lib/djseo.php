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

jimport('joomla.application.component.helper');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djcategory.php');

class DJClassifiedsSEO
{
	protected static $lookup;
	public static function getItemRoute($id, $catid = 0, $regid = 0,$main_cat = '', $extra_cats=array())
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		if(is_object($regid)){
			$extra_cats = $main_cat;
			$main_cat = $regid;
			$regid = 0;
		}
		
		$needles = array( 'item'  => array((int) $id) );		
		$needles_cat = array('item'  => array((int) $id));
		$needles_reg = array('item'  => array((int) $id));
		
		//Create the link
		$link = 'index.php?option=com_djclassifieds&view=item';
		
		if($main_cat && count($extra_cats)){
			foreach($extra_cats as $ecat){
				if($ecat->cat_id==$main_cat->id){
					$catid = $ecat->cat_id.':'.$ecat->alias;
					break;
				}
			}
		}
		
		
		$link .= '&id='. $id;
		
		
		if ((int)$catid >= 0 && (int)$regid >= 0){
			$needles['items']  = array('0'  => (int)$catid.'_'.(int)$regid);
		}

		
		if ((int)$catid >= 0)
		{
			$cat_path = DJClassifiedsCategory::getSEOParentPath((int)$catid);			
			if($cat_path)
			{
				$path = $cat_path;
				$path[] = '0';
				$needles_cat['items'] = ($path);
				$link .= '&cid='.$catid;
			}
		}		 

		if ((int)$regid >= 0){
			$reg_path = DJClassifiedsRegion::getSEOParentPath((int)$regid);
			if($reg_path)
			{
				$path = $reg_path;
				$path[] = '0:all';
				$needles_reg['items'] = ($path);
				$link .= '&rid='.$regid;
			}
		}
		
		
		//echo '<pre>';print_r($reg_path);die();
		$needles_item['item']  = array('0'  => (int)$id);
		 if ($item = self::_findItem($needles_item,'id')) {
			$link .= '&Itemid='.$item;
		}else if ($item = self::_findItem($needles,'cid_rig')) {
			$link .= '&Itemid='.$item;
		}else{
			$item = self::_findItem($needles_reg,'rid');			
			$active = $menus->getActive();
			if ($active && $active->component == 'com_djclassifieds') {
				$active_id = $active->id;
			} else {
				$default = $menus->getDefault();
				$active_id = $default->id;
			}
			
			$default_reg = 0;
			if(isset(self::$lookup['items']['rid'][0])) {
				$default_reg = self::$lookup['items']['rid'][0];
			}
											
			if($item!=$active_id && $item != $default_reg && $default_reg>0){
				$link .= '&Itemid='.$item;
			}else if ($item = self::_findItem($needles_cat)) {		
				$link .= '&Itemid='.$item;
			}	
		} 		
		
		return $link;
	}

	public static function getCategoryRoute($catid)
	{
		$needles = array(
			'items'  => array((int) $catid)
		);
		
		//Create the link
		$link = 'index.php?option=com_djclassifieds&view=items';		
		if ((int)$catid >= 0)
		{
			$cat_path = DJClassifiedsCategory::getSEOParentPath((int)$catid);			
			if($cat_path)
			{
				$path = $cat_path;
				$path[] = '0:all';			
				$needles['items'] = ($path);
				$link .= '&cid='.$catid;
			}
		}

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		return $link;
	}
	
	public static function getRegionRoute($regid)
	{
		$needles = array(
				'items'  => array((int) $regid)
		);
	
		//Create the link
		$link = 'index.php?option=com_djclassifieds&view=items';
		if ((int)$regid >= 0){
			$reg_path = DJClassifiedsRegion::getSEOParentPath((int)$regid);
			if($reg_path)
			{
				$path = $reg_path;
				$path[] = '0:all';
				$needles['items'] = ($path);
				$link .= '&rid='.$regid;
			}
		}
		//echo '<pre>';print_r($reg_path);die();
		if ($item = self::_findItem($needles,'rid')) {
			$link .= '&Itemid='.$item;
		}else if ($item = self::_findItem(array('items'  => array(0)),'cid')) {
			$link .= '&Itemid='.$item;
		}
		return $link;
	}	
	protected static function _findItem($needles = null, $element = 'cid')
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_djclassifieds');
			$items		= $menus->getItems('component_id', $component->id);

			if (count($items)) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view']))
					{
						$parameter = 'id';
						if ($item->query['view'] == 'items') {
							$parameter = 'cid';
							$parameter2 = 'rid';
						}											
						$view = $item->query['view'];
						if (isset($item->query['layout'])) {
							if ($item->query['layout'] == 'blog') {
								$view= $view."_blog";
							}elseif ($item->query['layout'] == 'favourites') {
								$view= $view."_favourites";
							}
						}						
						
						if ($item->query['view'] == 'items') {
							if (isset($item->query[$parameter]) && isset($item->query[$parameter2])) {
								if($item->query[$parameter] && $item->query[$parameter2]){
									//print_r($item->query);echo '1<br />';
									self::$lookup[$view]['cid_rig'][$item->query[$parameter].'_'.$item->query[$parameter2]] = $item->id;
								}else if($item->query[$parameter] && !$item->query[$parameter2]){
									//print_r($item->query);echo '2<br />';
									self::$lookup[$view]['cid'][$item->query[$parameter]] = $item->id;
								}else if(!$item->query[$parameter] && $item->query[$parameter2]){
									//print_r($item->query);echo '3<br />';
									self::$lookup[$view]['rid'][$item->query[$parameter2]] = $item->id;
								}else{
									///djcustom 								
									self::$lookup[$view]['cid'][0] = $item->id;
									//self::$lookup[$view]['rid'][0] = $item->id;
								}								
							}else if (isset($item->query[$parameter])) {
								//print_r($item->query);echo $item->id.' 4<br />';
								self::$lookup[$view]['cid'][$item->query[$parameter]] = $item->id;
								if($item->query[$parameter]==0){
									self::$lookup[$view]['rid'][0] = $item->id;
								}
							}
						}else if (isset($item->query[$parameter])) {
							self::$lookup[$view][$item->query[$parameter]] = $item->id;
						}else if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array($item->id);
						}
					}
				}
			}
		}
		//if($element=='cid_rig'){
			//echo '<pre>';print_r($needles);print_R(self::$lookup);die(); 
		//}
		
		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				//if (isset(self::$lookup[$view])){
					foreach($ids as $id)
					{
						/*if($element=='id'){
							echo $view.' '.$element.' '.$id;
							echo '<pre>';print_r($needles);print_R(self::$lookup);die();
						}*/
						
						if($element=='id'){
							if (isset(self::$lookup[$view][$id])) {
								return self::$lookup[$view][$id];
							}else{
								return null;
							}	
						}if($element=='cid_rig'){
							if (isset(self::$lookup[$view][$element][$id])) {
								return self::$lookup[$view][$element][$id];
							}else if (isset(self::$lookup[$view.'_blog'][$element][$id])) {
								return self::$lookup[$view.'_blog'][$element][$id];
							}	
						}else{
							if (isset(self::$lookup[$view][$element][(int)$id])) {
								return self::$lookup[$view][$element][(int)$id];
							}else if (isset(self::$lookup[$view.'_blog'][$element][(int)$id])) {
								return self::$lookup[$view.'_blog'][$element][(int)$id];
							}
						}
					   
					}
				//}
			}
			
			if(isset($needles['items'])){
				if (isset(self::$lookup['categories'][0]) && $element == 'cid') {
					return self::$lookup['categories'][0];
			   	}else{
			   		return null;
			   	}
			}
			
			
		}
		//else {
		$active = $menus->getActive();
		if ($active && $active->component == 'com_djclassifieds') {
			return $active->id;
		} else {
			$default = $menus->getDefault();
			return $default->id;
		}
		//}

		return null;
	}

	public static function getAliasName($name='')
	{
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$alias = mb_strtolower($name,"UTF-8");
        $alias = strip_tags($alias);
		if($par->get('seo_alias_urlsafe','1')){
			$alias = JFilterOutput::stringURLSafe($alias);
		}else{
			$alias = JFilterOutput::stringURLUnicodeSlug($alias);							
		}
        $alias=str_ireplace(' ', '_', $alias);       
		return $alias;
	}
	
	public static function getNewAdLink()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');	
		$menu_newad_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=additem',1);
		$new_ad_link='index.php?option=com_djclassifieds&view=additem';
		if($menu_newad_itemid){
			$new_ad_link .= '&Itemid='.$menu_newad_itemid->id;
		}       
		return $new_ad_link;
	}

	public static function getUserAdsLink(){
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$menu_userads_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=useritems',1);
		$menu_userads_link = 'index.php?option=com_djclassifieds&view=useritems';	
		if($menu_userads_itemid){
			$menu_userads_link .= '&Itemid='.$menu_userads_itemid->id;
		}       
		return $menu_userads_link;
	}
	
	public static function getUserFavAdsLink(){		
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$menu_fav_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&layout=favourites',1);	
		$menu_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&cid=0',1);
		$menu_item_blog = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&layout=blog&cid=0',1);
				
		if($menu_fav_item){
			$fav_ads_link='index.php?option=com_djclassifieds&view=items&layout=favourites&Itemid='.$menu_fav_item->id;
		}else if($menu_item){
			$fav_ads_link='index.php?option=com_djclassifieds&view=items&cid=0&Itemid='.$menu_item->id.'&fav=1';
		}else if($menu_item_blog){
			$fav_ads_link='index.php?option=com_djclassifieds&view=items&layout=blog&cid=0&Itemid='.$menu_item_blog->id.'&fav=1';
		}else{
			$fav_ads_link='index.php?option=com_djclassifieds&view=items&cid=0&fav=1';
		}
						
		return $fav_ads_link;
	}
	
	public static function getURLfromSlug($slug,$id_sufix=''){		
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$slug_a = explode(':',$slug);
		$url = '';
			if($par->get('seo_alias_in_url','1')){
				if(isset($slug_a[1])){
					if($id_sufix=='l'){
						$slug_a[1] = DJClassifiedsSEO::getAliasName($slug_a[1]);
					}
					if($par->get('seo_id_position','1')){		
						$url .= $slug_a[1].$par->get('seo_link_separator',',').$slug_a[0].$id_sufix;		 	
					}else{
						$url .= $slug_a[0].$id_sufix.$par->get('seo_link_separator',',').$slug_a[1];
					}	
				}else{
					$url = $slug_a[0].$id_sufix;	
				}		
			}else{
				$url = $slug_a[0].$id_sufix;
			}
						
		return $url;
	}

	public static function getIDfromURL($url){		
		$par = JComponentHelper::getParams( 'com_djclassifieds' );		
		$url_a = explode($par->get('seo_link_separator',','),str_ireplace(':', $par->get('seo_link_separator',','), $url));
			if(isset($url_a[1])){				
				if($par->get('seo_id_position','1')){		
					$id = end($url_a);
					unset($url_a[count($url_a)-1]);		 	
				}else{
					$id = (int)$url_a[0];
					unset($url_a[0]);
				}	
			}else{
				$id = (int)$url;	
			}		
			
			if($id==0){$id='';}
			
			$slug = '';			
			if (count($url_a) > 0 && $id) {
				$slug = ':';
				$slug .= implode('-',$url_a);
			}
			
		return $id.$slug;
	}

	public static function isIDRegion($url){		
		$par = JComponentHelper::getParams( 'com_djclassifieds' );		
		$url_a = explode($par->get('seo_link_separator',','),str_ireplace(':', $par->get('seo_link_separator',','), $url));
			if(isset($url_a[1])){				
				if($par->get('seo_id_position','1')){		
					$id = end($url_a);	 	
				}else{
					$id = $url_a[0];					
				}	
			}else{
				$id = $url;	
			}		
			
			if(substr($id, -1)=='l'){
				return true;
			}else{
				return false;
			}
			
	}

	public static function checkRegionURL($url){
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$url_a = explode($par->get('seo_link_separator',','),str_ireplace(':', $par->get('seo_link_separator',','), $url));
		$is_region = false;
		if(isset($url_a[1])){
			if($par->get('seo_id_position','1')){
				$id = end($url_a);
				
			}else{
				$id = $url_a[0];
			}
		}else{
			$id = $url;
		}
			
		if(strstr($id, 'l')){
			$is_region = true;
		}
						
		return $is_region;
	}	
	
	public static function getMainAdvertsItemid(){
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$menu_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&cid=0',1);
		$menu_item_blog = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&layout=blog&cid=0',1);
	
		$itemid = '';
		if($menu_item){
			$itemid='&Itemid='.$menu_item->id;
		}else if($menu_item_blog){
			$itemid='&Itemid='.$menu_item_blog->id;
		}
	
		return $itemid;
	}

	public static function getUserProfileItemid(){
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$menu_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=profile',1);
	
		$itemid = '';
		if($menu_item){
			$itemid='&Itemid='.$menu_item->id;
		}
	
		return $itemid;
	}

	function getArticleLink($id,$tmpl=0){
		require_once JPATH_SITE.'/components/com_content/helpers/route.php';
		$db= JFactory::getDBO();
		$article_link = '';
		$query = "SELECT a.id, a.alias, a.catid, c.alias as c_alias FROM #__content a "
				."LEFT JOIN #__categories c ON c.id=a.catid "
				."WHERE a.state=1 AND a.id=".$id;
					
		$db->setQuery($query);
		$article=$db->loadObject();
		
		if($article){
			$slug = $article->id.':'.$article->alias;
			$cslug = $article->catid.':'.$article->c_alias;
			$article_link = ContentHelperRoute::getArticleRoute($slug,$cslug);
			if($tmpl>0){
				$article_link .='&tmpl=component';
			}
			$article_link = JRoute::_($article_link,false);
		}
					
		return $article_link;
	}
	
}
?>
