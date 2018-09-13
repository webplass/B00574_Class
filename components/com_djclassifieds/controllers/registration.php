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


class DJClassifiedsControllerRegistration extends JControllerLegacy {	
	
	
	public function checkUsername(){
		
		header("Content-type: text/html; charset=utf-8");
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 		= JFactory::getDBO();
		$username   = $db->Quote($db->escape(JRequest::getVar('username','','','string'), true));
		$language 	= JFactory::getLanguage();
		$language->load('com_users', JPATH_SITE, null, true);
		
		$query ="SELECT count(u.id) FROM #__users u WHERE u.username=".$username." ";
		$db->setQuery($query);
		$u_exist =$db->loadResult();
		if($u_exist){
			echo JText::_('COM_USERS_REGISTER_USERNAME_MESSAGE');
		}
		die();
	}
	
	public function checkEmail(){
		header("Content-type: text/html; charset=utf-8");
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 		= JFactory::getDBO();
		$email 		= $db->Quote($db->escape(JRequest::getVar('email','','','string'), true));
		$language 	= JFactory::getLanguage();
		$language->load('com_users', JPATH_SITE, null, true);
	
		$query ="SELECT count(u.id) FROM #__users u WHERE u.email=".$email." ";
		$db->setQuery($query);
		$u_exist =$db->loadResult();
		if($u_exist){
			echo JText::_('COM_USERS_PROFILE_EMAIL1_MESSAGE');
		}
		die();
	}
	
	
	function save(){
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		jimport( 'joomla.database.table' );
		$app		= JFactory::getApplication();
		$Itemid		= JRequest::getInt('Itemid');
		$db 		= JFactory::getDBO();
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$dispatcher = JDispatcher::getInstance();
		$language 	= JFactory::getLanguage();
		$language->load('com_users', JPATH_SITE, null, true);
		$djp_row = JTable::getInstance('Profiles', 'DJClassifiedsTable');
		
		// Check for request forgeries.
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// If registration is disabled - Redirect to login page.
		if (JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));		
			return false;
		}
		
		JModelLegacy::addIncludePath(JPATH_BASE . '/components/com_users/models/','RegistrationModel');
		JForm::addFormPath(JPATH_BASE . '/components/com_users/models/forms');
		JForm::addFieldPath(JPATH_BASE . '/components/com_users/models/fields');
		$users_model = $this->getModel($name = 'Registration', $prefix = 'UsersModel'); 
		
		// Get the user data.
		$requestData = $app->input->post->get('jform', array(), 'array');
		
		// Validate the posted data.
		$form	= $users_model->getForm();
		
		//echo '<pre>';print_r($form);die();
		if (!$form)
		{
			JError::raiseError(500, $users_model->getError());
		
			return false;
		}
		
		
		JPluginHelper::importPlugin('captcha');
		$dispatcher = JDispatcher::getInstance();
		
		$dispatcher->trigger('onBeforeValidateDJClassifiedsSaveUser', array(&$users_model, &$requestData));
		
		$form->removeField('captcha');
		$data	= $users_model->validate($form, $requestData);
		
		$dispatcher->trigger('onBeforeDJClassifiedsSaveUser', array(&$users_model, &$data));
		
		$catpcha_test = $dispatcher->trigger('onCheckAnswer', array());
		if(count($catpcha_test)){
			if($catpcha_test[0]===false){
				$data = false;
				$users_model->setError(JText::_('COM_DJCLASSIFIEDS_INVALID_CODE'));
			}
		}
		
		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $users_model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $requestData);

			// Redirect back to the registration screen.
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration', false));

			return false;
		}
		
		if($par->get('gdpr_agreement',1)>0){
			$app->input->set('gdpr_privacy_policy_checkbox',1); 
		}
		
		// Attempt to save the data.
		$return	= $users_model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage($users_model->getError(), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_djclassifieds&view=registration&Itemid='.$Itemid, false));

			return false;
		}

		$username = $db->Quote($db->escape($data['username']), true);
		$query ="SELECT id FROM #__users u WHERE u.username=".$username." ";
		$db->setQuery($query);
		$user_id =$db->loadResult();
		
		//echo '<pre>';print_r($user_id);die();
		
		$group_id = $app->input->getInt('group_id',0);
		$djp_row->user_id= $user_id;
		$djp_row->group_id= $group_id;
		$djp_row->store();
		
		if($group_id>0){
			$query = "SELECT * FROM #__djcf_fields_groups WHERE id=".$group_id."  LIMIT 1";
			$db->setQuery($query);
			$group=$db->loadObject();
			if($group->groups_assignment){		
				JUserHelper::addUserToGroup($user_id, $group->groups_assignment);
			}
		}
									
			//add data do DJ-Classifieds profile
			$query = "SELECT f.* FROM #__djcf_fields f WHERE f.source=2 ";
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
				$query = "INSERT INTO #__djcf_fields_values_profile(`field_id`,`user_id`,`value`,`value_date`) VALUES ";
				foreach($fields_list as $fl){
					if($fl->type=='checkbox'){
						if(isset($_POST[$fl->name])){
							$field_v = $_POST[$fl->name];
							$f_value=';';
							for($fv=0;$fv<count($field_v);$fv++){
								$f_value .=$field_v[$fv].';';
							}
			
							$query .= "('".$fl->id."','".$user_id."','".$db->escape($f_value)."',''), ";
							$ins++;
						}
					}else if($fl->type=='date'){
						if(isset($_POST[$fl->name])){
							$f_var = JRequest::getVar( $fl->name,'','','string' );
							$query .= "('".$fl->id."','".$user_id."','','".$db->escape($f_var)."'), ";
							$ins++;
						}
					}else{
						if(isset($_POST[$fl->name])){
							if($a_tags_cf){
								$f_var = JRequest::getVar( $fl->name,'','','string',JREQUEST_ALLOWRAW );
								$f_var = strip_tags($f_var, $a_tags_cf);
							}else{
								$f_var = JRequest::getVar( $fl->name,'','','string' );
							}
							$query .= "('".$fl->id."','".$user_id."','".$db->escape($f_var)."',''), ";
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
				
			$new_avatar = $_FILES['new_avatar'];
			if (substr($new_avatar['type'], 0, 5) == "image")
			{
			
				$lang = JFactory::getLanguage();
				$icon_name = str_ireplace(' ', '_',$new_avatar['name'] );
				$icon_name = $lang->transliterate($icon_name);
				$icon_name = strtolower($icon_name);
				$icon_name = JFile::makeSafe($icon_name);
			
				$icon_name = $user_id.'_'.$icon_name;
				$icon_url = $icon_name;
				//$path = JPATH_SITE."/components/com_djclassifieds/images/profile/".$icon_name;
				$profile_path_rel = DJClassifiedsImage::generatePath($par->get('profile_img_path','/components/com_djclassifieds/images/profile/'),$user_id) ;
				$path = JPATH_SITE.$profile_path_rel.$icon_name ;
				
				move_uploaded_file($new_avatar['tmp_name'], $path);
			
				$nw = $par->get('profth_width',120);
				$nh = $par->get('profth_height',120);
				$nws = $par->get('prof_smallth_width',50);
				$nhs = $par->get('prof_smallth_height',50);
			
				$name_parts = pathinfo($path);
				$img_name = $name_parts['filename'];
				$img_ext = $name_parts['extension'];
				$new_path = JPATH_SITE.$profile_path_rel;
			
				//DJClassifiedsImage::makeThumb($path, $nw, $nh, 'ths');
					
				if($par->get('watermark',0)==1){
					$profile_watermark = 1;
				}else{
					$profile_watermark = 0;
				}
					
				DJClassifiedsImage::makeThumb($path,$new_path.$img_name.'_th.'.$img_ext, $nw, $nh, false, true, $profile_watermark);
				DJClassifiedsImage::makeThumb($path,$new_path.$img_name.'_ths.'.$img_ext, $nws, $nhs, false, true, $profile_watermark);
			
				$query = "INSERT INTO #__djcf_images(`item_id`,`type`,`name`,`ext`,`path`,`caption`,`ordering`) VALUES ";
				$query .= "('".$user_id."','profile','".$img_name."','".$img_ext."','".$profile_path_rel."','','1'); ";
				$db->setQuery($query);
				$db->query();

				
			}								
		
		// Flush the data from the session.
		$app->setUserState('com_users.registration.data', null);

		// Redirect to the profile screen.
		if ($return === 'adminactivate')
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
			//$this->setRedirect(JRoute::_('index.php?option=com_djclassifieds&view=registration&layout=complete&Itemid='.$Itemid, false));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
		elseif ($return === 'useractivate')
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
			//$this->setRedirect(JRoute::_('index.php?option=com_djclassifieds&view=registration&layout=complete&Itemid='.$Itemid, false));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
		else
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}


		JPluginHelper::importPlugin('djclassifieds');
		$dispatcher->trigger('onAfterDJClassifiedsSaveUser', array(&$data,$user_id));
		
		return true;
	}
	
	
	public function getFields(){
	
		header("Content-type: text/html; charset=utf-8");
		$app		= JFactory::getApplication();
		$group_id	= $app->input->getInt('group_id',0);
		$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
	
			
		$query ="SELECT f.* FROM #__djcf_fields f "
				."WHERE f.group_id=".$group_id." AND f.published=1 AND f.edition_blocked=0 AND f.source=2 ORDER by f.ordering ";
				$db->setQuery($query);
			 $fields_list =$db->loadObjectList();
			 //echo '<pre>'; print_r($db);print_r($fields_list);die();
			 	
			 	
			 if(count($fields_list)==0){
			 	die();
			 }else{
			 	//echo '<pre>';	print_r($fields_list);echo '</pre>';
			 	foreach($fields_list as $fl){
	
			 		echo '<div class="djform_row djrow_'.$fl->name.'">';
			 		if($fl->type=="inputbox"){
	
							$fl_value = htmlspecialchars($fl->default_value);
	
							$cl_price='';
							if($fl->numbers_only){
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
	
							$fl_value = htmlspecialchars($fl->default_value);
	
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
	
							$fl_value=$fl->default_value;
								
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
							$fl_value=$fl->default_value;
								
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
	
							$fl_value = $fl->default_value;
	
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
			 				
							if($fl->default_value=='current_date'){
								$fl_value = date("Y-m-d");
							}else{
								$fl_value = $fl->default_value;
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
	
							if($fl->default_value=='current_date'){
								$fl_value = date("Y-m-d");
								$fl_value_to = date("Y-m-d");
							}else{
								$fl_value = $fl->default_value;
								$fl_value_to = $fl->default_value;
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
	
							$fl_value = htmlspecialchars($fl->default_value);
	
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
	
			 		echo '</div><div class="clear_both"></div>';
			 		echo '</div>';
			 	}
			 	die();
		 	}
	}
	
	
	
}
?>