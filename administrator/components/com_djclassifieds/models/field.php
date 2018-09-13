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

jimport('joomla.application.component.modeladmin');

class DJClassifiedsModelField extends JModelAdmin
{

	public function getTable($type = 'Fields', $prefix = 'DJClassifiedsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		
		// Initialise variables.
		/*$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.item', 'item', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}*/

		return $form;
	}
    function getField() {
        global $option;
        $row = JTable::getInstance('Fields', 'DJClassifiedsTable');
		//print_r($row);die();
        $id = JRequest::getVar('id', '', '0', 'int');  
		if($id==0){
			$cid = JRequest::getVar('cid', array(0), '', 'array' );
  			$id = $cid[0];       	
		} 	      
        $row->load($id);
        return $row;
    }

   
    
    function getElement() {
            $id = JRequest::getVar('id', '', '', 'int');
            $query = "SELECT * FROM #__djcf_fields WHERE id='$id'";
            $el = $this->_getList($query, 0, 0);
        return $el[0];
    }
	

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'source = '.(int) $table->source;
		return $condition;
	}
	
	public static function getProfileFieldsSelect(){
	
		$db = JFactory::getDBO();
		$query ="SELECT * FROM #__djcf_fields WHERE source=2";
		$db->setQuery($query);
		$lists = $db->loadObjectList();
	
		$option = array();
		foreach($lists as $list){
			$op = new DJOptionList;
			$op->text = $list->name;;
			$op->value = $list->id;
			$option[]=$op;
		}
		return $option;
	}


	public static function getCategoriesOptions(){
	
		$cat_options = DJClassifiedsCategory::getCatSelect();
		$id = JRequest::getVar('id', '', '', 'int');
		if($id){
			$db = JFactory::getDBO();			
			$query = "SELECT cat_id, field_id FROM #__djcf_fields_xref WHERE field_id='$id' GROUP BY cat_id";
			$db->setQuery($query);
			$fields_cats = $db->loadObjectList('cat_id');
		}		
		
		$options = ''; 
		foreach($cat_options as $opt){
			if(isset($fields_cats[$opt->value])){
				$options .= '<option style="font-weight:bold;" value="'.$opt->value.'">'.$opt->text.'*</option>';
			}else{
				$options .= '<option value="'.$opt->value.'">'.$opt->text.'</option>';
			}						
		}
		return $options;
	}
	
	public function getFormGroups($source) {
	
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id as value, name as text')->from('#__djcf_fields_groups')->where('source='.$db->quote($source))->order('ordering asc');
		$db->setQuery($query);
		$groups = $db->loadObjectList();
	
		return $groups;
	
	}

}