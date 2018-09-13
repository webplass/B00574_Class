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
$document= JFactory::getDocument();
$config = JFactory::getConfig();

if($par->get('show_googlemap')==1){
	DJClassifiedsTheme::includeMapsScript();		
}

$map_styles = $par->get('gm_styles','');
if (trim($map_styles) == '') {
	$map_styles = '[]';
}


if(@$this->profile['details']->address || @$this->profile['details']->latitude){?>

<div class="profile_row row_location">
	<span class="profile_row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION'); ?></span>
	<span class="profile_row_value"  >
		<div class="" >
			<span class="row_value" style="display:inline-block;">
				<?php if($this->address){ ?>
					<span class="row_value_addr row_value_addr1">
						<?php echo $this->address; ?>
					</span>
				<?php } ?>
				
				<?php if($this->profile['details']->address){ ?>
					<span class="row_value_addr row_value_addr2">
						<?php 
						echo $this->profile['details']->address;
						if($par->get('show_postcode','0')){
							if($this->profile['details']->post_code){
								echo ', '.$this->profile['details']->post_code;
							}				
						}?>
					</span>					
				<?php } ?>
			</span>
		</div> 
		<?php if($par->get('show_googlemap')==1 && $this->profile['details']->latitude && $this->profile['details']->longitude){?>
			<div id="google_map_box" style="display:none;">
				 <div id='map' style='width: 100%; height: 210px;'></div>				 							      
			</div>	
		<?php }	?>		
	</div>
	<script type="text/javascript">
		<?php if($par->get('show_googlemap')==1 && $this->profile['details']->latitude && $this->profile['details']->longitude){?>
		window.addEvent('load', function(){ 
			mapaStart();
		});
	        var map;
	        var map_marker = new google.maps.InfoWindow();
	        var geokoder = new google.maps.Geocoder();
	        
			function addMarker(position,txt,icon)
			{
			    var MarkerOpt =  
			    { 
			        position: position, 
			        icon: icon,	
			        map: map
			    } 
			    var marker = new google.maps.Marker(MarkerOpt);
			    marker.txt=txt;
			     
			    google.maps.event.addListener(marker,"click",function()
			    {
			        map_marker.setContent(marker.txt);
			        map_marker.open(map,marker);
			    });
			    return marker;
			}
			    	
			 function mapaStart()    
			 {   		 	 
	             <?php
	             	 $icon_img = ''; 
					 $icon_size='';
					if($par->get('gm_icon',1)==1 && file_exists(JPATH_BASE.'/images/djcf_gmicon.png')){
	        			 $icon_size = getimagesize(JPATH_BASE.'/images/djcf_gmicon.png');
	                	 $icon_img = JURI::base()."images/djcf_gmicon.png";
	                 }elseif($par->get('gm_icon',1)==1){ 
	                	 $icon_size = getimagesize(JPATH_BASE.'/components/com_djclassifieds/assets/images/djcf_gmicon.png');
	                	 $icon_img = JURI::base()."components/com_djclassifieds/assets/images/djcf_gmicon.png";
	                 }
	                 //$icon_img = ''; 
	                 if($icon_img && is_array($icon_size)){ 
	                 	 $anchor_w = $icon_size[0]/2;?>
			             var size = new google.maps.Size(<?php echo $icon_size[0].','.$icon_size[1];?>);
			             var start_point = new google.maps.Point(0,0);
			             var anchor_point = new google.maps.Point(<?php echo $anchor_w.','.$icon_size[1];?>);   
			             var icon = new google.maps.MarkerImage("<?php echo $icon_img;?>", size, start_point, anchor_point);                
	                <?php }else{ ?>
	              		 var icon = '';  	
	                <?php }	                
	                $gm_scrollwheel = ($par->get('gm_scrollwheel','1')? 'true' : 'false');
	                ?>
	                
	             	
				<?php if($this->profile['details']->latitude!='0.000000000000000' && $this->profile['details']->longitude!='0.000000000000000'){ ?>
					document.getElementById("google_map_box").style.display='block';
					var adLatlng = new google.maps.LatLng(<?php echo $this->profile['details']->latitude.','.$this->profile['details']->longitude; ?>);
					var scrollw = <?php echo $gm_scrollwheel; ?>;
					    var MapOptions = {
					        zoom: <?php echo $par->get('gm_zoom','10'); ?>,
					    	scrollwheel: scrollw,
					  		center: adLatlng,
					  		mapTypeId: google.maps.MapTypeId.<?php echo $par->get('gm_type','ROADMAP'); ?>,
					  		navigationControl: true,
					  		styles: <?php echo $map_styles; ?>
					    };
					    map = new google.maps.Map(document.getElementById("map"), MapOptions); 				   
				    	var marker = addMarker(adLatlng,'<?php echo addslashes($marker_txt); ?>',icon);
				<?php }else{ ?>
					var adres = '<?php echo $this->country;if($this->city!='' ){echo ", ".str_ireplace("'", "&apos;",$this->city);} if($this->profile['details']->address!='' ){echo ", ".str_ireplace("'", "&apos;",$this->profile['details']->address);}?>';
					var scrollw = <?php echo $gm_scrollwheel; ?>;
					geokoder.geocode({address: adres}, function (results, status)
					{
					    if(status == google.maps.GeocoderStatus.OK)
					    {
					    	document.getElementById("google_map_box").style.display='block';
						    var MapOptions = {
						        zoom: <?php echo $par->get('gm_zoom','10'); ?>,
						        scrollwheel: scrollw,
						  		center: results[0].geometry.location,
						  		mapTypeId: google.maps.MapTypeId.ROADMAP,
						  		navigationControl: true,
						  		styles: <?php echo $map_styles; ?>
						    };
						    map = new google.maps.Map(document.getElementById("map"), MapOptions); 
					    	var marker = addMarker(results[0].geometry.location,'',icon);
					    }
					});		
				<?php } ?>      
			 }

		<?php }?>
		</script>
<?php } ?>