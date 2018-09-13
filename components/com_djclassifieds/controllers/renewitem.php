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


class DJClassifiedsControllerRenewItem extends JControllerLegacy {
	
	function save(){
		$app = JFactory::getApplication();
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');		
		jimport( 'joomla.database.table' );

    	$row = JTable::getInstance('Items', 'DJClassifiedsTable');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$user = JFactory::getUser();
				
		$db = JFactory::getDBO();
		$id = JRequest::getVar('id', 0, '', 'int' );
		$redirect = '';
			
			$menus		= $app->getMenu('site');
			$menu_item = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&cid=0',1);
			$menu_item_blog = $menus->getItems('link','index.php?option=com_djclassifieds&view=items&layout=blog&cid=0',1);
			
			$itemid = ''; 
			if($menu_item){
				$itemid='&Itemid='.$menu_item->id;
			}else if($menu_item_blog){
				$itemid='&Itemid='.$menu_item_blog->id;
			}

 		    $menu_newad_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=additem',1);
		    $new_ad_link='index.php?option=com_djclassifieds&view=additem';
			    if($menu_newad_itemid){
					$new_ad_link .= '&Itemid='.$menu_newad_itemid->id;
			    }		  	
				$new_ad_link = JRoute::_($new_ad_link,false);
		
			if($user->id=='0'){
				//$uri = "index.php?option=com_djclassifieds&view=items&cid=0".$itemid;
				$uri=DJClassifiedsSEO::getCategoryRoute('0:all');
				$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
				$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
			}
				
				
		if($id==0){		 	
			$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			//$redirect="index.php?option=com_djclassifieds&view=items&cid=0".$itemid;
			$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');
			$redirect = JRoute::_($redirect,false);
			$app->redirect($redirect, $message,'error');			
		}

	     $db = JFactory::getDBO();
		 	$query = "SELECT user_id FROM #__djcf_items WHERE id='".$id."' LIMIT 1";
		 	$db->setQuery($query);
		 	$item_user_id =$db->loadResult();	
			if($item_user_id!=$user->id){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');				
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message,'error');
			}
		 
		
		
		
		$row->load($id);		
	
		$row->exp_days = JRequest::getVar('exp_days', $par->get('exp_days'), '', 'int' );
		$row->type_id = JRequest::getVar('type_id', 0, '', 'int' );
				
		$row->promotions='';
		if($par->get('promotion','1')=='1'){
			
			$query = "SELECT p.* FROM #__djcf_promotions p WHERE p.published=1 ORDER BY p.id ";
			$db->setQuery($query);
			$promotions=$db->loadObjectList('id');
			
			$query = "SELECT p.* FROM #__djcf_promotions_prices p ORDER BY p.days ";
			$db->setQuery($query);
			$prom_prices=$db->loadObjectList();
			
			$query = "SELECT * FROM #__djcf_items_promotions WHERE item_id = ".$row->id." ORDER BY id";
			$db->setQuery($query);
			$old_promotions = $db->loadObjectList('prom_id');			
			
			foreach($promotions as $prom){
				$prom->prices = array();
			}
			foreach($prom_prices as $prom_p){
				$promotions[$prom_p->prom_id]->prices[$prom_p->days] = $prom_p;
			}
			
			$prom_to_pay = '';
			
			foreach($promotions as $prom){
				$days_left = 0;
				if(isset($old_promotions[$prom->id])){
					if($old_promotions[$prom->id]->date_exp>=date("Y-m-d H:i:s")){
						$old_prom_to_pay = $prom->name.'_'.$prom->id.'_'.$old_promotions[$prom->id]->days.',';
						if(strstr($old_row->pay_type, $old_prom_to_pay)){
							$days_left = 0;
						}else if($old_promotions[$prom->id]->date_exp>date("Y-m-d H:i:s")){
							$days_left = strtotime($old_promotions[$prom->id]->date_exp)-time();
						}
					}
				}
			
				if($days_left){
					$row->promotions .=$prom->name.',';
				}
					
				$prom_v = JRequest::getInt($prom->name,0);
				//echo $prom->name.' '.$prom_v.'<br />';
				if($prom_v){
					if(isset($prom->prices[$prom_v])){
						$pp = $prom->prices[$prom_v];
						//$ins++;
						if($pp->price){
							$new_prom = $prom->name.'_'.$prom->id.'_'.$pp->days.',';
							if(!strstr($row->pay_type, $new_prom)){
								$prom_to_pay .= $new_prom;
							}
								
						}
					}
			
				}
			}
		} 

		if(strstr($row->promotions, 'p_first')){
			$row->special = 1;
		}else{
			$row->special = 0;
		}

		$row->payed = 1;
		$row->pay_type = $prom_to_pay;
		$row->notify = 0;
		

		
				
		//echo '<pre>';print_r($row);die();echo '</pre>';
		if (!$row->store()){
			//echo $row->getError();exit ();	
    	}
   

		$redirect="index.php?option=com_djclassifieds&view=item&task=renew&id=".$row->id."&Itemid=".JRequest::getVar('Itemid','0');	
		$redirect = JRoute::_($redirect,false);		
		$app->redirect($redirect, $message);

	}
}

?>