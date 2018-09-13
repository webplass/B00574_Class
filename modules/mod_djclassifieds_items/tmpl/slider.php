<?php
/**
 * @version 2.0
 * @package DJ Classifieds Menu Module
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

defined ('_JEXEC') or die('Restricted access');

$cols = $params->get('columns_nr','1');
$items_in_col =ceil (count($items) / $cols);
$col_nr = 1;
$item_c = 0;
$last_row = count($items)%$cols;
$items_in_lr= $last_row;
$document= JFactory::getDocument();

$fav_a	= 0;
if($cfpar->get('favourite','1') && $params->get('show_fav_icon','0')==1){
	$fav_a	= 1;
}

$slide_dir='left';
 	if($document->direction=='rtl'){
 		$slide_dir='right';
	}else if (isset($_COOKIE["jmfdirection"])){
		if($_COOKIE["jmfdirection"]=='rtl'){
			$slide_dir='right';	
		}
	}else if (isset($_COOKIE["djdirection"])){
		if($_COOKIE["djdirection"]=='rtl'){
			$slide_dir='right';
		}
	}
?>
<div id="mod_djcf_slider<?php echo $module->id;?>" class="mod_djclassifieds_items mod_djcf_slider clearfix">
	<div class="djcf_slider_left blocked" id="mod_djcf_slider_left<?php echo $module->id;?>">&nbsp;</div>
	<div class="djcf_slider_loader" id="mod_djcf_slider_loader<?php echo $module->id;?>" ><div class="djcf_slider_loader_img" ></div></div>
	<div class="items-outer">
		<div class="items items-cols<?php echo $cols; ?>">
			<div class="items-content" id="items-content<?php echo $module->id;?>">
			<?php	
			foreach($items as $i){ ?>
				<div class="item-box">
					<div class="item-box-in">
						<?php 
						if(!$i->alias){
							$i->alias = DJClassifiedsSEO::getAliasName($i->name);
						}
						if(!$i->c_alias){
							$i->c_alias = DJClassifiedsSEO::getAliasName($i->c_name);
						}

						$item_c++;

						$item_class='';
						if($i->promotions){
							$item_class .=' promotion '.str_ireplace(',', ' ', $i->promotions);
						}
						
						if($i->published==2){
							$row .=' item_archived';
						}

						$icon_fav=0;
						if($user->id>0 && $fav_a){
							if($i->f_id){
								$icon_fav=1;
								$item_class .= ' item_fav';
							}
						}
						
						echo '<div class="item'.$item_class.'">';
						echo '<div class="title">';
						if($params->get('show_img')==1){
							if(count($i->images)){			
								echo '<a class="title_img" href="'.JRoute::_(DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name)).'">';
									$img_width = ($params->get('img_width','') ? ' width="'.$params->get('img_width','').'px" ' : '');
									$img_height = ($params->get('img_height','') ? ' height="'.$params->get('img_height','').'px" ' : '');
									if($params->get('img_type','ths')=='thm'){
										$thumb = $i->images[0]->thumb_m;
									}else if($params->get('img_type','ths')=='thb'){
										$thumb = $i->images[0]->thumb_b;
									}else{
										$thumb = $i->images[0]->thumb_s;
									}
									echo '<img '.$img_width.$img_height.' style="margin-right:3px;" src="'.JURI::base().$thumb.'" alt="'.str_ireplace('"', "'", $i->name).'" title="'.$i->images[0]->caption.'" />';
								echo '</a>';														
							}else if($params->get('show_default_img','0')>0){
								echo '<a class="title_img" href="'.JRoute::_(DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name)).'">';
								$show_cat_icon = false;
								if(isset($cat_images[$i->cat_id])){
									if($cat_images[$i->cat_id]->name){$show_cat_icon = true;}
								}
								if($params->get('show_default_img','0')==2 && $show_cat_icon){
									echo '<img style="margin-right:3px;" src="'.JURI::base(true).$cat_images[$i->cat_id]->path.$cat_images[$i->cat_id]->name.'_ths.'.$cat_images[$i->cat_id]->ext.'" alt="'.str_ireplace('"', "'", $i->name).'" title="'.$cat_images[$i->cat_id]->caption.'" />';
								}else{
									echo '<img style="margin-right:3px;" src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/no-image.png" alt="'.str_ireplace('"', "'", $i->name).'" />';
								}
								echo '</a>';
							}
							
						}

						if($params->get('show_title','1')==1){
							$title_c = $params->get('char_title_nr',0);
							if($title_c>0 && strlen($i->name)>$title_c){
								$i->name = mb_substr($i->name, 0, $title_c,'utf-8').' ...';
							}
							echo '<a class="title" href="'.JRoute::_(DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name)).'">'.$i->name.'</a>';
						}
						
						if($fav_a){
							if($user->id>0){
								echo '<span class="mfav_box" data-id="'.$i->id.'">';
								if($i->f_id){
									echo '<span class="fav_icon_link fav_icon fav_icon_a" >';
									//echo '<span class="fav_icon fav_icon_a"></span>';
									//echo '<span class="nfav_label">'.JText::_('COM_DJCLASSIFIEDS_FAVOURITE').'</span>';
									echo '</span>';
								}else{
									echo '<span class="fav_icon_link fav_icon fav_icon_na" >';
									//echo '<span class="fav_icon fav_icon_na"></span>';
									//echo '<span class="nfav_label">'.JText::_('COM_DJCLASSIFIEDS_ADD_TO_FAVOURITES').'</span>';
									echo '</span>';
								}
								echo '</span>';
							}else{
								echo '<span class="mfav_box" data-id="'.$i->id.'">';
								echo '<a href="index.php?option=com_djclassifieds&view=item&task=addFavourite&cid='.$i->cat_id.'&id='.$i->id.'" class="fav_icon_link fav_icon fav_icon_na" >';
								//echo '<span class="fav_icon fav_icon_na"></span>';
								//echo '<span class="nfav_label">'.JText::_('COM_DJCLASSIFIEDS_ADD_TO_FAVOURITES').'</span>';
								echo '</a>';
								echo '</span>';
							}
						}
						
						if(($params->get('show_date')==1) || ($params->get('show_cat')==1) || ($params->get('show_price')==1) || ($params->get('show_type','1')) || ($params->get('show_region','1'))){
							echo '<div class="date_cat">';
							if($params->get('show_date')==1){
								echo '<span class="date">';
								if($cfpar->get('date_format_type_modules',0)){
									echo DJClassifiedsTheme::dateFormatFromTo(strtotime($i->date_start));									
								}else{
									//echo date($cfpar->get('date_format','Y-m-d H:i:s'),strtotime($i->date_start));
									echo DJClassifiedsTheme::formatDate(strtotime($i->date_start),'',$cfpar->get('date_format_type_modules','Y-m-d H:i:s'));	
								}
								echo '</span>';
							}
							if($params->get('show_cat')==1){
								echo '<span class="category">';
								if($params->get('cat_link')==1){

									echo '<a class="title_cat" href="'.JRoute::_(DJClassifiedsSEO::getCategoryRoute($i->cat_id.':'.$i->c_alias)).'">'.$i->c_name.'</a>';
								}else{
									echo $i->c_name;
								}
								echo '</span>';
							}
							if($params->get('show_type','1') && $i->type_id>0){
								if(isset($types[$i->type_id])){
									echo '<span class="type">';
									$type = $types[$i->type_id];
									if($type->params->bt_class){
										$bt_class = ' '.$type->params->bt_class;
									}else{
										$bt_class = '';
									}
									if($type->params->bt_use_styles){
										if($params->get('show_type','1')==2){
									 	$style='style="display:inline-block;
									border:'.(int)$type->params->bt_border_size.'px solid '.$type->params->bt_border_color.';'
										.'background:'.$type->params->bt_bg.';'
									.'color:'.$type->params->bt_color.';'
									.$type->params->bt_style.'"';
									 	echo '<span class="type_button'.$bt_class.'" '.$style.' >'.$type->name.'</span>';
										}else{
										echo '<span class="type_label'.$bt_class.'" >'.$type->name.'</span>';
									}
									}else{
									echo '<span class="type_label'.$bt_class.'" >'.$type->name.'</span>';
								}
								echo '</span>';
								}
							}
							if($params->get('show_region')==1){
								echo '<span class="region">';
									echo '<a href="'.DJClassifiedsSEO::getRegionRoute($i->region_id.':'.$i->r_name).'">'.$i->r_name.'</a>';
								echo '</span>';
							}
							if($params->get('show_price')==1 && $i->price){
								echo '<span class="price">';
								echo DJClassifiedsTheme::priceFormat($i->price,$i->currency);
								echo '</span>';
							}
							echo '</div>';
						}
						echo '</div>';

						if($params->get('show_description')==1){
							echo '<div class="desc">';
							if($params->get('desc_source','0')==1){
								echo $i->description;
							}else{
								if($params->get('desc_link')==1){
									echo '<a href="'.JRoute::_(DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name)).'">';
								}
								$desc_c = $params->get('char_desc_nr');
								if($desc_c!=0 && $i->intro_desc!='' && strlen($i->intro_desc)>$desc_c){
										echo mb_substr($i->intro_desc, 0, $desc_c,'utf-8').' ...';
									}else{
										echo $i->intro_desc;
									}
									if($params->get('desc_link')==1){
									echo '</a>';
								}
							}
		
							echo '</div>';
						}
						
						if($params->get('custom_fields',0)){
							foreach($fields as $f){
								if($f->item_id == $i->id) {
						
									if($cfpar->get('show_empty_cf','1')==0){
										if($f->value=='' && ($f->value_date=='' || $f->value_date=='0000-00-00')){
											continue;
										}
									}
						
									if($f->source>0){
										continue;
									}
										
									$tel_tag = '';
									if(strstr($f->name, 'tel')){
										$tel_tag='tel:'.$f->value;
									}
						
									?>
									<div class="row_<?php echo $f->name;?> row_custom_field">
										<span class="row_label"><?php echo JText::_($f->label); ?></span>
										<span class="row_value<?php if($f->hide_on_start){echo ' djsvoc" title="'.htmlentities($f->value); }?>" rel="<?php echo $tel_tag; ?>" >
											<?php 
											if($f->type=='textarea'){							
												if($f->value==''){echo '---'; }
												else{echo $f->value;}								
											}else if($f->type=='checkbox'){
												if($f->value==''){echo '---'; }
												else{
													if($cfpar->get('cf_values_to_labels','0')){
														$ch_values = explode(';', substr($f->value,1,-1));
														foreach($ch_values as $chi=>$chv){
															if($chi>0){ echo ', ';}
															echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(array(' ',"'"), array('_',''), strtoupper($chv)));
														}
													}else{
														echo str_ireplace(';', ', ', substr($f->value,1,-1));
													}
												}
											}else if($f->type=='date'){
												if($f->value_date=='0000-00-00'){echo '---'; }
												else{
													echo DJClassifiedsTheme::formatDate(strtotime($f->value_date));
												}
											}else if($f->type=='link'){
												if($f->value==''){echo '---'; }
												else{
													if(strstr($f->value, 'http://') || strstr($f->value, 'https://')){
														echo '<a '.$f->params.' href="'.$f->value.'">'.str_ireplace(array("http://","https://"), array('',''), $f->value).'</a>';
													}else{
														echo '<a '.$f->params.' href="http://'.$f->value.'">'.$f->value.'</a>';;
													}																
												}							
											}else{
												if($f->value==''){echo '---'; }
												else{ 
													if($cfpar->get('cf_values_to_labels','0') && $f->type!='inputbox'){
														echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($f->value)));
													}else{
														if($f->hide_on_start){
															echo substr($f->value, 0,2).'..........<a href="javascript:void(0)" class="djsvoc_link">'.JText::_('COM_DJCLASSIFIEDS_SHOW').'</a>';
														}else{
															if($tel_tag){
																echo '<a href="'.$tel_tag.'">'.$f->value.'</a>';
															}else{
																echo $f->value;
															}
														}
													}
												}	
											}
											?>
										</span>
									</div>
								<?php }
							}
						}						
						
				echo '</div>';
				?>
					</div>
				</div>
				<?php 					
			}
			?>
				<div style="clear: both"></div>
			</div>
		</div>
	</div>
	<div class="djcf_slider_right" id="mod_djcf_slider_right<?php echo $module->id;?>">&nbsp;</div>
</div>
<script type="text/javascript">

function aslider<?php echo $module->id;?> (cols){
	
	var asllider_c<?php echo $module->id;?> = 0;
	var asllider_cols<?php echo $module->id;?> = cols;
	var asllider_all<?php echo $module->id;?> = <?php echo count($items);?>;
	
	var asllider_l<?php echo $module->id;?> = asllider_all<?php echo $module->id;?>-cols;
	if(asllider_l<?php echo $module->id;?><0){
		asllider_l<?php echo $module->id;?> = 0;
	}

	var items_outer = document.id('mod_djcf_slider<?php echo $module->id;?>').getElements('.items');
	var slider_box = document.id('items-content<?php echo $module->id;?>');
	var items_list = slider_box.getElements('.item-box');

	slider_box.setStyle('width','');
	slider_box.setStyle('margin','0');
	
	items_list.each(function(item,index){
		item.setStyle('width','');				
	});
	

	if(items_list.length==0){
		document.id('mod_djcf_slider_loader<?php echo $module->id;?>').setStyle('display','none');
		return true;
	}
	
	var slide_width = items_list[0].getSize().x;

	if(slide_width<80 && cols>1){
		var new_cols = cols-1;		
		items_outer[0].removeClass('items-cols'+cols);
		items_outer[0].addClass('items-cols'+new_cols);
		aslider<?php echo $module->id;?>(new_cols);
		
		return true;
	}else{
		var old_cols = cols-1;
		items_outer[0].removeClass('items-cols'+old_cols);
		items_outer.addClass('items-cols'+cols);
	}
	
	slider_box.setStyle('width',slide_width*asllider_all<?php echo $module->id;?>);
	
		items_list.each(function(item,index){
			item.setStyle('width',slide_width);				
		})
	var slide_height = slider_box.getSize().y;
		items_list.each(function(item,index){
			item.setStyle('height',slide_height);		
		})	
		
	slider_box.setStyle('height','auto');
	slider_box.tween('opacity', 1);
	document.id('mod_djcf_slider_loader<?php echo $module->id;?>').setStyle('display','none');
		

	
	
	var arrow_left = document.id('mod_djcf_slider_left<?php echo $module->id;?>');
	var arrow_right = document.id('mod_djcf_slider_right<?php echo $module->id;?>');
	
	if(asllider_all<?php echo $module->id;?>>asllider_cols<?php echo $module->id;?>){	
		arrow_left.setStyle('display','block');
		arrow_right.setStyle('display','block');

		arrow_right.removeClass('blocked');
		arrow_left.addClass('blocked');	
			
		arrow_left.removeEvents("click");
		arrow_left.addEvent('click',function(event){
			if(asllider_c<?php echo $module->id;?>>0){
				asllider_c<?php echo $module->id;?>--;
				slider_box.tween('margin-<?php echo $slide_dir;?>', asllider_c<?php echo $module->id;?>*-slide_width);			
				if(asllider_c<?php echo $module->id;?>==0){
					arrow_left.addClass('blocked');
					arrow_right.removeClass('blocked');		
				}else{
					arrow_left.removeClass('blocked');
					arrow_right.removeClass('blocked');
				}	
			}
		});
		
		arrow_right.removeEvents("click");
		arrow_right.addEvent('click',function(event){
			if(asllider_c<?php echo $module->id;?><asllider_l<?php echo $module->id;?>){
				asllider_c<?php echo $module->id;?>++;
				slider_box.tween('margin-<?php echo $slide_dir;?>', asllider_c<?php echo $module->id;?>*-slide_width);
				if(asllider_c<?php echo $module->id;?>==asllider_l<?php echo $module->id;?>){
					arrow_right.addClass('blocked');
					arrow_left.removeClass('blocked');		
				}else{
					arrow_left.removeClass('blocked');
					arrow_right.removeClass('blocked');
				}					
			}
		});
	}else{
		arrow_right.addClass('blocked');
		arrow_left.addClass('blocked');	
		arrow_left.setStyle('display','block');
		arrow_right.setStyle('display','block');
	}
	
} 		
		
window.addEvent('load', function(){		
	aslider<?php echo $module->id;?>(<?php echo $cols;?>);
});

window.addEvent('resize', function(){   
	aslider<?php echo $module->id;?>(<?php echo $cols;?>);
});


function DJFavChange<?php echo $module->id; ?>(){
	var favs = document.id(document.body).getElements('#mod_djcf_slider<?php echo $module->id; ?> .mfav_box');
	if (favs.length > 0) {						
		favs.each(function(fav) {
			fav.addEvent('click', function(evt) {
				//console.log(fav.getProperty('data-id'));
				
				var myRequest = new Request({  
				    url: '<?php echo JURI::base()?>index.php',
				    method: 'post',
					data: {
				      'option': 'com_djclassifieds',
				      'view': 'item',
				      'task': 'changeItemFavourite',
					  'item_id': fav.getProperty('data-id')						  						  
					  },
				    onRequest: function(){},
				    onSuccess: function(responseText){																					    	
						fav.innerHTML = responseText; 															
				    },
				    onFailure: function(){}
				});
				myRequest.send();
				
				
			});
		});					
	}
	
}

window.addEvent('load', function(){
	DJFavChange<?php echo $module->id; ?>();
});

</script>
