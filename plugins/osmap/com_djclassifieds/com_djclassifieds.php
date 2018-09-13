<?php
/**
* @version $Id: com_djclassifieds.php 68 2012-01-03 16:16:57Z  $
* @package DJ-Classifieds
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author: Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
*
*
* DJ-Classifieds is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Classifieds is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Classifieds. If not, see <http://www.gnu.org/licenses/>.
*
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
if(!defined("DS")){ define('DS',DIRECTORY_SEPARATOR);}
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djcategory.php');
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djseo.php');

class osmap_com_djclassifieds
{
    static function getTree( $osmap, $parent, &$params )
    {
        if ($osmap->isNews) // This component does not provide news content. don't waste time/resources
            return false;

        $catid=0;
		
        if ( strpos($parent->link, 'view=items') || strpos($parent->link, 'view=items&layout=blog') || strpos($parent->link, 'view=categories') ) {
	            $link_query = parse_url( $parent->link );
	            parse_str( html_entity_decode($link_query['query']), $link_vars);
	            $catid = osmap_com_djclassifieds::getParam($link_vars,'cid',0);
	        
	
	        $include_products = osmap_com_djclassifieds::getParam($params,'include_products',1);
	        $include_products = ( $include_products == 1
	                                  || ( $include_products == 2 && $osmap->view == 'xml') 
	                                  || ( $include_products == 3 && $osmap->view == 'html')
	                                  ||   $osmap->view == 'navigator');
	        $params['include_products'] = $include_products;
	
	        $priority = osmap_com_djclassifieds::getParam($params,'cat_priority',$parent->priority);
	        $changefreq = osmap_com_djclassifieds::getParam($params,'cat_changefreq',$parent->changefreq);
	        if ($priority  == '-1')
	            $priority = $parent->priority;
	        if ($changefreq  == '-1')
	            $changefreq = $parent->changefreq;
	
	        $params['cat_priority'] = $priority;
	        $params['cat_changefreq'] = $changefreq;
	
	        $priority = osmap_com_djclassifieds::getParam($params,'link_priority',$parent->priority);
	        $changefreq = osmap_com_djclassifieds::getParam($params,'link_changefreq',$parent->changefreq);
	        if ($priority  == '-1')
	            $priority = $parent->priority;
	
	        if ($changefreq  == '-1')
	            $changefreq = $parent->changefreq;
	
	        $params['link_priority'] = $priority;
	        $params['link_changefreq'] = $changefreq;
	
	        osmap_com_djclassifieds::getDJClassifiedsCategory($osmap,$parent,$params,$catid);
        }
    }

    /* Returns URLs of all Categories and links in of one category using recursion */
    static function getDJClassifiedsCategory (&$osmap, &$parent, &$params, &$catid )
    {
        $database = JFactory::getDBO();
        if($catid){        	
        	$categories = DJClassifiedsCategory::getSubCat($catid);
        }else{
        	$categories = DJClassifiedsCategory::getCatAll();	
        }
		
        //print_r($categories);die();
	    $query = "SELECT i.* FROM #__djcf_items i "
             	."WHERE i.date_exp > NOW() AND i.published=1 "
             	."ORDER BY i.cat_id ASC, i.name ASC ";

        $database->setQuery($query);
        $items = $database->loadObjectList();
		//$start_level = $osmap->level;
        $osmap->changeLevel(1);
		$level = 0;
		if(substr(JURI::root(false,''), -1)=='/'){
			$site_url = substr(JURI::root(false,''), 0,-1);
		}else{
			$site_url = JURI::root(false,'');	
		}
		
		foreach($categories as $cat) {
            /*if( !$row->created ) {
                $row->created = $osmap->now;
            }*/
            if($cat->level<$level){
            	$dif_l = $level-$cat->level;
				for($d=0;$d<$dif_l;$d++){
					$osmap->changeLevel(-1);	
				}        		
				$level = $cat->level;
			}else if($cat->level>$level){
				$osmap->changeLevel(1);
				$level = $cat->level;
			}
				
				if(!$cat->alias){
					$cat->alias = DJClassifiedsSEO::getAliasName($cat->name);					
				}
			
            $node = new stdclass;
            $node->name = $cat->name;
            //$node->link = 'index.php?option=com_djclassifieds&view=items&cid='.$cat->id.'&Itemid='.$parent->id;
            $node->link = $site_url.JRoute::_(DJClassifiedsSEO::getCategoryRoute($cat->id.':'.$cat->alias));
            $node->id = $parent->id;
            $node->uid = $parent->uid .'c'.$cat->id;
            $node->browserNav = $parent->browserNav;
            //$node->modified = $cat->created;
            $node->priority = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->expandible = true;
            $node->secure = $parent->secure;
			$osmap->printNode($node);
			
		    /* Returns URLs of all listings in the current category */
	    
	        if ($params['include_products']) {
				$osmap->changeLevel(1);
				$cat_a=0;
	            foreach($items as $key=>$item) {
	            	if($item->cat_id==$cat->id){
	            		if(!$item->alias){
							$item->alias = DJClassifiedsSEO::getAliasName($item->name);
						}	            		
		                $node = new stdclass;
		                $node->name = $item->name;
		                //$node->link = 'index.php?option=com_djclassifieds&view=item&cid='.$cat->id.'&id='.$item->id.'&Itemid='.$parent->id;
		                $node->link = $site_url.JRoute::_(DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$cat->alias));
		                $node->id = $parent->id;
		                $node->uid = $parent->uid.'i'.$item->id;
		                $node->browserNav = $parent->browserNav;
		                //$node->modified = ($row->created);
		                $node->priority = $params['link_priority'];
		                $node->changefreq = $params['link_changefreq'];
		                $node->expandible = false;
		                $node->secure = $parent->secure;
		                $osmap->printNode($node);
						$cat_a=1;
						unset($items[$key]);
					}else if($cat_a){
						break;
					}
	            }				
				$osmap->changeLevel(-1);
	        }		
        }
			if($level>0){
				for($d=0;$d<$level;$d++){
					$osmap->changeLevel(-1);	
				}        		
				$level = $cat->level;
			}
		//echo $osmap->level;
		//$osmap->Level = $start_level;
        $osmap->changeLevel(-1);        
    }

    static function getParam($arr, $name, $def)
    {
        $var = JArrayHelper::getValue( $arr, $name, $def, '' );
        return $var;
    }
}
