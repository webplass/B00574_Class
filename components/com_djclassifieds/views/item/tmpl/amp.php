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
JHTML::_('behavior.framework',true);
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.calendar');
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$app = JFactory::getApplication();
$config = JFactory::getConfig();
$user =  JFactory::getUser();
$Itemid = JRequest::getVar('Itemid', 0,'', 'int');
$item = $this->item;
$item_class='';
$icon_new_a	= $par->get('icon_new','1');
$icon_new_date = mktime(date("G"), date("i"), date("s"), date("m"), date("d")-$par->get('icon_new_time','3'), date("Y"));
$date_start = strtotime($item->date_start);
$icon_new=0;
$bid_active = 1;
if($item->quantity==0 && $item->buynow){
	$bid_active = 0;
}

$schema_type = $this->category->schema_type;
if(!$this->category->schema_type){
	$schema_type = 'Product'; 	
}

	if($item->promotions){
		$item_class .=' promotion '.str_ireplace(',', ' ', $item->promotions);
	}
	if($date_start>$icon_new_date && $icon_new_a){
		$icon_new=1;
		$item_class .= ' item_new';  
	}
	
	if($item->auction){
		$item_class .=' item_auction';
	}
	
	if($par->get('favourite','1') && $user->id>0){
		if($item->f_id){ $item_class .= ' item_fav'; }
	}

 
?>
<div id="dj-classifieds" class="clearfix djcftheme-<?php echo $this->theme;?>">			
<div class="dj-item<?php echo $item_class; ?>" itemscope="itemscope" itemtype="http://schema.org/<?php echo $schema_type; ?>" >
<?php	
	echo '<div class="title_top info"><h2 itemprop="name">'.$item->name.'</h2>';
	
		if($par->get('show_types','0') && $item->type_id>0){
			$registry = new JRegistry();			
			$registry->loadString($item->t_params);			
			$item->t_params = $registry->toObject();
			if($item->t_params->bt_class){
				$bt_class = ' '.$item->t_params->bt_class;
			}else{
				$bt_class = '';
			}			
			if($item->t_params->bt_use_styles){
			 	$style='style="display:inline-block;
			 			border:'.(int)$item->t_params->bt_border_size.'px solid '.$item->t_params->bt_border_color.';'
			 		   .'background:'.$item->t_params->bt_bg.';'
			 		   .'color:'.$item->t_params->bt_color.';'
			 		   .$item->t_params->bt_style.'"';
					   echo '<span class="type_button'.$bt_class.'" '.$style.' >'.$item->t_name.'</span>';							
			}else{
				echo '<span class="type_label'.$bt_class.'" >'.$item->t_name.'</span>';	
			}
		}
		
		if($par->get('favourite','1')){
			if($user->id>0 && $item->f_id){
				echo '<a class="fav_icon_link fav_icon_link_a" title="'.JText::_('COM_DJCLASSIFIEDS_DELETE_FROM_FAVOURITES').'" href="index.php?option=com_djclassifieds&view=item&task=removeFavourite&cid='.$item->cat_id.'&id='.$item->id.'&Itemid='.$Itemid.'">';
					//echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/themes/'.$this->theme.'/images/fav_a.png" class="fav_ico"/>';
					echo '<span class="fav_icon fav_icon_a" ></span>';
					echo '<span class="fav_label">'.JText::_('COM_DJCLASSIFIEDS_FAVOURITE').'</span>'; 
				echo '</a>';
			}else{
				echo '<a class="fav_icon_link fav_icon_link_na" title="'.JText::_('COM_DJCLASSIFIEDS_ADD_TO_FAVOURITES').'" href="index.php?option=com_djclassifieds&view=item&task=addFavourite&cid='.$item->cat_id.'&id='.$item->id.'&Itemid='.$Itemid.'">';
					//echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/themes/'.$this->theme.'/images/fav_na.png" class="fav_ico"/>';
					echo '<span class="fav_icon fav_icon_na" ></span>';
					echo '<span class="nfav_label">'.JText::_('COM_DJCLASSIFIEDS_ADD_TO_FAVOURITES').'</span>';
				echo '</a>';
			}	
						
		}
		if($icon_new){
			echo ' <span class="new_icon">'.JText::_('COM_DJCLASSIFIEDS_NEW').'</span>';
		} 
		
		if($par->get('sb_position','0')=='top' && $par->get('sb_code','')!=''){
			echo '<div class="sb_top">'.$par->get('sb_code','').'</div>';
		 }
	
	
	
	echo '</div>'; ?>
	<div class="dj-item-in">	
			<div class="djcf_images_generaldet_box">	
				<?php if(count($this->item_images) ){
					$img_info = getimagesize(JPATH_ROOT.$this->item_images[0]->thumb_b);
					$img_w_h = (isset($img_info[3])? $img_info[3] : '' ); ?>
					
		            	<amp-carousel width="<?php echo $img_info[0]; ?>"
					      height="<?php echo $img_info[1]; ?>"
					      layout="responsive"
					      type="slides">
					      <?php foreach($this->item_images as $img) { 
					      	$img_info = getimagesize(JPATH_ROOT.$img->thumb_b);
							$img_w_h = (isset($img_info[3])? $img_info[3] : '' );					      	
					      	?>
					      	<amp-img src="<?php echo JURI::base(true).$img->thumb_b; ?>"
						        <?php echo $img_w_h; ?>
						        layout="responsive"
						        alt="<?php echo $img->caption ?>"></amp-img>
					      <?php } ?>
					    </amp-carousel>
				<?php } ?>			
			</div>
			<?php if($item->price || $item->price_negotiable){ ?>
				<div class="row_gd">
					<div class="price_wrap">
						<?php if($item->price){?>
							<span class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE'); ?>:</span>
							<span class="row_value" itemprop="offers" itemscope="" itemtype="http://schema.org/Offer" >
								<span itemprop="price" ><?php echo $item->price; ?></span>
								<span itemprop="priceCurrency" ><?php echo $item->currency; ?></span>
							</span>
						<?php } ?>
						<?php if($item->price_negotiable){ ?>		
							<span class="row_negotiable">
								<?php echo JText::_('COM_DJCLASSIFIEDS_PRICE_IS_NEGOTIABLE'); ?>
							</span>							
						<?php } 				
						if($par->get('buynow','0') && $item->buynow){
						 	echo '<a class="button btn" href="'.$this->canonical_link.'" >'.JText::_('COM_DJCLASSIFIEDS_BUYNOW').'</a>';
						} ?>		 			
					</div>
					<?php 						
						if($par->get('auctions','0') && $item->auction){ ?>
							<div class="auction <?php echo $auction_cl;?>" id="djauctions">
								<div class="auction_bids">
									<div class="bids_title"><h3><?php echo JText::_('COM_DJCLASSIFIEDS_CURRENT_BIDS'); ?></h3></div>
									<?php 
									if(isset($this->bids[0]) && $item->price_reserve){
										if($this->bids[0]->price<$item->price_reserve){ ?>
											<div class="bids_subtitle"><?php echo JText::_('COM_DJCLASSIFIEDS_RESERVE_PRICE_NOT_REACHED'); ?></div>
									<?php }
									} ?> 
									
									<div class="bids_list">
										<?php if($this->bids){ ?>
											<div class="bids_row bids_row_title">
												<div class="bids_col bids_col_name"><?php echo JText::_('COM_DJCLASSIFIEDS_NAME'); ?>:</div>
												<div class="bids_col bids_col_date"><?php echo JText::_('COM_DJCLASSIFIEDS_DATE'); ?>:</div>
												<div class="bids_col bids_col_bid"><?php echo JText::_('COM_DJCLASSIFIEDS_BID'); ?>:</div>					
												<div class="clear_both"></div>
											</div>
											<?php foreach($this->bids as $bid){ 
												if ($par->get('mask_bidder_name','0')== 1) {
													$bid->u_name = mb_substr($bid->u_name, 0, 1,'UTF-8').'.....'.mb_substr($bid->u_name, -1, 1,'UTF-8');
												}
												?> 
												<div class="bids_row">
													<div class="bids_col bids_col_name">
														<?php echo $bid->u_name; ?>
													</div>
													<div class="bids_col bids_col_date"><?php echo DJClassifiedsTheme::formatDate(strtotime($bid->date)); ?></div>
													<div class="bids_col bids_col_bid">
														<?php echo DJClassifiedsTheme::priceFormat($bid->price,$item->currency);?>
													</div>
													<div class="clear_both"></div>
												</div>		
											<?php 
												if($bid->win){
													$bid_active = 0;
												}
											}?>			
										<?php }else{ ?>
											<div class="bids_row no_bids_row"><?php echo JText::_('COM_DJCLASSIFIEDS_NO_SUBMITTED_BIDS'); ?></div>	
										<?php }?>
										<div class="clear_both"></div>
									</div>
								</div>
								<div class="bids_form" id="djbids_form">
									<?php if($bid_active){
										echo '<a class="button btn" href="'.$this->canonical_link.'" >'.JText::_('COM_DJCLASSIFIEDS_PLACE_BID').'</a>'; 											
										}else{ ?>
										<div class="bids_close_info"><?php echo JText::_('COM_DJCLASSIFIEDS_AUCTION_IS_CLOSED'); ?></div>
									<?php }?>
									<div class="clear_both"></div>
								</div>
								<div id="djbid_alert"></div>
								<div id="djbid_message"></div>
							</div>
						<?php } ?>
				</div>
			<?php } ?>
			<h3><?php echo JText::_('COM_DJCLASSIFIEDS_DESCRIPTION'); ?></h3>
			<?php
				echo '<div class="description" itemprop="description" >';
					if($par->get('intro_desc_in_advert','0')){
						echo '<div class="intro_desc_content">'.$item->intro_desc.'</div>';
					}	
					if($item->description){
						echo '<div class="desc_content">';
							if($par->get('desc_plugins','')){
								echo JHTML::_('content.prepare',$item->description);
							}else{
								echo $item->description;
							}
						echo '</div>';
					}		
				echo '</div>';	?>
			
			
				
			<?php   	
			if(count($this->fields)>0){ ?>
				<div class="custom_det">
					<h3><?php echo JText::_('COM_DJCLASSIFIEDS_CUSTOM_DETAILS'); ?></h3>
					<?php  
					//echo '<pre>';print_r($this->fields);die();
					
					foreach($this->fields as $f){							
						if($par->get('show_empty_cf','1')==0){
							if($f->value=='' && ($f->value_date=='' || $f->value_date=='0000-00-00')){
								continue;
							}
						}
						if($f->source>0){continue;}				
						$tel_tag = '';
						if(strstr($f->name, 'tel')){
							$tel_tag='tel:'.$f->value;
						}
						?>
						<div class="row row_<?php echo $f->name;?>">
							<span class="row_label"><?php echo JText::_($f->label); ?></span>
							<span class="row_value" rel="<?php echo $tel_tag; ?>"  >
								<?php 
								if($f->type=='textarea'){							
									if($f->value==''){echo '---'; }
									else{echo $f->value;}								
								}else if($f->type=='checkbox'){
									if($f->value==''){echo '---'; }
									else{
										if($par->get('cf_values_to_labels','0')){
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
										if(!$f->date_format){$f->date_format = 'Y-m-d';}
										echo DJClassifiedsTheme::formatDate(strtotime($f->value_date),'','',$f->date_format);
									}
								}else if($f->type=='date_from_to'){
									if(!$f->date_format){$f->date_format = 'Y-m-d';}
									if($f->value_date=='0000-00-00'){echo '---'; }
									else{
										echo DJClassifiedsTheme::formatDate(strtotime($f->value_date),'','',$f->date_format);
									}
									
									if($f->value_date_to!='0000-00-00'){
										echo '<span class="date_from_to_sep"> - </span>'.DJClassifiedsTheme::formatDate(strtotime($f->value_date_to),'','',$f->date_format);
									}
								}else if($f->type=='link'){
									if($f->value==''){echo '---'; }
									else{
										if(strstr($f->value, 'http://') || strstr($f->value, 'https://')){
											echo '<a href="'.$f->value.'">'.str_ireplace(array("http://","https://"), array('',''), $f->value).'</a>';
										}else if(strstr($f->value, '@')){
											echo '<a href="mailto:'.$f->value.'">'.$f->value.'</a>';
										}else{
											echo '<a href="http://'.$f->value.'">'.$f->value.'</a>';
										}																	
									}							
								}else{
									if($f->value==''){echo '---'; }
									else{ 
										if($par->get('cf_values_to_labels','0') && $f->type!='inputbox'){
											echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($f->value)));
										}else{
											if($tel_tag){
												echo '<a href="'.$tel_tag.'">'.$f->value.'</a>';
											}else{
												echo $f->value;
											}			
										}
									}	
								}
								?>
							</span>
						</div>		
					<?php
					} ?>
				</div>
			<?php } ?>									
			<?php
			if(($par->get('show_regions','1') && $item->region_id) || ($par->get('show_address','1') && $item->address)){ ?>
				
					<h3><?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION'); ?></h3>
					<div class="row">
						<span class="row_value">
							<?php 
							echo $this->address.'<br />';
							echo $item->address;
							if($par->get('show_postcode','0')){
								if($item->post_code){
									echo ', '.$item->post_code;
								}				
							}
							?>
						</span>
					</div>
					<?php if($item->latitude!='0.000000000000000' && $item->longitude!='0.000000000000000'){ ?>
						<div class="row row_map">
							<span class="row_value">
								<?php 														
								$gm_url  = 'https://www.google.com/maps/embed/v1/place?';
								if($par->get('map_api_key_browser','')){
	        						$gm_url .= 'key='.$par->get('map_api_key_browser','').'&';
	        					} 
								$gm_url .= 'q='.$item->latitude.','.$item->longitude;
								
								?>							
								  <amp-iframe width="600"
								      height="400"
								      layout="responsive"
								      sandbox="allow-scripts allow-same-origin allow-popups"
								      frameborder="0"
								      src="<?php echo $gm_url; ?>">
								  </amp-iframe>
							</span>
						</div>
					<?php } ?>
				
			<?php }
			
			if((int)$par->get('show_video','0') && $item->video){
				$video_type = 'youtube';
				$video_id = ''; 		
				$video_host = '';		
				$video = '';		
				$video_parts = explode('/',$item->video);	
				if(isset($video_parts[2])) {
					if($video_parts[2]=='www.youtube.com' || $video_parts[2]=='youtube.com'){														
						$video_host = 'http://www.youtube.com/embed/';
						if($video_parts[3]=='embed' && isset($video_parts[4])){
							$video=$video_parts[4];
							$video_id = $video_parts[4];
						}else{
							$video = array_pop($video_parts);
							preg_match('/v=([\w\d\-]+)/', $video, $video);							
							$video_id = $video[1];	
							$video = $video[1].'?rel=0';							
						}										
					}else if($video_parts[2]=='youtu.be' && isset($video_parts[3])){
						$video_host = 'http://www.youtube.com/embed/';
						$video = $video_parts[3];
						$video_id = $video_parts[3];
					}else if($video_parts[2]=='vimeo.com'){
						$video_type = 'vimeo';	
						$video_host = 'http://player.vimeo.com/video/';
						$video = array_pop($video_parts);
						$video_id = $video;
						$video .= '?portrait=0&color=333'; 						
					}
				}			
	
				if($video_host){
					if($config->get('force_ssl',0)==2){
						$video_host = str_ireplace('http://','https://',$video_host);
					}
					?>
				<div class="video_box"><h3><?php echo JText::_('COM_DJCLASSIFIEDS_VIDEO'); ?></h3>
					<div class="row">
						<div class="row_value" >
							<?php if($video_type=='youtube'){ ?>
								<amp-youtube
								    data-videoid="<?php echo $video_id; ?>"
								    layout="responsive"
								    width="560" height="315"></amp-youtube>	
							<?php }else{ ?>
								<amp-vimeo
								    data-videoid="<?php echo $video_id; ?>"
								    layout="responsive"
								    width="560" height="315"></amp-vimeo>									
							<?php } /*?>	
							<div class="videoWrapper"><div class="videoWrapper-in">						
							<iframe width="560" height="315" src="<?php echo $video_host.$video;?>" allowfullscreen></iframe>
							</div></div> */ ?>				
						</div>
					</div>
				</div>
				<?php }
			}

			?>	 
			
			<div class="row_gd djcf_contact_profile"  itemprop="manufacturer" itemscope="" itemtype="http://schema.org/Organization" >				
			<?php 					
				if(($par->get('show_contact','1') && $item->contact) || ($par->get('show_website','1') && $item->website) || count($this->fields_contact)){?>
					<h3 class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_CONTACT'); ?></h3>
					<div class="contact_mainrow">						
						<span class="row_value"><?php 							
							if($item->contact){	
								echo $item->contact;				
							}
							
							if($par->get('show_website','1') && $item->website){
								if($item->contact){
									echo '<br />';		
								}				
								echo '<a itemprop="url" target="_blank" ';
								if($par->get('website_nofollow','1')){
									echo ' rel="nofollow" ';
								}
								echo 'href="';
								if(strstr($item->website, 'http://') || strstr($item->website, 'https://')){
									echo $item->website;
								}else{
									echo 'http://'.$item->website;
								}
								echo '">'.$item->website.'</a>';
							}
						
						 ?></span>
					 </div>
					 
					<?php if(count($this->fields_contact)>0){ ?>
							<?php 
							//echo '<pre>';print_r($this->fields);die(); 
							
							foreach($this->fields_contact as $f){
								if($f->name=='contact'){
									continue;
								}
								if($par->get('show_empty_cf','1')==0){
									if(!$f->value && ($f->value_date=='' || $f->value_date=='0000-00-00')){
										continue;
									}
								}
								if($f->source!=1){continue;}
								$tel_tag = '';
								if(strstr($f->name, 'tel')){
									$tel_tag='tel:'.$f->value;
								}
								?>
								<div class="contact_row row_<?php echo $f->name;?>">
									<span class="row_label"><?php echo JText::_($f->label); ?>:</span>
									<span class="row_value" <?php echo $f->params_display; ?> rel="<?php echo $tel_tag; ?>" >
										<?php 
										if($f->type=='textarea'){							
											if($f->value==''){echo '---'; }
											else{echo $f->value;}								
										}else if($f->type=='checkbox'){
											if($f->value==''){echo '---'; }
											else{
												if($par->get('cf_values_to_labels','0')){
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
												if(!$f->date_format){$f->date_format = 'Y-m-d';}
												echo DJClassifiedsTheme::formatDate(strtotime($f->value_date),'','',$f->date_format);
											}									
										}else if($f->type=='date_from_to'){
											if(!$f->date_format){$f->date_format = 'Y-m-d';}									
											if($f->value_date=='0000-00-00'){echo '---'; }
											else{
												echo DJClassifiedsTheme::formatDate(strtotime($f->value_date),'','',$f->date_format);
											}
		
											if($f->value_date_to!='0000-00-00'){
												echo '<span class="date_from_to_sep"> - </span>';
												echo DJClassifiedsTheme::formatDate(strtotime($f->value_date_to),'','',$f->date_format);
											}
										}else if($f->type=='link'){
											if($f->value==''){echo '---'; }
											else{
												if(strstr($f->value, 'http://') || strstr($f->value, 'https://')){
													echo '<a href="'.$f->value.'">'.str_ireplace(array("http://","https://"), array('',''), $f->value).'</a>';
												}else if(strstr($f->value, '@')){
													echo '<a href="mailto:'.$f->value.'">'.$f->value.'</a>';
												}else{
													echo '<a href="http://'.$f->value.'">'.$f->value.'</a>';
												}																
											}							
										}else{
											if($par->get('cf_values_to_labels','0') && $f->type!='inputbox'){
												echo JText::_('COM_DJCLASSIFIEDS_'.str_ireplace(' ', '_', strtoupper($f->value)));
											}else{		
												if($tel_tag){
													echo '<a href="'.$tel_tag.'">'.$f->value.'</a>';
												}else{
													echo $f->value;
												}											
												
											}
										}
										?>
									</span>
								</div>		
							<?php
							} ?>
					<?php }?>
					<?php if($par->get('ask_seller','0')==1 || ($par->get('abuse_reporting','0')==1 && $par->get('notify_user_email','')!='')){ ?>
						<a href="<?php echo JRoute::_(DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias),false); ?>" id="ask_form_button" class="btn button" ><?php echo JText::_('COM_DJCLASSIFIEDS_ASK_SELLER'); ?></a>
					<?php } ?>				 			 			 

				<?php } ?>
				
					<h3 class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_CREATED_BY'); ?></h3>
						<div class="row_value">
							<?php 
							if($item->user_id==0){
								echo JText::_('COM_DJCLASSIFIEDS_GUEST');
							}else{
								$uid_slug = $item->user_id.':'.DJClassifiedsSEO::getAliasName($item->username);
								?>
								<div class="profile_item_box">
									<?php 							
										echo '<a class="profile_img" href="'.JURI::base().'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug.DJClassifiedsSEO::getUserProfileItemid().'">';											
											if($this->profile['img']){
												$img_info = getimagesize(JPATH_ROOT.$this->profile['img']->path.$this->profile['img']->name.'_ths.'.$this->profile['img']->ext);
												$img_w_h = (isset($img_info[3])? $img_info[3] : '' );
												echo '<amp-img itemprop="image" '.$img_w_h.' src="'.JURI::base(true).$this->profile['img']->path.$this->profile['img']->name.'_ths.'.$this->profile['img']->ext.'" ></amp-img>';
											}else{
												echo '<amp-img itemprop="image" width="100" height="100" src="'.JURI::base(true).'/components/com_djclassifieds/assets/images/default_profile_s.png" ></amp-img>';	
											}									
										echo '</a>';
									?>
									<div class="profile_name_data">
										<?php echo '<a class="profile_name" href="'.JURI::base().'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug.DJClassifiedsSEO::getUserProfileItemid().'"><span itemprop="name" >'.$item->username.'</span> <span>('.$this->user_items_c.')</span></a>'; ?>
										<?php if($this->profile['data']){ ?>
											<div class="profile_data">
											<?php foreach($this->profile['data'] as $f){
												if($par->get('show_empty_cf','1')==0){
													if(!$f->value && ($f->value_date=='' || $f->value_date=='0000-00-00')){
														continue;
													}
												}
												?>
												<div class="profile_row row_<?php echo $f->name;?>">
													<span class="profile_row_label"><?php echo JText::_($f->label); ?>: </span>
													<span class="row_value" >
														<?php 
														if($f->type=='textarea'){							
															if($f->value==''){echo '---'; }
															else{echo $f->value;}								
														}else if($f->type=='checkbox'){
															if($f->value==''){echo '---'; }
															else{
																echo str_ireplace(';', ', ', substr($f->value,1,-1));
															}
														}else if($f->type=='date'){
															if($f->value_date==''){echo '---'; }
															else{
																echo DJClassifiedsTheme::formatDate(strtotime($f->value_date),'','',$f->date_format);
															}
														}else if($f->type=='link'){
															if($f->value==''){echo '---'; }
															else{
																if(strstr($f->value, 'http://') || strstr($f->value, 'https://')){
																	echo '<a '.$f->params.' href="'.$f->value.'">'.str_ireplace(array("http://","https://"), array('',''), $f->value).'</a>';;
																}else{
																	echo '<a '.$f->params.' href="http://'.$f->value.'">'.$f->value.'</a>';;
																}																
															}							
														}else{
															if($f->value==''){
																echo '---'; 												
															}else{
																echo $f->value;
															}	
														}
														?>
													</span>
												</div>		
											<?php }?>
											</div> 
									<?php }?>				
									</div>
								</div>			 	
								<?php 										 
							}?>
						</div>
					</div>
			</div>														
			<?php 
			
			 if((int)$par->get('showaddetails','1')){?>
				<div class="additional"><h3><?php echo JText::_('COM_DJCLASSIFIEDS_AD_DETAILS'); ?></h3>
					<div class="row">
						<span class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_AD_ID'); ?>:</span>
						<span class="row_value"><?php echo $item->id; ?></span>
					</div>
					<div class="row">
						<span class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_DISPLAYED'); ?>:</span>
						<span class="row_value"><?php echo $item->display; ?></span>
					</div>
					<div class="row">
						<span class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_AD_ADDED'); ?></span>
						<span class="row_value" >
							<?php echo $item->date_start;  ?>
						</span>
					</div>			
					<div class="row">
						<span class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_AD_EXPIRES'); ?>:</span>
						<span class="row_value">
							<?php echo DJClassifiedsTheme::formatDate(strtotime($item->date_exp));  ?>
						</span>
					</div>
					<div class="row">
						<span class="row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_IN_CATEGORIES'); ?>:</span>
						<span class="row_value" itemprop="category" >
							<?php
							echo '<a href="'.JURI::base().DJClassifiedsSEO::getCategoryRoute($item->cat_id.':'.$item->c_alias).'" >'.$item->c_name.'</a>';
							if($item->extra_cats){
								foreach($item->extra_cats as $ecat){					
									echo ', <a href="'.JURI::base().DJClassifiedsSEO::getCategoryRoute($ecat->id.':'.$ecat->alias).'" >'.$ecat->name.'</a>';
								}
							} ?>
						</span>
					</div>						
					
				</div>
			<?php } ?>					 		
		<div class="clear_both" ></div>
	</div>
	</div>	
</div>