<?php
use GuzzleHttp\json_decode;
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class DJClassifiedsControllerApi extends JControllerLegacy {
	
	function getStats(){
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();				
		$db 	= JFactory::getDBO();
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		
		if($par->get('anonymous_stats',1)==0){
			die();
		}
		
		$stats = array();
		
			$stats['ads_total'] = self::getAdverts(0);
		
			$stats['ads_active'] = self::getAdverts(1);
		
			$date_from = date("Y-m-d").' 00:00:00';
			$stats['ads_today'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")  , date("d")-1, date("Y")));
			$stats['ads_1d'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y-m-d",strtotime('monday this week')).' 00:00:00';
			$stats['ads_week'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")  , date("d")-7, date("Y")));
			$stats['ads_7d'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y-m").'-01 00:00:00';
			$stats['ads_month'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")-1 , date("d"), date("Y")));
			$stats['ads_30d'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y").'-01-01 00:00:00';
			$stats['ads_year'] = self::getAdverts(0,$date_from);
		
			$date_from = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m") , date("d"), date("Y")-1));
			$stats['ads_365d'] = self::getAdverts(0,$date_from);
		
			$stats['auctions_c'] = self::getAuctions();
		
			$stats['categories_c'] = self::getCategories();
			$stats['regions_c'] = self::getRegions();
		
			$stats_json = json_encode($stats);
			
			echo $stats_json;			
			die();

	}
	
	
	public static function getAdverts($pub=1,$date_from=''){
		$db= JFactory::getDBO();
	
		$date_now = date("Y-m-d H:i:s");
		$pub_w = '';
		if($pub){
			$pub_w = " AND i.published=1 AND i.date_exp > '".$date_now."' ";
		}
	
		$date_from_w = '';
		if($date_from){
			$date_from_w = " AND i.date_start>='".$date_from."' ";
		}
	
		$query = "SELECT COUNT(id) FROM #__djcf_items i WHERE 1 ".$pub_w.$date_from_w;
		$db->setQuery($query);
		$total=$db->loadResult();
	
		return $total;
	}
	
	
	public static function getAuctions(){
		$db= JFactory::getDBO();
		$date_now = date("Y-m-d H:i:s");
		$query = "SELECT COUNT(id) FROM #__djcf_items i "
				."WHERE i.date_exp > '".$date_now."' AND i.auction=1 AND i.published=1";
		$db->setQuery($query);
		$total=$db->loadResult();
	
		return $total;
	}
	
	public static function getCategories(){
		$db= JFactory::getDBO();
		$date_now = date("Y-m-d H:i:s");
		$query = "SELECT COUNT(id) FROM #__djcf_categories "
				."WHERE published=1";
		$db->setQuery($query);
		$total=$db->loadResult();
	
		return $total;
	}
	
	public static function getRegions(){
		$db= JFactory::getDBO();
		$date_now = date("Y-m-d H:i:s");
		$query = "SELECT COUNT(id) FROM #__djcf_regions "
				."WHERE published=1";
		$db->setQuery($query);
		$total=$db->loadResult();
	
		return $total;
	}
	
}

?>