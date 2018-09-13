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

// No direct access.
defined('_JEXEC') or die;
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djclassifieds'.DS.'tables');
jimport('joomla.application.component.controlleradmin');

class DJClassifiedsControllerPayments extends JControllerAdmin
{
	/*public function getModel($name = 'Payment', $prefix = 'DJClassifiedsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	*/
	
	function changeStatus(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('cid', array (), '', 'array');
		$row = JTable::getInstance('Payments', 'DJClassifiedsTable');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
				
		if(isset($ids[0])){
			$id = 	$ids[0];
			$status = JRequest::getVar('change_status_'.$id,'');
		}else{
			$redirect = 'index.php?option=com_djclassifieds&view=payments';
	    	$app->redirect($redirect, JText::_('COM_DJCLASSIFIEDS_WRONG_PAYMENT'));
		}
		$row->load($id);
			if($row->type==5){	//offer															
				$query ="SELECT o.* FROM #__djcf_offers o "
		   			."WHERE o.id=".$row->item_id." LIMIT 1";
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
				
			}else if($row->type==4){	//buy now															
				$query ="SELECT o.* FROM #__djcf_orders o "
		   			."WHERE o.id=".$row->item_id." LIMIT 1";
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
				
			}else if($row->type==3){ //subscription plans			
				$query = "SELECT p.*  FROM #__djcf_plans p WHERE p.id='".$row->item_id."' ";					
				$db->setQuery($query);
				$plan = $db->loadObject();
				$registry = new JRegistry();
				$registry->loadString($plan->params);
				$plan_params = $registry->toObject();
				
				//echo '<pre>';print_r($row);print_r($plan);print_r($plan_params);die();											
				if($status=='Completed' && $row->status!='Completed'){

					if($plan->groups_assignment && $row->user_id){						
						$client = JFactory::getUser($row->user_id);
						$ga = $client->groups;
						$ga[$plan->groups_assignment] = $plan->groups_assignment;						
						JUserHelper::setUserGroups($row->user_id, $ga);						
					}
					
					$date_start = date("Y-m-d H:i:s");
					$date_exp = '';
					if($plan_params->days_limit){
						$date_exp_time = time()+$plan_params->days_limit*24*60*60;
						$date_exp = date("Y-m-d H:i:s",$date_exp_time) ;
					}
										
					
					$query = "INSERT INTO #__djcf_plans_subscr (`user_id`,`plan_id`,`adverts_limit`,`adverts_available`,`date_start`,`date_exp`,`plan_params`) "
							."VALUES ('".$row->user_id."','".$plan->id."','".$plan_params->ad_limit."','".$plan_params->ad_limit."','".$date_start."','".$date_exp."','".addslashes($plan->params)."')";					
					$db->setQuery($query);
					$db->query();						
					$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_SUBSCRIPTION_PLAN_ADDED');	
				}																
			}else if($row->type==2){	 //promotion move to top			
				if($status=='Completed'){
					$item = JTable::getInstance('Items', 'DJClassifiedsTable');
					$item->load($row->item_id);
					$item->date_sort=date("Y-m-d H:i:s");
					$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_PROMOTION_MOVE_TO_TOP_ACTIVATED');
					if (!$item->store()){
						echo $row->getError();
						exit ();
					}
				}
					
			}else if($row->type==1){ //points package			
				$query = "SELECT p.points  FROM #__djcf_points p WHERE p.id='".$row->item_id."' ";					
				$db->setQuery($query);
				$points = $db->loadResult();
				//echo '<pre>';print_r($row);die();											
				if($status=='Completed' && $row->status!='Completed'){												
					$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
							."VALUES ('".$row->user_id."','".$points."','".addslashes(JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGE')." ".$row->method." ".JText::_('COM_DJCLASSIFIEDS_PAYMENT_ID')." ".$row->id." ".JText::_('COM_DJCLASSIFIEDS_COMPLETED'))."')";					
					$db->setQuery($query);
					$db->query();						
					$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_POINTS_PACKAGE_ADDED');	
				}else if($status!='Completed' && $row->status=='Completed'){
					$query = "INSERT INTO #__djcf_users_points (`user_id`,`points`,`description`) "
							."VALUES ('".$row->user_id."','-".$points."','".addslashes(JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGE')." ".$row->method." ".JText::_('COM_DJCLASSIFIEDS_PAYMENT_ID')." ".$row->id." ".$status)."')";			
					$db->setQuery($query);
					$db->query();														
					$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_POINTS_PACKAGE_DELETED');
				}																		
			}else{ //advert
				$item = JTable::getInstance('Items', 'DJClassifiedsTable');
				
				if($status=='Completed'){
					DJClassifiedsPayment::applayPromotions($row->item_id);
					$item->load($row->item_id);		
					$item->payed=1;
					$item->pay_type='';
					$item->published=1;
					$item->extra_images_to_pay = 0;
					$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_ADVERT_PUBLISHED');
					if($par->get('notify_status_change',2)>0){
						DJClassifiedsNotify::notifyUserPublication($item->id,'1');
					}					
				}else if($row->status!='Completed'){
					$item->load($row->item_id);
					$item->published=0;				
					$message = JText::_('COM_DJCLASSIFIEDS_STATUS_CHANGED_ADVERT_UNPUBLISHED');
				}			
				if (!$item->store()){
					echo $row->getError();
		        	exit ();	
		    	}							
			}			
		
		//echo '<pre>';print_r($row);die();
		
		$row->status = $status;		
		if (!$row->store()){
			 echo $row->getError(); exit ();
		}	

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterPaymentStatusChange', array($row));
		
		$redirect = 'index.php?option=com_djclassifieds&view=payments';
	    $app->redirect($redirect, $message);

	}
	

}