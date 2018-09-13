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
//JHTML::_( 'behavior.Mootools' );
jimport( 'joomla.html.editor' );
JHTML::_('behavior.calendar');
JHTML::_('behavior.modal');
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$document= JFactory::getDocument();
$dispatcher	= JDispatcher::getInstance();
$id = JRequest::getVar('id', '0', '', 'int');
if($id==0){
	$cid = JRequest::getVar('cid', array(0), '', 'array' );
	$id = $cid[0];
}

$document->addScript(JURI::root()."media/system/js/calendar-setup.js");
$document->addStyleSheet(JURI::root()."media/system/css/calendar-jos.css");

DJClassifiedsTheme::includeMapsScript();

?> 
		<form action="index.php?option=com_djclassifieds" method="post" name="adminForm" id="adminForm" enctype='multipart/form-data'>		
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#details" data-toggle="tab"><?php echo JText::_('COM_DJCLASSIFIEDS_BASIC_INFORMATIONS'); ?></a>
							</li>
							<li>
								<a href="#location" data-toggle="tab"><?php echo JText::_('COM_DJCLASSIFIEDS_LOCATION');?></a>
							</li>
							<li>
								<a href="#profile_custom_fields" data-toggle="tab"><?php echo JText::_('COM_DJCLASSIFIEDS_PROFILE_CUSTOM_FIELDS');?></a>
							</li>			
							<?php
								$tab_titles = $dispatcher->trigger('onAdminProfileEditTabTitle', array ($this->jprofile,$this->profile_fields));
								if(count($tab_titles)){
									foreach($tab_titles as $tab_title){
										echo $tab_title;
									}
								}
							?>			
						</ul>
						<div class="tab-content">
							<div class="tab-pane active" id="details">
									<div class="control-group">
										<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_PROFILE_IMAGE');?></div>
										<div class="controls">
						                    <?php
												if(!$this->images){
													echo JText::_('COM_DJCLASSIFIEDS_NO_PROFILE_IMAGE_INCLUDED');
												}else{
													$avatar_img = $this->images[0];
													echo '<img src="'.JURI::root().$avatar_img->path.$avatar_img->name.'_th.'.$avatar_img->ext.'" />';?>
													<input type="checkbox" name="del_avatar" id="del_avatar" value="<?php echo $avatar_img->id;?>"/>
													<input type="hidden" name="del_avatar_id" value="<?php echo $avatar_img->id;?>"/>
													<input type="hidden" name="del_avatar_path" value="<?php echo $avatar_img->path;?>"/>
													<input type="hidden" name="del_avatar_name" value="<?php echo $avatar_img->name;?>"/>
													<input type="hidden" name="del_avatar_ext" value="<?php echo $avatar_img->ext;?>"/>
													<?php echo JText::_('COM_DJCLASSIFIEDS_CHECK_TO_DELETE'); 
												}
											?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo JText::_('COM_DJCLASSIFIEDS_ADD_IMAGE');?><br />
						                    <?php echo JText::_('COM_DJCLASSIFIEDS_NEW_IMAGES_OVERWRITE_EXISTING_ONE');?>
						                </div>
										<div class="controls">
											<input type="file"  name="new_image" />
										</div>
									</div>							
									<div class="control-group">
										<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_NAME');?></div>
										<div class="controls"><?php echo $this->jprofile->name; ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo JText::_('JGLOBAL_USERNAME');?></div>
										<div class="controls"><?php echo $this->jprofile->username; ?></div>
									</div>															
									<div class="control-group">
										<div class="control-label"><?php echo JText::_('JGLOBAL_EMAIL');?></div>
										<div class="controls"><?php echo $this->jprofile->email; ?></div>
									</div>
									<div class="control-group">
										<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_VERIFIED_SELLER');?></div>
										<div class="controls">
											<select name="verified">
					               	    		<option value="0" <?php if($this->profile->verified=='0'){echo 'selected';}?> ><?php echo JText::_('COM_DJCLASSIFIEDS_NO');?></option>
												<option value="1" <?php if($this->profile->verified=='1'){echo 'selected';}?> ><?php echo JText::_('COM_DJCLASSIFIEDS_YES');?></option>
					               	    	</select>
										</div>
									</div>						
								</div>
								
								
							<div class="tab-pane" id="location">
								<div class="control-group">
									<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION');?></div>
									<div class="controls">
				                    	<?php
										$reg_sel = '<select id="reg_0" style="width:210px" name="regions[]" onchange="new_reg(0,this.value);"><option value="">'.JText::_('COM_DJCLASSIFIEDS_SELECT_LOCALIZATION').'</option>';
										$parent_id=0;	
										foreach($this->regions as $l){
											if($parent_id!=$l->parent_id){
												break;
											}	
											$reg_sel .= '<option value="'.$l->id.'">'.str_ireplace("'", "&apos;", $l->name).'</option>';							
											//$ri++;
										}
											$reg_sel .= '</select>';
											echo $reg_sel;
										
										?><div style="clear:both"></div>
										<div id="after_reg_0"></div>
										<script type="text/javascript">
											var regs=new Array();
											
										<?php
										$reg_sel = '<select style="width:210px" name="regions[]" id="reg_0" onchange="new_reg(0,this.value);">';
										$parent_id=0;	
										
										foreach($this->regions as $l){
											if($parent_id!=$l->parent_id){
												$reg_sel .= '</select>';
												echo "regs[$parent_id]='$reg_sel<div id=\"after_reg_$parent_id\"></div>';";
												$parent_id=$l->parent_id;
												$reg_sel = '<div style="clear:both"></div><select style="width:210px" name="regions[]" id="reg_'.$l->parent_id.'" onchange="new_reg('.$parent_id.',this.value);">';
												$reg_sel .= '<option value=""> - - - </option>';		
											}	
											$reg_sel .= '<option value="'.$l->id.'">'.str_ireplace("'", "&apos;", $l->name).'</option>';
										}
										$reg_sel .= '</select>';	
										echo "regs[$parent_id]='$reg_sel<div id=\"after_reg_$parent_id\"></div>';";
										
										?>	
										var current=0;
										
										function new_reg(parent,a_parent){
											if(regs[a_parent]){
												//alert(cats[v]);	
												$('after_reg_'+parent).innerHTML = regs[a_parent]; 
												$('reg_'+parent).value=a_parent;
											}else{
												$('after_reg_'+parent).innerHTML = '';
												$('reg_'+parent).value=a_parent;		
											}
											
										}
										<?php echo $this->reg_path;?>
										</script>										
									</div>
								</div>	
								<div class="control-group">
									<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_ADDRESS');?></div>
									<div class="controls">
										<input class="text_area" type="text" name="address" id="address" size="50" maxlength="250" value="<?php echo $this->profile->address; ?>" />
									</div>
								</div>	
								<div class="control-group">
									<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_POSTCODE');?></div>
									<div class="controls">
										<input class="text_area" type="text" name="post_code" id="post_code" size="50" maxlength="250" value="<?php echo $this->profile->post_code; ?>" />
									</div>
								</div>	
								<div class="control-group">
									<div class="control-label"></div>
									<div class="controls">
										<span style="color:#666"><?php echo JText::_('COM_DJCLASSIFIEDS_LAT_LONG_LEAVE_BLANK_TO_GENERATE');?></span>
									</div>
								</div>	
								<div class="control-group">
									<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_LATITUDE');?></div>
									<div class="controls">
										<input class="text_area" type="text" name="latitude" id="latitude" size="50" maxlength="250" value="<?php echo $this->profile->latitude; ?>" />
									</div>
								</div>	
								<div class="control-group">
									<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_LONGITUDE');?></div>
									<div class="controls">
										<input class="text_area" type="text" name="longitude" id="longitude" size="50" maxlength="250" value="<?php echo $this->profile->longitude; ?>" />
									</div>
								</div>
								<div class="control-group">
									<div class="control-label"></div>
									<div class="controls">
											<fieldset class="adminform">
												<legend><?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION'); ?></legend>
													<div id="google_map_box" style="display:none;">
														 <div id='map' style='width: 470px; height: 400px; border: 1px solid #666;'>						  
														 </div>      
													</div>
											</fieldset>	
									</div>
								</div>
							</div>								
								
								
							<div class="tab-pane" id="profile_custom_fields">	
								<div id="fieldgroup_form" class="control-group">
									<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_FIELDSGROUP');?></div>
									<div class="controls">
										<select name="group_id" class="inputbox">
											<option value="0"><?php echo JText::_('COM_DJCLASSIFIEDS_ALL_GROUPS'); ?></option>
											<?php echo JHtml::_('select.options', $this->profile_groups, 'value', 'text', $this->profile->group_id);?>
										</select>					
									</div> 
								</div>
							
												
								<?php foreach($this->profile_fields as $fl){			
									echo '<div class="control-group" >';
										echo '<div class="control-label">'.$fl->label.'</div>';	
										echo '<div class="controls">';
											if($fl->type=="inputbox" || $fl->type=="link"){
												echo '<input class="inputbox" type="text" name="'.$fl->name.'" '.$fl->params; 
												if($id>0){
													echo ' value="'.htmlspecialchars($fl->value).'" '; 	
												}else{
													echo ' value="'.htmlspecialchars($fl->default_value).'" ';
												}
												echo ' />';					
											}else if($fl->type=="textarea"){
												echo '<textarea name="'.$fl->name.'" '.$fl->params.' />'; 
												if($id>0){
													echo htmlspecialchars($fl->value); 	
												}else{
													echo htmlspecialchars($fl->default_value);
												}
												echo '</textarea>';
									
											}else if($fl->type=="selectlist"){
												echo '<select name="'.$fl->name.'" '.$fl->params.' >';
													$val = explode(';', $fl->values);
														if($id>0){
															$def_value=$fl->value; 	
														}else{
															$def_value=$fl->default_value;
														}
												//		print_r($fl);die();
													for($i=0;$i<count($val);$i++){
														if($def_value==$val[$i]){
															$sel="selected";
														}else{
															$sel="";
														}
														echo '<option '.$sel.' value="'.$val[$i].'">'.$val[$i].'</option>';
													}
													
												echo '</select>';									
											}else if($fl->type=="radio"){						
												$val = explode(';', $fl->values);
												echo '<div class="radiofield_box" style="float:left">';
													for($i=0;$i<count($val);$i++){
														$checked = '';
														if($id>0){
															if($fl->value == $val[$i]){
																$checked = 'CHECKED';
															}									 	
														}else{
															if($fl->default_value == $val[$i]){
																$checked = 'CHECKED';
															}						
														}
														
														echo '<div style="float:left;"><input type="radio" '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'" /><span class="radio_label" style="margin:5px 0px 0 10px;">'.$val[$i].'</span></div>';
														echo '<div style="clear:both"></div>';
													}	
												echo '</div>';											
											}else if($fl->type=="checkbox"){						
												$val = explode(';', $fl->values);
												echo '<div class="radiofield_box" style="float:left">';
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
														
														echo '<div style="float:left;"><input type="checkbox" '.$checked.' value ="'.$val[$i].'" name="'.$fl->name.'[]" /><span class="radio_label" style="margin:5px 0px 0 10px;vertical-align:middle;">'.$val[$i].'</span></div>';
														echo '<div style="clear:both"></div>';
													}	
												echo '</div>';	
											
											}else if($fl->type=="date"){
												echo '<input class="inputbox djcalendar" type="text" size="10" maxlenght="19" id="'.$fl->name.'" name="'.$fl->name.'" '.$fl->params; 
												if($id>0){
													echo ' value="'.$fl->value_date.'" '; 	
												}else{
													if($fl->default_value=='current_date'){
														echo ' value="'.date("Y-m-d").'" ';
													}else{
														echo ' value="'.$fl->default_value.'" ';	
													}
													
												}
												echo ' />';
												echo ' <img class="calendar" src="components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="'.$fl->name.'button" />';					
											}else if($fl->type=="date_from_to"){
												echo '<input class="inputbox djcalendar" type="text" size="10" maxlenght="19" id="'.$fl->name.'" name="'.$fl->name.'" '.$fl->params; 
												if($id>0){
													echo ' value="'.$fl->value_date.'" '; 	
												}else{
													if($fl->default_value=='current_date'){
														echo ' value="'.date("Y-m-d").'" ';
													}else{
														echo ' value="'.$fl->default_value.'" ';	
													}
													
												}
												echo ' />';
												echo ' <img class="calendar" src="components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="'.$fl->name.'button" />';					
												
												echo '<span class="date_from_to_sep"> - </span>';
												
												echo '<input class="inputbox djcalendar" type="text" size="10" maxlenght="19" id="'.$fl->name.'_to" name="'.$fl->name.'_to" '.$fl->params;
												if($id>0){
													echo ' value="'.$fl->value_date_to.'" ';
												}else{
													if($fl->default_value=='current_date'){
														echo ' value="'.date("Y-m-d").'" ';
													}else{
														echo ' value="'.$fl->default_value.'" ';
													}
														
												}
												echo ' />';
												echo ' <img class="calendar" src="components/com_djclassifieds/assets/images/calendar.png" alt="calendar" id="'.$fl->name.'_tobutton" />';
												
											}
										echo '</div>';	
									echo '</div>';
							 	} 
							 	if(!count($this->profile_fields)){
									echo '<div>'.JText::_('COM_DJCLASSIFIEDS_NO_PROFILE_FIELDS_SPECIFIED');
										echo ' <a href="index.php?option=com_djclassifieds&view=fields">'.JText::_('COM_DJCLASSIFIEDS_ADD_CUSTOM_FIELDS').'</a>';
									echo '</div>';
								}
							 	?>																																																																		
							</div>
							<?php
								$tab_contents = $dispatcher->trigger('onAdminProfileEditTabContent', array ($this->jprofile,$this->profile_fields));
								if(count($tab_contents)){
									foreach($tab_contents as $tab_content){
										echo $tab_content;
									}
								}
							?>			
														
						</div>
					</fieldset>
				</div>
			</div>	
			<input type="hidden" name="user_id" value="<?php echo $id; ?>" />
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
		</form>


<script language="javascript" type="text/javascript">
	window.addEvent("load", function(){
		mapaStart();
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
	})	
	
	function getCities(region_id){
		var el = document.getElementById("city");
		var before = document.getElementById("city").innerHTML.trim();	
		
		if(region_id>0){
			el.innerHTML = '<img src="<?php echo JURI::base(); ?>components/com_djclassifieds/images/loading.gif" />';
			var url = 'index.php?option=com_djclassifieds&task=getCities&r_id=' + region_id;
				var myRequest = new Request({
					    url: 'index.php',
					    method: 'post',
						data: {
					      'option': 'com_djclassifieds',
					      'task': 'item.getCities',
						  'r_id': region_id			  
						  },
					    onRequest: function(){
					        //myElement.set('html', '<div style="text-align:center;"><img style="margin-top:10px;" src="<?php echo JURI::base().'components/'.JRequest::getString('option').'/images/long_loader.gif';?>" /><br />loading...</div>');
					    },
					    onSuccess: function(responseText){																
							el.innerHTML = responseText;						 	
					    },
					    onFailure: function(){
					        myElement.set('html', 'Sorry, your request failed, please contact to ');
					    }
					});
					myRequest.send();	
		}else{
			el.innerHTML='<?php echo JText::_('COM_DJCLASSIFIEDS_PLEASE_SELECT_REGION');?>';
		}
		
	}
						 	
	</script>



	 <script type='text/javascript'>    

		var map;
		var adLatlng = new google.maps.LatLng(<?php echo $this->profile->latitude.','.$this->profile->longitude; ?>);
	    var my_lat   = '<?php echo $this->profile->latitude; ?>';
	    var my_lng   = '<?php echo $this->profile->longitude; ?>';
		var marker = '';

	        var map_marker = new google.maps.InfoWindow();
	        var geokoder = new google.maps.Geocoder();
	        
			function addMarker(position,txt,icon)
			{
			    var MarkerOpt =  
			    { 
			        position: position,
			        draggable: true,
			        icon: icon,	 	
			        map: map
			    } 
			    var marker = new google.maps.Marker(MarkerOpt);
			    //marker.txt=txt;
			     
			    /*google.maps.event.addListener(marker,"click",function()
			    {
			        map_marker.setContent(marker.txt);
			        map_marker.open(map,marker);
			    });*/


		        google.maps.event.addListener(marker, 'dragend', function(event) {
		            latlng  = marker.getPosition();
		            my_lat     = latlng.lat();
		            my_lng     = latlng.lng();
		            document.getElementById('latitude').value   = my_lat;
		            document.getElementById('longitude').value  = my_lng;
		        });

				document.id('latitude').addEvent('change', function(){
					my_lat = this.value;
					map.setCenter(new google.maps.LatLng(my_lat,my_lng));
					coord   = new google.maps.LatLng(my_lat, my_lng);
				    marker.setPosition(coord);						
				});
				document.id('longitude').addEvent('change', function(){
					my_lng = this.value;			
					map.setCenter(new google.maps.LatLng(my_lat,my_lng));
					coord   = new google.maps.LatLng(my_lat, my_lng);
				    marker.setPosition(coord);	
				});
			    
			    return marker;
			}
			    	
			 function mapaStart()    
			 {   			

				window.addEvent('scroll', function(){
					google.maps.event.trigger(document.getElementById('map'), 'resize');
					map.setCenter(adLatlng); 	
					google.maps.event.trigger(marker,'click');	
				} );
				 
		    	document.getElementById("google_map_box").style.display='block';	    	
			    var opcjeMapy = {
			       zoom: <?php echo $par->get('gm_zoom','10'); ?>,
			  		center:adLatlng,
			  		mapTypeId: google.maps.MapTypeId.<?php echo $par->get('gm_type','ROADMAP'); ?>,
			  		navigationControl: true
			    };
			    map = new google.maps.Map(document.getElementById("map"), opcjeMapy);
			    <?php	             	
	                 $icon_size = getimagesize(JPATH_ROOT.'/components/com_djclassifieds/assets/images/djcf_gmicon.png');
	                 $icon_img = str_ireplace('administrator/','', JURI::base())."components/com_djclassifieds/assets/images/djcf_gmicon.png";
	                 
	                 //$icon_img = ''; 
	                 if($icon_img && is_array($icon_size)){ 
	                 	 $anchor_w = $icon_size[0]/2;?>
			             var size = new google.maps.Size(<?php echo $icon_size[0].','.$icon_size[1];?>);
			             var start_point = new google.maps.Point(0,0);
			             var anchor_point = new google.maps.Point(<?php echo $anchor_w.','.$icon_size[1];?>);   
			             var icon = new google.maps.MarkerImage("<?php echo $icon_img;?>", size, start_point, anchor_point);                
	                <?php }else{ ?>
	              		 var icon = '';  	
	                <?php }?>
	                
		    	//marker = addMarker(adLatlng,'<?php echo addslashes(nl2br($marker_txt)); ?>',icon);
		    	marker = addMarker(adLatlng,'',icon);
				//google.maps.event.trigger(marker,'click');	      
			 }

	</script>
<?php echo '<div style="clear:both"></div>'.DJCFFOOTER; ?>