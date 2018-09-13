<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Component
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
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
JHTML::_('behavior.framework' );
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.modal');
JHTML::_('behavior.calendar');
$toolTipArray = array('className'=>'djcf_label');
JHTML::_('behavior.tooltip', '.Tips1', $toolTipArray);
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$id = JRequest::getVar('id', 0, '', 'int' );
$token = JRequest::getCMD('token', '' );

$group_id = $this->m_active->params->get('registration_gid',0);


	if(count($this->custom_fields_groups)){
		if($group_id>0){ ?>
			<input type="hidden" value="<?php echo $group_id?>" name="group_id" id="group_id" />
		<?php }else{ ?>
	 	<div class="djform_row">	            		
	        <label id="group_id-lbl" for="group_id" class="label"><?php echo JText::_('COM_DJCLASSIFIEDS_REGISTER_GROUP_LABEL'); ?> *</label>		            	
	        <div class="djform_field">
	            <select class="text_area required"  name="group_id" id="group_id" onchange="getFields(this.value)" />
	            	<option value=""></option>
	            	<?php foreach($this->custom_fields_groups as $group){
	            		echo '<option value="'.$group->id.'">'.$group->name.'</option>';
	            	}?>
	            </select>             	    	
	        </div>
	        <div class="clear_both"></div> 
	    </div>
	<?php } 
	}

	
	foreach($this->custom_contact_fields as $fl){
		echo '<div class="djform_row">';
		if($fl->type=="inputbox"){
			if($id>0 || $token){
				$fl_value = $fl->value;
			}else{
				$fl_value = $fl->default_value;
			}
			$fl_value = htmlspecialchars($fl_value);

			$val_class='';
			$req = '';
			$fl_cl = '';
			if($fl->required){
				$fl_cl = 'inputbox required';
				$req = ' * ';
			}else{
				$fl_cl = 'inputbox';
			}
			
			if($fl->numbers_only){
				$fl_cl .= ' validate-numeric';
			}
			$cl = 'class="'.$fl_cl.'" ';
			

			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
				echo '</label>';
			}else{
				echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
			}

			echo '<div class="djform_field">';

			echo '<input '.$cl.' type="text" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params;
			echo ' value="'.$fl_value.'" ';
			echo ' />';
		}else if($fl->type=="textarea"){
			if($id>0 || $token){
				$fl_value = $fl->value;
			}else{
				$fl_value = $fl->default_value;
			}
			$fl_value = htmlspecialchars($fl_value);

			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="inputbox required" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox"';
			}

			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
				echo '</label>';
			}else{
				echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
			}
			echo '<div class="djform_field">';
			echo '<textarea '.$cl.' id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params.' />';
			echo $fl_value;
			echo '</textarea>';
		}else if($fl->type=="selectlist"){
			if($id>0 || $token){
				$fl_value=$fl->value;
			}else{
				$fl_value=$fl->default_value;
			}

			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="inputbox required" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox"';
			}

			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
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
				echo '<option '.$sel.' value="'.$val[$i].'">'.$val[$i].'</option>';
			}

			echo '</select>';
		}else if($fl->type=="radio"){
			if($id>0 || $token){
				$fl_value=$fl->value;
			}else{
				$fl_value=$fl->default_value;
			}

			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="required validate-radio" ';
				$req = ' * ';
			}else{
				$cl = 'class=""';
			}

			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
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

				echo '<div style="float:left;"><input type="radio" '.$cl.'  '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'" /><span class="radio_label">'.$val[$i].'</span></div>';
				echo '<div class="clear_both"></div>';
			}
			echo '</div>';
		}else if($fl->type=="checkbox"){
			$val_class='';
			$req = '';
			if($id>0 || $token){
				$fl_value = $fl->value;
			}else{
				$fl_value = $fl->default_value;
			}

			if($fl->required){
				$cl = 'class="checkboxes required" ';
				$req = ' * ';
			}else{
				$cl = 'class=""';
			}
			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
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
				if($id>0 || $token){
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

				echo '<div style="float:left;"><input type="checkbox" id="dj'.$fl->name.$i.'" class="checkbox" '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'[]" /><span class="radio_label">'.$val[$i].'</span></div>';
				echo '<div class="clear_both"></div>';
			}
			echo '</fieldset>';
			echo '</div>';
		}else if($fl->type=="date"){


			if($id>0 || $token){
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
				$cl = 'class="inputbox required djcalendar" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox djcalendar"';
			}

			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
				echo '</label>';
			}else{
				echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
			}

			echo '<div class="djform_field">';

			echo '<input '.$cl.' type="text" size="10" maxlenght="19" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params;
			echo ' value="'.$fl_value.'" ';
			echo ' />';
			echo ' <img class="calendar" src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="dj'.$fl->name.'button" />';


		}else if($fl->type=="link"){
			if($id>0 || $token){
				$fl_value = $fl->value;
			}else{
				$fl_value = $fl->default_value;
			}
			$fl_value = htmlspecialchars($fl_value);

			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="inputbox required" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox"';
			}

			if($par->get('show_tooltips_newad','0') && $fl->description){
				echo '<label class="label Tips1" for="dj'.$fl->name.'" title="'.$fl->description.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req;
				echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/tip.png" alt="?" />';
				echo '</label>';
			}else{
				echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
			}

			echo '<div class="djform_field">';

			echo '<input '.$cl.' type="text" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params;
			echo ' value="'.$fl_value.'" ';
			echo ' />';
		}else{
			echo '<div class="djform_field">';
		}

		echo '</div><div class="clear_both"></div>';
		echo '</div>';
	}


?>

	<div class="djform_row extra_fields">        
		<div id="ex_fields"></div>         
        <div class="clear_both"></div>
    </div>
    
	<script type="text/javascript">
		function getFields(group_id){		
			var el = document.getElementById("ex_fields");
			var before = document.getElementById("ex_fields").innerHTML.trim();	
			
			if(group_id!=0){
				
				el.innerHTML = '<div style="text-align:center;margin-bottom:15px;"><img src="<?php echo JURI::base(); ?>components/com_djclassifieds/assets/images/loading.gif" /></div>';
				var url = '<?php echo JURI::base()?>index.php?option=com_djclassifieds&view=registration&task=getFields&group_id=' + group_id ;
							  var myRequest = new Request({
						    url: '<?php echo JURI::base()?>index.php',
						    method: 'post',
							data: {
						      'option': 'com_djclassifieds',
						      'view': 'registration',
						      'task': 'getFields',
							  'group_id': group_id			  
							  },
						    onRequest: function(){},
						    onSuccess: function(responseText){					    	
						    																
								el.innerHTML = responseText;
								var JTooltips = new Tips($$('.Tips1'), {	
							      showDelay: 200, hideDelay: 200, className: 'djcf_label', fixed: true
							   });						 	
							   var djcals = document.getElements('.djcalendar');
								if(djcals){
									var startDate = new Date(2008, 8, 7);
									djcals.each(function(djcla,index){
										Calendar.setup({
								            inputField  : djcla.id,
								            ifFormat    : "%Y-%m-%d",                  
								            button      : djcla.id+"button",
								            date      : startDate
								         });
									});
								}																
								
								document.formvalidator.attachToForm(document.id('djForm'));
						    },
						    onFailure: function(){
						    }
						});
						myRequest.send();	
						
			}else{
				el.innerHTML = '';
			}
			
		}	

	<?php if($group_id>0){ ?>
		window.addEvent("load", function(){
			getFields(<?php echo $group_id;?>);
		});
	<?php } ?>
		
	</script>