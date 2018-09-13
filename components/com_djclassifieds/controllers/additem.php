<?php
/**
* @version 2.0
* @package DJ Classifieds
* @subpackage DJ Classifieds Component
* @copyright Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer Ĺ�ukasz Ciastek - lukasz.ciastek@design-joomla.eu
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


class DJClassifiedsControllerAddItem extends JControllerLegacy {
	
	
	function captcha(){
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$app		= JFactory::getApplication();
		$token 		= JRequest::getCMD('token', '' );
		$token_link = ($token ? '&token='.$token : '');
		$subscr_id 		= JRequest::getCMD('subscr_id', '' );
		$subscr_link = ($subscr_id ? '&subscr_id='.$subscr_id : '');
			
		
		if($par->get('captcha_type','recaptcha')=='nocaptcha'){
			require_once(JPATH_COMPONENT.DS.'assets'.DS.'nocaptchalib.php');
		}else{
			require_once(JPATH_COMPONENT.DS.'assets'.DS.'recaptchalib.php');
		}
						
		$privatekey = $par->get('captcha_privatekey',"6LfzhgkAAAAAAOJNzAjPz3vXlX-Bw0l-sqDgipgs");
		$is_valid = false;
		
			if($par->get('captcha_type','recaptcha')=='nocaptcha'){
				$response = null;
				$reCaptcha = new ReCaptcha($privatekey);
				if ($_POST["g-recaptcha-response"]) {
					$response = $reCaptcha->verifyResponse(
							$_SERVER["REMOTE_ADDR"],
							$_POST["g-recaptcha-response"]
					);
					if ($response != null && $response->success) {
						$is_valid = true;
					}
				}
			}else{		
			  $resp = recaptcha_check_answer ($privatekey,
	                                  $_SERVER["REMOTE_ADDR"],
	                                  $_POST["recaptcha_challenge_field"],
	                                  $_POST["recaptcha_response_field"]);
			  $is_valid = $resp->is_valid;
			}
			
			if ($is_valid) {
				$session = &JFactory::getSession();		
				$session->set('captcha_sta','1');				
				$message = '';	
			}else {								
				$message = JText::_("COM_DJCLASSIFIEDS_INVALID_CODE");			
			}
		  $menus = $app->getMenu();
		  $menu_newad_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=additem',1);
		  $new_ad_link='index.php?option=com_djclassifieds&view=additem';
		    if($menu_newad_itemid){
				$new_ad_link .= '&Itemid='.$menu_newad_itemid->id;
		    }		  	
			$new_ad_link = JRoute::_($new_ad_link.$token_link.$subscr_link,false);
			$app->redirect($new_ad_link,$message,'error');	
	}	
	
	
	
	public function getCities(){
		 $region_id = JRequest::getVar('r_id', '0', '', 'int');
	     
	     $db = & JFactory::getDBO();
	     $query ="SELECT r.name as text, r.id as value "
	     		."FROM #__djcf_regions r WHERE r.parent_id = ".$region_id." ORDER BY name";			
	     $db->setQuery($query);
		 $cities =$db->loadObjectList();
		 
		 echo '<select name="city" class="inputbox" >';
		 echo '<option value="">'.JText::_('COM_DJCLASSIFIEDS_SELECT_CITY').'</option>';
		    echo JHtml::_('select.options', $cities, 'value', 'text', '');
		 echo '</select>';
		 die();
	}
	public function getFields(){

		global $mainframe;
		header("Content-type: text/html; charset=utf-8");
	     $cid	= JRequest::getVar('cat_id', '0', '', 'int');
	     $mcat_ids	= JRequest::getVar('mcat_ids', '');
		 $id 	= JRequest::getInt('id', '0','post');
		 $id_copy 	= JRequest::getInt('id_copy', '0','post');
		 $token = JRequest::getCMD('token', '' );
		 $par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		 $db 	= JFactory::getDBO();
		 $user 	= JFactory::getUser();
		 
			 if($id==0){
			 	$id=$id_copy;
			 }		 	
		 
			 if($user->id==0){
			 	$id=0;
			 }
	     
		 $item='';
		 if($id>0){
		 	$query = "SELECT * FROM #__djcf_items WHERE id='".$id."' LIMIT 1";
		 	$db->setQuery($query);
		 	$item =$db->loadObject();	
			if($item->user_id!=$user->id){
				if($par->get('admin_can_edit_delete','0')==0 || !$user->authorise('core.admin')){
					$id=0;
				}
			}
		 }else if($token){
		 	$query = "SELECT * FROM #__djcf_items WHERE token='".addslashes($token)."' AND user_id=0 LIMIT 1";
		 	$db->setQuery($query);
		 	$item =$db->loadObject();	
			if($item){
				$id=$item->id;
			}
		 }
		 
		 $mcats_list = '';
		 if($mcat_ids){
		 	$mcats = explode(',', $mcat_ids);		 	
		 	foreach($mcats as $mcat){
		 		$mc = intval(str_ireplace('p', '', $mcat));
		 		if($mc>0){
		 			$mcats_list .= $mc.',';
		 		}		 		
		 	}		 	
		 }
		 
		 if($mcats_list){
		 	$mcats_list .= $cid;
		 	$cat_where = ' IN ('.$mcats_list.')';
		 }else{
		 	
		 	$cat_where = ' = '.$cid.' ';
		 }
		  
		 
	     $query ="SELECT f.*, v.value, v.value_date, v.value_date_to, fx.ordering FROM #__djcf_fields f, #__djcf_fields_xref fx "
		 		."LEFT JOIN (SELECT * FROM #__djcf_fields_values WHERE item_id=".$id.") v "
				."ON v.field_id=fx.field_id "
		 		."WHERE f.id=fx.field_id AND fx.cat_id ".$cat_where." AND f.published=1 AND f.edition_blocked=0 GROUP BY fx.field_id ORDER BY fx.cat_id, fx.ordering ";
	     $db->setQuery($query);
		 $fields_list =$db->loadObjectList();
		 //echo '<pre>'; print_r($db);print_r($fields_list);die(); 
		 
		 
		 if(count($fields_list)==0){
		 	die();
		 }else{
		 		//echo '<pre>';	print_r($fields_list);echo '</pre>';						 	
		 	foreach($fields_list as $fl){		 		
				if($id>0 && $fl->value==''){
					if($fl->name=='price'){
						$fl->value = $item->price; 
					}else if($fl->name=='contact'){
						$fl->value = $item->contact;
					}
				}
				if($fl->name=='price' && $par->get('show_price','1')!=2){
					continue;
				}else if($fl->name=='contact' && $par->get('show_contact','1')!=2){
					continue;
				}
				
				if($fl->profile_source && $user->id && $id==0){				
					$query ="SELECT value FROM #__djcf_fields_values_profile WHERE field_id=".$fl->profile_source." AND user_id=".$user->id;
					$db->setQuery($query);
					$profile_value =$db->loadResult();
					$fl->default_value = $profile_value;
				}
				
				echo '<div class="djform_row djrow_'.$fl->name.'">';
				if($fl->type=="inputbox"){
						if($id>0){
							$fl_value = $fl->value; 	
						}else{
							$fl_value = $fl->default_value;
						}	
						$fl_value = htmlspecialchars($fl_value);								
						
						$cl_price='';
						if($fl->name=='price'){
							if($par->get('price_only_numbers','0')){
								$cl_price=' validate-numeric';
							}
						}else if($fl->numbers_only){
							$cl_price=' validate-numeric';
						}
						
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required'.$cl_price.'" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox'.$cl_price.'"';
						}												
						
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
						} 
						
						echo '<div class="djform_field">';															

						echo '<input '.$cl.' type="text" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params; 
						echo ' value="'.$fl_value.'" '; 	
						echo ' />';					
				}else if($fl->type=="textarea"){
						if($id>0){
							$fl_value = $fl->value; 	
						}else{
							$fl_value = $fl->default_value;
						}
						$fl_value = htmlspecialchars($fl_value);						
						
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox"';
						}
						
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
						}
						echo '<div class="djform_field">';
						echo '<textarea '.$cl.' id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params.' />'; 
						echo $fl_value; 	
						echo '</textarea>';					
				}else if($fl->type=="selectlist"){
						if($id>0){
							$fl_value=$fl->value; 	
						}else{
							$fl_value=$fl->default_value;
						}
			
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox"';
						}
					
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{		
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
						}
						echo '<div class="djform_field">';						
						echo '<select '.$cl.' id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params.' >';
							if(substr($fl->values, -1)==';'){
								$fl->values = substr($fl->values, 0,-1);
							}
							$val = explode(';', $fl->values);
							for($i=0;$i<count($val);$i++){
								if($fl_value==$val[$i]){
									$sel="selected";
								}else{
									$sel="";
								}
								echo '<option '.$sel.' value="'.$val[$i].'">';
									if($par->get('cf_values_to_labels','0')){
										echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($val[$i])));
									}else{
										echo $val[$i];	
									}								
								echo '</option>';
							}
							
						echo '</select>';					
				}else if($fl->type=="radio"){				
						if($id>0){
							$fl_value=$fl->value; 	
						}else{
							$fl_value=$fl->default_value;
						}
			
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="required validate-radio" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class=""';
						}
						
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{								
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
						}
						echo '<div class="djform_field">';		
							if(substr($fl->values, -1)==';'){
								$fl->values = substr($fl->values, 0,-1);
							}
						$val = explode(';', $fl->values);
						echo '<div class="radiofield_box" style="float:left">';
							for($i=0;$i<count($val);$i++){
								$checked = '';
									if($fl_value == $val[$i]){
										$checked = 'CHECKED';
									}									 	
								
								echo '<div style="float:left;"><input type="radio" '.$cl.'  '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'" id="dj'.$fl->name.$i.'" />';
									echo '<label for="dj'.$fl->name.$i.'" class="radio_label dj'.$fl->name.$i.'">';
										if($par->get('cf_values_to_labels','0')){
											echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($val[$i])));
										}else{
											echo $val[$i];
										}
									echo '</label>';
								echo '</div>';
								echo '<div class="clear_both"></div>';
							}	
						echo '</div>';	
				}else if($fl->type=="checkbox"){					
						$val_class='';
						$req = '';
						if($id>0){
							$fl_value = $fl->value;
						}else{
							$fl_value = $fl->default_value;
						}
						
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="checkboxes required" '.$val_class.' ';
							$req = ' * ';
						}else{
							$cl = 'class=""';
						}
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{		
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
						}
						echo '<div class="djform_field">';	
							if(substr($fl->values, -1)==';'){
								$fl->values = substr($fl->values, 0,-1);
							}							
						$val = explode(';', $fl->values);
						echo '<div class="radiofield_box" style="float:left">';
						echo '<fieldset id="dj'.$fl->name.'" '.$cl.' >';
							for($i=0;$i<count($val);$i++){
								$checked = '';
								if($id>0){									
									if(strstr($fl->value,';'.$val[$i].';' )){
										$checked = 'CHECKED';
									}									 	
								}else{
									$def_val = explode(';', $fl->default_value);
									for($d=0;$d<count($def_val);$d++){
										if($def_val[$d] == $val[$i]){
											$checked = 'CHECKED';
										}											
									}
					
								}
								
								echo '<div style="float:left;"><input type="checkbox" id="dj'.$fl->name.$i.'" class="checkbox" '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'[]" />';
									echo '<label for="dj'.$fl->name.$i.'" class="radio_label dj'.$fl->name.$i.'">';
										if($par->get('cf_values_to_labels','0')){
											echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($val[$i])));
										}else{
											echo $val[$i];	
										}
									echo '</label>';									
								echo '</div>';
								echo '<div class="clear_both"></div>';
							}
						echo '</fieldset>';		
						echo '</div>';	
				}else if($fl->type=="date"){
					
					
						if($id>0){
							$fl_value = $fl->value_date; 	
						}else{
							if($fl->default_value=='current_date'){
								$fl_value = date("Y-m-d");
							}else{
								$fl_value = $fl->default_value;	
							}
						}						
						
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required djcalendar" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox djcalendar"';
						}
						
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
						} 
						
						echo '<div class="djform_field">';															

						echo '<input '.$cl.' type="text" size="10" maxlenght="19" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params; 
						echo ' value="'.$fl_value.'" '; 	
						echo ' />';	
						echo ' <img class="calendar" src="'.JURI::base().'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="dj'.$fl->name.'button" />';
						
											
				}else if($fl->type=="date_from_to"){
					
					
						if($id>0){
							$fl_value = $fl->value_date;
							$fl_value_to = $fl->value_date_to;
						}else{
							if($fl->default_value=='current_date'){
								$fl_value = date("Y-m-d");
								$fl_value_to = date("Y-m-d");
							}else{
								$fl_value = $fl->default_value;
								$fl_value_to = $fl->default_value;
							}
						}						
						
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required djcalendar" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox djcalendar"';
						}
						
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
						} 
						
						echo '<div class="djform_field">';															

						echo '<input '.$cl.' type="text" size="10" maxlenght="19" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params; 
						echo ' value="'.$fl_value.'" '; 	
						echo ' />';	
						echo ' <img class="calendar" src="'.JURI::base().'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="dj'.$fl->name.'button" />';
						
						echo '<span class="date_from_to_sep"> - </span>';

						echo '<input '.$cl.' type="text" size="10" maxlenght="19" id="dj'.$fl->name.'_to" name="'.$fl->name.'_to" '.$fl->params;
						echo ' value="'.$fl_value_to.'" ';
						echo ' />';
						echo ' <img class="calendar" src="'.JURI::base().'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="dj'.$fl->name.'_tobutton" />';
						
											
				}else if($fl->type=="link"){
						if($id>0){
							$fl_value = $fl->value; 	
						}else{
							$fl_value = $fl->default_value;
						}	
						$fl_value = htmlspecialchars($fl_value);								
						
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox"';
						}												
						
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
						} 
						
						echo '<div class="djform_field">';															

						echo '<input '.$cl.' type="text" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params; 
						echo ' value="'.$fl_value.'" '; 	
						echo ' />';					
				}else if($fl->type=="date_min_max"){
					
						$val_class='';
						$req = '';
						if($fl->required){
							if($fl_value==''){
								$val_class=' aria-required="true" ';			
							}else{
								$val_class=' aria-invalid="false" ';
							}
							$cl = 'class="inputbox required" '.$val_class.' required="required"';
							$req = ' * ';
						}else{
							$cl = 'class="inputbox"';
						}
					
						if($par->get('show_tooltips_newad','0') && $fl->description){
							echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
								echo ' <img src="'.JURI::base().'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
							echo '</label>';	
						}else{		
							echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
						}
						echo '<div class="djform_field date">';
						echo '<div data-use-time="'.$fl->date_use_time.'" class="input-group datetimepicker-container datetimepicker-start-container" style="position:relative"><input '.$cl.' type="text" data-start="'.$fl->value_date_start.'" id="'.$fl->name.'_start" name="'.$fl->name.'_start" placeholder="'.JText::_('COM_DJCLASSIFIEDS_DATE_START').'"/><span class="input-group-addon"><span class="icon-calendar"></span></span></div>';
						if(!$fl->date_start_only){
							echo '<div data-use-time="'.$fl->date_use_time.'" class="input-group datetimepicker-container datetimepicker-end-container" style="position:relative"><input type="text" data-end="'.$fl->value_date_end.'" id="'.$fl->name.'_end" name="'.$fl->name.'_end" placeholder="'.JText::_('COM_DJCLASSIFIEDS_DATE_END').'"/><span class="input-group-addon"><span class="icon-calendar"></span></span></div>';
						}
						if($fl->date_all_day){
							echo '<input value="value1" '.($fl->all_day ? ' checked' : '').' type="checkbox" class="chx-datetimepicker" id="chx_'.$fl->name.'" name="'.$fl->name.'_all_day"><label for="chx_'.$fl->name.'">'.JText::_('COM_DJCLASSIFIEDS_DATE_ALL_DAY').'</label>';
						}
				}
				
				if($fl->name=='price'){
					if($par->get('show_price','1')==2){
						if($par->get('unit_price_list','')){
	                     	$c_list = explode(';', $par->get('unit_price_list',''));
							 echo '<select class="price_currency" style="margin-left:5px;" name="currency">';
							 for($cl=0;$cl<count($c_list);$cl++){
							 	if($c_list[$cl]==$item->currency){
							 		$csel=' SELECTED ';
							 	}else{
							 		$csel='';
								}
							 	echo '<option '.$csel.' name="'.$c_list[$cl].' ">'.$c_list[$cl].'</option>';
							 }
							 echo '</select>';
	                     	
	                     }else{
	                     	echo ' '.$par->get('unit_price','EUR');
							echo '<input type="hidden" name="currency" value="" >';
	                     }
						 
						 if($par->get('show_price_negotiable','0')){ 
                     	 	echo '<div class="price_neg_box">';
                     			echo '<input type="checkbox" autocomplete="off" name="price_negotiable" value="1" ';
									if($id>0){
                     					if($item->price_negotiable){ echo 'checked="CHECKED"';}
                     				} 
                     			echo '/>';
                     			echo '<span>'.JText::_('COM_DJCLASSIFIEDS_PRICE_NEGOTIABLE').'</span>';
                     	    echo '</div>';
                         }else{ 
                     		echo '<input type="hidden" name="price_negotiable" value="0" />';
                        } 
						
					}	
				}
				
				echo '</div><div class="clear_both"></div>';			
				echo '</div>';	
		 	}		 				
		 	die();
	 	}	
	}	
	

	public function getBuynowOptions(){
		global $mainframe;
		header("Content-type: text/html; charset=utf-8");
		$cid = JRequest::getVar('cat_id', '0', '', 'int');
		$id = JRequest::getVar('id', '0', '', 'int');
	
		if(!$id){die();}
	
		$db = JFactory::getDBO();
		$query ="SELECT f.* FROM #__djcf_fields_values_sale f "
				."WHERE f.item_id =".$id." ORDER BY f.id";
		$db->setQuery($query);
		$fields_list =$db->loadObjectList();
		//echo '<pre>'; print_r($db);print_r($fields_list);die();
			
			
		if(count($fields_list)){
			//echo '<pre>';	print_r($fields_list);echo '</pre>';
			$c_time = time();
				
			foreach($fields_list as $fl){
				echo '<div class="bn-option-outer" id="bn-field_box'.$fl->id.'-'.$c_time.'">';
				$options = json_decode($fl->options);
				foreach($options as $opt){
					echo '<div class="bn_field_outer">';
					echo '<span class="label">'.$opt->label.'</span>';
					echo '<input type="text" class="inputbox" name="bn-'.$opt->name.'[]" value="'.$opt->value.'" />';
					echo '</div>';
				}
				echo '<div class="bn_field_outer bn_quantity">';
				echo '<span class="label">'.JText::_('COM_DJCLASSIFIEDS_QUANTITY').'</span>';
				echo '<input class="inputbox" type="text" value="'.$fl->quantity.'" name="bn-quantity[]" />';
				echo '</div>';
				echo '<span class="button" onclick="deleteBuynowField(\'bn-field_box'.$fl->id.'-'.$c_time.'\')" >'.JText::_('COM_DJCLASSIFIEDS_DELETE').'</span>';
				echo '<div style="clear:both"></div></div>';
			}
				
		}
		die();
	}
	
	
	
	public function getBuynowFields(){
		global $mainframe;
		header("Content-type: text/html; charset=utf-8");
		$cid = JRequest::getVar('cat_id', '0', '', 'int');
		$id = JRequest::getVar('id', '0', '', 'int');
		$type=JRequest::getInt('type',1);
		// echo $id;
		$db = JFactory::getDBO();
		$query ="SELECT f.*, fx.ordering FROM #__djcf_fields f, #__djcf_fields_xref fx "
				."WHERE f.id=fx.field_id AND fx.cat_id  = ".$cid." AND f.published=1 AND f.in_buynow ORDER BY f.label";
		$db->setQuery($query);
		$fields_list =$db->loadObjectList();
		//echo '<pre>'; print_r($db);print_r($fields_list);die();
			
			
		if(count($fields_list)){
			//echo '<pre>';	print_r($fields_list);echo '</pre>';
			$c_time = time();
			echo '<div class="bn-option-outer" id="bn-field_box'.$type.'-'.$c_time.'">';
			foreach($fields_list as $fl){
				echo '<div class="bn_field_outer">';
				echo '<span class="label">'.$fl->label.'</span>';
				if($type==1){
					echo '<select class="inputbox" name="bn-'.$fl->name.'[]" '.$fl->params.' >';
					if(substr($fl->buynow_values, 0,1)!=';'){
						$fl->buynow_values = ';'.$fl->buynow_values;
					}
					$val = explode(';', $fl->buynow_values);
					$def_value=$fl->default_value;
						
					for($i=0;$i<count($val);$i++){
						if($def_value==$val[$i]){
							$sel="selected";
						}else{
							$sel="";
						}
						echo '<option '.$sel.' value="'.$val[$i].'">'.$val[$i].'</option>';
					}
					echo '</select>';
				}else{
					echo '<input type="text" class="inputbox" name="bn-'.$fl->name.'[]" value="" />';
				}
				echo '</div>';
			}
			echo '<div class="bn_field_outer bn_quantity">';
			echo '<span class="label">'.JText::_('COM_DJCLASSIFIEDS_QUANTITY').'</span>';
			echo '<input class="inputbox" type="text" value="0" name="bn-quantity[]" />';
			echo '</div>';
			echo '<span class="button" onclick="deleteBuynowField(\'bn-field_box'.$type.'-'.$c_time.'\')" >'.JText::_('COM_DJCLASSIFIEDS_DELETE').'</span>';
			echo '<div style="clear:both"></div></div>';
		}
		die();
	}
	
	
	public function getRegionSelect(){
	
		header("Content-type: text/html; charset=utf-8");
		$id 	= JRequest::getInt('reg_id', '0');
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();	
		
		if($id>0){
			//$query = "SELECT * FROM #__djcf_regions WHERE parent_id='".$id."' AND published=1 ORDER BY name ";
			$query = "SELECT * FROM #__djcf_regions WHERE parent_id='".$id."' AND published=1 ORDER BY name COLLATE utf8_polish_ci";
			$db->setQuery($query);
			$regions =$db->loadObjectList();
			if($regions){													
					echo '<div class="clear_both"></div>';
						echo '<select style="width:210px" name="regions[]" id="reg_'.$id.'" onchange="new_reg('.$id.',this.value,new Array());">';
							echo '<option value="">'.JTEXT::_('COM_DJCLASSIFIEDS_LOCATION_SELECTOR_EMPTY_VALUE').'</option>';					
							foreach($regions as $region){		
								echo '<option value="'.$region->id.'">'.str_ireplace("'", "&apos;", $region->name).'</option>';							
							}
						echo '</select>';
				echo "<div id=\"after_reg_$id\"></div>";
			}
		}
		
		die();
	}
	
	public function getCategorySelect(){
	
		header("Content-type: text/html; charset=utf-8");
		if(strpos(JRequest::getVar('cat_id', ''), 'p') !== false){ die(); }
		$id 	= JRequest::getInt('cat_id', '0');
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$unit_price = $par->get('unit_price','');
		$points_a = $par->get('points',0);
		$mc = JRequest::getVar('mc', '');
		$subscr_id = JRequest::getInt('subscr_id', '');
	 
		$show_paid = 1;
		$show_price = 1;
		
		if($id>0){
			
			
			if($subscr_id){
				$query = "SELECT * FROM #__djcf_plans_subscr p "
						."WHERE user_id=".$user->id." AND id=".$subscr_id;
			
						$db->setQuery($query);
						$plan=$db->loadObject();
						//echo '<pre>';print_r($cat);die();
						if($plan){														
							$registry = new JRegistry();
							$registry->loadString($plan->plan_params);
							$plan_params = $registry->toObject();
							if($plan_params->pay_categories){
								$show_paid = 1;
								$show_price = 0;
							}else{
								$show_paid = 0;
								$show_price = 0;
							}
						}
			}
			
			
			
			$query = "SELECT * FROM #__djcf_categories WHERE id='".$id."' AND published=1 ";
			$db->setQuery($query);
			$parent_cat =$db->loadObject();
			
				
			$lj = '';
			$ls = '';						
			$g_list = '0';
			if($user->groups){
				$g_list = implode(',',$user->groups);	
			}									
			if (!$g_list){
				$g_list = '0';
			}
			if($user->id){
				$ls=',g.g_active';
				$lj="LEFT JOIN (SELECT COUNT(id) as g_active, cat_id FROM #__djcf_categories_groups " 
				   ."WHERE group_id in(".$g_list.") GROUP BY cat_id ) g ON g.cat_id=c.id ";
				$lj_where = ' AND (c.access=0 OR (c.access=1 AND g.g_active>0 ))';
			}else{
				$lj_where = ' AND c.access=0 ';	
			}
			
			$query = "SELECT c.* FROM #__djcf_categories c "
					.$lj
					." WHERE c.parent_id='".$id."' AND c.published=1 ".$lj_where
					."ORDER by c.ordering ";
			$db->setQuery($query);
			$cats =$db->loadObjectList();
			
			if($cats){
					$cl_select = '';
					if($parent_cat->ads_disabled){
						$cl_select = ' class="cat_sel required validate-djcat" ';						
					}
				echo '<div class="clear_both"></div>';
				if($mc!=''){
					echo '<select '.$cl_select.' style="width:210px" name="mcats'.$mc.'[]" id="mcat'.$mc.'_'.$id.'" onchange="new_mcat('.$id.',this.value,new Array(),'.$mc.');getFields(this.value, true);">';
				}else{
					echo '<select '.$cl_select.' style="width:210px" name="cats[]" id="cat_'.$id.'" onchange="new_cat('.$id.',this.value,new Array());getFields(this.value,false);">';
				}
				
			    echo '<option value="p'.$id.'">'.JTEXT::_('COM_DJCLASSIFIEDS_CATEGORY_SELECTOR_EMPTY_VALUE').'</option>';
				foreach($cats as $cat){
					if($cat->price>0){
						if($show_paid==0){
							continue;
						}
						if($show_price==1){
							$cat->price = $cat->price/100;
							$cat->name .= ' (';
							if($points_a!=2){
								$cat->name .= DJClassifiedsTheme::priceFormat($cat->price,$unit_price);
							}
							if($cat->points>0 && $points_a){
								if($points_a!=2){
									$cat->name .= ' - ';
								}
								$cat->name .= $cat->points.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
							}
							if($cat->price_special>0){
								$cat->name .= ' - '.DJClassifiedsTheme::priceFormat($cat->price_special,$unit_price).' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
							}
							$cat->name .= ')';
						}
					}
					echo '<option value="'.$cat->id.'">'.str_ireplace("'", "&apos;", $cat->name).'</option>';
				}
				echo '</select>';
				if($mc!=''){
					echo "<div id=\"after_mcat".$mc."_$id\"></div>";
				}else{
					echo "<div id=\"after_cat_$id\"></div>";
				}
			}
		}
	
		die();
	}	
	
	public function getCategories(){
	
		header("Content-type: text/html; charset=utf-8");
		$id 	= JRequest::getInt('reg_id', '0');
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
	
		if($id>0){
			$query = "SELECT * FROM #__djcf_regions WHERE parent_id='".$id."' AND published=1 ORDER by name ";
			$db->setQuery($query);
			$regions =$db->loadObjectList();
			if($regions){
				echo '<div class="clear_both"></div>';
				echo '<select style="width:210px" name="regions[]" id="reg_'.$id.'" onchange="new_reg('.$id.',this.value,new Array());">';
				echo '<option value="">'.JTEXT::_('COM_DJCLASSIFIEDS_LOCATION_SELECTOR_EMPTY_VALUE').'</option>';
				foreach($regions as $region){
					echo '<option value="'.$region->id.'">'.str_ireplace("'", "&apos;", $region->name).'</option>';
				}
				echo '</select>';
				echo "<div id=\"after_reg_$id\"></div>";
			}
		}
	
		die();
	}	
	
	public function checkEmail(){
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 		= JFactory::getDBO();
		$email 		= $db->Quote($db->escape(JRequest::getVar('email','','','string'), true));
		$email_v 	= JRequest::getVar('email','','','string');
		
		$query ="SELECT count(u.id) FROM #__users u WHERE u.email=".$email." ";
		$db->setQuery($query);
		$u_exist =$db->loadResult();
		if($u_exist){
			echo JText::_('COM_DJCLASSIFIEDS_EMAIL_EXIST_IN_OUR_DATABASE_PLEASE_LOGIN');
		}else if($par->get('adverts_limit','0')){
			$query ="SELECT count(i.id) FROM #__djcf_items i WHERE i.email=".$email." ";
			$db->setQuery($query);
			$ads_l =$db->loadResult();
			if($ads_l>=$par->get('adverts_limit','0')){
				echo JText::_('COM_DJCLASSIFIEDS_ADVERTS_LIMIT_REACHED_FOR_THIS_EMAIL');
			}
		}else if(str_replace("'","",$email) && !filter_var(str_replace("'","",$email_v),FILTER_VALIDATE_EMAIL)){
			echo JText::_('COM_DJCLASSIFIEDS_EMAIL_ADDRESS_NOT_VALID');
		}
		die();
	}
	
	
	function save(){
		$app = JFactory::getApplication();
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');		
		jimport( 'joomla.database.table' );
		JPluginHelper::importPlugin('djclassifieds');

    	$row = JTable::getInstance('Items', 'DJClassifiedsTable');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$dispatcher = JDispatcher::getInstance();		
				
		$db = JFactory::getDBO();
		$id = JRequest::getVar('id', 0, '', 'int' );
		$token 	= JRequest::getCMD('token', '');
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
		

		if($user->id==0 && $id>0){		 	
			$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			//$redirect="index.php?option=com_djclassifieds&view=items&cid=0".$itemid;
			$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');
			$redirect = JRoute::_($redirect,false);
			$app->redirect($redirect, $message,'error');			
		}
		
	     $db = JFactory::getDBO();
		 if($id>0){
		 	$query = "SELECT user_id FROM #__djcf_items WHERE id='".$id."' LIMIT 1";
		 	$db->setQuery($query);
		 	$item_user_id =$db->loadResult();	
		 	
		 	$wrong_ad = 0;
		 		
		 	if($item_user_id!=$user->id){
		 		$wrong_ad = 1;
		 		if($user->id && $par->get('admin_can_edit_delete','0') && $user->authorise('core.admin')){
		 			$wrong_ad = 0;
		 		}
		 	}
		 	
		 	
			if($wrong_ad){
				$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
				$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');				
				$redirect = JRoute::_($redirect,false);
				$app->redirect($redirect, $message,'error');
			}
		 }
		
		if($par->get('user_type')==1 && $user->id=='0'){
			//$uri = "index.php?option=com_djclassifieds&view=items&cid=0".$itemid;
			$uri=DJClassifiedsSEO::getCategoryRoute('0:all');
			$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
			$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
		}		
		
		$row->bind(JRequest::get('post'));
		
		if($token && !$user->id && !$id){
			$query = "SELECT i.id FROM #__djcf_items i "
					."WHERE i.user_id=0 AND i.token=".$db->Quote($db->escape($token));
			$db->setQuery($query);
			$ad_id=$db->loadResult();
			if($ad_id){
				$row->id = $ad_id;
			}else{
				$uri=DJClassifiedsSEO::getCategoryRoute('0:all');
				$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
				$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));				
			}
		}

		$dispatcher->trigger('onAfterInitialiseDJClassifiedsSaveAdvert', array(&$row,&$par));
		
		if($par->get('title_char_limit','0')>0){
			$row->name = mb_substr($row->name, 0,$par->get('title_char_limit','100'),"UTF-8");
		}
			
		if((int)$par->get('allow_htmltags','0')){
			$row->description = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
			
			$allowed_tags = explode(';', $par->get('allowed_htmltags',''));
			$a_tags = '';
			for($a = 0;$a<count($allowed_tags);$a++){				
				$a_tags .= '<'.$allowed_tags[$a].'>';	
			}
			
			$row->description = strip_tags($row->description, $a_tags);
		}else{
			$row->description = nl2br(JRequest::getVar('description', '', 'post', 'string'));
		}
		

		$row->intro_desc = mb_substr(strip_tags(nl2br($row->intro_desc)), 0,$par->get('introdesc_char_limit','120'),"UTF-8"); 
		if(!$row->intro_desc){
			$row->intro_desc = mb_substr(strip_tags($row->description), 0,$par->get('introdesc_char_limit','120'),"UTF-8");
		}
		
		
		$row->contact = nl2br(JRequest::getVar('contact', '', 'post', 'string'));
		$row->price_negotiable = JRequest::getInt('price_negotiable', '0');
		$row->bid_min = str_ireplace(',','.',JRequest::getVar('bid_min', '', 'post', 'string'));
		$row->bid_max = str_ireplace(',','.', JRequest::getVar('bid_max', '', 'post', 'string'));
		$row->price_reserve = str_ireplace(',','.', JRequest::getVar('price_reserve', '', 'post', 'string'));
		
		
		if(!$id && !$token && !$user->id && ($par->get('guest_can_edit',0) || $par->get('guest_can_delete',0))){			
			$characters = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			$row->token = '';							
			for ($p = 0; $p < 20; $p++) {
				$row->token .= $characters[mt_rand(0, strlen($characters))];
			}				
		}
   /*
	//removing images from folder and from database
	$path = JPATH_BASE."/components/com_djclassifieds/images/";
    $images = $row->image_url;
		if(isset($_POST['del_img'])){			
			$del_image = $_POST['del_img'];	
		}else{
			$del_image = array();
		}    
    

    for ($i = 0; $i < count($del_image); $i++){

        $images = str_replace($del_image[$i].';', '', $images);
        //deleting the main image
        if (JFile::exists($path.$del_image[$i])){
            JFile::delete($path.$del_image[$i]);
        }
        //deleting thumbnail of image
		if (JFile::exists($path.$del_image[$i].'.thb.jpg')){
            JFile::delete($path.$del_image[$i].'.thb.jpg');
        }
        if (JFile::exists($path.$del_image[$i].'.th.jpg')){
            JFile::delete($path.$del_image[$i].'.th.jpg');
        }
		if (JFile::exists($path.$del_image[$i].'.thm.jpg')){
            JFile::delete($path.$del_image[$i].'.thm.jpg');
        }
        if (JFile::exists($path.$del_image[$i].'.ths.jpg')){
            JFile::delete($path.$del_image[$i].'.ths.jpg');
        }
    }

 
    //add images
    $new_files = $_FILES['image'];
    if(count($new_files['name'])>0 && $row->id==0){			
		$query = "SELECT id FROM #__djcf_items ORDER BY id DESC LIMIT 1";
		$db->setQuery($query);
		$last_id =$db->loadResult();
		$last_id++;
	}else{
		$last_id= $row->id;
	}
	
	$nw = (int)$par->get('th_width',-1);
    $nh = (int)$par->get('th_height',-1);
	$nws = $par->get('smallth_width',-1);
    $nhs = $par->get('smallth_height',-1);
	$nwm = $par->get('middleth_width',-1);
    $nhm = $par->get('middleth_height',-1);			
	$nwb = $par->get('bigth_width',-1);
    $nhb = $par->get('bigth_height',-1);		
	$img_maxsize = $par->get('img_maxsize',0);		
		if($img_maxsize>0){
			$img_maxsize = $img_maxsize*1024*1024;
		}
	
	$lang = JFactory::getLanguage();
    for ($i = 0; $i < count($new_files['name']); $i++)
    {
        if (substr($new_files['type'][$i], 0, 5) == "image")
        {
   			if($img_maxsize>0 && $new_files['size'][$i]>$img_maxsize){
   				$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_TO_BIG_IMAGE').' : \''.$new_files['name'][$i].'\'','error');
				continue;
			}
			if(!getimagesize($new_files['tmp_name'][$i])){
				$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_WRONG_IMAGE_TYPE').' : \''.$new_files['name'][$i].'\'','error');
				continue;
			}				
			$n_name = $last_id.'_'.$new_files['name'][$i];    				
			$n_name = $lang->transliterate($n_name);
			$n_name = strtolower($n_name);
			$n_name = JFile::makeSafe($n_name);
			        	
        	$new_path = JPATH_BASE."/components/com_djclassifieds/images/".$n_name;
			$nimg= 0;			
			while(JFile::exists($new_path)){
				$nimg++;
    			$n_name = $last_id.'_'.$nimg.'_'.$new_files['name'][$i];
					$n_name = $lang->transliterate($n_name);
					$n_name = strtolower($n_name);
					$n_name = JFile::makeSafe($n_name);            	
        		$new_path = JPATH_BASE."/components/com_djclassifieds/images/".$n_name;
			}
			$images .= $n_name.';';
        	move_uploaded_file($new_files['tmp_name'][$i], $new_path);
			//DJClassifiedsImage::makeThumb($new_path, $nw, $nh, 'th');
			 	DJClassifiedsImage::makeThumb($new_path, $nws, $nhs, 'ths');
				DJClassifiedsImage::makeThumb($new_path, $nwm, $nhm, 'thm');
				DJClassifiedsImage::makeThumb($new_path, $nwb, $nhb, 'thb');
        }else if($new_files['name'][$i]){
			$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_WRONG_IMAGE_TYPE').' : \''.$new_files['name'][$i].'\'','error');	        	
        }
    }
	
    $row->image_url = $images;
    */
		
    $row->image_url = '';
	$duration_price =0;
		if($row->id==0){			
			if($par->get('durations_list','')){
				$exp_days = JRequest::getVar('exp_days', $par->get('exp_days'), '', 'int' );
				$query = "SELECT * FROM #__djcf_days WHERE days = ".$exp_days;	
				$db->setQuery($query);								
				$duration = $db->loadObject();
				if($duration){
					$duration_price = $duration->price; 	
				}else{
					//$exp_days = $par->get('exp_days','7');						
					$message = JText::_('COM_DJCLASSIFIEDS_WRONG_DURATION_LIMIT');					
					$app->redirect($new_ad_link, $message,'error');
				}				 
			}else{
				$exp_days = $par->get('exp_days','7');
			}												
			
			if($exp_days==0){
				$row->date_exp = "2038-01-01 00:00:00"; 
			}else{
				$row->date_exp = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")  , date("d")+$exp_days, date("Y")));
			}
			if($row->date_exp=='1970-01-01 1:00:00'){
				$row->date_exp = '2038-01-19 00:00:00';
			}
			$row->exp_days = $exp_days;
			$row->date_start = date("Y-m-d H:i:s");
		}
		
		$row->date_mod = date("Y-m-d H:i:s");		

		$row->cat_id= end($_POST['cats']);
		if(!$row->cat_id){
			$row->cat_id =$_POST['cats'][count($_POST['cats'])-2];
		}	
		$row->cat_id = str_ireplace('p', '', $row->cat_id);
		
		/*if($par->get('region_add_type','1')){
			$g_area = JRequest::getVar('g_area','');
			$g_locality = JRequest::getVar('g_locality','');
			$g_country = JRequest::getVar('g_country','');			
			$latlong = str_ireplace(array('(',')'), array('',''), JRequest::getVar('latlong',''));
			
				$query = "SELECT id FROM #__djcf_regions WHERE name = '".$g_area."'";	
				$db->setQuery($query);
				$parent_r_id = $db->loadResult();
				
				if($parent_r_id){					
					$query = "SELECT id FROM #__djcf_regions WHERE name = '".$g_locality."' AND parent_id=".$parent_r_id;	
					$db->setQuery($query);
					$region_id = $db->loadResult();
					
					if($region_id){
						$row->region_id=$region_id;
					}else{					
						$region_row = &JTable::getInstance('Regions', 'DJClassifiedsTable');
							$region_row->country=0;
							$region_row->city=1;
							$region_row->name=$g_locality;
							$region_row->parent_id=$parent_r_id;
													
							//$ll = explode(',', $latlong);
							//$region_row->latitude=$ll[0];
							//$region_row->longitude=$ll[0];	
							$region_row->published=1;
							//echo '<pre>';print_r($region_row);die();							
							if (!$region_row->store()){
				        		exit ();	
				    		}
						$row->region_id=$region_row->id;	
					}
				}else{
					$query = "SELECT id FROM #__djcf_regions WHERE name = '".$g_country."' ";	
					$db->setQuery($query);
					$country_id = $db->loadResult();
					
					if(!$country_id){$country_id=0;}
					
					$area_row = &JTable::getInstance('Regions', 'DJClassifiedsTable');
						$area_row->country=0;
						$area_row->city=0;
						$area_row->name=$g_area;
						$area_row->parent_id=$country_id;
						$area_row->published=1;
						//echo '<pre>';print_r($region_row);die();							
						if (!$area_row->store()){
			        		exit ();	
			    		}
					
					$region_row = &JTable::getInstance('Regions', 'DJClassifiedsTable');
						$region_row->country=0;
						$region_row->city=1;
						$region_row->name=$g_locality;
						$region_row->parent_id=$area_row->id;
												
						//$ll = explode(',', $latlong);
						//$region_row->latitude=$ll[0];
						//$region_row->longitude=$ll[0];
						$region_row->published=1;		
						//echo '<pre>';print_r($region_row);die();							
						if (!$region_row->store()){
			        		exit ();	
			    		}
					$row->region_id=$region_row->id;	
					
				} 						
		}else{*/
			$row->region_id= end($_POST['regions']);
			if(!$row->region_id){
				$row->region_id =$_POST['regions'][count($_POST['regions'])-2];
			}	
		//}
				
		if(($row->region_id || $row->address) && (($row->latitude=='0.000000000000000' && $row->longitude=='0.000000000000000') || (!$row->latitude && !$row->longitude))){			
			$address= '';
			if($row->region_id){
				$reg_path = DJClassifiedsRegion::getParentPath($row->region_id);
				for($r=count($reg_path)-1;$r>=0;$r--){
					if($reg_path[$r]->country){
						$address = $reg_path[$r]->name; 
					}
					if($reg_path[$r]->city){
						if($address){	$address .= ', ';}					
						$address .= $reg_path[$r]->name;
											 
					}				
				}
			}
			if($address){	$address .= ', ';}
			$address .= $row->address;
			if($row->post_code){
				$address .= ', '.$row->post_code;	
			}
			
			$loc_coord = DJClassifiedsGeocode::getLocation($address);
			if(is_array($loc_coord)){
				$row->latitude = $loc_coord['lat'];
				$row->longitude = $loc_coord['lng'];
			}
		}
		
		//echo '<pre>';print_r($row);die();
		if($row->id==0){
			$row->user_id = $user->id;
			$row->ip_address = $_SERVER['REMOTE_ADDR'];
		}
		
				
		$row->promotions='';
		if($par->get('promotion','1')=='1'){
			$query = "SELECT p.* FROM #__djcf_promotions p WHERE p.published=1 ORDER BY p.id ";	
			$db->setQuery($query);
			$promotions=$db->loadObjectList('id');
				
			$query = "SELECT p.* FROM #__djcf_promotions_prices p ORDER BY p.days ";
			$db->setQuery($query);
			$prom_prices=$db->loadObjectList();
			foreach($promotions as $prom){
				$prom->prices = array();
			}
			foreach($prom_prices as $prom_p){
				if(isset($promotions[$prom_p->prom_id])){
					$promotions[$prom_p->prom_id]->prices[$prom_p->days] = $prom_p;
				}				 
			}
 
			//echo '<pre>';print_r($promotions);//die();			
			
			
			/*foreach($promotions as $prom){
				if(JRequest::getVar($prom->name,'0')){
					$row->promotions .=$prom->name.',';
				}
			}
			if($row->promotions){
				$row->promotions = substr($row->promotions, 0,-1);
			}*/
		}
		/*else if($row->id>0){
			$row->promotions=$old_row->promotions;
		}  */

		/*if(strstr($row->promotions, 'p_first')){
			$row->special = 1;
		}else{
			$row->special = 0;
		}*/
		
		$cat='';
		if($row->cat_id){			
			$query = "SELECT name,alias,price,autopublish FROM #__djcf_categories WHERE id = ".$row->cat_id;	
			$db->setQuery($query);
			$cat = $db->loadObject();
			if(!$cat->alias){
				$cat->alias = DJClassifiedsSEO::getAliasName($cat->name);	
			}
		}
		
		$type = '';
		$type_price = 0;
		if($row->type_id){
			$type = DJClassifiedsPayment::getTypePrice($user->id,$row->type_id);
			$type_price = $type->price;
		}
		//print_r($type_price);die();
		
		$is_new=1;
		$old_promotions = '';
		if($row->id>0){	
			$query = "SELECT * FROM #__djcf_items WHERE id = ".$row->id;			
			$db->setQuery($query);
			$old_row = $db->loadObject();

			$query = "SELECT * FROM #__djcf_fields WHERE edition_blocked = 1 ";
			$db->setQuery($query);
			$fields_blocked = $db->loadObjectList();
			$fields_blocked_where = '';
			if(count($fields_blocked)){
				$fields_blocked_ids = '';
				foreach($fields_blocked as $fb){
					if($fields_blocked_ids){$fields_blocked_ids .=',';}
					$fields_blocked_ids .= $fb->id;
				}
				$fields_blocked_where = 'AND field_id NOT IN ('.$fields_blocked_ids.')';
			}
			
			$query = "DELETE FROM #__djcf_fields_values WHERE item_id= ".$row->id." ".$fields_blocked_where;
	    	$db->setQuery($query);
	    	$db->query();

	    	$query = "DELETE FROM #__djcf_fields_values_sale WHERE item_id= ".$row->id." ";
	    	$db->setQuery($query);
	    	$db->query();
	    	
	    	$query = "SELECT * FROM #__djcf_items_promotions WHERE item_id = ".$row->id." ORDER BY id";
	    	$db->setQuery($query);
	    	$old_promotions = $db->loadObjectList('prom_id');
			
			$row->payed = $old_row->payed;
			$row->pay_type = $old_row->pay_type;
			$row->exp_days = $old_row->exp_days;
			$row->alias = $old_row->alias;
			$row->published = $old_row->published;
			$row->metarobots = $old_row->metarobots;
			$is_new=0;			
		}
		if(!$row->alias){
			$row->alias = DJClassifiedsSEO::getAliasName($row->name);	
		}
		
		$dispatcher->trigger('onBeforePaymentsDJClassifiedsSaveAdvert', array(&$row,$is_new,&$cat, &$promotions, &$type_price));
				

		  	 if($cat->autopublish=='0'){
				if($par->get('autopublish')=='1'){
					$row->published = 1;
					if($row->id){
						$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY');						
					}else{
						$message = JText::_('COM_DJCLASSIFIEDS_AD_ADDED_SUCCESSFULLY');
					}					 
				}else{
					$row->published = 0;					
					if($row->id){
						$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY_WAITING_FOR_PUBLISH');						
					}else{
						$message = JText::_('COM_DJCLASSIFIEDS_AD_ADDED_SUCCESSFULLY_WAITING_FOR_PUBLISH');
					}					  
					//$redirect="index.php?option=com_djclassifieds&view=items&cid=0".$itemid;
					//$redirect=DJClassifiedsSEO::getItemRoute($row->id.':'.$row->alias,$row->cat_id.':'.$i->c_alias);					
				}
			 }elseif($cat->autopublish=='1'){
				$row->published = 1;
				if($row->id){
					$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY');						
				}else{
					$message = JText::_('COM_DJCLASSIFIEDS_AD_ADDED_SUCCESSFULLY');
				}					  
			 }elseif($cat->autopublish=='2'){
				$row->published = 0;
				if($row->id){
					$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY_WAITING_FOR_PUBLISH');						
				}else{
					$message = JText::_('COM_DJCLASSIFIEDS_AD_ADDED_SUCCESSFULLY_WAITING_FOR_PUBLISH');
				}
				$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');
			 }

			$pay_redirect=0;
			$row->pay_type='';
			$row->payed=1;
			//echo '<pre>';print_r($old_row);print_r($row);die();
			if(isset($old_row)){
				if($cat->price==0 && $row->promotions=='' && !strstr($old_row->pay_type, 'duration') && $type_price==0){
					$row->payed = 1;
					$row->pay_type ='';					
				}else if(($old_row->cat_id!=$row->cat_id && $cat->price>0) || ($old_row->promotions!=$row->promotions) || strstr($old_row->pay_type, 'duration') || $old_row->pay_type || ($old_row->type_id!=$row->type_id && $type_price>0) ){							
					$row->pay_type = '';
					if($old_row->cat_id!=$row->cat_id && $cat->price>0){
						$row->pay_type = 'cat,';
					}else if($old_row->cat_id==$row->cat_id && $cat->price>0 && strstr($old_row->pay_type, 'cat')){
						$row->pay_type = 'cat,';
					}
					//if($old_row->promotions!=$row->promotions){						
						/*$prom_new = explode(',', $row->promotions);
						for($pn=0;$pn<count($prom_new);$pn++){
							if(!strstr($old_row->promotions, $prom_new[$pn]) || strstr($old_row->pay_type, $prom_new[$pn])){
								$row->pay_type .= $prom_new[$pn].',';		
							}	
						}*/						
					//}
					if(strstr($old_row->pay_type, 'duration')){
						$row->pay_type .= 'duration,';	
					}
					
					if(strstr($old_row->pay_type, 'type,') || ($type_price>0 && $old_row->type_id!=$row->type_id)){
						$row->pay_type .= 'type,';
					}
					
					if($row->pay_type){
						$row->published = 0;
						$row->payed = 0;
						$pay_redirect=1;						
					}
					//echo $row->pay_type;print_r($old_row);
					//print_r($row);echo $pay_redirect;die();			
												
				}else if($row->payed==0 && ($cat->price>0 || $row->promotions!='')){
					$row->payed = 0;
					$row->published = 0;
					$pay_redirect=1;
				}
				
			}else if($cat->price>0 || $duration_price>0 || $type_price>0){												
				if($cat->price>0){
					$row->pay_type .= 'cat,';
				}				
				if($duration_price>0){
					$row->pay_type .= 'duration,';
				}
				if($type_price>0){
					$row->pay_type .= 'type,';
				}
				/*if($row->promotions!=''){
					$row->pay_type .= $row->promotions.',';
				}*/
				$row->published = 0;
				$row->payed = 0;
				$pay_redirect=1;	
			}else{
				$row->payed = 1;
				$row->pay_type = '';
			} 
			
			$mcat_limit = JRequest::getInt('mcat_limit',0);
			$mcat_ids = array();
			for($mi=0;$mi<$mcat_limit;$mi++){
				$mcat = $app->input->get('mcats'.$mi,array(),'ARRAY');
				if(count($mcat)){
					$mc = intval(str_ireplace('p', '', end($mcat)));
					if($mc>0){
						$mcat_ids[] = $mc;
					}
				}
			}
			
			if(count($mcat_ids)){
				$mcat_ids = implode(',', $mcat_ids);
				if($is_new){
					$query = "SELECT * FROM #__djcf_categories WHERE id IN (".$mcat_ids.") AND price>0 ";
					$db->setQuery($query);
					$mcat_list = $db->loadObjectList();
						
					foreach($mcat_list as $mc){
						$row->pay_type .= 'mc'.$mc->id.',';
						$row->published = 0;
						$row->payed = 0;
						$pay_redirect=1;
					}
				}else{
						
					$query = "SELECT * FROM #__djcf_items_categories WHERE item_id= ".$row->id." ";
					$db->setQuery($query);
					$mcat_old_list = $db->loadObjectList('cat_id');
						
					$query = "SELECT * FROM #__djcf_categories WHERE id IN (".$mcat_ids.") AND price>0 ";
					$db->setQuery($query);
					$mcat_list = $db->loadObjectList();
					foreach($mcat_list as $mc){
						$add_mc = 0;
						if(!isset($mcat_old_list[$mc->id])){
							$add_mc =1;
						}else if(strstr($old_row->pay_type, 'mc'.$mc->id.',')){
							$add_mc =1;
						}
			
						if($add_mc){
							$row->pay_type .= 'mc'.$mc->id.',';
							$row->published = 0;
							$row->payed = 0;
							$pay_redirect=1;
						}
					}
				}
			}	
			
			
			
			
		//check for free promotions	
		/*if(!strstr($row->pay_type, 'cat') && !strstr($row->pay_type, 'duration') && strstr($row->pay_type, 'p_')){
			$prom_to_pay = explode(',', $row->pay_type);
			$prom_price = 0;
			for($pp=0;$pp<count($prom_to_pay);$pp++){
				foreach($promotions as $prom){
					if($prom->name==$prom_to_pay[$pp]){
						$prom_price += $prom->price;  
					}
				}	
			}	
			
			if($prom_price==0){
				$row->pay_type='';
				$redirect='';
				$pay_redirect=0;
				if(($cat->autopublish=='0' && $par->get('autopublish')=='1') || $cat->autopublish=='1'){
					$row->published = 1;					 
				}
			}
		}*/
		
		if($user->id && $par->get('ad_preview','0') && JRequest::getInt('preview_value',0)){
			$row->published = 0;			
		} 		
				
		//echo '<pre>';print_r($row);die();echo '</pre>';
		$dispatcher->trigger('onBeforeDJClassifiedsSaveAdvert', array(&$row,$is_new));
		
		if($row->pay_type){
			$pay_redirect=1;
		}
		
		if (!$row->store()){
			//echo $row->getError();exit ();	
    	}
    	if($is_new){    		
    		$query ="UPDATE #__djcf_items SET date_sort=date_start WHERE id=".$row->id." ";
    		$db->setQuery($query);
    		$db->query();    		
    	}

    	$item_images = '';
    	$images_c = 0;
    	if(!$is_new){
    		$query = "SELECT * FROM #__djcf_images WHERE item_id=".$row->id." AND type='item' ";
    		$db->setQuery($query);
    		$item_images =$db->loadObjectList('id');
    		$images_c = count($item_images);
    	}
    	
    	$img_ids = JRequest::getVar('img_id',array(),'post','array');
    	$img_captions = JRequest::getVar('img_caption',array(),'post','array');
    	$img_images = JRequest::getVar('img_image',array(),'post','array');
    	$img_rotate = JRequest::getVar('img_rotate',array(),'post','array');
    	 
    	$img_id_to_del='';
    	
    	if($item_images){
	    	foreach($item_images as $item_img){
	    		$img_to_del = 1;
	    		foreach($img_ids as $img_id){
	    			if($item_img->id==$img_id){
	    				$img_to_del = 0;    				
	    				break;
	    			}
	    		}
	    		if($img_to_del){
	    			$images_c--;
	    			$path_to_delete = JPATH_ROOT.$item_img->path.$item_img->name;
	    			if (JFile::exists($path_to_delete.'.'.$item_img->ext)){
	    				JFile::delete($path_to_delete.'.'.$item_img->ext);
	    			}
	    			if (JFile::exists($path_to_delete.'_ths.'.$item_img->ext)){
	    				JFile::delete($path_to_delete.'_ths.'.$item_img->ext);
	    			}
	    			if (JFile::exists($path_to_delete.'_thm.'.$item_img->ext)){
	    				JFile::delete($path_to_delete.'_thm.'.$item_img->ext);
	    			}
	    			if (JFile::exists($path_to_delete.'_thb.'.$item_img->ext)){
	    				JFile::delete($path_to_delete.'_thb.'.$item_img->ext);
	    			}
	    			$img_id_to_del .= $item_img->id.',';
	    		}
	    	}
	    	if($img_id_to_del){
	    		$query = "DELETE FROM #__djcf_images WHERE item_id=".$row->id." AND type='item' AND ID IN (".substr($img_id_to_del, 0, -1).") ";
	    		$db->setQuery($query);
	    		$db->query();
	    	}
    	}

    	$last_id= $row->id;

    	$imglimit = $par->get('img_limit','3');
    	$nw = (int)$par->get('th_width',-1);
    	$nh = (int)$par->get('th_height',-1);
    	$nws = (int)$par->get('smallth_width',-1);
    	$nhs = (int)$par->get('smallth_height',-1);
    	$nwm = (int)$par->get('middleth_width',-1);
    	$nhm = (int)$par->get('middleth_height',-1);
    	$nwb = (int)$par->get('bigth_width',-1);
    	$nhb = (int)$par->get('bigth_height',-1);
    	 
    	$img_ord = 1;
    	$img_to_insert = 0;
    	$query_img = "INSERT INTO #__djcf_images(`item_id`,`type`,`name`,`ext`,`path`,`caption`,`ordering`) VALUES ";
    	//$new_img_path = JPATH_SITE."/components/com_djclassifieds/images/item/";
    	$new_img_path_rel = DJClassifiedsImage::generatePath($par->get('advert_img_path','/components/com_djclassifieds/images/item/'),$last_id) ;
    	$new_img_path = JPATH_SITE.$new_img_path_rel ;
    	
    	for($im = 0;$im<count($img_ids);$im++){
    		if($img_ids[$im]){
    			if($img_rotate[$im]%4>0){
    				$img_rot = $item_images[$img_ids[$im]];
    				//echo $img_rotate[$im]%4;
    				//  			print_r($img_rot);die();
    			
    			
    				if($par->get('leave_small_th','0')==0){
	    				if (JFile::exists($new_img_path.$img_rot->name.'_ths.'.$img_rot->ext)){
	    					JFile::delete($new_img_path.$img_rot->name.'_ths.'.$img_rot->ext);
	    						
	    				}
    				}
    				if (JFile::exists($new_img_path.$img_rot->name.'_thm.'.$img_rot->ext)){
    					JFile::delete($new_img_path.$img_rot->name.'_thm.'.$img_rot->ext);
    				}
    				if (JFile::exists($new_img_path.$img_rot->name.'_thb.'.$img_rot->ext)){
    					JFile::delete($new_img_path.$img_rot->name.'_thb.'.$img_rot->ext);
    				}
    					
    				DJClassifiedsImage::makeThumb($new_img_path.$img_rot->name.'.'.$img_rot->ext,$new_img_path.$img_rot->name.'_ths.'.$img_rot->ext, $nws, $nhs);
    				DJClassifiedsImage::makeThumb($new_img_path.$img_rot->name.'.'.$img_rot->ext,$new_img_path.$img_rot->name.'_thm.'.$img_rot->ext, $nwm, $nhm);
    				DJClassifiedsImage::makeThumb($new_img_path.$img_rot->name.'.'.$img_rot->ext,$new_img_path.$img_rot->name.'_thb.'.$img_rot->ext, $nwb, $nhb);
    					
    				//print_r($img_ids);print_r($img_rotate[$im]);die();
    			}
    			    			
    			if($item_images[$img_ids[$im]]->ordering!=$img_ord || $item_images[$img_ids[$im]]->caption!=$img_captions[$im]){
    				$query = "UPDATE #__djcf_images SET ordering='".$img_ord."', caption='".$db->escape($img_captions[$im])."' WHERE item_id=".$row->id." AND type='item' AND id=".$img_ids[$im]." ";
    				$db->setQuery($query);
    				$db->query();
    			}
    		}else{
    			if($images_c>=$imglimit){
    				break;
    			}
    			$new_img_name = explode(';',$img_images[$im]);
    			if(is_array($new_img_name)){
    				$new_img_name_u =JPATH_ROOT.'/tmp/djupload/'.$new_img_name[0];
    				if (JFile::exists($new_img_name_u)){
    					if(getimagesize($new_img_name_u)){
    						$new_img_n = $last_id.'_'.str_ireplace(' ', '_',$new_img_name[1]);
    						$new_img_n = $lang->transliterate($new_img_n);
    						$new_img_n = strtolower($new_img_n);
    						$new_img_n = JFile::makeSafe($new_img_n);
    							    						
							$nimg= 0;
							$name_parts = pathinfo($new_img_n);
							$img_name = $name_parts['filename'];
							$img_ext = $name_parts['extension'];
							$new_path_check = $new_img_path.$new_img_n;
							$new_path_check = str_ireplace('.'.$img_ext, '_thm.'.$img_ext, $new_path_check);
    						
    						while(JFile::exists($new_path_check)){
    							$nimg++;
    							$new_img_n = $last_id.'_'.$nimg.'_'.str_ireplace(' ', '_',$new_img_name[1]);
    							$new_img_n = $lang->transliterate($new_img_n);
    							$new_img_n = strtolower($new_img_n);
    							$new_img_n = JFile::makeSafe($new_img_n);
    							$new_path_check = $new_img_path.$new_img_n;
    						
    							$new_path_check = str_ireplace('.'.$img_ext, '_thm.'.$img_ext, $new_path_check);
    						}
    						    							
    						rename($new_img_name_u, $new_img_path.$new_img_n);
    						$name_parts = pathinfo($new_img_n);
    						$img_name = $name_parts['filename'];
    						$img_ext = $name_parts['extension'];
    						
    						DJClassifiedsImage::makeThumb($new_img_path.$new_img_n,$new_img_path.$img_name.'_ths.'.$img_ext, $nws, $nhs);
    						DJClassifiedsImage::makeThumb($new_img_path.$new_img_n,$new_img_path.$img_name.'_thm.'.$img_ext, $nwm, $nhm);
    						DJClassifiedsImage::makeThumb($new_img_path.$new_img_n,$new_img_path.$img_name.'_thb.'.$img_ext, $nwb, $nhb);
    						$query_img .= "('".$row->id."','item','".$img_name."','".$img_ext."','".$new_img_path_rel."','".$db->escape($img_captions[$im])."','".$img_ord."'), ";
    						$img_to_insert++;
    						if($par->get('store_org_img','1')==0){
    							JFile::delete($new_img_path.$new_img_n);
    						}
    					}
    				}
    			}
    			$images_c++;
    		}
    		$img_ord++;    		 	
    	}
    	
    	if($img_to_insert){
    		$query_img = substr($query_img, 0, -2).';';
    		$db->setQuery($query_img);
    		$db->query();
    	}    	
    	
    	$imgfreelimit = $par->get('img_free_limit','-1');
    	if($imgfreelimit>-1 && $images_c>$imgfreelimit){
    		$extra_images = $images_c - $imgfreelimit;
    		$images_to_pay = $extra_images;    		
    		if(!$is_new){
    			if($old_row->extra_images>=$images_to_pay){
    				$images_to_pay = 0;
    			}else{
    				$images_to_pay = $images_to_pay - $old_row->extra_images;
    			}
    		}
    		
    		$images_to_pay = $images_to_pay + $old_row->extra_images_to_pay;
    		
    		if($images_to_pay>0){
    			$row->extra_images = $extra_images;
    			$row->extra_images_to_pay = $images_to_pay;
    			$row->pay_type .= 'extra_img,';    			
    			$row->published = 0;
    			$row->payed = 0;
    			$pay_redirect=1;
    			$row->store();
    		}
    	}    	
    	
    	
    	$desc_chars_limit = $par->get('pay_desc_chars_free_limit',0);
    	$desc_c = strlen($row->description);
    	if($par->get('pay_desc_chars',0) && $desc_c>$desc_chars_limit){
    		$extra_chars = $desc_c - $desc_chars_limit;
    		$chars_to_pay = $extra_chars;
    		if(!$is_new){
    			if($old_row->extra_chars>=$chars_to_pay){
    				$chars_to_pay = 0;
    			}else{
    				$chars_to_pay = $chars_to_pay - $old_row->extra_chars;
    			}
    		}
    		$chars_to_pay = $chars_to_pay + $old_row->extra_chars_to_pay;
    	
    		if($chars_to_pay>0){
    			$row->extra_chars = $extra_chars;
    			$row->extra_chars_to_pay = $chars_to_pay;
    			$row->pay_type .= 'extra_chars,';
    			$row->published = 0;
    			$row->payed = 0;
    			$pay_redirect=1;
    			$row->store();
    		}
    	}
    	
    	
    	$mcat_limit = JRequest::getInt('mcat_limit',0);
    	$mcats_list = '';
    	if($mcat_limit>0){
    		for($mi=0;$mi<$mcat_limit;$mi++){
    			$mcat = $app->input->get('mcats'.$mi,'array','ARRAY');
    			if(count($mcat)){    					
    				$mc = intval(str_ireplace('p', '', end($mcat)));
    				if($mc>0){
    					$mcats_list .= $mc.',';
    				}
    			}
    		}	
    	
    	}
    	
    	 	
    		
    	if($mcats_list){
    		$mcats_list .= $row->cat_id;
    		$mcat_where = ' IN ('.$mcats_list.')';
    	}else{    	
    		$mcat_where = ' = '.$row->cat_id.' ';
    	}
    	
    	
		$query = "SELECT f.* FROM #__djcf_fields f "
			  	."LEFT JOIN #__djcf_fields_xref fx ON f.id=fx.field_id "
		 		."WHERE fx.cat_id  ".$mcat_where." GROUP BY fx.field_id "
		 		."UNION "
		 		."SELECT f.* FROM #__djcf_fields f "
			  	."LEFT JOIN #__djcf_fields_xref fx ON f.id=fx.field_id "
		 		."WHERE f.source=1 AND f.edition_blocked=0 "		
		 				
		
		;
	     $db->setQuery($query);
		 $fields_list =$db->loadObjectList();
		 //echo '<pre>'; print_r($db);print_r($fields_list);die();
		
		$a_tags_cf = '';
		if((int)$par->get('allow_htmltags_cf','0')){						
			$allowed_tags_cf = explode(';', $par->get('allowed_htmltags_cf',''));
			for($a = 0;$a<count($allowed_tags_cf);$a++){				
				$a_tags_cf .= '<'.$allowed_tags_cf[$a].'>';	
			}			
		}
		
		 $ins=0;		 
		 if(count($fields_list)>0){
			$query = "INSERT INTO #__djcf_fields_values(`field_id`,`item_id`,`value`,`value_date`,`value_date_to`) VALUES ";
				foreach($fields_list as $fl){
					if($fl->type=='checkbox'){
						if(isset($_POST[$fl->name])){
							$field_v = $_POST[$fl->name];
							$f_value=';';
								for($fv=0;$fv<count($field_v);$fv++){
									$f_value .=$field_v[$fv].';'; 
								}

							$query .= "('".$fl->id."','".$row->id."','".$db->escape($f_value)."','',''), ";
							$ins++;	
						}
					}else if($fl->type=='date'){
						if(isset($_POST[$fl->name])){							
							$f_var = JRequest::getVar( $fl->name,'','','string' );							
							$query .= "('".$fl->id."','".$row->id."','','".$db->escape($f_var)."',''), ";
							$ins++;	
						}
					}else if($fl->type=='date_from_to'){
						if(isset($_POST[$fl->name]) || isset($_POST[$fl->name.'_to'])){							
							$f_var = JRequest::getVar( $fl->name,'','','string' );
							$f_var_to = JRequest::getVar( $fl->name.'_to','','','string' );
							$query .= "('".$fl->id."','".$row->id."','','".$db->escape($f_var)."','".$db->escape($f_var_to)."'), ";
							$ins++;	
						}
					}else if($fl->type=='date_min_max'){
						if(isset($_POST[$fl->name.'_start']) || isset($_POST[$fl->name.'_end'])){
							$f_var_start = JRequest::getVar( $fl->name.'_start','','','string' );
							$f_var_end = JRequest::getVar( $fl->name.'_end','','','string' );
							$f_var_all_day = isset($_POST[$fl->name.'_all_day']) ? '1' : '0';
							$query2 = "INSERT INTO #__djcf_fields_values(`field_id`,`item_id`,`value`,`value_date`,`value_date_start`,`value_date_end`,`all_day`) VALUES ";
							$query2 .= "('".$fl->id."','".$row->id."','','','".$db->escape($f_var_start)."','".$db->escape($f_var_end)."','".$f_var_all_day."');";
							$db->setQuery($query2);
    						$db->query();
						}
					}else{					
						if(isset($_POST[$fl->name])){
							if($a_tags_cf){
								$f_var = JRequest::getVar( $fl->name,'','','string',JREQUEST_ALLOWRAW );	
								$f_var = strip_tags($f_var, $a_tags_cf);								
							}else{
								$f_var = JRequest::getVar( $fl->name,'','','string' );
							}																			
							$query .= "('".$fl->id."','".$row->id."','".$db->escape($f_var)."','',''), ";
							$ins++;	
						}
					}
				}
			}
		  //print_r($query);die();
			if($ins>0){
				$query = substr($query, 0, -2).';';
				$db->setQuery($query);
    			$db->query();	
			}
			
			
			$query ="SELECT f.* FROM #__djcf_fields f "
					."LEFT JOIN #__djcf_fields_xref fx ON f.id=fx.field_id "
					."WHERE fx.cat_id  = ".$row->cat_id." AND f.in_buynow=1 ";
			$db->setQuery($query);
			$fields_list =$db->loadObjectList();
			//echo '<pre>'; print_r($_POST);print_r($fields_list);die();
			
			$ins=0;
			if(count($fields_list)>0){
				$query = "INSERT INTO #__djcf_fields_values_sale(`item_id`,`quantity`,`options`) VALUES ";
				$bn_quantity = JRequest::getVar('bn-quantity',array());
				$quantity_total = 0;
				foreach($fields_list as &$fl){
					$fl->bn_values = JRequest::getVar('bn-'.$fl->name,array());
				}
			
				$bn_options = array();
				for($q=0;$q<count($bn_quantity);$q++){
					if($bn_quantity[$q]=='' || $bn_quantity[$q]==0){
						continue;
					}
					$bn_option = array();
					$bn_option['quantity'] = $bn_quantity[$q];
					$bn_option['options'] = array();
					$quantity_total = $quantity_total + $bn_quantity[$q];
					foreach($fields_list as &$fl){
						if($fl->bn_values[$q]){
							$bn_opt = array();
							$bn_opt['id'] = $fl->id;
							$bn_opt['name'] = $fl->name;
							$bn_opt['label'] = $fl->label;
							$bn_opt['value'] = $fl->bn_values[$q];
							$bn_option['options'][]=$bn_opt;
						}
					}
					if(count($bn_option['options'])){
						$bn_options[] = $bn_option;
					}
				}
			
				if(count($bn_options)){
					foreach($bn_options as $opt){
						$query .= "('".$row->id."','".$opt['quantity']."','".$db->escape(json_encode($opt['options']))."'), ";
						$ins++;
					}
			
					if($ins){
						$query = substr($query, 0, -2).';';
						$db->setQuery($query);
						$db->query();
												
						$query ="UPDATE #__djcf_items SET quantity=".$quantity_total." WHERE id=".$row->id." ";
						$db->setQuery($query);
						$db->query();
						$row->quantity = $quantity_total;						
					}
				}
			}						
			
			//PROMOTIONS
			if($par->get('promotion','1')=='1'){
			
				$query_del = "DELETE FROM #__djcf_items_promotions WHERE item_id=".$row->id." AND date_exp>NOW()";
				$db->setQuery($query_del);
				$db->query();
				
				$prom_query = "INSERT INTO #__djcf_items_promotions(`item_id`,`prom_id`,`date_exp`,`days`) VALUES ";
				$ins_prom=0;
				$prom_to_pay = '';

				foreach($promotions as $prom){					
					$days_left = 0;
					$days_left_default = 0;
					if(isset($old_promotions[$prom->id])){
						/*if($old_promotions[$prom->id]->days==$pp->days && $old_promotions[$prom->id]->date_exp>=date("Y-m-d H:i:s")){
						 if(strstr($old_row->pay_type, $prom->name.'_'.$prom->id.'_'.$pp->days.',')){
						 $prom_to_pay .= $prom->name.'_'.$prom->id.'_'.$pp->days.',';
						 }
						 }else if($old_promotions[$prom->id]->days!=$pp->days){
						 $old_prom_to_pay = $prom->name.'_'.$prom->id.'_'.$old_promotions[$prom->id]->days.',';
						 	
						 if(strstr($old_row->pay_type, $old_prom_to_pay)){
						 $days_left = 0;
						 }else if($old_promotions[$prom->id]->date_exp>date("Y-m-d H:i:s")){
						 $days_left = strtotime($old_promotions[$prom->id]->date_exp)-mktime();
						 }
						 //echo $days_left;die();
						 $query_del = "DELETE FROM #__djcf_items_promotions WHERE item_id=".$row->id." AND prom_id=".$prom->id." AND date_exp>NOW()";
						 $db->setQuery($query_del);
						 $db->query();
						 }*/
							
						if($old_promotions[$prom->id]->date_exp>=date("Y-m-d H:i:s")){
							$old_prom_to_pay = $prom->name.'_'.$prom->id.'_'.$old_promotions[$prom->id]->days.',';
							if(strstr($old_row->pay_type, $old_prom_to_pay)){
								$days_left = 0;
							}else if($old_promotions[$prom->id]->date_exp>date("Y-m-d H:i:s")){
								$days_left = strtotime($old_promotions[$prom->id]->date_exp)-time();
								$days_left_default = $old_promotions[$prom->id]->days;
							}
							//echo $days_left;die();
							//$query_del = "DELETE FROM #__djcf_items_promotions WHERE item_id=".$row->id." AND prom_id=".$prom->id." AND date_exp>NOW()";
							//$db->setQuery($query_del);
							//$db->query();
					
						}
					}
						
					//$prom_exp_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s")+$days_left, date("m")  , date("d")+$pp->days, date("Y")));
					//$query .= "('".$row->id."','".$prom->id."','".$prom_exp_date."','".$pp->days."'), ";
					if($days_left){
						$row->promotions .=$prom->name.',';
					}
					
					$prom_v = JRequest::getInt($prom->name,0);
					//echo $prom->name.' '.$prom_v.'<br />';
					if($prom_v){
						if(isset($prom->prices[$prom_v])){
							$pp = $prom->prices[$prom_v];															
							//$ins++;
							if($pp->price>0){
								$new_prom = $prom->name.'_'.$prom->id.'_'.$pp->days.',';
								if(!strstr($row->pay_type, $new_prom)){
									$prom_to_pay .= $new_prom;
								}									
							}else{									
								if($days_left){
									$prom_exp_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s")+$days_left, date("m")  , date("d")+$pp->days, date("Y")));
									$prom_query .= "('".$row->id."','".$prom->id."','".$prom_exp_date."','".$days_left_default."'), ";										
								}else{
									$row->promotions .= $prom->name.',';
									$prom_exp_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")  , date("d")+$pp->days, date("Y")));
									$prom_query .= "('".$row->id."','".$prom->id."','".$prom_exp_date."','".$pp->days."'), ";
								}
								$ins_prom++;									
							}
						}else if($days_left){
							$prom_exp_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s")+$days_left, date("m")  , date("d"), date("Y")));
							$prom_query .= "('".$row->id."','".$prom->id."','".$prom_exp_date."','".$days_left_default."'), ";
							$ins_prom++;
						}
						
					}else{
						if($days_left){
							$prom_exp_date = date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s")+$days_left, date("m")  , date("d"), date("Y")));
							$prom_query .= "('".$row->id."','".$prom->id."','".$prom_exp_date."','".$days_left_default."'), ";
							$ins_prom++;
						}						
					}
				}
				
				if($ins_prom){
					$prom_query = substr($prom_query, 0, -2).';';
					$db->setQuery($prom_query);
					$db->query();
				}

				if(strstr($row->promotions, 'p_first')){
					$row->special = 1;
				}else{
					$row->special = 0;
				}
				
				//echo $prom_to_pay;die();
				//print_r($query);die();
				//echo '<pre>';print_r($old_row);print_r($old_promotions);die();
				
				/*if($ins>0 || $prom_to_pay){
					if(strstr($row->promotions, 'p_first')){
						$row->special = 1;
					}else{
						$row->special = 0;
					}
					
					if($ins){
						$query = substr($query, 0, -2).';';
						$db->setQuery($query);
						$db->query();
					}*/
					
					if($prom_to_pay){											
						$row->published = 0;
						$row->payed = 0;
						$pay_redirect=1;
						$row->pay_type .= $prom_to_pay;											
					}
					$row->store();
			//	}
			
				//echo '<pre>';print_r($row);die();
			}			
		
		if($par->get('notify_admin','0')){
			if($id>0 || (!$id && $token)){
				$new_ad = 0;
			}else{
				$new_ad = 1;
			}
			if($par->get('notify_admin','0')==1){
				DJClassifiedsNotify::notifyAdmin($row,$cat,$new_ad);	
			}else if($par->get('notify_admin','0')==2 && $id==0){
				DJClassifiedsNotify::notifyAdmin($row,$cat,$new_ad);	
			}
			
		}
		if($id==0 && $par->get('user_new_ad_email','0') && ($user->id>0 || ($par->get('email_for_guest','0') && $row->email))){						
			DJClassifiedsNotify::notifyNewAdvertUser($row,$cat);
		}					 
				
		$dispatcher->trigger('onAfterDJClassifiedsSaveAdvert', array(&$row,$is_new));
		
		if($user->id && $par->get('ad_preview','0') && JRequest::getInt('preview_value',0)){
			$pay_redirect = 0;
			$message = JTExt::_('COM_DJCLASSIFIEDS_PREVIEW_OF_ADVERT');
			$redirect=DJClassifiedsSEO::getItemRoute($row->id.':'.$row->alias,$row->cat_id.':'.$cat->alias);
			if(strstr($redirect, '?')){
				$redirect .= '&prev=1';
			}else{
				$redirect .= '?prev=1';
			}
		}
		
		if($pay_redirect==1){
			$menu_uads_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=useritems',1);
			$redirect= 'index.php?option=com_djclassifieds&view=payment&id='.$row->id;
			if($menu_uads_itemid){
				$redirect .= '&Itemid='.$menu_uads_itemid->id;
			}
			//$redirect= 'index.php?option=com_djclassifieds&view=payment&id='.$row->id.$itemid;
			
			if($row->id){
				$message = JTExt::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY_CHOOSE_PAYMENT');						
			}else{
				$message = JTExt::_('COM_DJCLASSIFIEDS_AD_ADDED_SUCCESSFULLY_CHOOSE_PAYMENT');
			}	
		}
	
		if(!$redirect){
			//$redirect= 'index.php?option=com_djclassifieds&view=item&cid='.$row->cat_id.'&id='.$row->id.$itemid;
			$redirect= DJClassifiedsSEO::getItemRoute($row->id.':'.$row->alias,$row->cat_id.':'.$cat->alias);	
		}
		
		$redirect = JRoute::_($redirect,false);		
		$app->redirect($redirect, $message);

	}
	
	
	public function publish(){				
		$app = JFactory::getApplication();
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$user = JFactory::getUser();		
		$db = JFactory::getDBO();
		$id = JRequest::getInt('id', 0);
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
		
		
		if($user->id==0 || $id==0 || $par->get('ad_preview','0')==0){
			$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			//$redirect="index.php?option=com_djclassifieds&view=items&cid=0".$itemid;
			$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');
			$redirect = JRoute::_($redirect,false);
			$app->redirect($redirect, $message,'error');
		}			
		
		$query = "SELECT i.*, c.alias as c_alias, c.autopublish as c_autopublish FROM #__djcf_items i, #__djcf_categories c "				
				." WHERE i.cat_id=c.id AND i.id='".$id."' LIMIT 1";
		$db->setQuery($query);
		$item =$db->loadObject();
		
		$wrong_ad = false;
		
		if($item->user_id!=$user->id){
			$wrong_ad = true;
		}
		
		if($par->get('admin_can_edit_delete','0') && $user->authorise('core.admin')){
			$wrong_ad = false;
		}
		
		if(!$item){
			$wrong_ad = true;
		}
		
		if($wrong_ad){
			$message = JText::_('COM_DJCLASSIFIEDS_WRONG_AD');
			$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');
			$redirect = JRoute::_($redirect,false);
			$app->redirect($redirect, $message,'error');
		}

		if($item->pay_type && $item->payed==0 ){
			$menu_uads_itemid = $menus->getItems('link','index.php?option=com_djclassifieds&view=useritems',1);
			$redirect= 'index.php?option=com_djclassifieds&view=payment&id='.$item->id;
			if($menu_uads_itemid){
				$redirect .= '&Itemid='.$menu_uads_itemid->id;
			}
				
			if($row->id){
				$message = JTExt::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY_CHOOSE_PAYMENT');
			}else{
				$message = JTExt::_('COM_DJCLASSIFIEDS_AD_ADDED_SUCCESSFULLY_CHOOSE_PAYMENT');
			}
		}else{
			if($item->c_autopublish=='0'){
				if($par->get('autopublish')=='1'){
					$query ="UPDATE #__djcf_items SET published=1 WHERE id=".$item->id." ";
					$db->setQuery($query);
					$db->query();					
					$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY');					
				}else{					
					$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY_WAITING_FOR_PUBLISH');	
				}
			}elseif($item->c_autopublish=='1'){
				$query ="UPDATE #__djcf_items SET published=1 WHERE id=".$item->id." ";
				$db->setQuery($query);
				$db->query();
				$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY');				
			}elseif($item->c_autopublish=='2'){
				$message = JText::_('COM_DJCLASSIFIEDS_AD_SAVED_SUCCESSFULLY_WAITING_FOR_PUBLISH');				
				$redirect=DJClassifiedsSEO::getCategoryRoute('0:all');
			}
		}
				
		if(!$redirect){
			$redirect= DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias);
		}
		
		$redirect = JRoute::_($redirect,false);
		$app->redirect($redirect, $message);
		
		return true;
	} 
	
	function rotateImage(){
		$img_src = JRequest::getVar('img_src');
		$filename = JPATH_BASE.'/'.$img_src;
		
		if (! list ($w, $h, $type, $attr) = getimagesize($filename)) {
			return false;
		}
		
		switch($type)
		{
			case 1:
				$source = imagecreatefromgif($filename);
				break;
			case 2:
				$source = imagecreatefromjpeg($filename);
				break;
			case 3:
				$source = imagecreatefrompng($filename);
				break;
			default:
				return  false;
				break;
		}
		
		$degrees = 90;
		
		$rotate = imagerotate($source, $degrees, 0);
		imagealphablending($rotate, false);
		imagesavealpha($rotate, true);
				
		switch($type)
		{
			case 1:
				imagegif($rotate, $filename);
				break;
			case 2:
				imagejpeg($rotate, $filename, '100');
				break;
			case 3:
				imagepng($rotate, $filename);
				break;
		}
		//die();
		
		imagedestroy($source);
		imagedestroy($rotate);
		
		if(strstr($img_src, 'com_djclassifieds/images/')){
			$img_from = array('.jpg','.jpeg','.png','.gif');
			$img_to = array('_thb.jpg','_thb.jpeg','_thb.png','_thb.gif');
			$img_src = str_ireplace($img_from, $img_to, JRequest::getVar('img_src'));			
			$filename = JPATH_BASE.'/'.$img_src;
			
			
				if (! list ($w, $h, $type, $attr) = getimagesize($filename)) {
					return false;
				}
				
				switch($type)
				{
					case 1:
						$source = imagecreatefromgif($filename);
						break;
					case 2:
						$source = imagecreatefromjpeg($filename);
						break;
					case 3:
						$source = imagecreatefrompng($filename);
						break;
					default:
						return  false;
						break;
				}
				
				$degrees = 90;
				
				$rotate = imagerotate($source, $degrees, 0);
				imagealphablending($rotate, false);
				imagesavealpha($rotate, true);
						
				switch($type)
				{
					case 1:
						imagegif($rotate, $filename);
						break;
					case 2:
						imagejpeg($rotate, $filename, '100');
						break;
					case 3:
						imagepng($rotate, $filename);
						break;
				}
				//die();
				
				imagedestroy($source);
				imagedestroy($rotate);
		}
	
		die();
		return true;
	}	
	
	public function getDurationSelect(){
	
		header("Content-type: text/html; charset=utf-8");
		$id 	= JRequest::getInt('cat_id', '0');
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$points_a = $par->get('points',0);
	
		$db= JFactory::getDBO();
			
		$query = "SELECT COUNT(c.id) FROM #__djcf_categories c  ";
		$db->setQuery($query);
		$cats_total=$db->loadResult();
	
		$query = "SELECT d.*, IFNULL(c.cat_c,0) AS cat_c FROM #__djcf_days d "
				."LEFT JOIN (SELECT COUNT(id) as cat_c, day_id FROM #__djcf_days_xref GROUP BY day_id) c ON c.day_id=d.id "
				."WHERE d.published=1 AND (c.cat_c IS NULL OR d.id IN
							(SELECT day_id FROM #__djcf_days_xref WHERE cat_id='".$id."')  )"
				."ORDER BY d.days ";

				$db->setQuery($query);
				$days=$db->loadObjectList('days');

				if(isset($days[0])){
					$day_0 = $days[0];
					unset($days[0]);
					$days[0] = $day_0;
				}
					
					
				echo '<select id="exp_days" name="exp_days">';
				foreach($days as $day){
					echo '<option value="'.$day->days.'"';
					/*if($day->days==$exp_days){
						echo ' SELECTED ';
					}*/
					echo '>';
					if($day->days==0){
						echo JText::_('COM_DJCLASSIFIEDS_UNLIMITED');
					}else if($day->days==1){
						echo $day->days.'&nbsp;'.JText::_('COM_DJCLASSIFIEDS_DAY');
					}else{
						echo $day->days.'&nbsp;'.JText::_('COM_DJCLASSIFIEDS_DAYS');
					}

					if($day->price !='0.00' && $points_a!=2){
						//echo '&nbsp;-&nbsp;'.$day->price.'&nbsp;'.$par->get('unit_price');
						echo '&nbsp;-&nbsp;'.DJClassifiedsTheme::priceFormat($day->price,$par->get('unit_price'));
					}
					if($day->points>0 && $points_a){
						echo '&nbsp;-&nbsp;'.$day->points.'&nbsp;'.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
					}
					if($day->price_special>0){
						echo '&nbsp;-&nbsp;'.DJClassifiedsTheme::priceFormat($day->price_special,$par->get('unit_price')).' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
					}
					echo '</option>';
				}
				echo '</select>';

				die();
	}	
	
}

?>