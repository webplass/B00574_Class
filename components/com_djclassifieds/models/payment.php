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

class DjclassifiedsModelPayment extends JModelLegacy{	
	function getUserItem($id){
		$db = JFactory::getDBO();
		
		$query ="SELECT i.*,c.name as c_name, c.alias as c_alias, c.price as c_price, c.price_special as c_price_special, c.points as c_points, t.name as t_name FROM #__djcf_items i "
			   ."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
			   ."LEFT JOIN #__djcf_types t ON t.id=i.type_id "
			   ."WHERE i.id=".$id." LIMIT 1";
		$db->setQuery($query);
		$item = $db->loadObject();
		
		return $item;
	}	
	
	function getDuration($day){
		$db= JFactory::getDBO();
		$query = "SELECT d.* FROM #__djcf_days d "
				."WHERE d.days=".$day;
		$db->setQuery($query);
		$day=$db->loadObject();

		return $day;
	}	

	function getPromotions(){
		$db= JFactory::getDBO();
		
		$query = "SELECT p.* FROM #__djcf_promotions p WHERE p.published=1 ORDER BY p.id ";
		$db->setQuery($query);
		$promotions=$db->loadObjectList('id');
		
		$query = "SELECT p.* FROM #__djcf_promotions_prices p ORDER BY p.days ";
		$db->setQuery($query);
		$prom_prices=$db->loadObjectList();
			foreach($promotions as &$prom){
				$prom->prices = array();
			}
			foreach($prom_prices as $prom_p){
				if(isset($promotions[$prom_p->prom_id])){
					$promotions[$prom_p->prom_id]->prices[$prom_p->days] = $prom_p;
				}
			}
		
		return $promotions;
	}	
	
	
	function getPoints($id){
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDBO();
		$user   = JFactory::getUser();		
		$menus	= $app->getMenu('site');
		
		$query = "SELECT p.* FROM #__djcf_points p "
				."WHERE p.published=1 AND p.id=".$id.' LIMIT 1';
		$db->setQuery($query);
		$points=$db->loadObject();
		if($points){
			if($points->price==0 && $user->id){
				$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
						."VALUES ('".$user->id."','".$points->points."','\"".$points->name."\" - ".JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGE')."')";
				$db->setQuery($query);
				$db->query();
				
				$menu_ppackages_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=points',1);
				$user_upoints_link='index.php?option=com_djclassifieds&view=userpoints';
				if($menu_upoints_itemid){
					$user_upoints_link .= '&Itemid='.$menu_upoints_itemid->id;
				}
				$app->redirect($user_upoints_link,JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGES_ADDED'));
			}
		}
		return $points;
	}
	
		function getUserPoints(){				
			$user = JFactory::getUser();
			$db= JFactory::getDBO();
			
			$query = "SELECT SUM(p.points)FROM #__djcf_users_points p "
					."WHERE p.user_id='".$user->id."' ";										
				
			$db->setQuery($query);
			$points_count=$db->loadResult();	
			if(!$points_count){
				$points_count = 0;
			}
			//echo '<pre>';print_r($db);print_r($points_count);echo '<pre>';die();	
			return $points_count;
		}	
		
		function getPlan($id){
			$app    = JFactory::getApplication();
			$user   = JFactory::getUser();
			$db	    = JFactory::getDBO();
			$menus	= $app->getMenu('site');
			$date_now   = date("Y-m-d H:i:s");
			
			
			$query = "SELECT p.* FROM #__djcf_plans p "
					."WHERE p.published=1 AND p.id=".$id;
			$db->setQuery($query);
			$plan = $db->loadObject();
			
			if(!$plan){
				$menu_plans_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=plans',1);
				$plans_link='index.php?option=com_djclassifieds&view=plans';
				if($menu_plans_itemid){
					$plans_link .= '&Itemid='.$menu_plans_itemid->id;
				}
				$app->redirect($plans_link, JText::_('COM_DJCLASSIFIEDS_WRONG_SUBSCRIPTION_PLAN','error'));
			}
			
			$query = "SELECT count(ps.id) FROM #__djcf_plans_subscr ps, #__djcf_plans p "
					."WHERE ps.user_id='".$user->id."' AND p.id=ps.plan_id AND p.id=".$plan->id." "
					." AND (ps.date_exp > '".$date_now."' OR ps.date_exp='0000-00-00 00:00:00') AND ps.adverts_available>0 ";
				
			$db->setQuery($query);
			$user_plans=$db->loadResult();
			
			if($user_plans>0){
				$menu_userplans_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=userplans',1);
				$userplans_link='index.php?option=com_djclassifieds&view=userplans';
				if($menu_userplans_itemid){
					$userplans_link .= '&Itemid='.$menu_userplans_itemid->id;
				}
				
				$app->redirect($userplans_link, JText::_('COM_DJCLASSIFIEDS_YOU_ALREADY_HAVE_THIS_PLAN_ACTIVE'));
			}
			
			
			return $plan;
		}
		
		
		function activateMoveToTopPromotion($id){
			
			$app  = JFactory::getApplication();
			$par  = JComponentHelper::getParams( 'com_djclassifieds' );
			$user = JFactory::getUser();
			$db   = JFactory::getDBO();
			$id   = JRequest::getInt('id', 0);
			
			
			$query ="SELECT i.*, c.points as c_points, c.autopublish as c_autopublish, c.alias as c_alias FROM #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
					."WHERE i.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			$redirect_a=0;
			if(!$item){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			}
			if($item->user_id!=$user->id){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			}
			if($user->id==0){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN');
			}
				
			if($redirect_a){
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message);
			}
			
			$query = "SELECT SUM(p.points)FROM #__djcf_users_points p "
					."WHERE p.user_id='".$user->id."' ";
			
			$db->setQuery($query);
									
				$message = JText::_('COM_DJCLASSIFIEDS_POINTS_PAYMENT_CONFIRMED_PROMOTION_MOVE_TO_TOP_ACTIVATED');
				$redirect=DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias);
		
				$date_sort=date("Y-m-d H:i:s");
				$query = "UPDATE #__djcf_items SET date_sort='".$date_sort."' "
						."WHERE id=".$item->id." ";
				$db->setQuery($query);
				$db->query();
		
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message);									
		}
		
		function getOrder($id){
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			$redirect_a = 0;
				
			$query ="SELECT o.* FROM #__djcf_orders o "
					."WHERE o.id=".$id." AND o.user_id=".$user->id." LIMIT 1";
			$db->setQuery($query);
			$order = $db->loadObject();
		
			if(!$order){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			}
			if($user->id==0){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN');
			}
		
			if($redirect_a){
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message);
			}
		
			return $order;
		}
		
		
		function getOffer($id){
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			$redirect_a = 0;
		
			$query ="SELECT o.* FROM #__djcf_offers o "
					."WHERE o.id=".$id." AND o.user_id=".$user->id." LIMIT 1";
			$db->setQuery($query);
			$offer = $db->loadObject();
		
			if(!$offer){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			}
			if($user->id==0){
				$redirect_a=1;
				$message = JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN');
			}
		
			if($redirect_a){
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message);
			}
		
			return $offer;
		}		
		
		function getCategories(){
			$db= JFactory::getDBO();
			$query = "SELECT * FROM #__djcf_categories "
					."WHERE published=1";
					$db->setQuery($query);
					$cats=$db->loadObjectList('id');
		
					return $cats;
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

