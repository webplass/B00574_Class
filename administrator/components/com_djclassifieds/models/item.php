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

class DJClassifiedsModelItem extends JModelAdmin
{

	public function getTable($type = 'Items', $prefix = 'DJClassifiedsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		return $form;
	}
    function getItem($pk = null) {
        global $option;
        $row = JTable::getInstance('Items', 'DJClassifiedsTable');
		//print_r($row);die();
        $id = JRequest::getVar('id', '', '0', 'int');  
		if($id==0){
			$cid = JRequest::getVar('cid', array(0), '', 'array' );
  			$id = $cid[0];       	
		} 	      
        $row->load($id);
        return $row;
    }

    function getItemImages(){    
    	$id = JRequest::getInt('id', '0');
    	if($id==0){
    		$cid = JRequest::getVar('cid', array(0), '', 'array' );
    		$id = $cid[0];
    	}
    	$items_img = '';
    	if($id){
	    	$db = JFactory::getDBO();
	    	$query ="SELECT img.* FROM #__djcf_images img "
	    			."WHERE img.type='item' AND img.item_id=".$id." ORDER BY img.ordering";
	    	$db->setQuery($query);
	    	$items_img =$db->loadObjectList();
	    	//echo '<pre>'; print_r($db);print_r($items_img);die();
    	}
    	return $items_img;
    } 
 

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'cat_id = '.(int) $table->cat_id;
		return $condition;
	}
	/*
	function getMainRegions(){
			$id = JRequest::getVar('id', '', '0', 'int');
			$db= &JFactory::getDBO();
			$query = "SELECT r.id as value, r.name as text FROM #__djcf_regions r "
					." WHERE r.parent_id=0 ORDER BY r.name ";
	
			$db->setQuery($query);
			$allelems=$db->loadObjectList();
	
			return $allelems;
	}
	
	function getRegion($region_id){
			$db= &JFactory::getDBO();
			$query = "SELECT r.* FROM #__djcf_regions r "
					." WHERE r.id=".$region_id." LIMIT 1";
	
			$db->setQuery($query);
			$allelems=$db->loadObject();

			return $allelems;
	}
	
	function getCities($parent_id){
			$id = JRequest::getVar('id', '', '0', 'int');
			$db= &JFactory::getDBO();
			$query = "SELECT r.id as value, r.name as text FROM #__djcf_regions r "
					." WHERE r.parent_id=".$parent_id." ORDER BY r.name ";
	
			$db->setQuery($query);
			$allelems=$db->loadObjectList();
	
			return $allelems;
	}*/
	
	function getCategories(){
			$db= JFactory::getDBO();
			$query = "SELECT c.* FROM #__djcf_categories c "
					."ORDER BY c.parent_id, c.ordering ";
	
			$db->setQuery($query);
			$cats=$db->loadObjectList();
	
			return $cats;
	}
	
	function getRegions(){
			$db= JFactory::getDBO();
			$query = "SELECT r.* FROM #__djcf_regions r "
					."ORDER BY r.parent_id, r.name ";
	
			$db->setQuery($query);
			$regions=$db->loadObjectList();
	
			return $regions;
	}
	
	function getDutarions(){
		$db= JFactory::getDBO();
			$query = "SELECT d.* FROM #__djcf_days d "
					."ORDER BY d.days ";	
			$db->setQuery($query);
			$days=$db->loadObjectList();
	
			return $days;
	}
	
	function getPayment(){
			$id = JRequest::getVar('id', '0', '', 'int');
			if($id==0){
				$cid = JRequest::getVar('cid', array(0), '', 'array' );
				$id = $cid[0];
			}
			if($id>0){
				$db= JFactory::getDBO();
				$query = "SELECT p.* FROM #__djcf_payments p "
						."WHERE p.item_id = ".$id." LIMIT 1 ";
	
				$db->setQuery($query);
				$payment=$db->loadObject();
			}else{
				$payment='';
			}

			return $payment;
	}
	
	function getAbuseRaports(){
			$id = JRequest::getVar('id', '0', '', 'int');
			if($id==0){
				$cid = JRequest::getVar('cid', array(0), '', 'array' );
				$id = $cid[0];
			}
			if($id>0){
				$db= JFactory::getDBO();				
				$query = "SELECT COUNT(a.id) FROM #__djcf_items_abuse a "
						."WHERE a.item_id = ".$id." ";
	
				$db->setQuery($query);
				$abuse=$db->loadResult();			
			}else{
				$abuse=0;
			}

			return $abuse;
	}
	
	function getPromotions(){
			$db= JFactory::getDBO();
			$query = "SELECT p.*, '' as prices FROM #__djcf_promotions p "
					."ORDER BY p.ordering,p.id ";
	
			$db->setQuery($query);
			$promotions=$db->loadObjectList('id');
						
			$query = "SELECT p.* FROM #__djcf_promotions_prices p "
					."ORDER BY p.days ";
			$db->setQuery($query);
			$prom_prices=$db->loadObjectList();		
			
				foreach($prom_prices as $pp){
					if(isset($promotions[$pp->prom_id])){
						if(!is_array($promotions[$pp->prom_id]->prices)){
							$promotions[$pp->prom_id]->prices = array();
						}	
						$promotions[$pp->prom_id]->prices[$pp->days]=$pp;
					}
				}
			
			//echo '<pre>';print_r($promotions);die();			
	
			return $promotions;
	}		
	
	function getItemPromotions(){
		$id = JRequest::getVar('id', '0', '', 'int');
		if($id==0){
			$cid = JRequest::getVar('cid', array(0), '', 'array' );
			$id = $cid[0];
		}
		$promotions = '';
		if($id>0){
			$db= JFactory::getDBO();
			$query = "SELECT p.* FROM #__djcf_items_promotions p "
					."WHERE item_id=".$id;
	
					$db->setQuery($query);
					$promotions=$db->loadObjectList('prom_id');
					//echo '<pre>';print_r($promotions);die();
		}
		return $promotions;
	}	
	
	function getselUsers() {
    
        $doc = JFactory::getDocument();
        $version = new JVersion;
        
		    $id = JRequest::getVar('id', '0', '', 'int');
		    if($id==0){
		    	$cid = JRequest::getVar('cid', array(0), '', 'array' );
		    	$id = $cid[0];
		    }
			if($id>0){
				$db= JFactory::getDBO();	
				$query = "SELECT u.name, i.user_id FROM #__djcf_items i "
					."LEFT JOIN #__users u ON i.user_id=u.id "
					."WHERE i.id='".$id."' LIMIT 1";
			
				$db->setQuery($query);
				$us=$db->loadObject();
				$user_id =$us->user_id;
				$user_name =$us->name; 	
				$user_name_v = $us->name;
			}else{
				$user_id =0;
				$user_name  = JText::_('COM_DJCLASSIFIEDS_SELECT_USER');
				$user_name_v = '';
			}

			if (version_compare($version->getShortVersion(), '3.5', '<')) {
				$js = "function jSelectUser_jform_created_by(id, title) {
						var old_id = document.getElementById(\"jform_created_by_id\").value;
						if (old_id != id) {
							document.getElementById(\"jform_created_by_id\").value = id;
							document.getElementById(\"jform_created_by_name\").value = title;
							
						}
						SqueezeBox.close();
					}";
		        $doc->addScriptDeclaration($js);
		        $name = 'id';
		        $link = 'index.php?option=com_users&view=users&layout=modal&tmpl=component&field=jform_created_by';
		        
		        JHTML::_('behavior.modal', 'a.modal');
		        $html = '<div style="float: left;"><input style="background: #ffffff;" type="text" id="jform_created_by_name" size="20" value="'.$user_name.'" disabled="disabled" /></div>';
		        $html .= '<div class="button2-left"><div class="blank">';
				$html .='<a class="modal" title="'.JText::_('').'" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('Select').'</a>';
				$html .='</div></div>';
				$html .= '<input type="hidden" id="jform_created_by_id" name="user_id" value="'.($user_id).'" />';
		        $this->_selusers = $html;
			}else{				
				$doc->addScript(JURI::root()."/media/jui/js/fielduser.min.js");
				$js = "jQuery(document).ready(function($) {
					   $('#userModal_jform_created_by').on('show', function() {
					       var modalBodyHeight = $(window).height()-147;
					       $('.modal-body').css('max-height', modalBodyHeight);
					       $('body').addClass('modal-open');
					   }).on('hide', function () {															
					       $('body').removeClass('modal-open'); 
					   });
					});";
				$doc->addScriptDeclaration($js);
				
				$html = '<div data-button-select=".button-select" data-input-name=".field-user-input-name" data-input=".field-user-input" data-modal-height="400px" data-modal-width="100%" data-modal=".modal" data-url="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field={field-user-id}&amp;ismoo=0&amp;excluded=WyIiXQ==" class="field-user-wrapper">
							<div class="input-append">
								<input type="text" class="field-user-input-name " readonly="" placeholder="'.JText::_('JLIB_FORM_SELECT_USER').'" value="'.$user_name_v.'" id="jform_created_by">
								<a title="'.JText::_('JLIB_FORM_CHANGE_USER').'" class="btn btn-primary button-select"><span class="icon-user"></span></a>
								<div class="modal hide fade" tabindex="-1" id="userModal_jform_created_by" style="display: none;" aria-hidden="true">
									<div class="modal-header">
											<button data-dismiss="modal" class="close" type="button">×</button>
											<h3>'.JText::_('JLIB_FORM_CHANGE_USER').'</h3>
									</div>
									<div class="modal-body"></div><div class="modal-footer">
									<button data-dismiss="modal" class="btn">'.JText::_('JCANCEL').'</button></div>
								</div>
							</div>
							<input type="hidden" data-onchange="" class="field-user-input" value="'.$user_id.'" name="user_id" id="jform_created_by_id">
						</div>';
																	
				$this->_selusers = $html;
			}
        
        return $this->_selusers;
    }
    
    function getCustomContact(){
   		global $mainframe;

   		$id = JRequest::getVar('id', '0', '', 'int');
   		if($id==0){
   			$cid = JRequest::getVar('cid', array(0), '', 'array' );
   			$id = $cid[0];
   		}
    	$db = JFactory::getDBO();
    	$query ="SELECT f.*, v.value, v.value_date, v.value_date_to FROM #__djcf_fields f "
    			."LEFT JOIN (SELECT * FROM #__djcf_fields_values WHERE item_id=".$id.") v "
    					."ON v.field_id=f.id "
    			."WHERE f.source=1 AND f.published=1 ORDER BY f.ordering";
    	$db->setQuery($query);
    	$fields_list =$db->loadObjectList();
    	//echo '<pre>'; print_r($db);print_r($fields_list);die();
    	return $fields_list;
    }
    
    function getViewLevels(){
    	$db= JFactory::getDBO();
    	$query = "SELECT * FROM #__viewlevels "
    			."ORDER BY ordering";
    
    	$db->setQuery($query);
    	$view_levels=$db->loadObjectList();
    
    	return $view_levels;
    }
    
    function getBids(){
    	$id = JRequest::getInt('id', '0');
    	if($id==0){
    		$cid = JRequest::getVar('cid', array(0), '', 'array' );
    		$id = $cid[0];
    	}
    	$bids = '';
    	if($id){
    		$db= JFactory::getDBO();
    		$query = "SELECT a.*, u.name as u_name FROM #__djcf_auctions a "
    				."LEFT JOIN #__users u ON a.user_id=u.id "
    				."WHERE a.item_id=".$id." ORDER BY a.date DESC";
    		$db->setQuery($query);
    		$bids=$db->loadObjectList();
    		//echo '<pre>';print_r($bids);die();
    	}
    	return $bids;
    }
    
    function getBuyNow(){
    	$id = JRequest::getInt('id', '0');
    	if($id==0){
    		$cid = JRequest::getVar('cid', array(0), '', 'array' );
    		$id = $cid[0];
    	}
    	$orders = '';
    	if($id){
    		$db= JFactory::getDBO();
    		$query = "SELECT a.*, u.name as u_name FROM #__djcf_orders a "
    				."LEFT JOIN #__users u ON a.user_id=u.id "
    						."WHERE a.item_id=".$id." ORDER BY a.date DESC";
    		$db->setQuery($query);
    		$orders=$db->loadObjectList();
    		//echo '<pre>';print_r($orders);die();
    	}
    	return $orders;
    }    
    
    function getUnits(){
    	$db= JFactory::getDBO();
    	$query = "SELECT u.* FROM #__djcf_items_units u "
    			."ORDER BY u.ordering ";
    
    	$db->setQuery($query);
    	$units=$db->loadObjectList();
    
    	return $units;
    }    
	
/*
	
	public function delete(&$cid) {
		if (count( $cid ))
		{
			$cids = implode(',', $cid);
			
			$query = "SELECT image_url FROM #__djc2_items WHERE id IN ( ".$cids." )";
			$this->_db->setQuery($query);
			$rows = $this->_db->loadObjectList();
			foreach($rows as $row) {
				$field_controllerImage	= new DJCatalogImage();
				$field_controllerImage->prepareToDelete($row->image_url);
			}
		}	
		return parent::delete($cid);
	}*/
}