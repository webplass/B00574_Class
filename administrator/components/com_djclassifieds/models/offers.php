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
defined ('_JEXEC') or die('Restricted access');

/*Items Model*/

//jimport('joomla.application.component.model');
jimport('joomla.application.component.modellist');

class DjClassifiedsModelOffers extends JModelList{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'o.id', 'i.name',				
				'id', 'u.name','u.email',				
				'i.ui_name', 'i.ui_email',
				'o.price','o.date'
								
			);
		}

		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		// List state information.
		parent::populateState('o.id', 'desc');
		$app = JFactory::getApplication();

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$status = $this->getUserStateFromRequest($this->context.'.filter.status', 'filter_status', '');
		$this->setState('filter.status', $status);
				
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.status');
		
		return parent::getStoreId($id);
	}
	
	public function _buildWhere(){		
		$app = JFactory::getApplication();
		$where= '';
		
		$status = $this->getState('filter.status');
		echo $status;
		if (is_numeric($status)) {
			if($status>-1 && $status<3){
				$where .= " AND o.status = '".$status."' AND o.paid = 0 ";	
			}else if($status == 3){
				$where .= " AND o.paid = 1 AND o.confirmed = 0 ";
			}else if($status == 4){
				$where .= " AND o.confirmed = 1 AND o.request = 0 ";
			}else if($status == 5){
				$where .= " AND o.request = 1 ";
			}else if($status == 6){
				$where .= " AND o.admin_paid = 1 ";
			}
			
		}

		$search = $this->getState('filter.search');		
		if (!empty($search)) {
			$db= JFactory::getDBO();
			
			$search_by_id = '';
			if(is_numeric($search)){
				$search_by_id = " OR o.id=".$search." OR o.item_id=".$search." ";
			}
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$where .= " AND (i.name LIKE ".$search." OR u.name LIKE ".$search." OR u.email LIKE ".$search." OR i.ui_name LIKE ".$search." OR i.ui_email LIKE ".$search." ".$search_by_id." )";
		}
		
		return $where;
	}
	
	function getOffers(){
		if(empty($this->_items)) {
			//$limit = JRequest::getVar('limit', '25', '', 'int');
			//$limitstart = JRequest::getVar('limitstart', '0', '', 'int');
			$limit = $this->getState('list.limit');
			$limitstart = $this->getState('list.start');
			
			$orderCol	= $this->state->get('list.ordering');
			$orderDirn	= $this->state->get('list.direction');								
			
			$db= JFactory::getDBO();				
			
			$query = "SELECT o.*, i.name as i_name, u.name as u_name, u.email as u_email, i.ui_name, i.ui_email,ui_id FROM #__djcf_offers o "
					."LEFT JOIN (SELECT i.*, u.name as ui_name, u.email as ui_email, u.id as ui_id FROM #__djcf_items i, #__users u 
								WHERE u.id=i.user_id ) i ON i.id=o.item_id "
					."LEFT JOIN #__users u ON u.id=o.user_id "
					." WHERE 1 ".$this->_buildWhere()." ORDER BY ".$orderCol." ".$orderDirn." ";
			
			$this->_items = $this->_getList($query, $limitstart, $limit);
			
		//	$db->setQuery($query);$items=$db->loadObjectList();echo '<pre>';print_r($db);print_r($items);die();
		}
		return $this->_items;
	}
	
	/*protected function getRepaymentConditions($table)
	{
		$condition = array();
		$condition[] = 'item_id = '.(int) $table->cat_id;
		return $condition;
	}*/

	
	public function getOffersItems(){
		if(empty($this->_countItems)) {
			$db= JFactory::getDBO();
			$query = "SELECT count(o.id) FROM #__djcf_offers o WHERE 1 ".$this->_buildWhere();
			$db->setQuery($query);
			$this->_countItems=$db->loadResult();
		}
		return $this->_countItems;
	}
	
	public function _getListQuery(){
		$query = "SELECT o.*, i.name as i_name, u.name as u_name, u.email as u_email, i.ui_name, i.ui_email FROM #__djcf_offers o "
				."LEFT JOIN (SELECT i.*, u.name as ui_name, u.email as ui_email FROM #__djcf_items i, #__users u 
							WHERE u.id=i.user_id ) i ON i.id=o.item_id "
				."LEFT JOIN #__users u ON u.id=o.user_id "
				." WHERE 1 ".$this->_buildWhere();	
			
		return $query;
	}


}
?>