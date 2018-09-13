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

// No direct access
defined('_JEXEC') or die;
class DJClassifiedsTableTypes extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djcf_types', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}				
		
		return parent::bind($array, $ignore);
	}
	
	
	public function store($updateNulls = false)
	{
		$success = parent::store($updateNulls);
	
		if (!$success) {
			return false;
		}
		
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		$gp_check = $app->input->getVar('gp_check',array());
		$gp_input = $app->input->getVar('gp_input',array());
		$gp_points = $app->input->getVar('gp_points',array());
		$gp_price_special = $app->input->getVar('gp_price_special',array());
		$item_id = (int)$this->id;
		
		$query  = "DELETE FROM #__djcf_groups_prices WHERE type='type' AND item_id=".$item_id;
		$db->setQuery($query);
		$db->query();
		
		
		$query = "INSERT INTO #__djcf_groups_prices(`type`,`item_id`,`group_id`,`price`,`points`,`price_special`) VALUES ";
		$gp = 0;
		for($i=0;$i<count($gp_check);$i++){		
			$price_s = 0;
			if(isset($gp_price_special[$i])){
				$price_s = $gp_price_special[$i];
			}	
			if($gp_input[$i]){
				$query .= "('type','".$item_id."','".$gp_check[$i]."','".$gp_input[$i]."','".$gp_points[$i]."','".$price_s."'), ";
				$gp++;
			}
		}
		if($gp){
			$query = substr($query, 0,-2).';';
			$db->setQuery($query);
			$db->query();						
		}
				
		return true;
	
	}
	
}
