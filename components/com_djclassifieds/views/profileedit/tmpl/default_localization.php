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

//jimport('joomla.media.images');
JHTML::_('behavior.framework','More');
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.modal');
$toolTipArray = array('className'=>'djcf_label');
JHTML::_('behavior.tooltip', '.Tips1', $toolTipArray);


$par 	  = JComponentHelper::getParams( 'com_djclassifieds' );
$app 	  = JFactory::getApplication();
$user 	  = JFactory::getUser();
$document = JFactory::getDocument();
$config   = JFactory::getConfig();

DJClassifiedsTheme::includeMapsScript();			

$map_styles = $par->get('gm_styles','');
if (trim($map_styles) == '') {
	$map_styles = '[]';
}

?>

	
		
        <?php if(count($this->regions) && $par->get('show_regions','1') && $par->get('profile_regions','0')){?>
    		<div class="djform_row">
                <?php if($par->get('show_tooltips_newad','0')){ ?>
	            	<label class="label Tips1" for="reg_0" id="reg_0-lbl" title="<?php echo JTEXT::_('COM_DJCLASSIFIEDS_LOCALIZATION_TOOLTIP')?>">
	                    <?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION');?> *
	                    <img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/tip.png" alt="?" />
	                </label>	                               			                	
				<?php }else{ ?>
	            	<label class="label" for="reg_0" id="cat_0-lbl">
	                	  <?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION'); ?> *					
	                </label>
            	<?php } ?>
                <div class="djform_field" id="locations_list">
	                <?php	               		     	                    
							$reg_sel = '<select autocomplete="off" id="reg_0" class="required" style="width:210px" name="regions[]" onchange="new_reg(0,this.value,new Array());"><option value="">'.JText::_('COM_DJCLASSIFIEDS_SELECT_LOCALIZATION').'</option>';
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
							
							?><div class="clear_both"></div>
							<div id="after_reg_0"></div>
							<script type="text/javascript">
								var regs=new Array();
									
							var current=0;
							
							function new_reg(parent,a_parent, r_path){

								  	var myRequest = new Request({
									    url: '<?php echo JURI::base()?>index.php',
									    method: 'post',
										data: {
									      'option': 'com_djclassifieds',
									      'view': 'additem',
									      'task': 'getRegionSelect',
										  'reg_id': a_parent				  
										  },
									    onRequest: function(){
									    	document.id('after_reg_'+parent).innerHTML = '<div style="text-align:center;"><img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/loading.gif" alt="..." /></div>';
									    	},
									    onSuccess: function(responseText){																
									    	if(responseText){	
												document.id('after_reg_'+parent).innerHTML = responseText; 
												document.id('reg_'+parent).value=a_parent;
											}else{
												document.id('after_reg_'+parent).innerHTML = '';
												document.id('reg_'+parent).value=a_parent;		
											}	
											if(r_path != 'null'){
												if(r_path.length>0){
													var first_path = r_path[0].split(',');												
													r_path.shift();
													new_reg(first_path[0],first_path[1],r_path);												
												}
											}
									    },
									    onFailure: function(){}
									});
									myRequest.send();	

								
								/*if(regs[a_parent]){
									//alert(cats[v]);	
									document.id('after_reg_'+parent).innerHTML = regs[a_parent]; 
									document.id('reg_'+parent).value=a_parent;
								}else{
									document.id('after_reg_'+parent).innerHTML = '';
									document.id('reg_'+parent).value=a_parent;		
								}*/
								
							}
							
							<?php if(count($this->reg_path)){
								$r_path = $this->reg_path;
								echo 'var reg_path = new Array();';
								$reg_path_f = 'new_reg(';
								for($r=count($r_path)-1;$r>-1;$r--){
									if($r<count($r_path)-1){
										$ri = count($r_path) - $r -2;
										echo "reg_path[$ri]='$r_path[$r]';";
									}else{
										$reg_path_f .= $r_path[$r];
									}
								}			
								
								echo $reg_path_f.',reg_path);';																
							}?>
						</script>						
                </div>
                <div class="clear_both"></div>
            </div>  
            <?php }else{ ?>
            	<input type="hidden" name="regions[]" value="0" />
            <?php } ?>
            <?php if($par->get('show_address','1') && $par->get('profile_address','0')){?>
	            <div class="djform_row">
	                <?php if($par->get('show_tooltips_newad','0')){ ?>
		            	<label class="label Tips1" title="<?php echo JTEXT::_('COM_DJCLASSIFIEDS_ADDRESS_TOOLTIP')?>">
		                    <?php echo JText::_('COM_DJCLASSIFIEDS_ADDRESS');?>
		                    <img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/tip.png" alt="?" />
		                </label>	                               			                	
					<?php }else{ ?>
		            	<label class="label">
		                	  <?php echo JText::_('COM_DJCLASSIFIEDS_ADDRESS'); ?>					
		                </label>
	            	<?php } ?>
	                <div class="djform_field">
	                    <input class="text_area" type="text" name="address" id="address" size="50" maxlength="250" value="<?php echo $this->profile->address; ?>" />
	                </div>
	                <div class="clear_both"></div> 
	            </div>
            <?php } ?>      
            
            <?php if($par->get('show_postcode','0') && $par->get('profile_postcode','0')){?>
            	<div class="djform_row">
            		<?php if($par->get('show_tooltips_newad','0')){ ?>
		            	<label class="label Tips1" title="<?php echo JTEXT::_('COM_DJCLASSIFIEDS_POSTCODE_TOOLTIP')?>">
		                    <?php echo JText::_('COM_DJCLASSIFIEDS_POSTCODE');?>
		                    <img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/tip.png" alt="?" />
		                </label>	                               			                	
					<?php }else{ ?>
		            	<label class="label">
		                	  <?php echo JText::_('COM_DJCLASSIFIEDS_POSTCODE'); ?>					
		                </label>
	            	<?php } ?>
	                <div class="djform_field">
	                    <input class="text_area" type="text" name="post_code" id="post_code" size="50" maxlength="250" value="<?php echo $this->profile->post_code; ?>" />
	                </div>
	                <div class="clear_both"></div> 
	            </div>             
            <?php
			 }       
			if($par->get('allow_user_lat_lng','0') && $par->get('profile_regions','0')){ ?>
            	<div class="djform_row" <?php if($par->get('allow_user_lat_lng','0')==2){echo 'style="display:none;"';} ?> >
            		<?php if($par->get('show_tooltips_newad','0')){ ?>
		            	<label class="label Tips1" title="<?php echo JTEXT::_('COM_DJCLASSIFIEDS_LATITUDE_TOOLTIP')?>">
		                    <?php echo JText::_('COM_DJCLASSIFIEDS_LATITUDE');?>
		                    <img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/tip.png" alt="?" />		                    
		                </label>	                               			                	
					<?php }else{ ?>
		            	<label class="label">
		                	  <?php echo JText::_('COM_DJCLASSIFIEDS_LATITUDE'); ?>			
		                </label>
	            	<?php } ?>
	                <div class="djform_field">
	                    <input class="text_area" type="text" name="latitude" id="latitude" size="50" maxlength="250" value="<?php echo $this->profile->latitude; ?>" />
	                </div>
	                <div class="clear_both"></div> 
	            </div>   
	            <div class="djform_row" <?php if($par->get('allow_user_lat_lng','0')==2){echo 'style="display:none;"';} ?> >
            		<?php if($par->get('show_tooltips_newad','0')){ ?>
		            	<label class="label Tips1" title="<?php echo JTEXT::_('COM_DJCLASSIFIEDS_LONGITUDE_TOOLTIP')?>">
		                    <?php echo JText::_('COM_DJCLASSIFIEDS_LONGITUDE');?>
		                    <img src="<?php echo JURI::base(true) ?>/components/com_djclassifieds/assets/images/tip.png" alt="?" />
		                </label>	                               			                	
					<?php }else{ ?>
		            	<label class="label">
		                	  <?php echo JText::_('COM_DJCLASSIFIEDS_LONGITUDE'); ?>			
		                </label>
	            	<?php } ?>
	                <div class="djform_field">
	                    <input class="text_area" type="text" name="longitude" id="longitude" size="50" maxlength="250" value="<?php echo $this->profile->longitude; ?>" />
	                </div>
	                <div class="clear_both"></div> 
	            </div>	            
	            <div class="djform_row">
	            	<div class="djmap_intro">
	            		<div class="djmap_intro_desc"><?php echo JText::_('COM_DJCLASSIFIEDS_ADDITEM_SELECT_ON_MAP'); ?></div>
	            		<span class="button" type="button" id="map_use_my_location"><?php echo JText::_('COM_DJCLASSIFIEDS_USE_MY_LOCATION')?></span>
	            		<?php if($par->get('show_address','1')){?>
	            			<span class="button" type="button" id="map_update_latlng"><?php echo JText::_('COM_DJCLASSIFIEDS_UPDATE_USING_ADDRESS')?></span>
	            		<?php } ?>									
	            		<div class="clear_both"></div>
	            		<div id="mapalert"><?php echo JText::_('COM_DJCLASSIFIEDS_WE_CANT_FIND_COORDS_ON_ADDRESS');?></div>
	            	</div>
					<div id="djmap" style="width:100%;height:300px;"></div>
	            </div>
            <?php
			 }       ?>
			 
			 
             

<script type="text/javascript">	
	
	function getCities(region_id){
		var el = document.getElementById("city");
		var before = document.getElementById("city").innerHTML.trim();	
		
		if(region_id>0){
			el.innerHTML = '<img src="<?php echo JURI::base(); ?>components/com_djclassifieds/assets/images/loading.gif" />';
			var url = '<?php echo JURI::base()?>index.php?option=com_djclassifieds&view=additemtask=getCities&r_id=' + region_id <?php if($this->profile->id){echo "+ '&id='+".$this->profile->id;} ?>;
				var myRequest = new Request({
					    url: '<?php echo JURI::base()?>index.php',
					    method: 'post',
						data: {
					      'option': 'com_djclassifieds',
					      'view': 'additem',
					      'task': 'getCities',
						  'r_id': region_id
						  <?php if($this->profile->id){echo ",'id':'".$this->profile->id."'";} ?>					  
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



	<?php if($par->get('allow_user_lat_lng','0') && $par->get('profile_regions','0')){ 
		if(($id || $token) && $this->profile->latitude && $this->profile->longitude){
			$lat = $this->profile->latitude;
			$lon = $this->profile->longitude;
		}else if(isset($_COOKIE["djcf_latlon"])) {
			$lat_lon = explode('_', $_COOKIE["djcf_latlon"]);
			$lat = $lat_lon[0];
			$lon = $lat_lon[1];
		}else{
			$loc_coord = DJClassifiedsGeocode::getLocation($par->get('map_lat_lng_address','England, London'));
			if(is_array($loc_coord)){
				$lat = $loc_coord['lat'];
				$lon = $loc_coord['lng'];
			}else{
				$lat = '';
				$lon = '';
			}
		}				
		$gm_scrollwheel = ($par->get('gm_scrollwheel','1')? 'true' : 'false');
		?>	
	    var map         = null;
	    var marker      = null;
	    var start_lat   = '<?php echo $lat; ?>';
	    var start_lon   = '<?php echo $lon; ?>';
	    var start_zoom  = <?php echo $par->get('gm_zoom','10'); ?>;
		var my_lat = start_lat;
		var my_lng = start_lon;
		var geokoder = new google.maps.Geocoder();
		var scrollw = <?php echo $gm_scrollwheel; ?>;
		
	    function initDjMap() {
	        //var zoom    = 13;
	        var coord   = new google.maps.LatLng(start_lat, start_lon);
	
	        var mapoptions = {
	            zoom: start_zoom,
	            scrollwheel: scrollw,
	            center: coord,
	            mapTypeControl: true,
	            navigationControl: true,
	            zoomControl: true,        
	            mapTypeId: google.maps.MapTypeId.ROADMAP,
	            styles: <?php echo $map_styles; ?>
	        }
	
	        // create the map
	        map = new google.maps.Map(document.getElementById('djmap'),mapoptions);
	
	        marker  = new google.maps.Marker({
	            position: coord,
	            draggable: true,
	            visible: true,
	            clickable: false,
	            map: map
	        });
	
	        google.maps.event.addListener(marker, 'dragend', function(event) {
	            latlng  = marker.getPosition();
	            my_lat     = latlng.lat();
	            my_lng     = latlng.lng();
	            document.getElementById('latitude').value   = my_lat;
	            document.getElementById('longitude').value  = my_lng;
	        });
	
	        google.maps.event.trigger(map, 'resize');
			map.setCenter(new google.maps.LatLng(my_lat,my_lng));
			
			map.setZoom( map.getZoom() );
	
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
			document.id('map_use_my_location').addEvent('click', function(){
			  if(navigator.geolocation){
				  navigator.geolocation.getCurrentPosition(showDJPosition);
			   }else{
				   x.innerHTML="<?php echo JText::_('COM_DJCLASSIFIEDS_GEOLOCATION_IS_NOT_SUPPORTED_BY_THIS_BROWSER');?>";}
			});
			<?php if($par->get('show_address','1')){?>
				document.id('map_update_latlng').addEvent('click', function(){
					updateLatLngFromAddress();
				});		
			<?php } ?>									
			<?php if(!$id && $par->get('show_address','1')){ ?>
				document.id('address').addEvent('change', function(){
					updateLatLngFromAddress();
				});
			<?php } ?>													
		}
		
	    function showDJPosition(position){
		  	var exdate=new Date();
		  	exdate.setDate(exdate.getDate() + 1);
			var ll = position.coords.latitude+'_'+position.coords.longitude;
		  	document.cookie = "djcf_latlon=" + ll + "; expires=" + exdate.toUTCString();
		  	my_lat = position.coords.latitude;
		  	my_lng = position.coords.longitude;				
		  	document.getElementById('latitude').value   = my_lat;
            document.getElementById('longitude').value  = my_lng;
			map.setCenter(new google.maps.LatLng(my_lat,my_lng));
			var coord   = new google.maps.LatLng(my_lat, my_lng);
		    marker.setPosition(coord);	
	  	}

	  	function updateLatLngFromAddress(){
		  	var address = '';
		  	$$(document.getElementsByName('regions[]')).each(function(el){
		  		if(el.value!=0){address = address+el.getSelected().get('text')+', ';}
		  	});
		  	address = address+document.id('address').value;
		  		geokoder.geocode(
				  	{address: address}, 
				  	function (results, status){
					    if(status == google.maps.GeocoderStatus.OK){
					    	my_lat = results[0].geometry.location.lat();
						  	my_lng = results[0].geometry.location.lng();				
						  	document.getElementById('latitude').value   = my_lat;
				            document.getElementById('longitude').value  = my_lng;
							map.setCenter(new google.maps.LatLng(my_lat,my_lng));
							var coord   = new google.maps.LatLng(my_lat, my_lng);
						    marker.setPosition(coord);	
						}else{
							document.id('mapalert').setStyle('display','block');
					      	(function() {
							    document.id('mapalert').setStyle('display','none');
							  }).delay(5000);   
						}
				});
		}
    <?php } ?>	    
	
	window.addEvent("load", function(){
		<?php if($par->get('allow_user_lat_lng','0') && $par->get('profile_regions','0')){ ?>
		initDjMap();
		<?php } ?>	
	});

</script>