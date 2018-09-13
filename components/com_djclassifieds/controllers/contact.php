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


class DJClassifiedsControllerContact extends JControllerLegacy {
	
	function bidderMessage(){
		$app 		= JFactory::getApplication();		
		$user 		= JFactory::getUser();				
		$db 		= JFactory::getDBO();
		$bid_id	 	= JRequest::getInt('bid', 0);
		$id	 		= JRequest::getInt('id', 0);
		$db			= JFactory::getDBO();
		$m_title	= JRequest::getVar('c_title', 0);
		$m_message  = JRequest::getVar('c_message', 0);
		
		$e_mesage = '';
		$e_type =  '';
		$ms = 0;
		
		if($user->id>0){
			
			$query = "SELECT i.*, c.name as c_name,c.alias as c_alias, u.name as u_name, u.email as u_email FROM #__djcf_items i, #__djcf_categories c, #__users u  "
					."WHERE c.id=i.cat_id AND u.id=i.user_id AND i.id= ".$id." LIMIT 1 ";
			
			$db->setQuery($query);
			$item=$db->loadObject();
			//echo '<pre>';print_r($db);print_r($item);die();
			if($item->user_id==$user->id){
				
				$query = "SELECT a.*, u.email FROM #__djcf_auctions a, #__users u "
						."WHERE a.user_id=u.id AND a.id= ".$bid_id." AND a.item_id= ".$id." LIMIT 1 ";
				
				$db->setQuery($query);
				$bid=$db->loadObject();
				
				if($bid){
					$bidder = JFactory::getUser($bid->user_id);
					DJClassifiedsNotify::messageAuthorToBidder($id,$bidder,$item,$bid->price,$user,$m_title,$m_message);
					$ms = 1;					
				}else{
					$e_mesage = JText::_('COM_DJCLASSIFIEDS_WRONG_BID');
					$e_type = 'error';
				}
			}else{
				$e_mesage = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$e_type = 'error';
			}
		}else{
			$e_mesage = JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN');
			$e_type = 'error';
		}


		$redirect="index.php?option=com_djclassifieds&view=contact&id=".$id."&bid=".$bid->id."&ms=".$ms."&tmpl=component";	
		//$redirect = JRoute::_($redirect,false);		
		$app->redirect($redirect, $e_mesage);

	}
	
	
	function saveOfferResponse(){
		$app 		= JFactory::getApplication();
		$user 		= JFactory::getUser();
		$db 		= JFactory::getDBO();
		$offer_id	= JRequest::getInt('offer_id', 0);
		$item_id	= JRequest::getInt('item_id', 0);
		$db			= JFactory::getDBO();
		$o_status	= JRequest::getVar('offer_status', 0);
		$o_message  = JRequest::getVar('offer_msg', 0);
		$itemid  	= JRequest::getInt('Itemid', 0);
		$return_view= JRequest::getVar('return_view', 'useritems');
	
		$e_mesage = '';
		$e_type =  '';
		$ms = 0;
	
		if($user->id>0){
				
			$query = "SELECT i.*, c.name as c_name,c.alias as c_alias, u.name as u_name, u.email as u_email FROM #__djcf_items i, #__djcf_categories c, #__users u  "
					."WHERE c.id=i.cat_id AND u.id=i.user_id AND i.id= ".$item_id." LIMIT 1 ";
				
			$db->setQuery($query);
			$item=$db->loadObject();
			//echo '<pre>';print_r($db);print_r($item);die();
			if($item->user_id==$user->id){
	
				$query = "SELECT o.* FROM #__djcf_offers o "
						."WHERE o.id= ".$offer_id." AND o.item_id= ".$item_id." LIMIT 1 ";
	
				$db->setQuery($query);
				$offer=$db->loadObject();
	
				if($offer){
						
					$query="UPDATE #__djcf_offers SET response='".addslashes($o_message)."', status='".$o_status."' WHERE id=".$offer->id." ";
					$db->setQuery($query);
					$db->query();
						
						
					if($o_status==1){
						$query="INSERT INTO #__djcf_orders (`item_id`, `user_id`, `ip_address`, `price`,`currency`, `quantity`, `status`,`item_name`)"
								." VALUES ( '".$offer->item_id."','".$offer->user_id."','".$offer->ip_address."', '".round($offer->price/$offer->quantity,2)."', '".$offer->currency."','".$offer->quantity."','1','".addslashes($item->name)."')";
						$db->setQuery($query);
						$db->query();
					}
						
					$bidder = JFactory::getUser($offer->user_id);
						
					$offer_info = array();
					$offer_info['price'] = $offer->price;
					$offer_info['quantity'] = $offer->quantity;
					$offer_info['msg'] = $offer->message;
					$offer_info['offerer_name'] = $bidder->name;
					$offer_info['offerer_email'] = $bidder->email;
					$offer_info['status'] = $o_status;
					$offer_info['response'] = $o_message;
						
					DJClassifiedsNotify::messageOfferAuthorToOfferer($id,$bidder,$item,$offer_info);
					$e_mesage = JText::_('COM_DJCLASSIFIEDS_MESSAGE_SEND');
					$ms = 1;
				}else{
					$e_mesage = JText::_('COM_DJCLASSIFIEDS_WRONG_OFFER');
					$e_type = 'error';
				}
			}else{
				$e_mesage = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$e_type = 'error';
			}
		}else{
			$e_mesage = JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN');
			$e_type = 'error';
		}	
	
		$redirect="index.php?option=com_djclassifieds&view=".$return_view."&Itemid=".$itemid;		
		$redirect = JRoute::_($redirect,false);
		$app->redirect($redirect, $e_mesage);
	
	}
	
	
}

?>