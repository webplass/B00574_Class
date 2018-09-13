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
$config = JFactory::getConfig();

	$document= JFactory::getDocument();
	
	$par = JComponentHelper::getParams( 'com_djclassifieds' );
	$gm_cat_icons=array();
	if($par->get('gm_icon',1)==1){
		if(file_exists(JPATH_BASE.'/images/djcf_gmicon.png')){
			$gm_icon_default = JURI::base().'/images/djcf_gmicon.png';
			$icon_size = getimagesize(JPATH_BASE.'/images/djcf_gmicon.png');
			$gm_icon_default_w = $icon_size[0];
			$gm_icon_default_h = $icon_size[0];
			$gm_icon_default_a = $icon_size[0]/2;  	
		}else{
			$gm_icon_default = JURI::base().'components/com_djclassifieds/assets/images/djcf_gmicon.png';
			$icon_size = getimagesize(JPATH_BASE.'/components/com_djclassifieds/assets/images/djcf_gmicon.png');
			$gm_icon_default_w = $icon_size[0];
			$gm_icon_default_h = $icon_size[0];
			$gm_icon_default_a = $icon_size[0]/2;	
		}		
		

		if ($handle = opendir(JPATH_BASE.'/images/')) {		
		    while (false !== ($icon_f = readdir($handle))) {
		    	if(strstr($icon_f, 'djcf_gmicon')){
		    		$icon_n = (int)str_ireplace('djcf_gmicon_','', $icon_f);
					if($icon_n>0){
						$icon_size = getimagesize(JPATH_BASE.'/images/'.$icon_f);
						$gm_cat_icons[$icon_n] = array();
						$gm_cat_icons[$icon_n]['img'] = JURI::base().'images/'.$icon_f;
						$gm_cat_icons[$icon_n]['width'] = $icon_size[0];
						$gm_cat_icons[$icon_n]['height'] = $icon_size[1];
						
					}
		    	}
		    }		
		    closedir($handle);
		}
    }else{ 
  		$gm_icon_default = '';  	
    }
    
    $map_styles = $params->get('gm_styles','');    
    
?>
<div class="dj_cf_maps">
	<div id="djmod_map_box<?php echo $module->id;?>" style="display:none;">
		 <div id='djmod_map<?php echo $module->id;?>' class="djmod_map" style='width: <?php echo $params->get('map_width');?>; height: <?php echo $params->get('map_height');?>; border: 1px solid #666; '>						  
		 </div>      
	</div>
</div>

<script type="text/javascript">


window.addEvent('domready', function(){
	
		djmodMapaStart<?php echo $module->id;?>();
		
		
		
});
	

	     var djmod_map<?php echo $module->id;?> = new BMap.Map("djmod_map<?php echo $module->id;?>");
         //var djmod_map_marker<?php echo $module->id;?> = new google.maps.InfoWindow();
         var djmod_geokoder<?php echo $module->id;?> = new BMap.Geocoder();
         var items_address = new Array();
		 var items_desc = new Array();
         
		function djmodAddMarker(position,icon,title,txt)
		{	
					
			var mkr = new BMap.Marker(position, {
					    icon: icon,
					    title: title
					});
			//djmod_map<?php echo $module->id;?>.addOverlay(mkr);
			var info = new BMap.InfoWindow(txt);
			mkr.addEventListener("click", function(){
			    this.openInfoWindow(info);
			});

			djmod_map<?php echo $module->id;?>.addOverlay(mkr);
			
		    return mkr;
		}
		
		    	
		 function djmodMapaStart<?php echo $module->id;?>()    
		 {   		 

			djmod_geokoder<?php echo $module->id;?>.getPoint('<?php echo $params->get('start_address');?>', function (point)
			{
				 
			    if(point){			    
				 document.getElementById("djmod_map_box<?php echo $module->id;?>").style.display='block';
				 	<?php if($center_coords){ ?>
		 				var map_center = new BMap.Point($center_coords['lng'], $center_coords['lat']) ;
				 	<?php }else if($advert){ ?>
				 		var map_center = new BMap.Point($advert->longitude, $advert->latitude);
					<?php }else{ ?>
						var map_center = point;
					<?php } ?>
				 	 
				     djmod_map<?php echo $module->id;?>.centerAndZoom( map_center , 10) ; 
				     <?php if($params->get('enable_scrolling','true')){?>
				     	djmod_map<?php echo $module->id;?>.enableScrollWheelZoom();
				     <?php } ?>	
				     
					 var size = new BMap.Size(32,32); 

					<?php 
					$ia=0;
					foreach($items as $item){			
						if(!$item->alias){
							$item->alias = DJClassifiedsSEO::getAliasName($item->name);
						}
						if(!$item->c_alias){
							$item->c_alias = DJClassifiedsSEO::getAliasName($item->c_name);					
						}
						if(isset($gm_cat_icons[$item->cat_id])){
							
							$gm_icon_a = $gm_cat_icons[$item->cat_id]['width']/2; ?>
			                var size = new BMap.Size(<?php echo $gm_cat_icons[$item->cat_id]['width'].','.$gm_cat_icons[$item->cat_id]['height'];?>);
		             		var anchor_point = new BMap.Size(<?php echo $gm_icon_a.','.$gm_cat_icons[$item->cat_id]['height'];?>);
		             		var info_anchor_point = new BMap.Size(10, 0);
		             		
		             		var icon = new BMap.Icon('<?php echo $gm_cat_icons[$item->cat_id]['img'];?>',size , {
									    anchor: anchor_point,
									    infoWindowAnchor: info_anchor_point
									});
		             		 
						<?php }else if($gm_icon_default){?>

		             		var size = new BMap.Size(<?php echo $gm_icon_default_w.','.$gm_icon_default_h;?>);
		             		var anchor_point = new BMap.Size(<?php echo $gm_icon_default_a.','.$gm_icon_default_h;?>);
		             		var info_anchor_point = new BMap.Size(10, 0);
		             		
		             		var icon = new BMap.Icon('<?php echo $gm_icon_default;?>',size , {
									    anchor: anchor_point,
									    infoWindowAnchor: info_anchor_point
									});		             		
		             		
						<?php }else{ ?>
							var icon = '';
						<?php } ?>										
							<?php								
				    			$marker_txt = '<div style="width:200px;margin-bottom:0px"><div style="margin-bottom:5px;">';
								$marker_txt .= '<a style="text-decoration:none !important;" href="'.JRoute::_(DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias,$item->region_id.':'.$item->r_name)).' ">';
									if(count($item->images)){																		
										$marker_txt .= '<img style="float:left;margin:5px 10px 0 0;"  width="60px" src="'.JURI::base().$item->images[0]->thumb_s.'" /> ';									
									}									
				    				$marker_txt .= '<strong>'.addslashes($item->name).'</strong><br />';
									$marker_txt .= '<span style="color:#333333">'.addslashes(str_replace(array("\n","\r","\r\n"), '',$item->intro_desc)).'</span>';																						
								$marker_txt .='</a></div></div>';
								
									if($item->latitude!='0.000000000000000' && $item->longitude!='0.000000000000000'){ 
										$item_lat =$item->latitude;
										$item_long =$item->longitude;
										if(isset($items_ll[$item_lat.'_'.$item_long])){
											$item_lat += (rand(10000,99999)/100000 - 0.6)/1000;
											$item_long += (rand(10000,99999)/100000 - 0.6)/1000;
										}
										$items_ll[$item_lat.'_'.$item_long] = 1;										
										?>
										var adLatlng = new BMap.Point(<?php echo $item_long.','.$item_lat; ?>);
										djmodAddMarker(adLatlng,icon,'<?php echo addslashes($item->name); ?>','<?php echo $marker_txt; ?>');
									<?php }else{ ?>
										//items_address[<?php echo $ia; ?>]='<?php echo $item->country.", ".$item->city; if($item->address!='' ){echo ", ".$item->address;}?>';				    			
				    					//items_desc[<?php echo $ia; ?>]='<?php echo $marker_txt; ?>';
				    				<?php $ia++;			
									 }?>
							
				    	<?php } ?>																														    
			    	}else{
						console.log('Wrong start address');
				    }
				});
			
		    
		    }  
			<?php /*
					if($params->get('start_geoloc','0')==1){ ?>	
					window.addEvent('domready', function(){				
						if(navigator.geolocation){
							navigator.geolocation.getCurrentPosition(modSearchShowDJPosition<?php echo $module->id;?>,
							 function(error){
								 //alert("<?php echo str_ireplace('"', "'",JText::_(''));?>");
								 console.log(error);         			
							}, {
								timeout: 30000, enableHighAccuracy: true, maximumAge: 90000
							});
						} 	
						function modSearchShowDJPosition<?php echo $module->id;?>(position){
							var userLatlng = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
							djmod_map<?php echo $module->id;?>.setCenter(userLatlng);													  
					  	}
					});
				<?php } */ ?>



</script>
