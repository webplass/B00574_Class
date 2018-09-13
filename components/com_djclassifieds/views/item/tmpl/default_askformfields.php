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
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$app = JFactory::getApplication();
$item = $this->item;
$is_date = 0;
if(count($this->custom_ask_seller)){
	echo '<div class="ask_form_fields">';
	foreach($this->custom_ask_seller as $fl){
	echo '<div class="djform_row">';
		if($fl->type=="inputbox"){
			$fl_value = $fl->default_value;
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
	
			echo '<label for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';			
	
			echo '<div class="djform_field">';
	
			echo '<input '.$cl.' type="text" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params;
			echo ' value="'.$fl_value.'" ';
			echo ' />';
		}else if($fl->type=="textarea"){
			$fl_value = $fl->default_value;
			$fl_value = htmlspecialchars($fl_value);
	
			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="inputbox required" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox"';
			}

			echo '<label for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
			
			echo '<div class="djform_field">';
			echo '<textarea '.$cl.' id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params.' />';
			echo $fl_value;
			echo '</textarea>';
		}else if($fl->type=="selectlist"){
			$fl_value=$fl->default_value;
	
			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="inputbox required" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox"';
			}
	
			echo '<label  for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
			
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
			$fl_value=$fl->default_value;
	
			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="required validate-radio" ';
				$req = ' * ';
			}else{
				$cl = 'class=""';
			}

			echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
			
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
			$fl_value = $fl->default_value;
	
			if($fl->required){
				$cl = 'class="checkboxes required" ';
				$req = ' * ';
			}else{
				$cl = 'class=""';
			}

			echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl">'.$fl->label.$req.'</label>';
			
			echo '<div class="djform_field">';
			if(substr($fl->values, -1)==';'){
				$fl->values = substr($fl->values, 0,-1);
			}
			$val = explode(';', $fl->values);
			echo '<div class="radiofield_box" style="float:left">';
			echo '<fieldset id="dj'.$fl->name.'" '.$cl.' >';
			for($i=0;$i<count($val);$i++){
				$checked = '';				
				$def_val = explode(';', $fl->default_value);
				for($d=0;$d<count($def_val);$d++){
					if($def_val[$d] == $val[$i]){
						$checked = 'CHECKED';
					}
				}
	
				echo '<div style="float:left;"><input type="checkbox" id="dj'.$fl->name.$i.'" class="checkbox" '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'[]" /><span class="radio_label">'.$val[$i].'</span></div>';
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
				$cl = 'class="inputbox required djcalendar" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox djcalendar"';
			}
	
			echo '<label for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
	
			echo '<div class="djform_field">';
	
			echo '<input '.$cl.' type="text" size="10" maxlenght="19" id="dj'.$fl->name.'" name="'.$fl->name.'" '.$fl->params;
			echo ' value="'.$fl_value.'" ';
			echo ' />';
			echo ' <img class="calendar" src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="dj'.$fl->name.'button" />';
			$is_date = 1;
	
		}else if($fl->type=="link"){
	
			$fl_value = $fl->default_value;
			$fl_value = htmlspecialchars($fl_value);
	
			$val_class='';
			$req = '';
			if($fl->required){
				$cl = 'class="inputbox required" ';
				$req = ' * ';
			}else{
				$cl = 'class="inputbox"';
			}
	
			echo '<label class="label" for="dj'.$fl->name.'" id="dj'.$fl->name.'-lbl" >'.$fl->label.$req.'</label>';
	
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
	echo '</div>';
}
if($is_date){ ?>
<script type="text/javascript">
	window.addEvent('domready', function(){
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
	//	document.formvalidator.attachToForm(document.id('djForm'));
	});
</script>		
<?php }