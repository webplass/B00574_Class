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

JHTML::_('behavior.modal');

class DJClassifiedsPayment {

	function __construct(){
	}

	public static function getTypePrice($user_id,$type_id){		
		
		$db= JFactory::getDBO();	
		$query = "SELECT * FROM #__djcf_types t "
				."WHERE t.published=1 AND t.id=".(int)$type_id." "
				."LIMIT 1";
		
			$db->setQuery($query); 
			$type=$db->loadObject();
		//echo '<pre>';print_r($type);echo '</pre>';die();
		
		if($user_id>0 && $type){
			$user = JFactory::getUser($user_id);
			if(count($user->groups)){
				$g_list = implode(',',$user->groups);
				$query = "SELECT gp.* FROM #__djcf_groups_prices gp "
						."WHERE  gp.type='type' AND gp.group_id in(".$g_list.") AND item_id= ".$type->id;
					
				$db->setQuery($query);
				$types_prices=$db->loadObjectList();
					
				//echo '<pre>';print_r($types_prices);echo '</pre>';die();
				if($types_prices){
					foreach($types_prices as $tp){						
						if($type->price>$tp->price){
							$type->price=$tp->price;
							$type->price_special=$tp->price_special;
						}
						if($type->points>$tp->points){
							$type->points=$tp->points;
						}						
					}
				}
			}
		}
		
		//echo '<pre>';print_r($type);echo '</pre>';die();		
		
		return $type;
		
	}
	
	public static function getUserPaypal($user_id){
 
		$db= JFactory::getDBO();
		$query ="SELECT v.value FROM #__djcf_fields_values_profile v, #__djcf_fields f "
				."WHERE f.name='paypal_email' AND f.source=2 AND v.field_id=f.id AND v.user_id=".$user_id." LIMIT 1";
		$db->setQuery($query);
		$paypay_user = $db->loadResult();
		
		return $paypay_user;
	}
	
	public static function confirmUserPayment($item, $type,$price_total,$method,$coupon_code=''){
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		jimport( 'joomla.database.table' );
		
		$app  = JFactory::getApplication();
		$par  = JComponentHelper::getParams( 'com_djclassifieds' );		
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();				
		$menus	= $app->getMenu('site');
		$row 	= JTable::getInstance('Payments', 'DJClassifiedsTable');
		
		if($type=='plan'){
			$plan = $item;
					
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
			$message = JText::_('COM_DJCLASSIFIEDS_SUBSCRIPTION_PLAN_ACTIVATED');

			$menu_userplans_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=userplans',1);
			$userplans_link='index.php?option=com_djclassifieds&view=userplans';
			if($menu_userplans_itemid){
				$userplans_link .= '&Itemid='.$menu_userplans_itemid->id;
			}

			/*$payment_info = array();
			$payment_info['itemname']=$plan->name;
			$payment_info['amount']=$p_amount;
			$payment_info['info']=$up_description;
			$payment_info['payment_id']=0;

			DJClassifiedsNotify::notifyAdminPaymentPoints($type,$payment_info);*/

			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Completed';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = 0;
			$row->coupon = $coupon_code;
			if($coupon_code){
				$row->coupon_discount = $price_total;
			}
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
						

		
		
		}else if($type=='points'){			
					
				$row->item_id = $id;
				$row->user_id = $user->id;
				$row->method = $method;
				$row->status = 'Completed';
				$row->ip_address = $_SERVER['REMOTE_ADDR'];
				$row->price = 0;
				$row->coupon = $coupon_code;
				if($coupon_code){
					$row->coupon_discount = $price_total;
				}
				$row->type=1;
				
				$row->store();		
				
				$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
						."VALUES ('".$user->id."','".$item->points."','".addslashes(JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGE')." <br />".JText::_('COM_DJCLASSIFIEDS_PAYMENT_ID')).' '.$payment->id."')";
				$db->setQuery($query);
				$db->query();
				
				$message = JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGES_ADDED');
				
				$menu_userpoints_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=userpoints',1);
				$userpoints_link='index.php?option=com_djclassifieds&view=userpoints';
				if($menu_userpoints_itemid){
					$userpoints_link .= '&Itemid='.$menu_userpoints_itemid->id;
				}
				$redirect = JRoute::_($userpoints_link,false);
				$app->redirect($redirect, $message);
			
			
		}else if($type=='prom_top'){
			
				/*$up_description = JText::_('COM_DJCLASSIFIEDS_PAYMENT_FOR_PROMOTION_MOVE_TO_TOP').'<br />'.JText::_('COM_DJCLASSIFIEDS_ADVERT_ID').": ".$item->id.'<br />'.JText::_('COM_DJCLASSIFIEDS_TITLE').": ".$item->name;
					
			    $payment_info = array();
				$payment_info['itemname']=$item->name;
				$payment_info['amount']=$p_amount;
				$payment_info['info']=$up_description;
				$payment_info['payment_id']=0;
					
				DJClassifiedsNotify::notifyAdminPaymentPoints($type,$payment_info);*/

				$query ="SELECT i.*, c.points as c_points, c.autopublish as c_autopublish, c.alias as c_alias FROM #__djcf_items i "
						."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
						."WHERE i.id=".$item." LIMIT 1";
				$db->setQuery($query);
				$item_ad = $db->loadObject();				
			
				$row->item_id = $item_ad->id;
				$row->user_id = $user->id;
				$row->method = 'points';
				$row->status = 'Completed';
				$row->ip_address = $_SERVER['REMOTE_ADDR'];
				$row->price = 0;
				$row->coupon = $coupon_code;
				if($coupon_code){
					$row->coupon_discount = $price_total;
				}
				$row->type=2;
				$row->store();
				
				
				$message = JText::_('COM_DJCLASSIFIEDS_POINTS_PAYMENT_CONFIRMED_PROMOTION_MOVE_TO_TOP_ACTIVATED');
				$redirect=DJClassifiedsSEO::getItemRoute($item_ad->id.':'.$item_ad->alias,$item_ad->cat_id.':'.$item_ad->c_alias);
		
				$date_sort=date("Y-m-d H:i:s");
				$query = "UPDATE #__djcf_items SET date_sort='".$date_sort."' "
						."WHERE id=".$item_ad->id." ";
				$db->setQuery($query);
				$db->query();
		
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message);
		
			
		}else{	
			$up_description = JText::_('COM_DJCLASSIFIEDS_PAYMENT_FOR_ADVERT').'<br />'.JText::_('COM_DJCLASSIFIEDS_ADVERT_ID').": ".$item->id.'<br />'.JText::_('COM_DJCLASSIFIEDS_TITLE').": ".$item->name;

			/*$payment_info = array();
			$payment_info['itemname']=$item->name;
			$payment_info['amount']=$p_amount;
			$payment_info['info']=$up_description;
			$payment_info['payment_id']=0;

			DJClassifiedsNotify::notifyAdminPaymentPoints($type,$payment_info);*/

			$row->item_id = $item->id;
			$row->user_id = $user->id;
			$row->method = 'points';
			$row->status = 'Completed';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = 0;
			$row->coupon = $coupon_code;
			if($coupon_code){
				$row->coupon_discount = $price_total;
			}
			$row->type=0;
			$row->store();

			$query ="SELECT * FROM #__djcf_categories "
					."WHERE id=".$item->cat_id." LIMIT 1";
			$db->setQuery($query);
			$cat = $db->loadObject();
			
			
			$pub=0;
			if(($cat->autopublish=='1') || ($cat->autopublish=='0' && $par->get('autopublish')=='1')){
				$pub = 1;
				$message = JText::_('COM_DJCLASSIFIEDS_THANKS_FOR_PAYMENT_ADVERT_PUBLISHED');
			}else{
				$message = JText::_('COM_DJCLASSIFIEDS_THANKS_FOR_PAYMENT_WAIT_FOR_CONFIRMATION');
				
			}
			
			self::applayPromotions($item->id);
			
			$redirect=DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias);

			$query = "UPDATE #__djcf_items SET payed=1, pay_type='',extra_images_to_pay='0',extra_chars_to_pay='0', published='".$pub."' "
					."WHERE id=".$item->id." ";
			$db->setQuery($query);
			$db->query();

			$redirect = JRoute::_($redirect,false);
			$app->redirect($redirect, $message);							
		}
	}
	
	public static function processPayment($id, $type,$method){
		
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		jimport( 'joomla.database.table' );
		$db 	= JFactory::getDBO();
		$app 	= JFactory::getApplication();		
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$user 	= JFactory::getUser();		
		$row 	= JTable::getInstance('Payments', 'DJClassifiedsTable');
		
		JPluginHelper::importPlugin( 'djclassifiedspayment' );
		JPluginHelper::importPlugin( 'djclassifieds' );
		$dispatcher = JDispatcher::getInstance();
				
		$pdata = array();
		
		if($type=='prom_top'){
			$query ="SELECT i.* FROM #__djcf_items i "
					."WHERE i.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			if(!isset($item)){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
			}
		
			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Start';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = $par->get('promotion_move_top_price',0);
			$row->type=2;
						
			$pdata['amount'] = $par->get('promotion_move_top_price',0);
			$pdata['itemname'] = $item->name;
			$pdata['item_cid'] = '&cid='.$item->cat_id;
			
			$dispatcher->trigger('onPrepareProcessPayment', array (& $id, & $par, $type, &$row, &$pdata));						
			$row->store();		
			
			$pdata['item_id'] = $row->id;
			
			$dispatcher->trigger('onAfterPrepareProcessPayment', array (& $id, & $par, $type, &$row, &$pdata));
			
		}else if($type=='points'){
			$query ="SELECT p.* FROM #__djcf_points p "
					."WHERE p.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$points = $db->loadObject();
			if(!isset($points)){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_POINTS_PACKAGE');
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
			}
			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Start';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = $points->price;
			$row->type=1;
		
			$pdata['amount'] = $points->price;
			$pdata['itemname'] = $points->name;
			$pdata['item_cid'] = '';
			$dispatcher->trigger('onPrepareProcessPayment', array (& $points, & $par, $type, &$row, &$pdata));
			
			$row->store();							
			$pdata['item_id'] = $row->id;
			
			$dispatcher->trigger('onAfterPrepareProcessPayment', array (& $points, & $par, $type, &$row, &$pdata));
			
		}else if($type=='plan'){
			$query ="SELECT p.* FROM #__djcf_plans p "
					."WHERE p.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$plan = $db->loadObject();
			if(!isset($plan)){
				$message = JText::_('COM_DJCLASSIFIEDS_SUBSCRIPTION_PLAN');
				$redirect="index.php?option=com_djclassifieds&view=plans";
			}
			 
		
			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Start';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = $plan->price;
			$row->type=3;
			
			$pdata['amount'] = $plan->price;
			$pdata['itemname'] = $plan->name;
			$pdata['item_cid'] = '';
			
			$dispatcher->trigger('onPrepareProcessPayment', array (& $plan, & $par, $type, &$row, &$pdata));			
			$row->store();
					
			$pdata['item_id'] = $row->id;
			
			$dispatcher->trigger('onAfterPrepareProcessPayment', array (& $plan, & $par, $type, &$row, &$pdata));
			
		}else if($type=='order'){
				
			$query ="SELECT o.* FROM #__djcf_orders o "
					."WHERE o.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$order = $db->loadObject();
				
			$query ="SELECT o.* FROM #__djcf_orders_shipping o "
					."WHERE o.order_id=".$id." LIMIT 1";
			$db->setQuery($query);
			$order_shipping = $db->loadObject();
			
			$query ="SELECT i.*, c.price as c_price FROM #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
					."WHERE i.id=".$order->item_id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			if(!isset($item)){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
			}
				
			$paypay_user = DJClassifiedsPayment::getUserPaypal($item->user_id);
				
			if($paypay_user){
				$paypal_email = $paypay_user;
			}
				
			$quantity = JRequest::getInt('quantity',1);
			$price_total = $order->price*$order->quantity;
			if($order_shipping){
				$price_total += $order_shipping->price;
			}
		
			//print_r($price_total);die();
				
			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Start';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = $price_total;
			$row->type=4;
					
			$pdata['amount'] = $price_total;
			$pdata['itemname'] = $item->name;
			$pdata['item_cid'] = '';
			
			$dispatcher->trigger('onPrepareProcessPayment', array (& $order, & $par, $type, &$row, &$pdata));		
			$row->store();							
			
			$pdata['item_id'] = $row->id;
			
			$dispatcher->trigger('onAfterPrepareProcessPayment', array (& $order, & $par, $type, &$row, &$pdata));
			
		}else if($type=='offer'){
				
			$query ="SELECT o.* FROM #__djcf_offers o "
					."WHERE o.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$order = $db->loadObject();
				
			$query ="SELECT i.*, c.price as c_price FROM #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
					."WHERE i.id=".$order->item_id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			if(!isset($item)){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
			}
				
			$paypay_user = DJClassifiedsPayment::getUserPaypal($item->user_id);
				
			if($paypay_user){
				$paypal_email = $paypay_user;
			}
				
			$price_total = $order->price;
		
			//print_r($price_total);die();
				
			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Start';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = $price_total;
			$row->type=5;
			
			$pdata['amount'] = $price_total;
			$pdata['itemname'] = $item->name;
			$pdata['item_cid'] = '';
		
			$dispatcher->trigger('onPrepareProcessPayment', array (& $order, & $par, $type, &$row, &$pdata));
			$row->store();
							
			$pdata['item_id'] = $row->id;
			$dispatcher->trigger('onAfterPrepareProcessPayment', array (& $order, & $par, $type, &$row, &$pdata));
			
		}else{
			$query ="SELECT i.*, c.price as c_price FROM #__djcf_items i "
					."LEFT JOIN #__djcf_categories c ON c.id=i.cat_id "
					."WHERE i.id=".$id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			if(!isset($item)){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$redirect="index.php?option=com_djclassifieds&view=items&cid=0";
			}
				
			$amount = 0;
		
			if(strstr($item->pay_type, 'cat')){
				$amount += $item->c_price/100;
			}
			
			if(strstr($item->pay_type, 'mc')){
				$query = "SELECT * FROM #__djcf_categories "
						."WHERE published=1";
						$db->setQuery($query);
						$categories=$db->loadObjectList('id');
			
						$pay_elems = explode(',', $item->pay_type);
						foreach($pay_elems as $pay_el){
							if(strstr($pay_el, 'mc')){
								$mc_id = str_ireplace('mc', '', $pay_el);
								$mcat = $categories[$mc_id];
			
								$amount += $mcat->price/100;;
							}
						}
			
			}
			
			if(strstr($item->pay_type, 'type,')){
				$itype = DJClassifiedsPayment::getTypePrice($item->user_id,$item->type_id);
				$amount += $itype->price;
			}
		
		
			$query = "SELECT * FROM #__djcf_days d "
					."WHERE d.days=".$item->exp_days." LIMIT 1";
			$db->setQuery($query);
			$day = $db->loadObject();
		
		
			if(strstr($item->pay_type, 'duration_renew')){
				$amount += $day->price_renew;
			}else if(strstr($item->pay_type, 'duration')){
				$amount += $day->price;
			}
		
			if(strstr($item->pay_type, 'extra_img_renew')){
				if($day->img_price_default){
					$amount += $par->get('img_price_renew','0')*$item->extra_images_to_pay;
				}else{
					$amount += $day->img_price_renew*$item->extra_images_to_pay;
				}
			}else if(strstr($item->pay_type, 'extra_img')){
				if($day->img_price){
					$amount += $day->img_price*$item->extra_images_to_pay;
				}else{
					$amount += $par->get('img_price','0')*$item->extra_images_to_pay;
				}				
			}
		
			if(strstr($item->pay_type, 'extra_chars_renew')){
				if($day->char_price_default){
					$amount += $par->get('desc_char_price_renew','0')*$item->extra_chars_to_pay;
				}else{
					$amount += $day->char_price_renew*$item->extra_chars_to_pay;
				}
			}else if(strstr($item->pay_type, 'extra_chars')){
				if(@$day->char_price_default){
					$amount += $par->get('desc_char_price','0')*$item->extra_chars_to_pay;
				}else{
					if(isset($day->char_price)){
						$amount += $par->get('desc_char_price','0')*$item->extra_chars_to_pay;
					}else{
						$amount += $day->char_price*$item->extra_chars_to_pay;
					}
					
				}
			}
		
			/*$query = "SELECT p.* FROM #__djcf_promotions p "
					."WHERE p.published=1 ORDER BY p.id ";
			$db->setQuery($query);
			$promotions=$db->loadObjectList();
			foreach($promotions as $prom){
				if(strstr($item->pay_type, $prom->name)){
					$amount += $prom->price;
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
			unset($prom);
						
			foreach($prom_prices as $prom_p){
				if(isset($promotions[$prom_p->prom_id])){
					$promotions[$prom_p->prom_id]->prices[$prom_p->days] = $prom_p;
				}
			}
			
			foreach($promotions as $prom){
				$pay_type_a = explode(',', $item->pay_type);
				foreach($pay_type_a as $pay_type_e){
					if(strstr($pay_type_e, $prom->name)){
						$pay_type_ep = explode('_', $pay_type_e);
						if(isset($prom->prices[$pay_type_ep[3]])){
							$amount+=$prom->prices[$pay_type_ep[3]]->price;
						}
					}
				}
			}
			
				
			/*$query = 'DELETE FROM #__djcf_payments WHERE item_id= "'.$id.'" ';
			 $db->setQuery($query);
			$db->query();
		
		
			$query = 'INSERT INTO #__djcf_payments ( item_id,user_id,method,  status)' .
			' VALUES ( "'.$id.'" ,"'.$user->id.'","'.$ptype.'" ,"Start" )'
			;
			$db->setQuery($query);
			$db->query();*/
		
			$row->item_id = $id;
			$row->user_id = $user->id;
			$row->method = $method;
			$row->status = 'Start';
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
			$row->price = $amount;
			$row->type=0;
		
			$pdata['amount'] = $amount;
			$pdata['itemname'] = $item->name;
			$pdata['item_cid'] = '&cid='.$item->cat_id;
			
			$dispatcher->trigger('onPrepareProcessPayment', array (& $item, & $par, $type, &$row, &$pdata));
			
			$row->store();							
			
			$pdata['item_id'] = $row->id;
			
			$dispatcher->trigger('onAfterPrepareProcessPayment', array (& $item, & $par, $type, &$row, &$pdata));
		}
		
		return $pdata; 
	}
	
	public static function completePayment($id,$price, $txn_id=''){
		$db = JFactory::getDBO();
		$par = &JComponentHelper::getParams( 'com_djclassifieds' );
		$user	= JFactory::getUser();
		
			$query = "SELECT p.*  FROM #__djcf_payments p "
					."WHERE p.id='".$id."' ";
			$db->setQuery($query);
			$payment = $db->loadObject();
		
				if($payment){
					if(floatval($price) != floatval($payment->price)){
						//die('Wrong amount');
						return false;
					}
						
					$query = "UPDATE #__djcf_payments SET status='Completed',transaction_id='".$txn_id."' "
							."WHERE id=".$id." ";
							$db->setQuery($query);
							$db->query();
								
								
							if($payment->type==5){	//offer
								$query ="SELECT o.* FROM #__djcf_offers o "
										."WHERE o.id=".$payment->item_id." LIMIT 1";
										$db->setQuery($query);
										$order = $db->loadObject();
		
										$query = "SELECT i.*  FROM #__djcf_items i "
												."WHERE i.id='".$order->item_id."' ";
												$db->setQuery($query);
												$item = $db->loadObject();
		
												$query="UPDATE #__djcf_offers SET paid=1 "
														." WHERE id=".$order->id;
														$db->setQuery($query);
														$db->query();
		
							}else if($payment->type==4){	//buy now
								$query ="SELECT o.* FROM #__djcf_orders o "
										."WHERE o.id=".$payment->item_id." LIMIT 1";
										$db->setQuery($query);
										$order = $db->loadObject();
		
		
										$query = "SELECT i.*  FROM #__djcf_items i "
												."WHERE i.id='".$order->item_id."' ";
												$db->setQuery($query);
												$item = $db->loadObject();
		
												$new_quantity = $item->quantity - $order->quantity;
												$new_published = '';
												if($new_quantity==0){
													$new_published = ", published=0 ";
												}
												$query="UPDATE #__djcf_items SET quantity='".$new_quantity."' ".$new_published
												." WHERE id=".$item->id;
												$db->setQuery($query);
												$db->query();
		
												$query="UPDATE #__djcf_orders SET status=1 "
														." WHERE id=".$order->id;
														$db->setQuery($query);
														$db->query();
		
		
														$buyer = JFactory::getUser($order->user_id);
														DJClassifiedsNotify::notifyBuynowBuyer($item->id,$buyer,$order->quantity,$order->item_option);
														DJClassifiedsNotify::notifyBuynowAuthor($item->id,$buyer,$order->quantity,$order->item_option);
		
							}else if($payment->type==3){ //subscription plans
								$query = "SELECT p.*  FROM #__djcf_plans p WHERE p.id='".$payment->item_id."' ";
								$db->setQuery($query);
								$plan = $db->loadObject();
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
										."VALUES ('".$payment->user_id."','".$plan->id."','".$plan_params->ad_limit."','".$plan_params->ad_limit."','".$date_start."','".$date_exp."','".addslashes($plan->params)."')";
										$db->setQuery($query);
										$db->query();
											
										if($plan->groups_assignment && $payment->user_id){
											$client = JFactory::getUser($payment->user_id);
											$ga = $client->groups;
											$ga[$plan->groups_assignment] = $plan->groups_assignment;
											JUserHelper::setUserGroups($payment->user_id, $ga);
										}
											
										$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_SUBSCRIPTION_PLAN_ADDED');
		
							}else if($payment->type==2){
								$date_sort = date("Y-m-d H:i:s");
								$query = "UPDATE #__djcf_items SET date_sort='".$date_sort."' "
										."WHERE id=".$payment->item_id." ";
										$db->setQuery($query);
										$db->query();
							}else if($payment->type==1){
		
								$query = "SELECT p.points  FROM #__djcf_points p WHERE p.id='".$payment->item_id."' ";
								$db->setQuery($query);
								$points = $db->loadResult();
		
								$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
										."VALUES ('".$payment->user_id."','".$points."','".addslashes(JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGE')." <br />".JText::_('COM_DJCLASSIFIEDS_PAYMENT_ID')).' '.$payment->id."')";
										$db->setQuery($query);
										$db->query();
							}else{
								$query = "SELECT c.*  FROM #__djcf_items i, #__djcf_categories c "
										."WHERE i.cat_id=c.id AND i.id='".$payment->item_id."' ";
										$db->setQuery($query);
										$cat = $db->loadObject();
		
										self::applayPromotions($payment->item_id);
										
										$pub=0;
										if(($cat->autopublish=='1') || ($cat->autopublish=='0' && $par->get('autopublish')=='1')){
											$pub = 1;
										}
		
										$query = "UPDATE #__djcf_items SET payed=1, pay_type='',extra_images_to_pay='0',extra_chars_to_pay='0', published='".$pub."' "
												."WHERE id=".$payment->item_id." ";
												$db->setQuery($query);
												$db->query();
		
												if($pub){
													if($par->get('notify_status_change',2)>0){
														DJClassifiedsNotify::notifyUserPublication($payment->item_id,'1');
													}
												}
							}
						$payment->status='Completed';
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('onAfterPaymentStatusChange', array($payment));
						
					return true;	
				}
			return false;
		}

		
		public static function applayPromotions($item_id){
			$app = JFactory::getApplication();
			$db = JFactory::getDBO();
			
			$query = "SELECT * FROM #__djcf_items_promotions WHERE item_id = ".$item_id." ORDER BY id";
			$db->setQuery($query);
			$old_promotions = $db->loadObjectList('prom_id');
			
			$query = "SELECT * FROM #__djcf_items WHERE id = ".$item_id." LIMIT 1";
			$db->setQuery($query);
			$item = $db->loadObject();
			
			$query = "SELECT * FROM #__djcf_promotions";
			$db->setQuery($query);
			$promotions = $db->loadObjectList('id');
			
			if($item->pay_type){
				$query = "INSERT INTO #__djcf_items_promotions(`item_id`,`prom_id`,`date_exp`,`days`) VALUES ";
				$ins=0;
				$pay_type = explode(',', $item->pay_type);
				foreach($pay_type as $pay_t){
					if(strstr($pay_t, 'p_')){
						$days_left = 0;
						$pay_prom = explode('_', $pay_t);
						$prom_id = $pay_prom[2];
						$prom_days = $pay_prom[3];
						if($prom_id){
							if(isset($old_promotions[$prom_id])){
								if($old_promotions[$prom_id]->date_exp>=date("Y-m-d H:i:s")){																																
									$days_left = strtotime($old_promotions[$prom_id]->date_exp)-time();								
								}
								$query_del = "DELETE FROM #__djcf_items_promotions WHERE item_id=".$item->id." AND prom_id=".$prom_id." ";
								$db->setQuery($query_del);
								$db->query();
							}
							$prom_exp_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s")+$days_left, date("m")  , date("d")+$pay_prom[3], date("Y")));
							$query .= "('".$item->id."','".$prom_id."','".$prom_exp_date."','".$prom_days."'), ";
							$ins++;
						}
						//print_r($pay_prom);
					}
				}
				//echo $query;die();
				if($ins){
					$query = substr($query, 0, -2).';';
					$db->setQuery($query);
					$db->query();
				}
			}
			
			$date_now = date("Y-m-d H:i:s"); 
			$query = "SELECT * FROM #__djcf_items_promotions WHERE item_id = ".$item_id." AND date_exp>'".$date_now."' ORDER BY id";
			$db->setQuery($query);
			$new_promotions = $db->loadObjectList('prom_id');
			
			$new_prom = '';
			foreach($new_promotions as $prom){
				$new_prom .= $promotions[$prom->prom_id]->name.',';
			}
			
			if(strstr($new_prom, 'p_first')){
				$special = 1;
			}else{ 
				$special = 0;
			}
			
			$query = "UPDATE #__djcf_items SET promotions='".$new_prom."', special='".$special."' WHERE id=".$item_id."  ";
			$db->setQuery($query);
			$db->query();
			
			//echo '<pre>';echo $new_prom;print_r($new_promotions);die();
			
			return $new_promotions; 						
			
		}
		
		
		public static function updatePromotions(){
			$app = JFactory::getApplication();
			$db = JFactory::getDBO();
			$date_now = date("Y-m-d H:i:s"); 
				
			$query = "SELECT item_id FROM #__djcf_items_promotions WHERE date_exp <= '".$date_now."' AND updated=0 GROUP BY item_id ";
			$db->setQuery($query);
			$items_ex = $db->loadObjectList();
						
			if(count($items_ex)){
				$id_list= '';
				$items_list = array();
				foreach($items_ex as $item){
					if($id_list){
						$id_list .= ','.$item->item_id;
					}else{
						$id_list .= $item->item_id;
					}
					$items_list[$item->item_id] = '';
				}
				
				$query = "SELECT * FROM #__djcf_items_promotions WHERE date_exp > '".$date_now."' AND item_id IN (".$id_list.") ";
				$db->setQuery($query);
				$items_proms = $db->loadObjectList();
				
				$query = "SELECT * FROM #__djcf_promotions ";
				$db->setQuery($query);
				$promotions = $db->loadObjectList('id');
				
				foreach($items_proms as $item_prom){
					$items_list[$item_prom->item_id] .=  $promotions[$item_prom->prom_id]->name.',';
				}
				foreach($items_ex as $item){
					$i_prom = $items_list[$item->item_id];
					
					if(strstr($i_prom, 'p_first')){ $special = 1;
					}else{ $special = 0;}
					
					$query = "UPDATE #__djcf_items SET promotions = '".$i_prom."' , special = '".$special."' WHERE id = '".$item->item_id."' ";
					$db->setQuery($query);					
					$db->query();
				}
				
				$query = "UPDATE #__djcf_items_promotions SET updated=1 WHERE date_exp <= '".$date_now."' AND updated=0  ";
				$db->setQuery($query);
				$db->query();
									
			}
			
			return true;
				
		}
		
}
