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

class DJClassifiedsType {

	function __construct(){
	}

	public static function getTypesSelect($show_price=false){		
		
		$db= JFactory::getDBO();
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$points_a = $par->get('points',0);

		$query = "SELECT t.id as value, t.name as text, '0' as disabled, t.price, t.points, t.price_special FROM #__djcf_types t "
				."WHERE t.published=1 "
				."ORDER BY t.ordering";
		
			$db->setQuery($query); 
			$types=$db->loadObjectList('value');
			
			if($show_price){
				$user = JFactory::getUser();
				if(count($user->groups)){				
					$g_list = implode(',',$user->groups);							
					$query = "SELECT gp.* FROM #__djcf_groups_prices gp "
							."WHERE  gp.type='type' AND gp.group_id in(".$g_list.") ";
					
					$db->setQuery($query);
					$types_prices=$db->loadObjectList();
					
					//echo '<pre>';print_r($types_prices);echo '</pre>';die();
					if($types_prices){
						foreach($types_prices as $tp){
							if(isset($types[$tp->item_id])){
								if($types[$tp->item_id]->price>$tp->price){
									$types[$tp->item_id]->price=$tp->price;
									$types[$tp->item_id]->price_special=$tp->price_special;
								}
								if($types[$tp->item_id]->points>$tp->points){
									$types[$tp->item_id]->points=$tp->points;
								}
							}
						}
					}
					//echo '<pre>';print_r($types);echo '</pre>';die();
				}
				
				foreach($types as $t){
					if($t->price>0 && $points_a!=2){	
						$t->text .= '&nbsp;-&nbsp;'.strip_tags(DJClassifiedsTheme::priceFormat($t->price,$par->get('unit_price')));
					}
					if($t->points>0){
						$t->text .= '&nbsp;-&nbsp;'.$t->points.'&nbsp;'.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');	
					}
					if($t->price_special>0){
						$t->text .= ' - '.strip_tags(DJClassifiedsTheme::priceFormat($t->price_special,$par->get('unit_price')).' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT'));
					}
				}
			}
			
		//echo '<pre>';print_r($types);echo '</pre>';die();
		
		return $types;
		
	}
	
	
	public static function getTypesLabels($show_price=false){
	
		$db= JFactory::getDBO();
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$points_a = $par->get('points',0);
	
		$query = "SELECT * FROM #__djcf_types t "
				."WHERE t.published=1 "
				."ORDER BY t.ordering";
	
		$db->setQuery($query);
		$types=$db->loadObjectList('id');
		
		foreach($types as $type){
			
			$registry = new JRegistry();
			$registry->loadString($type->params);
			$type->params = $registry->toObject();
			if($type->params->bt_use_styles){
				$style='style="display:inline-block;
							 			border:'.(int)$type->params->bt_border_size.'px solid '.$type->params->bt_border_color.';'
									 		   .'background:'.$type->params->bt_bg.';'
								 		   		.'color:'.$type->params->bt_color.';'
								   				.$type->params->bt_style.'"';
				$type->preview =  '<span class="type_button" '.$style.' >'.$type->name.'</span>';
			}else{
				$type->preview = $type->name;
			}
			//echo '<pre>';print_r($type);die();
		}
		
			
		if($show_price){
			$user = JFactory::getUser();
			if(count($user->groups)){
				$g_list = implode(',',$user->groups);
				$query = "SELECT gp.* FROM #__djcf_groups_prices gp "
						."WHERE  gp.type='type' AND gp.group_id in(".$g_list.") ";
					
				$db->setQuery($query);
				$types_prices=$db->loadObjectList();
					
				//echo '<pre>';print_r($types_prices);echo '</pre>';die();
				if($types_prices){
					foreach($types_prices as $tp){
						if(isset($types[$tp->item_id])){
							if($types[$tp->item_id]->price>$tp->price){
								$types[$tp->item_id]->price=$tp->price;
								$types[$tp->item_id]->price_special=$tp->price_special;
							}
							if($types[$tp->item_id]->points>$tp->points){
								$types[$tp->item_id]->points=$tp->points;
							}
						}
					}
				}
				//echo '<pre>';print_r($types);echo '</pre>';die();
			}
	
			foreach($types as $t){
				$t->pricing = '';
				if($t->price>0 && $points_a!=2){
					$t->pricing .= strip_tags(DJClassifiedsTheme::priceFormat($t->price,$par->get('unit_price')));
				}
				if($t->points>0){
					if($points_a!=2){
						$t->pricing .= '&nbsp;-&nbsp;';
					}
					$t->pricing .= $t->points.'&nbsp;'.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
				}
				if($t->price_special>0){
					$t->pricing .= ' - '.strip_tags(DJClassifiedsTheme::priceFormat($t->price_special,$par->get('unit_price')).' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT'));
				}
			}
		}else{
			foreach($types as $t){
				$t->pricing = '';
			}
		}
			
		//echo '<pre>';print_r($types);echo '</pre>';die(); 
	
		return $types;
	
	}

}
