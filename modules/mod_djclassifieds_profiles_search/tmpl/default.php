<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Search Module
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
JHTML::_('behavior.calendar');
	$app 		= JFactory::getApplication();
	$config		= JFactory::getConfig();
	$document	= JFactory::getDocument();
	$menus		= $app->getMenu('site');
	$menu_item 	= $menus->getItems('link','index.php?option=com_djclassifieds&view=profiles',1);
			
	
	$menu_custom = '';
	if($params->get('results_itemid',0)){
		$menu_custom = $menus->getItem($params->get('results_itemid',0));		
	}	
			
	$itemid = ''; 
	$itemid_url = '';
	$link_reset='index.php?option=com_djclassifieds&view=profiles';
	
	if($menu_custom){
		$itemid=$menu_custom->id;
		$link_reset=$menu_custom->link;
		$itemid_url = $menu_custom->link;
	}
		
	if(!$itemid && $menu_item){
		$itemid=$menu_item->id;
		$link_reset .= '&Itemid='.$itemid;
		$itemid_url = $menu_item->link;		
	}	
	
	$link_reset .= '&reset=1';
	
	$cid=0;	
	if($params->get('fallow_cat','1')==1 && JRequest::getVar('option') == 'com_djclassifieds'){
		$cid = JRequest::getInt('cid','0');
	}	
	
	if($params->get('show_address','0')==1){ 
		DJClassifiedsTheme::includeMapsScript();		
	}		
	
?>
<div id="mod_djcf_search<?php echo $module->id;?>" class="dj_cf_search">
<form action="<?php echo JRoute::_($itemid_url.'&Itemid='.$itemid.'&se=1');?>" method="get" name="form-search<?php echo $module->id?>" id="form-search<?php echo $module->id?>">
	<?php if($config->get('sef')!=1 || !$itemid){ ?>
		<input type="hidden" name="option" value="com_djclassifieds" />
	   	<input type="hidden" name="view" value="profiles" />
	    <input type="hidden" name="Itemid" value="<?php echo $itemid;?>" /> 
   	<?php } ?> 
   	<input type="hidden" name="se" value="1" />  	   
   	<?php   	
   	
   	if($params->get('show_input','1')==1){   ?>
   		<div class="search_word djcf_se_row"> 	
		   	<?php $s_value = htmlspecialchars(JRequest::getVar('p_search',''), ENT_COMPAT, 'UTF-8');		   	
		   	 if($params->get('show_input_label','0')==1){ ?>
		   	 	<label><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_INPUT_LABEL'); ?></label>
		   	 <?php } ?>
		   	 <input type="text" id="input_search<?php echo $module->id?>" size="12" name="p_search" class="inputbox first_input" value="<?php echo $s_value; ?>" placeholder="<?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH'); ?>" />
		</div>
	<?php }
	if(($params->get('show_postcode','0')==1 || $params->get('show_address','0')==1|| $params->get('show_geoloc','0')==1	) && $params->get('radius_list','')){	
			$s_postcode_value = JRequest::getVar('p_se_postcode','');
			$s_address_value = JRequest::getVar('p_se_address','');
			if(!$s_address_value && $user_address){
				$s_address_value = $user_address;
			}
			$radius_l = explode(',', $params->get('radius_list',''));
			$radius_list = array();
			$radius_unit = $params->get('radius_unit','km');
			foreach($radius_l as $radius){
				if($radius_unit=='mile'){
					$radius_label = $radius.' '.JText::_('COM_DJCLASSIFIEDS_SEARCH_MILES');
				}else{
					$radius_label = $radius.' '.JText::_('COM_DJCLASSIFIEDS_SEARCH_KM');
				}
				$radius_list[] = array('value'=>$radius,'text'=>$radius_label,'disabled'=>0);
			}
			
			if($params->get('show_address','0')){
				$se_radius_cl = 'se_radius_address';
			}else{
				$se_radius_cl = 'se_radius_postcode';
			}

			if($params->get('show_geoloc','0')){
				$se_radius_cl .= ' se_radius_geoloc';
			}
			
			?>
			<div class="search_radius djcf_se_row <?php echo $se_radius_cl;?>">
				<?php if($params->get('show_postcode_label','0')==1){ ?>
					<?php if($params->get('show_geoloc','0')==1){ ?>
	   					<label><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_GEOLOCALIZATION_LABEL'); ?></label>
	   				<?php }else if($params->get('show_address','0')==1){ ?>
	   					<label><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_ADDRESS_LABEL'); ?></label>
	   				<?php }else{ ?>
	   					<label><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_POSTCODE_LABEL'); ?></label>
					<?php } ?>	
	   			<?php } ?> 		
	   			<?php if($params->get('show_address','0')==1){ ?>
					<input type="text" size="12" id="se_address<?php echo $module->id;?>" name="p_se_address" class="inputbox" value="<?php echo $s_address_value; ?>"  placeholder="<?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH_ADDRESS'); ?>" />
				<?php } else if($params->get('show_postcode','0')==1){?>
					<input type="text" size="12" name="p_se_postcode" id="se_postcode<?php echo $module->id?>" class="inputbox" value="<?php echo $s_postcode_value; ?>" placeholder="<?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH_POSTCODE'); ?>" />
				<?php } ?>
				
				<?php if($params->get('show_geoloc','0')==1){ ?>
					<?php if($params->get('show_address','0')==1 || $params->get('show_postcode','0')==1){ ?>
						<span class="se_geoloc_or_label"><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_OR'); ?></span>
					<?php } ?>
					<span class="se_geoloc_icon button" id="se_geoloc_icon<?php echo $module->id;?>" title="<?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_GEOLOC_TOOLTIP_INFO'); ?>" ></span>
					<input type="hidden" name="p_se_geoloc" id="se_geoloc<?php echo $module->id;?>" value="<?php echo JRequest::getVar('p_se_geoloc',''); ?>" />
					<?php if($user_address && $params->get('show_address','0')!=1){?>
						<div class="se_geoloc_address" >
							<?php echo $user_address; ?>
						</div>
					<?php } ?>
				<?php } ?>
				
				<div class="search_radius_range">				
					<?php if($params->get('show_radius_label','0')==1){ ?>	
						<label class="range_label"><?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH_SEARCH_RANGE'); ?></label>
					<?php } ?>
					<input type="hidden" name="p_se_postcode_c"  value="<?php echo $params->get('postcode_country',''); ?>"  />
					<input type="hidden" name="p_se_radius_unit"  value="<?php echo $radius_unit; ?>"  />
					<select  name="p_se_radius" class="inputbox" >
						<option value=""><?php echo JText::_('COM_DJCLASSIFIEDS_SEARCH_SEARCH_RANGE');?></option>
						<?php echo JHtml::_('select.options', $radius_list, 'value', 'text', JRequest::getFloat('p_se_radius',$params->get('default_radius','50')), true);?>
					</select>
				</div>
			</div>
	<?php }
 	if($params->get('show_loc','1')==1){	?>
	<div class="search_regions djcf_se_row">
		<?php if($params->get('show_loc_label','0')==1){ ?>
	   		<label><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_LOZALIZATION_LABEL'); ?></label>
	   	<?php } ?> 			
		<?php 	
			
			if($params->get('loc_select_type',0)==1){
				$reg_sel = '<select  class="inputbox" id="se'.$module->id.'_reg_0" name="p_se_regs[]"><option value="0">'.JText::_('COM_DJCLASSIFIEDS_SELECT_LOCALIZATION').'</option>';
				
				foreach($regions as $reg){
					$r_name = str_ireplace("'", "&apos;", $reg->name);
					for($lev=0;$lev<$reg->level;$lev++){
						$r_name ="- ".$r_name;
					}
					$reg_sel .= '<option value="'.$reg->id.'">'.$r_name.'</option>';
				}
				$reg_sel .= '</select>';
				echo $reg_sel;
				
			}else{
				$reg_sel = '<select  class="inputbox" id="se'.$module->id.'_reg_0" name="p_se_regs[]" onchange="se'.$module->id.'_new_reg(0,this.value,new Array());"><option value="0">'.JText::_('COM_DJCLASSIFIEDS_SELECT_LOCALIZATION').'</option>';
				$parent_id=0;	
				$lc=0;
				$lcount = count($regions);
				
				foreach($regions as $l){
					$lc++;
					if($parent_id!=$l->parent_id){
						$reg_sel .= '</select>';
						echo $reg_sel;
						break;
					}	
					$reg_sel .= '<option value="'.$l->id.'">'.str_ireplace("'", "&apos;", $l->name).'</option>';
					
					if($parent_id==$l->parent_id && $lc==$lcount){
						$reg_sel .= '</select>';
						echo $reg_sel;
						break;
					}
				}
			}
				
				?>

				<div id="se<?php echo $module->id;?>_after_reg_0"></div>
				<script type="text/javascript">
					var se<?php echo $module->id;?>_regs=new Array();

				var se<?php echo $module->id;?>_current=0;
				
				function se<?php echo $module->id;?>_new_reg(parent,a_parent,r_path){

				  	var myRequest = new Request({
					    url: '<?php echo JURI::base()?>index.php',
					    method: 'post',
						data: {
					      'option': 'com_djclassifieds',
					      'view': 'item',
					      'task': 'getRegionSelect',
						  'reg_id': a_parent,
						  'mod_id': <?php echo $module->id;?>,
						  'prefix': 'p_'				  
						  },
					    onRequest: function(){
					    	document.id('se<?php echo $module->id;?>_after_reg_'+parent).innerHTML = '<div style="text-align:center;"><img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/loading.gif" alt="..." /></div>';
					    	},
					    onSuccess: function(responseText){																
					    	if(responseText){	
								document.id('se<?php echo $module->id;?>_after_reg_'+parent).innerHTML = responseText; 
								document.id('se<?php echo $module->id;?>_reg_'+parent).value=a_parent;
							}else{
								document.id('se<?php echo $module->id;?>_after_reg_'+parent).innerHTML = '';
								document.id('se<?php echo $module->id;?>_reg_'+parent).value=a_parent;		
							}	
					    	//support for IE
							document.id('se<?php echo $module->id;?>_reg_'+parent).blur();
							if(r_path != 'null'){
								if(r_path.length>0){
									var first_path = r_path[0].split(',');												
									r_path.shift();
									se<?php echo $module->id;?>_new_reg(first_path[0],first_path[1],r_path);												
								}
							}
					    },
					    onFailure: function(){}
					});
					myRequest.send();	
					
				}				
				</script>

		</div>	
	<?php } ?>
	<?php if($params->get('show_custom_fields',0)==1 && count($custom_fields)){ ?>
		<div id="profile_search<?php echo $module->id;?>_ex_fields" class="search_ex_fields">
		<?php 		
			foreach($custom_fields as $fl){
		 			$fl_class='djseform_field djse_type_'.$fl->type.' djse_field_'.$fl->id;
		 			if($fl->search_type=='checkbox_accordion_o'){
		 				$fl_class .= ' djfields_accordion_o';
		 			}else if($fl->search_type=='checkbox_accordion_c'){
		 				//$fl_class .= ' djfields_accordion_c';
		 				$fl_class .= $session->get('p_se_'.$fl->id,'') ? ' djfields_accordion_o' : ' djfields_accordion_c';
		 			}
		 			echo '<div class="'.$fl_class.'">';
					echo '<span style="font-weight:bold;" class="label">'.$fl->label.'</span>';
					if($fl->type=='date' || $fl->type=='date_from_to'){
						if($fl->search_type=='inputbox'){	
							if($session->get('p_se_'.$fl->id,'')!=''){
								$value = $session->get('p_se_'.$fl->id,'');
							}else{
								$value = '';
							}
							echo '<input class="inputbox djsecal" type="text" size="10" maxlenght="19" value="'.$value.'" id="se_'.$fl->id.'" name="p_se_'.$fl->id.'" />';
							echo ' <img class="calendar" src="'.JURI::base().'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="se_'.$fl->id.'button" />';	
						}else if($fl->search_type=='inputbox_min_max'){
								if($session->get('p_se_'.$fl->id.'_min','')!=''){
									$value = $session->get('p_se_'.$fl->id.'_min','');
								}else{
									$value = '';
								}
							echo '<span class="from_class">'.JText::_('COM_DJCLASSIFIEDS_FROM').'</span>'.' ';
							echo '<input class="inputbox djsecal" type="text" size="10" maxlenght="19" value="'.$value.'" id="se_'.$fl->id.'_min" name="p_se_'.$fl->id.'_min" />';
							echo ' <img class="calendar" src="'.JURI::base().'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="se_'.$fl->id.'_minbutton" />';
							echo '<br />';
								if($session->get('p_se_'.$fl->id.'_max','')!=''){
									$value = $session->get('p_se_'.$fl->id.'_max','');
								}else{
									$value = '';
								}
							echo '<span class="to_class">'.JText::_('COM_DJCLASSIFIEDS_TO').'</span>'.' ';
							echo '<input class="inputbox djsecal" type="text" size="10" maxlenght="19" value="'.$value.'" id="se_'.$fl->id.'_max" name="p_se_'.$fl->id.'_max" />';
							echo ' <img class="calendar" src="'.JURI::base().'/components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="se_'.$fl->id.'_maxbutton" />';
						}
					}else{
						if($fl->search_type=='inputbox'){	
							if($session->get('p_se_'.$fl->id,'')!=''){
								$value = $session->get('p_se_'.$fl->id,'');
							}else{
								$value = '';
							}
							echo '<input class="inputbox" type="text" size="30" value="'.$value.'" name="p_se_'.$fl->id.'" />';	
						}else if($fl->search_type=='select'){														
							echo '<select class="inputbox" name="p_se_'.$fl->id.'"  >';
								if(substr($fl->search_value1, -1)==';'){
									$fl->search_value1 = substr($fl->search_value1, 0,-1);
								}
								$val = explode(';', $fl->search_value1);
								$fl_value = $session->get('p_se_'.$fl->id,'');
								for($i=0;$i<count($val);$i++){
									if($fl_value==$val[$i]){
										$sel="selected";
									}else{
										$sel="";
									}
									if($val[$i]==''){
										echo '<option '.$sel.' value="'.$val[$i].'">'.JText::_('COM_DJCLASSIFIEDS_FILTER_ALL').'</option>';
									}else{
										echo '<option '.$sel.' value="'.$val[$i].'">';
											if($comparams->get('cf_values_to_labels','0')){
												echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($val[$i])));
											}else{
												echo $val[$i];
											}
										echo '</option>';	
									}
									
								}
								
							echo '</select>';
						}else if($fl->search_type=='radio'){
							if(substr($fl->search_value1, -1)==';'){
									$fl->search_value1 = substr($fl->search_value1, 0,-1);
								}
							$val = explode(';', $fl->search_value1);
							$fl_value = $session->get('p_se_'.$fl->id,'');
							echo '<div class="radiofield_box">';
								for($i=0;$i<count($val);$i++){
									$checked = '';
										if($fl_value == str_ireplace('+', ' ', $val[$i])){
											$checked = 'CHECKED';
										}									 	
									
									echo '<div class="radiofield_box_v"><input type="radio" class="inputbox" '.$checked.' value ="'.$val[$i].'" name="p_se_'.$fl->id.'" id="se_'.$fl->id.'_'.$i.'"  />';
										echo '<label for="se_'.$fl->id.'_'.$i.'" class="radio_label">';
											if($comparams->get('cf_values_to_labels','0')){
												echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($val[$i])));
											}else{
												echo $val[$i];	
											}
										echo '</label>';
									echo '</div>';
								}	
								echo '<div class="clear_both"></div>';								
							echo '</div>';	
						}else if($fl->search_type=='checkbox' || $fl->search_type=='checkbox_accordion_o' || $fl->search_type=='checkbox_accordion_c'){
								if(substr($fl->search_value1, -1)==';'){
									$fl->search_value1 = substr($fl->search_value1, 0,-1);
								}							
							$val = explode(';', $fl->search_value1);
							
							echo '<div class="se_checkbox">';
								for($i=0;$i<count($val);$i++){
									$checked = '';
		
									//$def_val = explode(',', $session->get('p_se_'.$fl->id,''));
									$def_val = explode(';', str_ireplace(',', ';', $session->get('p_se_'.$fl->id,'')));
									
										for($d=0;$d<count($def_val);$d++){
											if($def_val[$d] == $val[$i]){
												$checked = 'CHECKED';
											}											
										}
									
									echo '<div class="se_checkbox_v"><input type="checkbox" '.$checked.' value ="'.$val[$i].'" name="p_se_'.$fl->id.'[]" id="se_'.$fl->id.'_'.$i.'" />';
										echo '<label for="se_'.$fl->id.'_'.$i.'" class="radio_label">';
											if($comparams->get('cf_values_to_labels','0')){
												echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($val[$i])));
											}else{
												echo $val[$i];
											}
										echo '</label>';
									echo '</div>';
									
								}
							echo '<div class="clear_both"></div>';
							echo '</div>';	
	
						}else if($fl->search_type=='inputbox_min_max'){
								if($session->get('p_se_'.$fl->id.'_min','')!=''){
									$value = $session->get('p_se_'.$fl->id.'_min','');
								}else{
									$value = '';
								}
							echo '<span class="from_class">'.JText::_('COM_DJCLASSIFIEDS_FROM').'</span>'.' '.'<input style="width:30px;" class="inputbox" type="text" size="10" value="'.$value.'" name="p_se_'.$fl->id.'_min" />';
								if($session->get('p_se_'.$fl->id.'_max','')!=''){
									$value = $session->get('p_se_'.$fl->id.'_max','');
								}else{
									$value = '';
								}
							echo '<span class="to_class">'.JText::_('COM_DJCLASSIFIEDS_TO').'</span>'.' '.'<input style="width:30px;" class="inputbox" type="text" size="10" value="'.$value.'" name="p_se_'.$fl->id.'_max" />';
						}else if($fl->search_type=='select_min_max'){
							echo '<span class="from_class">'.JText::_('COM_DJCLASSIFIEDS_FROM').'</span>';
								if(substr($fl->search_value1, -1)==';'){
									$fl->search_value1 = substr($fl->search_value1, 0,-1);
								}
								$se_v1 = explode(';', $fl->search_value1);
									echo '<select style="width:auto;" name="p_se_'.$fl->id.'_min" >';
									if($session->get('p_se_'.$fl->id.'_min','')!=''){
										$value = $session->get('p_se_'.$fl->id.'_min','');
									}else{
										$value = '';
									}
										for($i=0;$i<count($se_v1);$i++){
											if($value==$se_v1[$i]){
												$sel=' selected="selected"  ';
											}else{
												$sel= '';
											}
											echo '<option '.$sel.' class="inputbox" value="'.$se_v1[$i].'">';
												if($comparams->get('cf_values_to_labels','0')){
													echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($se_v1[$i])));
												}else{
													echo $se_v1[$i];
												}
											echo '</option>';
										}
									echo '</select>';
							echo '<span class="to_class new">'.JText::_('COM_DJCLASSIFIEDS_TO').'</span>';
									if(substr($fl->search_value2, -1)==';'){
										$fl->search_value2 = substr($fl->search_value2, 0,-1);
									}
									$se_v2 = explode(';', $fl->search_value2);
									echo '<select style="width:auto;" name="p_se_'.$fl->id.'_max" >';
									if($session->get('p_se_'.$fl->id.'_max','')!=''){
										$value = $session->get('p_se_'.$fl->id.'_max','');
									}else{
										if(count($se_v2)){
											$value = end($se_v2);
										}else{
											$value = '';	
										}
										
									}
										for($i=0;$i<count($se_v2);$i++){
											
											if($value==$se_v2[$i]){
												$sel=' selected="selected" ';
											}else{
												$sel= '';
											}
											echo '<option '.$sel.' class="inputbox" value="'.$se_v2[$i].'">';
												if($comparams->get('cf_values_to_labels','0')){
													echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($se_v2[$i])));
												}else{
													echo $se_v2[$i];
												}
											echo '</option>';
										}
									echo '</select>';
						}else if($fl->search_type=='date_min_max'){
							$value1 = $session->get('p_se_'.$fl->id.'_min','');
							echo '<input type="hidden" class="daterange_min" value="'.$value1.'" name="p_se_'.$fl->id.'_min">';
							$value2 = $session->get('p_se_'.$fl->id.'_max','');
							echo '<input type="hidden" class="daterange_max" value="'.$value2.'" name="p_se_'.$fl->id.'_max">';
							echo '<div class="input-group datetimepicker-container">
							<span class="icon icon-calendar"> </span>
							<input type="text" class="daterange" value="" placeholder="'.JText::_('COM_DJCLASSIFIEDS_DATERANGE').'"/>
							</div>';
						}
					}
					 
				echo '</div>';
		 	}	?>
										
		</div>
		<div style="clear:both"></div>
	<?php } ?>		
		

	<div class="search_buttons">										
		<button type="submit" class="button btn"><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_SEARCH');?></button>		
		<?php 
		if((JRequest::getInt('se',0)==1 || (JRequest::getInt('cid',0)>0 && JRequest::getInt('option','')=='com_djclassifieds')) && ($params->get('show_reset','1')>0) ){ 
			if($params->get('show_reset','1')==1){ ?>
				<a href="<?php echo JRoute::_($link_reset);?>" class="reset_button"><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_RESET');?></a>	
			<?php }else{ ?>
				<a href="<?php echo JRoute::_($link_reset);?>" class="button"><?php echo JText::_('MOD_DJCLASSIFIEDS_SEARCH_RESET');?></a>
			<?php } ?>
			
				
		<?php } ?>
	</div>
	 
</form>
 
<div style="clear:both"></div>
</div>

<?php
$cat_id_se = 0;
if($params->get('show_cat','1')==1){
	if(JRequest::getVar('se','0','','string')!='0' && isset($_GET['p_se_cats'])){
		if(is_array($_GET['p_se_cats'])){
			$cat_id_se= end($_GET['p_se_cats']);
			if($cat_id_se=='' && count($_GET['p_se_cats'])>2){
				$cat_id_se =$_GET['p_se_cats'][count($_GET['p_se_cats'])-2];
			}
		}else{
			$cat_ids_se = explode(',', JRequest::getVar('p_se_cats'));
			$cat_id_se = end($cat_ids_se);
		}		
		$cat_id_se = str_ireplace('p', '', $cat_id_se);
		$cat_id_se = (int)$cat_id_se;
	}
	if($cat_id_se=='0'){		
		$cat_id_se = $cid;	
	}	
		
	$se_parents = array();
	
	if($cat_id_se){
		$se_parents[] = $cat_id_se;
	}	
	
	$act_parent = 0;
	/*if($cat_id_se > 0){
		
		foreach($list as $c){
			if($cat_id_se == $c->id ){
				$se_parents[] = $c->parent_id.','.$c->id;
				$act_parent = $c->parent_id;
				break;		
			}
		}
		
		while($act_parent!=0){
			foreach($list as $c){
				if($act_parent == $c->id ){
					$se_parents[] = $c->parent_id.','.$c->id;
					$act_parent = $c->parent_id;
					break;		
				}
			}	
		}
		
	}*/
}
$reg_id_se = 0;

if($params->get('show_loc','1')==1){
	$act_reg_parent = 0;
	if(JRequest::getVar('se','0','','string')!='0' && isset($_GET['p_se_regs'])){		
		if(is_array($_GET['p_se_regs'])){
			$reg_id_se= end($_GET['p_se_regs']);
			if($reg_id_se=='' && count($_GET['p_se_regs'])>=2){
				$reg_id_se =$_GET['p_se_regs'][count($_GET['p_se_regs'])-2];
			}
		}else{
			$reg_ids_se = explode(',', JRequest::getVar('p_se_regs'));
			$reg_id_se = end($reg_ids_se);
		}
		$reg_id_se=(int)$reg_id_se;		
	}
	
	if($reg_id_se=='0'){
		$reg_id_se = JRequest::getInt('rid',DJClassifiedsRegion::getDefaultRegion());	
	}	
	$se_reg_parents = array();
	if($reg_id_se){
		$se_reg_parents[] = $reg_id_se; 	
	}
	
	if($reg_id_se > 0){
		foreach($regions as $r){
			if($reg_id_se == $r->id ){
				$se_reg_parents[] = $r->parent_id.','.$r->id;
				$act_reg_parent = $r->parent_id;
				break;		
			}
		}
		while($act_reg_parent!=0){
			foreach($regions as $r){
				if($act_reg_parent == $r->id ){
					$se_reg_parents[] = $r->parent_id.','.$r->id;
					$act_reg_parent = $r->parent_id;
					break;		
				}
			}	
		}
	}	
}

if($cat_id_se > 0 || $reg_id_se > 0){ 

	?>
	<script type="text/javascript">
		window.addEvent("load", function(){
			<?php 		
			if($cat_id_se>0){
				$c_path = $se_parents;
				if($params->get('cat_select_type','0')==1){
					echo "document.id('se".$module->id."_cat_0').value=".$cat_id_se.";";
				}else{
					echo 'var cat_path = new Array();';
					$cat_path_match = false;
					$cat_path_f = 'se'.$module->id.'_new_cat(';
					for($r=count($c_path)-1;$r>0;$r--){
						if($r<count($c_path)-1){
							$ri = count($c_path) - $r -2;
							echo "cat_path[$ri]='$c_path[$r]';";
						}else{
							$cat_path_f .= $c_path[$r];
							$cat_path_match = true;
						}
					}
					
					if($cat_path_match) echo $cat_path_f.',cat_path);'; 
				}
				?>

				
			<?php } ?>
			<?php
			if($reg_id_se > 0){				
					$r_path = $se_reg_parents; 
					
					if($params->get('loc_select_type',0)==1){
						echo "document.id('se".$module->id."_reg_0').value=".$reg_id_se.";";
					}else{
						echo 'var reg_path = new Array();';
						$reg_path_f = 'se'.$module->id.'_new_reg(';
						for($r=count($r_path)-1;$r>0;$r--){
							if($r<count($r_path)-1){
								$ri = count($r_path) - $r -2;
								echo "reg_path[$ri]='$r_path[$r]';";
							}else{
								$reg_path_f .= $r_path[$r];
							}
						}
						
						echo $reg_path_f.',reg_path);';
					}
			}	 ?>			
		});
	</script>
	<?php
	
}

	if($cat_id_se==0 && $params->get('show_cat','1')==1 && $params->get('cat_id','0')>0){
		if(JRequest::getVar('option','')!='com_djclassifieds' || (JRequest::getInt('se',0)==0 && JRequest::getInt('option','')=='com_djclassifieds')){
			$cat_id = $params->get('cat_id','0');
			$se_parents = array();
			$se_parents[]=$cat_id;
			$act_parent = 0;
				foreach($list as $c){
					if($cat_id == $c->id ){
						$se_parents[] = $c->parent_id.','.$c->id;
						$act_parent = $c->parent_id;
						break;		
					}
				}
				while($act_parent!=0){
					foreach($list as $c){
						if($act_parent == $c->id ){
							$se_parents[] = $c->parent_id.','.$c->id;
							$act_parent = $c->parent_id;
							break;		
						}
					}	
				}
				
				//print_r($se_parents);die();
		
		?>
			<script type="text/javascript">		
			window.addEvent("load", function(){
				<?php
					if($cat_id>0){
					
						$c_path = $se_parents;
							
						echo 'var cat_path = new Array();';
										$cat_path_f = 'se'.$module->id.'_new_cat(';
										for($r=count($c_path)-1;$r>0;$r--){
										if($r<count($c_path)-1){
						$ri = count($c_path) - $r -2;
							echo "cat_path[$ri]='$c_path[$r]';";
						}else{
							$cat_path_f .= $c_path[$r];
						}
					}
						
					echo $cat_path_f.',cat_path);'; ?>

					
				<?php } ?>


			});
			</script>
			
			
			
			
		<?php 	
		} 
	}	
	
	if($params->get('show_address','0')==1){ ?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				djcfmodSearchPlaces<?php echo $module->id;?>();		
			});
			
			function djcfmodSearchPlaces<?php echo $module->id;?>(){
				var input = (document.getElementById('se_address<?php echo $module->id;?>'));								
				var aut_options = '';
				<?php if($params->get('api_country','')!=''){ ?>
					var aut_options = {					
						  componentRestrictions: {country: '<?php echo $params->get('api_country',''); ?>'}
						};
				<?php } ?>
				var autocomplete = new google.maps.places.Autocomplete(input,aut_options);									 
				var infowindow = new google.maps.InfoWindow();
				var last_place = '';
					google.maps.event.addListener(autocomplete, 'places_changed', function() {				

				  });
				  
				 
		            // dojo.connect(input, 'onkeydown', function(e) {
		            google.maps.event.addDomListener(input, 'keydown', function(e) {
		                    if (e.keyCode == 13)
		                    {
		                            if (e.preventDefault)
		                            {
		                                    e.preventDefault();
		                            }
		                            else
		                            {
		                                    // Since the google event handler framework does not handle early IE versions, we have to do it by our self. :-(
		                                    e.cancelBubble = true;
		                                    e.returnValue = false;
		                            }
		                    }
		            }); 
				  
			}		
			
			
		</script>
	<?php }
	
	if($params->get('show_geoloc','0')==1){ ?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				document.id('se_geoloc_icon<?php echo $module->id;?>').addEvent('click',function(event){					
					if(navigator.geolocation){
						navigator.geolocation.getCurrentPosition(modSearchShowDJPosition<?php echo $module->id;?>,
							 function(error){
	         					//alert("<?php echo str_ireplace('"', "'",JText::_(''));?>");
								 alert(error.message);
	         				
						    }, {
						         timeout: 30000, enableHighAccuracy: true, maximumAge: 90000
						    });
					} 
				})
			});		
	


			function modSearchShowDJPosition<?php echo $module->id;?>(position){
			  	var exdate=new Date();
			  	exdate.setDate(exdate.getDate() + 1);
				var ll = position.coords.latitude+'_'+position.coords.longitude;
				document.cookie = "djcf_latlon=" + ll + "; expires=" + exdate.toUTCString()+";path=/";
			  	//document.id('se_postcode<?php echo $module->id?>').value = '00-000';
			  	document.id('se_geoloc<?php echo $module->id?>').value = '1';
			  	document.id('form-search<?php echo $module->id; ?>').submit();				  	
		  	}
		</script>
	
	
	<?php } ?>
	
	<?php if($params->get('show_custom_fields',0)==1 && count($custom_fields)){ ?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				var djcals = document.getElements('.djsecal');
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
				
				var djfields_accordion_o = document.getElements('#profile_search<?php echo $module->id;?>_ex_fields .djfields_accordion_o');
				if(djfields_accordion_o){										
					djfields_accordion_o.each(function(djfields_acc_o,index){
						 new Fx.Accordion(djfields_acc_o.getElements('.label'),
								 djfields_acc_o.getElements('.se_checkbox'), {
								alwaysHide : true,
								display : 0,
								duration : 100,
								onActive : function(toggler, element) {
									toggler.addClass('active');
									element.addClass('in');
								},
								onBackground : function(toggler, element) {
									toggler.removeClass('active');
									element.removeClass('in');
								}
							});
					})										
				}
				
				var djfields_accordion_c = document.getElements('#profile_search<?php echo $module->id;?>_ex_fields .djfields_accordion_c');
				if(djfields_accordion_c){										
					djfields_accordion_c.each(function(djfields_acc_c,index){
						 new Fx.Accordion(djfields_acc_c.getElements('.label'),
								djfields_acc_c.getElements('.se_checkbox'), {
								alwaysHide : true,
								display : -1,
								duration : 100,
								onActive : function(toggler, element) {
									toggler.addClass('active');
									element.addClass('in');
								},
								onBackground : function(toggler, element) {
									toggler.removeClass('active');
									element.removeClass('in');
								}
							});
					})										
				}	
			});
		</script>	
	<?php } ?>