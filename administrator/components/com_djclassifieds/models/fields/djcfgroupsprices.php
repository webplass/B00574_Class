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

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDjcfgroupsprices extends JFormField {
	
	protected $type = 'Djcfgroupsprices';
	
	protected function getInput()
	{
		$html = array();
		
		$db= JFactory::getDBO();		
		$item_id = $this->form->getValue('id');
		$item_type = $this->form->getName();
		
			$query = "SELECT * FROM #__usergroups ORDER BY title";
			$db->setQuery($query);
			$groups=$db->loadObjectList('id');
			
			if($item_id){
				$query = "SELECT * FROM #__djcf_groups_prices WHERE type='type' AND item_id=".$item_id." ";
				$db->setQuery($query);
				$user_groups=$db->loadObjectList('group_id');
			}
			
			$html = '';
            foreach($groups as $group){
            	$group_ch = "";
            	$group_input = ' disabled="true" ';
            	$group_price = '';
            	$group_points = '';
            	$group_price_special = '';
            	if(isset($user_groups[$group->id])){
            		$group_ch = ' checked="checked" ';
            		$group_input = '';
            		$group_price = $user_groups[$group->id]->price;
            		$group_points = $user_groups[$group->id]->points;
            		$group_price_special = $user_groups[$group->id]->price_special;
            	}
            	$html .= '<div class="group_price" style="margin-bottom:10px;" >';
            		$html .= '<input autocomplete="off" style="margin-top:0px;" '.$group_ch.' id="gp_check'.$group->id.'" type="checkbox" name="gp_check[]" value="'.$group->id.'" onchange="groupPriceChange(this);" >';
            		$html .= '<label for="gp_check'.$group->id.'" style="width:250px;display:inline-block;margin:0 10px 0 5px ;">'.$group->title.'</label> ';           
            		$html .= '<input autocomplete="off" placeholder="'.JText::_('COM_DJCLASSIFIEDS_CUSTOM_PRICE').'" class="validate-numeric" '.$group_input.' type="input" id="gp_input'.$group->id.'" name="gp_input[]" value="'.$group_price.'"  > ';
            		$html .= '<input autocomplete="off" placeholder="'.JText::_('COM_DJCLASSIFIEDS_CUSTOM_POINTS').'" class="validate-numeric" '.$group_input.' type="input" id="gp_points'.$group->id.'" name="gp_points[]" value="'.$group_points.'"  >';
            		$html .= '<input autocomplete="off" class="special_prices" style="display:none" placeholder="'.JText::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE').'" class="validate-numeric" '.$group_input.' type="input" id="gp_price_special'.$group->id.'" name="gp_price_special[]" value="'.$group_price_special.'"  >';
            		$html .= '';
            	$html .= '</div>';
            }
	          
            
            $html .= '<script type="text/javascript">
	            function groupPriceChange(v){            		
	            	if(v.checked){
	            		document.id("gp_input"+v.value).set("disabled","");
            			document.id("gp_points"+v.value).set("disabled","");
            			document.id("gp_price_special"+v.value).set("disabled","");
	            	}else{
	            		document.id("gp_input"+v.value).set("disabled","disabled");
            			document.id("gp_points"+v.value).set("disabled","disabled");
            			document.id("gp_price_special"+v.value).set("disabled","disabled");
	            	}	            	
	            	return null;	            
	            }
            </script>';
	          //echo '<pre>';print_r($groups);die();
			
	
		return $html;
	}
	

}
?>