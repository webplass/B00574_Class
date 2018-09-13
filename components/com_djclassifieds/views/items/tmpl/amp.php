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
$toolTipArray = array('className'=>'djcf');
JHTML::_('behavior.tooltip', '.Tips1', $toolTipArray);
//$par = JComponentHelper::getParams( 'com_djclassifieds' );
$par = DJClassifiedsParams::getParams();
$user= JFactory::getUser();

$main_id= JRequest::getVar('cid', 0, '', 'int');
$se = JRequest::getVar('se', '0', '', 'int');
$fav_a	= $par->get('favourite','1');
$icon_new_a	= $par->get('icon_new','1');
$icon_new_date = mktime(date("G"), date("i"), date("s"), date("m"), date("d")-$par->get('icon_new_time','3'), date("Y"));

$order = JRequest::getCmd('order', $par->get('items_ordering','date_e'));
$ord_t = JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
if($ord_t=="desc"){
	$ord_t='asc';
}else{
	$ord_t='desc';
}

$sw = JRequest::getVar('search', '', '', 'string');
$uid	= JRequest::getVar('uid', 0, '', 'int');
$se = JRequest::getVar('se', '0', '', 'int');

$Itemid = JRequest::getVar('Itemid', 0, 'int');

$cat_id_se = 0;
if(JRequest::getVar('se','0','','string')!='0' && isset($_GET['se_cats'])){
	if(is_array($_GET['se_cats'])){
		$cat_id_se= end($_GET['se_cats']);
		if($cat_id_se=='' && count($_GET['se_cats'])>2){
			$cat_id_se =$_GET['se_cats'][count($_GET['se_cats'])-2];
		}
	}else{
		$cat_ids_se = explode(',', JRequest::getVar('se_cats'));
		$cat_id_se = end($cat_ids_se);
	}		
	$cat_id_se = str_ireplace('p', '', $cat_id_se);
	$cat_id_se = (int)$cat_id_se;
}
$cats_all = DJClassifiedsCategory::getCategories();

?>
	<div class="dj-items-blog">
		<?php if($this->main_cat){ ?>
			<h1><?php echo $this->main_cat->name; ?></h1>
		<?php } ?>
	<div class="djcf_items_blog">
	<?php 		
		$r=TRUE;
		
		if($par->get('showitem_jump',0)){
			$anch = '#dj-classifieds';
		}else{
			$anch='';
		}	
		$ii=0;	
		
		foreach($this->items as $i){
			if(!$i->alias){
				$i->alias = DJClassifiedsSEO::getAliasName($i->name);
			}
			if(!$i->c_alias){
				$i->c_alias = DJClassifiedsSEO::getAliasName($i->c_name);					
			}						
			$ii++;
			//if($i->special==1){$row=' special special_first';}else{$row='';}
			$row = '';
			if($i->promotions){
				$row .=' promotion '.str_ireplace(',', ' ', $i->promotions);
			}
			if($i->auction){
				$row .=' item_auction';
			}
			$icon_fav=0;
			if($user->id>0 && $fav_a){
				if($i->f_id){
					$icon_fav=1;
					$row .= ' item_fav';  
				}
			}
			$icon_new=0;		
			$date_start = strtotime($i->date_start);
			if($date_start>$icon_new_date && $icon_new_a){
				$icon_new=1;
				$row .= ' item_new';  
			}
			
			$schema_type = 'Product';
			if(isset($cats_all[$i->cat_id]->schema_type)){
				if($cats_all[$i->cat_id]->schema_type){
					$schema_type = $cats_all[$i->cat_id]->schema_type;		
				}
			}			
			
			echo '<div class="item_box'.$row.'" itemscope="itemscope" itemtype="http://schema.org/'.$schema_type.'" ><div class="item_box_in clearfix">';
			echo '<div class="title">';
				echo '<h2 itemprop="name" ><a href="'.JURI::base().DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name,$this->main_cat,$i->extra_cats).$anch.'" >'.$i->name.'</a></h2>';			
				if($par->get('show_types','0') && $i->type_id>0){
					if(isset($this->types[$i->type_id])){
						$type = $this->types[$i->type_id];
						if($type->params->bt_class){
							$bt_class = ' '.$type->params->bt_class; 
						}else{
							$bt_class = '';
						}	
						if($type->params->bt_use_styles){
						 	$style='style="display:inline-block;
						 			border:'.(int)$type->params->bt_border_size.'px solid '.$type->params->bt_border_color.';'
						 		   .'background:'.$type->params->bt_bg.';'
						 		   .'color:'.$type->params->bt_color.';'
						 		   .$type->params->bt_style.'"';
								   echo '<span class="type_button'.$bt_class.'" '.$style.' >'.$type->name.'</span>';							
						}else{
							echo '<span class="type_label'.$bt_class.'" >'.$type->name.'</span>';		
						}
					}
				}								
				if($icon_fav){
					//echo ' <img src="'.JURI::base(true).'/components/com_djclassifieds/themes/'.$this->theme.'/images/fav_a.png" class="fav_ico"/>';
					echo '<span class="fav_icon fav_icon_a" ></span>';
				}			
				if($icon_new){
					echo ' <span class="new_icon">'.JText::_('COM_DJCLASSIFIEDS_NEW').'</span>';
				}
				if($i->auction){
					echo '<span class="auction_icon" ></span>';
				}
				if(strstr($i->promotions, 'p_special')){
					echo '<span class="p_special_icon"></span>';
				}
				
			echo '</div>';			
			echo '<div class="blog_det">';	
				
					if(count($i->images)){ ?>
						<div class="item_img">																	
							<?php if(count($i->images) ){
							$img_info = getimagesize(JPATH_ROOT.$i->images[0]->thumb_b);
							$img_w_h = (isset($img_info[3])? $img_info[3] : '' ); ?>
							
				            	<amp-carousel width="<?php echo $img_info[0]; ?>"
							      height="<?php echo $img_info[1]; ?>"
							      layout="responsive"
							      type="slides">
							      <?php foreach($i->images as $img) { 
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
				<?php }																						
								//echo '<div class="cat_name"><span class="label_title">'.JText::_('COM_DJCLASSIFIEDS_CATEGORY').'</span>'.$i->c_name.'</div>';								
					echo '<div class="category" itemprop="category"><span class="label_title"></span>';				
						echo '<a href="'.JURI::base().DJClassifiedsSEO::getCategoryRoute($i->cat_id.':'.$i->c_alias).'" >'.$i->c_name.'</a>';									
					echo '</div>';
					
					if($par->get('blog_location','1') && $i->r_name && $par->get('show_regions','1')){
						echo '<div class="region">'.$i->r_name.'</div>';
					}
					if($par->get('blog_price','1') && $i->price && $par->get('show_price','1')){
						echo '<div class="price"  itemprop="offers" itemscope="" itemtype="http://schema.org/Offer" >';
								echo '<span itemprop="price" >'.$i->price.'</span>';
								echo '<span itemprop="priceCurrency" >'.$i->currency.'</span>';
						echo '</div>';	
					}
					
					echo '<div class="item_box_bottom">';		
						echo '<div class="item_desc"><span class="label_title">'.JText::_('COM_DJCLASSIFIEDS_DESCRIPTION').'</span>';
							echo '<span class="desc_info"  itemprop="description" >'.mb_substr(strip_tags($i->intro_desc), 0,$par->get('introdesc_char_limit','120'),'UTF-8');
						echo '</span></div>';					
					echo '</div>';		
													
					foreach($i->fields as $f_id => $field){
						if($this->custom_fields[$f_id]->in_blog && $field!=''){
							echo '<div class="cf_box">';
								echo '<span class="label_title">'.$this->custom_fields[$f_id]->label.' : </span>';
								if($this->custom_fields[$f_id]->type=='checkbox'){
									echo str_ireplace(';', ', ', substr($field,1,-1));
								}else if($this->custom_fields[$f_id]->type=='link'){
									if($field==''){echo '---'; }
									else{
										if(strstr($field, 'http://') || strstr($field, 'https://')){
											echo '<a '.$this->custom_fields[$f_id]->params.' href="'.$field.'">'.str_ireplace(array("http://","https://"), array('',''), $field).'</a>';;
										}else{
											echo '<a '.$this->custom_fields[$f_id]->params.' href="http://'.$field.'">'.$field.'</a>';;
										}
									}
								}else{
									echo $field;	
								}								
							echo '</div>';
						}
					}										
					if($par->get('blog_readmore','1')){					
						echo '<div class="see_details_box">';
							echo '<a class="see_details" href="'.JURI::base().DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name,$this->main_cat,$i->extra_cats).$anch.'" >'.JText::_('COM_DJCLASSIFIEDS_SEE_DETAILS').'</a>';
						echo '</div>';
					}					
		
			echo '</div>';
			if(strstr($i->promotions, 'p_special')){
				echo '<span class="p_special_img">&nbsp;</span>';
			} 
			echo '</div></div>';
		}
		?>	
		<?php
			echo '<div class="clear_both" ></div>';			
			
		if(count($this->items)==0){
			echo '<div class="no_results" style="padding-left:30px;">';
				if($se>0){
					echo JText::_('COM_DJCLASSIFIEDS_NO_RESULTS');	
				}else if($main_id){
					echo JText::_('COM_DJCLASSIFIEDS_NO_CATEGORY_RESULTS');
				}
				
			echo '</div>';
		}
		?>
		</div>
		<?php 
		if($this->pagination->getPagesLinks()){
			echo '<div class="pagination" >';
			echo $this->pagination->getPagesLinks();
			echo '</div>';
		}		
		?>
	</div>	