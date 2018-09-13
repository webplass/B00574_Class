<?php
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

class DJClassifiedsControllerPayment extends JControllerLegacy {

	function payPoints(){
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		jimport( 'joomla.database.table' );
		
		$app  = JFactory::getApplication();
		$par  = JComponentHelper::getParams( 'com_djclassifieds' );		
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();
		$id   = JRequest::getInt('id', 0);
		$type = JRequest::getVar('type', '');
		$menus	= $app->getMenu('site');
		$row 	= JTable::getInstance('Payments', 'DJClassifiedsTable');
		
		

			$query ="SELECT i.*, c.points as c_points, c.autopublish as c_autopublish, c.alias as c_alias, r.name as r_name FROM #__djcf_items i "
				   ."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
				   ."LEFT JOIN #__djcf_regions r ON r.id=i.region_id "
				   ."WHERE i.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			$redirect_a=0;
			if(!$item && $type!='plan'){
				$redirect_a=1;				
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			}			
			if($item->user_id!=$user->id && $type!='plan'){
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
			$points_count=$db->loadResult();
																	
			$p_amount = 0;
			
			if($type=='plan'){						
				$query = "SELECT p.*  FROM #__djcf_plans p WHERE p.id='".$id."' ";
				$db->setQuery($query);
				$plan = $db->loadObject();
				
				if($plan){	
					$p_amount= $plan->points;			
					if($points_count>=$p_amount){
						
						

						$registry = new JRegistry();
						$registry->loadString($plan->params);
						$plan_params = $registry->toObject();
							
						//echo '<pre>';print_r($plan_params);die();
						$date_start = date("Y-m-d H:i:s");
						$date_exp = '';
						if($plan_params->days_limit){
							$date_exp_time = time()+$plan_params->days_limit*24*60*60;
							$date_exp = date("Y-m-d H:i:s",$date_exp_time) ;
						}
						$query = "INSERT INTO #__djcf_plans_subscr (`user_id`,`plan_id`,`adverts_limit`,`adverts_available`,`date_start`,`date_exp`,`plan_params`) "
								."VALUES ('".$user->id."','".$plan->id."','".$plan_params->ad_limit."','".$plan_params->ad_limit."','".$date_start."','".$date_exp."','".addslashes($plan->params)."')";
						$db->setQuery($query);
						$db->query();				
						
						$up_description = JText::_('COM_DJCLASSIFIEDS_PAYMENT_FOR_SUBSCRIPTION_PLAN').'<br />'.JText::_('COM_DJCLASSIFIEDS_PLAN_ID').": ".$plan->id.'<br />'.JText::_('COM_DJCLASSIFIEDS_PLAN').": ".$plan->name;
						$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
								."VALUES ('".$user->id."','-".$p_amount."','".addslashes($up_description)."')";
						$db->setQuery($query);
						$db->query();
					
						$message = JText::_('COM_DJCLASSIFIEDS_SUBSCRIPTION_PLAN_ACTIVATED');						
						
						$menu_userplans_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=userplans',1);
						$userplans_link='index.php?option=com_djclassifieds&view=userplans';
						if($menu_userplans_itemid){
							$userplans_link .= '&Itemid='.$menu_userplans_itemid->id;
						}							
						
						$payment_info = array();						
						$payment_info['itemname']=$plan->name;
						$payment_info['amount']=$p_amount;
						$payment_info['info']=$up_description;
						$payment_info['payment_id']=0;
						
						DJClassifiedsNotify::notifyAdminPaymentPoints($type,$payment_info);
						
						$row->item_id = $id;
						$row->user_id = $user->id;
						$row->method = 'points';
						$row->status = 'Completed';
						$row->ip_address = $_SERVER['REMOTE_ADDR'];
						$row->price = $p_amount;
						$row->type=3;
						$row->store();
						
						if($plan->groups_assignment && $row->user_id){
							$client = JFactory::getUser($row->user_id);
							$ga = $client->groups;
							$ga[$plan->groups_assignment] = $plan->groups_assignment;
							JUserHelper::setUserGroups($row->user_id, $ga);
						}
						
						$redirect = JRoute::_($userplans_link,false);
						$app->redirect($redirect, $message);
					
					}else{
						$message = JText::_('COM_DJCLASSIFIEDS_NOT_ENOUGHT_POINTS');
						$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
						$redirect = JRoute::_($redirect,false);
						$app->redirect($redirect, $message);
					}
				}else{
					$message = JText::_('COM_DJCLASSIFIEDS_WRONG_SUBSCRIPTION_PLAN');
					$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
					$redirect = JRoute::_($redirect,false);
					$app->redirect($redirect, $message);
				}
				
				
			}else if($type=='prom_top'){
				$p_amount= $par->get('promotion_move_top_points',0);
				if($points_count>=$p_amount){
					$up_description = JText::_('COM_DJCLASSIFIEDS_PAYMENT_FOR_PROMOTION_MOVE_TO_TOP').'<br />'.JText::_('COM_DJCLASSIFIEDS_ADVERT_ID').": ".$item->id.'<br />'.JText::_('COM_DJCLASSIFIEDS_TITLE').": ".$item->name;
					$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
							."VALUES ('".$user->id."','-".$p_amount."','".addslashes($up_description)."')";
					$db->setQuery($query);
					$db->query();
					
					$payment_info = array();						
					$payment_info['itemname']=$item->name;
					$payment_info['amount']=$p_amount;
					$payment_info['info']=$up_description;
					$payment_info['payment_id']=0;
					
					DJClassifiedsNotify::notifyAdminPaymentPoints($type,$payment_info);
					
					$row->item_id = $id;
					$row->user_id = $user->id;
					$row->method = 'points';
					$row->status = 'Completed';
					$row->ip_address = $_SERVER['REMOTE_ADDR'];
					$row->price = $p_amount;
					$row->type=2;
					$row->store();
				
					$message = JText::_('COM_DJCLASSIFIEDS_POINTS_PAYMENT_CONFIRMED_PROMOTION_MOVE_TO_TOP_ACTIVATED');				
					$redirect=DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias,$item->region_id.':'.$item->r_name);
				
					$date_sort=date("Y-m-d H:i:s");
					$query = "UPDATE #__djcf_items SET date_sort='".$date_sort."' "
							."WHERE id=".$item->id." ";
					$db->setQuery($query);
					$db->query();
				
					$redirect = JRoute::_($redirect,false);
					$app->redirect($redirect, $message);
				
				}else{
					$message = JText::_('COM_DJCLASSIFIEDS_NOT_ENOUGHT_POINTS');
					$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
					$redirect = JRoute::_($redirect,false);
					$app->redirect($redirect, $message);
				}
			}else{		
				if(strstr($item->pay_type, 'cat')){			
					$p_amount += $item->c_points; 
				}
				if(strstr($item->pay_type, 'type,')){
					$itype = DJClassifiedsPayment::getTypePrice($item->user_id,$item->type_id);
					$p_amount += $itype->points;
				}
				
				/*if(strstr($item->pay_type, 'duration_renew')){			
					$query = "SELECT d.points_renew FROM #__djcf_days d "
					."WHERE d.days=".$item->exp_days;
					$db->setQuery($query);
					$p_amount += $db->loadResult()*$item->extra_images_to_pay;
				}else if(strstr($item->pay_type, 'duration')){			
					$query = "SELECT d.points FROM #__djcf_days d "
					."WHERE d.days=".$item->exp_days;
					$db->setQuery($query);
					$p_amount += $db->loadResult()*$item->extra_images_to_pay;
				}
				
				if(strstr($item->pay_type, 'extra_img_renew')){
					$query = "SELECT d.img_points_renew FROM #__djcf_days d "
							."WHERE d.days=".$item->exp_days;
					$db->setQuery($query);
					$p_amount += $db->loadResult();
				}else if(strstr($item->pay_type, 'extra_img')){
					$query = "SELECT d.img_points FROM #__djcf_days d "
							."WHERE d.days=".$item->exp_days;
					$db->setQuery($query);
					$p_amount += $db->loadResult();
				}
				
				if(strstr($item->pay_type, 'extra_chars_renew')){
					$query = "SELECT d.char_points_renew FROM #__djcf_days d "
							."WHERE d.days=".$item->exp_days;
					$db->setQuery($query);
					$p_amount += $db->loadResult();
				}else if(strstr($item->pay_type, 'extra_chars')){
					$query = "SELECT d.char_points FROM #__djcf_days d "
							."WHERE d.days=".$item->exp_days;
					$db->setQuery($query);
					$p_amount += $db->loadResult();
				}*/
				
				$query = "SELECT * FROM #__djcf_days d "
						."WHERE d.days=".$item->exp_days." LIMIT 1";
				$db->setQuery($query);
				$day = $db->loadObject();
				
				
				if(strstr($item->pay_type, 'duration_renew')){
					$p_amount += $day->points_renew;
				}else if(strstr($item->pay_type, 'duration')){
					$p_amount += $day->points;
				}
				
				if(strstr($item->pay_type, 'extra_img_renew')){
					if($day->img_price_default){
						$p_amount += $par->get('img_price_renew_points','0')*$item->extra_images_to_pay;
					}else{
						$p_amount += $day->img_points_renew*$item->extra_images_to_pay;
					}
				}else if(strstr($item->pay_type, 'extra_img')){
					if($day->img_price_default){
						$p_amount += $par->get('img_price_points','0')*$item->extra_images_to_pay;
					}else{
						$p_amount += $day->img_points*$item->extra_images_to_pay;
					}
				}
				
				if(strstr($item->pay_type, 'extra_chars_renew')){
					if($day->char_price_default){
						$p_amount += $par->get('desc_char_price_renew_points','0')*$item->extra_chars_to_pay;
					}else{
						$p_amount += $day->char_points_renew*$item->extra_chars_to_pay;
					}
				}else if(strstr($item->pay_type, 'extra_chars')){
					if($day->char_price_default){
						$p_amount += $par->get('desc_char_price_points','0')*$item->extra_chars_to_pay;
					}else{
						$p_amount += $day->char_points*$item->extra_chars_to_pay;
					}
				}
				
				/*$query = "SELECT p.* FROM #__djcf_promotions p "
					."WHERE p.published=1 ORDER BY p.id ";
				$db->setQuery($query);
				$promotions=$db->loadObjectList();
				foreach($promotions as $prom){
					if(strstr($item->pay_type, $prom->name)){	
						$p_amount += $prom->points; 
					}	
				}*/
				
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
					$promotions[$prom_p->prom_id]->prices[$prom_p->days] = $prom_p;
				}
				
				foreach($promotions as $promm){
					$pay_type_a = explode(',', $item->pay_type);
					foreach($pay_type_a as $pay_type_e){
						if(strstr($pay_type_e, $promm->name)){
							$pay_type_ep = explode('_', $pay_type_e);
							if(isset($promm->prices[$pay_type_ep[3]])){
								$p_amount+=$promm->prices[$pay_type_ep[3]]->points;
							}
						}
					}
				}
				//echo $p_amount;die();
				
				if($points_count>=$p_amount){
					$up_description = JText::_('COM_DJCLASSIFIEDS_PAYMENT_FOR_ADVERT').'<br />'.JText::_('COM_DJCLASSIFIEDS_ADVERT_ID').": ".$item->id.'<br />'.JText::_('COM_DJCLASSIFIEDS_TITLE').": ".$item->name;
					$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
							."VALUES ('".$user->id."','-".$p_amount."','".addslashes($up_description)."')";					
					$db->setQuery($query);
					$db->query();
					
					DJClassifiedsPayment::applayPromotions($id);
					
						$payment_info = array();						
						$payment_info['itemname']=$item->name;
						$payment_info['amount']=$p_amount;
						$payment_info['info']=$up_description;
						$payment_info['payment_id']=0;
						
						DJClassifiedsNotify::notifyAdminPaymentPoints($type,$payment_info);
						
						$row->item_id = $id;
						$row->user_id = $user->id;
						$row->method = 'points';
						$row->status = 'Completed';
						$row->ip_address = $_SERVER['REMOTE_ADDR'];
						$row->price = $p_amount;
						$row->type=0;
						$row->store();
						
						$pub=0;
						if(($item->c_autopublish=='1') || ($item->c_autopublish=='0' && $par->get('autopublish')=='1')){						
							$pub = 1;							
							$message = JText::_('COM_DJCLASSIFIEDS_POINTS_PAYMENT_CONFIRMED_ADVERT_PUBLISHED'); 						
						}else{
							$message = JText::_('COM_DJCLASSIFIEDS_POINTS_PAYMENT_CONFIRMED_ADVERT_WAITING_FOR_PUBLISH');
						}
						$redirect=DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias,$item->region_id.':'.$item->r_name);
				
						$query = "UPDATE #__djcf_items SET payed=1, pay_type='',extra_images_to_pay='0',extra_chars_to_pay='0', published='".$pub."' "
								."WHERE id=".$item->id." ";					
						$db->setQuery($query);
						$db->query();	
						
						$redirect = JRoute::_($redirect,false);
						$app->redirect($redirect, $message);
						
				}else{
					$message = JText::_('COM_DJCLASSIFIEDS_NOT_ENOUGHT_POINTS');
					$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
					$redirect = JRoute::_($redirect,false);
					$app->redirect($redirect, $message);
				}
			}		
		

	}
	
	
	function exchangePoints(){
		$app = JFactory::getApplication();
		$points = $app->input->getFloat('ext_points');
		$source = $app->input->getCmd('source');
		$itemid = $app->input->getInt('Itemid');
		$dispatcher	= JDispatcher::getInstance();		
		$dispatcher->trigger('onExchangePoints', array ($source,$points,$itemid));
		return true;
	}

	function activateFreePlan(){
		$app 	= JFactory::getApplication();		
		$id 	= $app->input->getInt('id');
		$db		= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$menus	= $app->getMenu('site');
		$date_now   = date("Y-m-d H:i:s");
		
		if($user->id=='0'){
			$uri = JFactory::getURI();
			$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
			$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
		}
		
		
		$query = "SELECT p.* FROM #__djcf_plans p "
				."WHERE p.published=1 AND p.id=".$id;
		$db->setQuery($query);
		$plan=$db->loadObject();
		
		if($plan->price==0){
			$activation_allowed = 1;
			$plans_plugin = JPluginHelper::getPlugin('djclassifieds', 'plans');
			$plugin_params = new JRegistry($plans_plugin->params);
			$free_expiration =  $plugin_params->get('ps_free_plan_expiration','0');
			
			if($free_expiration){
				$query = "SELECT COUNT(ps.id) FROM #__djcf_plans_subscr ps, #__djcf_plans p "
						."WHERE ps.user_id='".$user->id."' AND p.id=ps.plan_id AND p.id=".$plan->id." "
						." AND ( ((ps.date_exp > '".$date_now."' OR ps.date_exp='0000-00-00 00:00:00') AND ps.adverts_available>0) " 
						."OR (p.price=0 AND (ps.date_exp > '".$date_now."' OR ps.date_exp='0000-00-00 00:00:00') AND ps.adverts_available=0) )  ";
				$db->setQuery($query);
				$plan_count=$db->loadResult();
				if($plan_count>0){
					$activation_allowed = 0;
				}
			}
						
			
			if($activation_allowed){						
				$registry = new JRegistry();
				$registry->loadString($plan->params);
				$plan_params = $registry->toObject();
					
				//echo '<pre>';print_r($plan_params);die();
				$date_start = date("Y-m-d H:i:s");
				$date_exp = '';
				if($plan_params->days_limit){
					$date_exp_time = time()+$plan_params->days_limit*24*60*60;
					$date_exp = date("Y-m-d H:i:s",$date_exp_time) ;
				}
				$query = "INSERT INTO #__djcf_plans_subscr (`user_id`,`plan_id`,`adverts_limit`,`adverts_available`,`date_start`,`date_exp`,`plan_params`) "
						."VALUES ('".$user->id."','".$plan->id."','".$plan_params->ad_limit."','".$plan_params->ad_limit."','".$date_start."','".$date_exp."','".addslashes($plan->params)."')";
				$db->setQuery($query);
				$db->query();
				
	
				if($plan->groups_assignment && $user->id){
					$client = JFactory::getUser($user->id);
					$ga = $client->groups;
					$ga[$plan->groups_assignment] = $plan->groups_assignment;
					JUserHelper::setUserGroups($user->id, $ga);
				}
					
				
				$message = JText::_('COM_DJCLASSIFIEDS_SUBSCRIPTION_PLAN_ACTIVATED');						
							
				$menu_userplans_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=userplans',1);
				$userplans_link='index.php?option=com_djclassifieds&view=userplans';
				if($menu_userplans_itemid){
					$userplans_link .= '&Itemid='.$menu_userplans_itemid->id;
				}										
				$redirect = JRoute::_($userplans_link,false);
				$app->redirect($redirect, $message);
			}else{
				$message = JText::_('COM_DJCLASSIFIEDS_YOU_ALREADY_HAVE_THIS_PLAN_ACTIVE');
					
				$menu_userplans_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=userplans',1);
				$userplans_link='index.php?option=com_djclassifieds&view=userplans';
				if($menu_userplans_itemid){
					$userplans_link .= '&Itemid='.$menu_userplans_itemid->id;
				}
				$redirect = JRoute::_($userplans_link,false);
				$app->redirect($redirect, $message);
			}
			
		}else{
			$app->redirect(JURI::base(),JText::_('COM_DJCLASSIFIEDS_WRONG_SUBSCRIPTION_PLAN'));
		}
		
		return true;
		
	}
	
	
	function confirmoffer(){
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt('id');
		$db		= JFactory::getDBO();
		$user 	= JFactory::getUser();
	
		$query ="SELECT o.* FROM #__djcf_offers o "
				."WHERE o.id=".$id." LIMIT 1";
				$db->setQuery($query);
				$offer = $db->loadObject();
	
				$url = 'index.php?option=com_djclassifieds&view=userofferssub';
				$url = JRoute::_($url,false);
	
				if($offer){
					if($user->id==$offer->user_id){
						$query="UPDATE #__djcf_offers SET confirmed=1 "
								." WHERE id=".$offer->id;
								$db->setQuery($query);
								$db->query();
								DJClassifiedsNotify::notifyConfirmOfferAuthor($offer->item_id,$user,$offer->price,$offer->quantity);
	
								$msg = JText::_('COM_DJCLASSIFIEDS_OFFER_CONFIRMED');
								$app->redirect($url,$msg);
					}
				}
				$msg = JText::_('COM_DJCLASSIFIEDS_WRONG_OFFER');
				$app->redirect($url,$msg,'error');
					
				return true;
	}
	
	function requestoffer(){
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt('id');
		$db		= JFactory::getDBO();
		$user 	= JFactory::getUser();
	
		$query ="SELECT o.* FROM #__djcf_offers o "
				."WHERE o.id=".$id." LIMIT 1";
				$db->setQuery($query);
				$offer = $db->loadObject();
	
				$url = 'index.php?option=com_djclassifieds&view=useroffersrec';
				$url = JRoute::_($url,false);
	
				if($offer){
					$query = "SELECT i.*  FROM #__djcf_items i "
							."WHERE i.id='".$offer->item_id."' ";
							$db->setQuery($query);
							$item = $db->loadObject();
							if($user->id==$item->user_id && $offer->confirmed){
								$query="UPDATE #__djcf_offers SET request=1 "
										." WHERE id=".$offer->id;
										$db->setQuery($query);
										$db->query();
										DJClassifiedsNotify::notifyRequestOfferAdmin($offer->item_id,$offer->price,$offer->quantity);
	
	
										$msg = JText::_('COM_DJCLASSIFIEDS_PAYMENT_FOR_OFFER_REQUESTED');
										$app->redirect($url,$msg);
							}
				}
				$msg = JText::_('COM_DJCLASSIFIEDS_WRONG_OFFER');
				$app->redirect($url,$msg,'error');
					
				return true;
	}	
	
}

?>