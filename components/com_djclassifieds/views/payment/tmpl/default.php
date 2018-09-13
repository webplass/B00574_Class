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
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.modal');
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$points_a = $par->get('points',0);
$app = JFactory::getApplication();
$user = JFactory::getUser();
$menus	= $app->getMenu('site');
$menu_points = $menus->getItems('link','index.php?option=com_djclassifieds&view=points',1);
if($menu_points){
	$itemid = '&Itemid='.$menu_points->id;
}else{$itemid='';}

$mod_attribs=array();
$mod_attribs['style'] = 'xhtml';

?>
<div id="dj-classifieds" class="djcftheme-<?php echo $par->get('theme','default');?>">
	<?php	$modules_djcf = &JModuleHelper::getModules('djcf-payment-top');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-payment-top clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';		
		}	?>
		<?php
		if($this->item->user_id && $user->id){
			echo '<div class="payment_back_to_edit">';
				echo '<a class="back_to_edit" href="index.php?option=com_djclassifieds&view=additem&id='.$this->item->id.$itemid.'">'.JText::_('COM_DJCLASSIFIEDS_BACK_TO_EDITION').'</a>';
			echo '</div>';
		}else if($this->item->token && $par->get('guest_can_edit',0)==1){
			echo '<div class="payment_back_to_edit">';
				echo '<a class="back_to_edit" href="index.php?option=com_djclassifieds&view=additem&id=0&token='.$this->item->token.$itemid.'">'.JText::_('COM_DJCLASSIFIEDS_BACK_TO_EDITION').'</a>';
			echo '</div>';
		}
		
		?>
	<table cellpadding="0" cellspacing="0" width="98%" border="0" class="paymentdetails first">
		<tr>
			<td class="td_title">
				<h2><?php echo JText::_('COM_DJCLASSIFIEDS_PAYMENT_DETAILS');?></h2>
			</td>
		</tr>
		<tr>
			<td class="td_pdetails">
				<?php 
					$p_count =0;
					$p_total=0;
					$p_total_special=0;
					$points_total=0;
					if(strstr($this->item->pay_type, 'cat')){
						$c_price = $this->item->c_price/100;
						echo '<div class="pd_row"><span>'.JText::_('COM_DJCLASSIFIEDS_CATEGORY').'</span><span class="price">';
						if($points_a!=2){
							echo DJClassifiedsTheme::priceFormat($c_price,$par->get('unit_price',''));
						}
						if($points_a && $this->item->c_points){
							if($points_a!=2){
								echo ' / ';
							}
							echo $this->item->c_points.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
						}
						if($this->item->c_price_special>0){
							echo ' / '.$this->item->c_price_special.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
							$p_total_special += $this->item->c_price_special;
						}
						echo '</span></div>';
						$p_total+=$c_price;
						$points_total+=$this->item->c_points;
						$p_count++;
					}	
					
					if(strstr($this->item->pay_type, 'mc')){
						$pay_elems = explode(',', $this->item->pay_type);
						foreach($pay_elems as $pay_el){
							if(strstr($pay_el, 'mc')){
								$mc_id = str_ireplace('mc', '', $pay_el);
								$mcat = $this->categories[$mc_id];
								$c_price = $mcat->price/100;
					
								echo '<div class="pd_row"><span>'.JText::_('COM_DJCLASSIFIEDS_CATEGORY').' ('.$mcat->name.')'.'</span><span class="price">';
								if($points_a!=2){
									echo DJClassifiedsTheme::priceFormat($c_price,$par->get('unit_price',''));
								}
								if($points_a && $mcat->points){
									if($points_a!=2){
										echo ' / ';
									}
									$c_points = $mcat->points;
									echo ' '.$c_points.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
								}
								echo '</span></div>';
								$p_total+=$c_price;
								$points_total+=$mcat->points;
								$p_count++;
							}
						}
					}
					
					if(strstr($this->item->pay_type, 'type,')){
						$t_price = $this->itype->price;
						echo '<div class="pd_row"><span>'.JText::_('COM_DJCLASSIFIEDS_TYPE').' "'.$this->item->t_name.'"</span><span class="price">';
						if($points_a!=2){
							echo DJClassifiedsTheme::priceFormat($t_price,$par->get('unit_price',''));
						}
						if($points_a && $this->itype->points){
							if($points_a!=2){
								echo ' / ';
							}
							echo $this->itype->points.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
						}
						if($this->itype->price_special>0){
							echo ' / '.$this->itype->price_special.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
							$p_total_special += $this->itype->price_special;
						}
						echo '</span></div>';
						$p_total+=$t_price;
						$points_total+=$this->itype->points;
						$p_count++;
					}												
					if(strstr($this->item->pay_type, 'duration')){
						echo '<div class="pd_row"><span>'.JText::_('COM_DJCLASSIFIEDS_DURATION').' ';
							if($this->item->exp_days==0){
								echo JText::_('COM_DJCLASSIFIEDS_UNLIMITED');
							}else if($this->item->exp_days==1){
								echo $this->item->exp_days.' '.JText::_('COM_DJCLASSIFIEDS_DAY');
							}else{
								echo $this->item->exp_days.' '.JText::_('COM_DJCLASSIFIEDS_DAYS');
							}
														
							if(strstr($this->item->pay_type, 'duration_renew')){								
								echo '</span><span class="price">';
								if($points_a!=2){
									echo DJClassifiedsTheme::priceFormat($this->duration->price_renew,$par->get('unit_price',''));
								}
								if($points_a && $this->duration->points_renew){
									if($points_a!=2){
										echo ' / ';
									}
									echo $this->duration->points_renew.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
								}
								if($this->duration->price_renew_special>0){
									echo ' / '.$this->duration->price_renew_special.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
									$p_total_special += $this->duration->price_renew_special;
								}
								echo '</span></div>';
								$p_total+=$this->duration->price_renew;
								$points_total+=$this->duration->points_renew;		
							}else{
								echo '</span><span class="price">';
								if($points_a!=2){
									echo DJClassifiedsTheme::priceFormat($this->duration->price,$par->get('unit_price',''));
								}
								if($points_a && $this->duration->points){
									if($points_a!=2){
										echo ' / ';
									}
									echo $this->duration->points.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
								}
								if($this->duration->price_special>0){
									echo ' / '.$this->duration->price_special.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
									$p_total_special += $this->duration->price_special;
								}
								
								echo '</span></div>';
								$p_total+=$this->duration->price;
								$points_total+=$this->duration->points;
							}						
						
						$p_count++;			
					}

					foreach($this->promotions as $prom){
						$pay_type_a = explode(',', $this->item->pay_type);
						foreach($pay_type_a as $pay_type_e){
							if(strstr( $pay_type_e, $prom->name)){
								$pay_type_ep = explode('_', $pay_type_e);
								$prom_price = 0;
								$prom_points = 0;
								$prom_price_special = 0;
								if(isset($prom->prices[$pay_type_ep[3]])){
									$prom_price = $prom->prices[$pay_type_ep[3]]->price;
									$prom_points = $prom->prices[$pay_type_ep[3]]->points;
								}
								
								echo '<div class="pd_row"><span>'.JText::_($prom->label);
									if($pay_type_ep[3]==1){
										echo ' - '.$pay_type_ep[3].' '.JText::_('COM_DJCLASSIFIEDS_DAY');
									}else{
										echo ' - '.$pay_type_ep[3].' '.JText::_('COM_DJCLASSIFIEDS_DAYS');
									}
								echo '</span>';
								echo '<span class="price">';
									if($points_a!=2){
										echo DJClassifiedsTheme::priceFormat($prom_price,$par->get('unit_price',''));
									}
									if($points_a && $prom_points){
										if($points_a!=2){
											echo ' / ';
										}
										echo $prom_points.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
									}
									if($prom_price_special>0){
										echo ' / '.$prom_price_special.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
										$p_total_special += $prom_price_special;
									}
								echo '</span></div>';
								$p_total+=$prom_price;
								$points_total+=$prom_points;
								$p_count++;			
							}	
						}
					}
					
					if(strstr($this->item->pay_type, 'extra_img')){
						$extraimg = $this->item->extra_images_to_pay;
						echo '<div class="pd_row"><span>'.JText::_('COM_DJCLASSIFIEDS_ADDITIONAL_IMAGES').' '.$this->item->extra_images_to_pay.' ';
							$img_price_special = 0;
							if(strstr($this->item->pay_type, 'extra_img_renew')){
								$img_price	= $par->get('img_price_renew','0');
								$img_points	= $par->get('img_price_renew_points','0');
								if(isset($this->duration->img_price_default)){
									if($this->duration->img_price_default==0){
										$img_price = $this->duration->img_price_renew;
										$img_points = $this->duration->img_points_renew;
									}	
								}																		
								if(isset($this->special_prices['img_price_renew'])){
									$img_price_special	= $this->special_prices['img_price_renew'];
								}																				
							}else{
								$img_price	= $par->get('img_price','0');
								$img_points	= $par->get('img_price_points','0');
								if(isset($this->duration->img_price_default)){
									if($this->duration->img_price_default==0){
										$img_price = $this->duration->img_price;
										$img_points = $this->duration->img_points;
									}	
								}
								if(isset($this->special_prices['img_price'])){
									$img_price_special	= $this->special_prices['img_price'];
								}
							}							
							
							echo '</span><span class="price">';
							if($points_a!=2){
								echo DJClassifiedsTheme::priceFormat($img_price*$extraimg,$par->get('unit_price',''));
							}
							if($points_a && $img_points){
								if($points_a!=2){
									echo ' / ';
								}
								echo $img_points*$extraimg.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
							}
							if($img_price_special>0){
								echo ' / '.$img_price_special*$extraimg.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
							}
							echo '</span></div>';
							$p_total+=$img_price*$extraimg;
							$p_total_special += $img_price_special*$extraimg;
							$points_total+=$img_points*$extraimg;														
						
						$p_count++;			
					}
					
					if(strstr($this->item->pay_type, 'extra_chars')){
						$extrachar = $this->item->extra_chars_to_pay;
						echo '<div class="pd_row"><span>'.JText::_('COM_DJCLASSIFIEDS_ADDITIONAL_CHARS').' '.$this->item->extra_chars_to_pay.' ';
							$char_price_special = 0;
							if(strstr($this->item->pay_type, 'extra_chars_renew')){
								if($this->duration->char_price_default==0){																	
									$char_price = $this->duration->char_price_renew;
									$char_points = $this->duration->char_points_renew;
								}else{
									$char_price	= $par->get('desc_char_price_renew','0');
									$char_points	= $par->get('desc_char_price_renew_points','0');
								}	
								if(isset($this->special_prices['desc_char_price_renew'])){
									$char_price_special	= $this->special_prices['desc_char_price_renew'];
								}															
							}else{
								$char_price	= $par->get('desc_char_price','0');
								$char_points	= $par->get('desc_char_price_points','0');
								if(isset($this->duration->char_price_default)){
									if($this->duration->char_price_default==0){
										$char_price = $this->duration->char_price;
										$char_points = $this->duration->char_points;
									}	
								}
								
								if(isset($this->special_prices['desc_char_price'])){
									$char_price_special	= $this->special_prices['desc_char_price'];
								}
							}

							
							echo '</span><span class="price">';
							if($points_a!=2){
								echo DJClassifiedsTheme::priceFormat($char_price*$extrachar,$par->get('unit_price',''));
							}
							if($points_a && $char_points){
								if($points_a!=2){
									echo ' / ';
								}
								echo $char_points*$extrachar.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
							}
							if($char_price_special>0){
								echo ' / '.$char_price_special*$extrachar.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
							}							
							
							echo '</span></div>';
							$p_total+=$char_price*$extrachar;
							$p_total_special += $char_price_special*$extrachar;
							$points_total+=$char_points*$extrachar;							
						
						$p_count++;
					}					
					
					if(count($this->plugin_payments)){
						foreach($this->plugin_payments as $extra_p){
							echo $extra_p;
						}
					}
					
					$p_total = $this->price_total;
					
					if($p_count>1 || $par->get('vat_value','-1')>-1 || $points_a!=2 ){
						if($par->get('vat_value','-1')>-1 && $points_a!=2){
							$p_net = round($p_total/(1+($par->get('vat_value','-1')*0.01)),2);
							$p_special_net = 0;
							$p_special_tax = 0;
							if($p_total_special>0){
								$p_special_net = round($p_total_special/(1+($par->get('vat_value','-1')*0.01)),2);
								$p_special_tax = $p_total_special - $p_special_net;
							}							
							echo '<div class="pd_row_net"><span>'.JText::_('COM_DJCLASSIFIEDS_NET_COST').'</span>';
								echo '<span class="price">'.DJClassifiedsTheme::priceFormat($p_net,$par->get('unit_price',''));
									if($p_special_net>0){
										echo ' / '.$p_special_net.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
									}
								echo '</span>';							
							echo '</div>';
							echo '<div class="pd_row_tax"><span>'.JText::_('COM_DJCLASSIFIEDS_TAX').' ('.$par->get('vat_value','-1').'%)</span>';
								echo '<span class="price">'.DJClassifiedsTheme::priceFormat($p_total-$p_net,$par->get('unit_price',''));
									if($p_special_tax>0){
										echo ' / '.$p_special_tax.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
									}
								echo '</span>';
							echo '</div>';
						}
						$points_total = round($points_total);
						echo '<div class="pd_row_total"><span>'.JText::_('COM_DJCLASSIFIEDS_TOTAL').'</span>';
						echo '<span class="price">';
							if($points_a!=2){
								echo DJClassifiedsTheme::priceFormat($p_total,$par->get('unit_price',''));
							}
							if($points_a && $points_total){
								if($points_a!=2){
									echo ' / ';
								}
								echo $points_total.' '.JTEXT::_('COM_DJCLASSIFIEDS_POINTS');
							}
							if($p_total_special>0){
								echo ' / '.$p_total_special.' '.JTEXT::_('COM_DJCLASSIFIEDS_SPECIAL_PRICE_SHORT');
							}
						echo '</span></div>';
					}
					
					if($par->get('terms',1)>0 && $par->get('terms_article_id',0)>0 && $this->terms_link){ ?>
						<div class="pd_row pd_terms" >
					    	<div class="terms_and_conditions" styles="text-align:center;margin-top:10px;">
					                <input type="checkbox" name="terms_and_conditions" id="terms_and_conditions0" value="1" class="inputbox" />                	
									<?php 					 
									echo ' <span class="label_terms" for="terms_and_conditions" id="terms_and_conditions-lbl" >'.JText::_('COM_DJCLASSIFIEDS_I_AGREE_TO_THE').' </span>';					
									if($par->get('terms',0)==1){
										echo '<a href="'.$this->terms_link.'" target="_blank">'.JText::_('COM_DJCLASSIFIEDS_TERMS_AND_CONDITIONS').'</a>';
									}else if($par->get('terms',0)==2){
										echo '<a href="'.$this->terms_link.'" rel="{size: {x: 700, y: 500}, handler:\'iframe\'}" class="modal" target="_blank">'.JText::_('COM_DJCLASSIFIEDS_TERMS_AND_CONDITIONS').'</a>';
									} ?> *
					                <div class="clear_both"></div>
					     	</div>
					     </div>
					<?php } ?>	
					
			 		 <?php if($par->get('privacy_policy',0)>0 && $par->get('privacy_policy_article_id',0)>0){ ?>
			 		 	<div class="pd_row pd_terms pd_policy" >
					    	<div class="terms_and_conditions privacy_policy" styles="text-align:center;margin-top:10px;">
					                <input type="checkbox" name=privacy_policy id="privacy_policy0" value="1" class="inputbox" />                	
									<?php 					 
									echo ' <span class="label_terms" for="privacy_policy" id="privacy_policy-lbl" >'.JText::_('COM_DJCLASSIFIEDS_I_AGREE_TO_THE').' </span>';					
									if($par->get('privacy_policy',0)==1){
										echo '<a href="'.$this->privacy_policy_link.'" target="_blank">'.JText::_('COM_DJCLASSIFIEDS_PRIVACY_POLICY').'</a>';
									}else if($par->get('privacy_policy',0)==2){
										echo '<a href="'.$this->privacy_policy_link.'" rel="{size: {x: 700, y: 500}, handler:\'iframe\'}" class="modal" target="_blank">'.JText::_('COM_DJCLASSIFIEDS_PRIVACY_POLICY').'</a>';
									} ?> *
					                <div class="clear_both"></div>
					     	</div>
					     </div>
			 		 
					 <?php } ?>	
			
			 		 	<?php if($par->get('gdpr_agreement',1)>0){ ?>				
			    		<div class="pd_row pd_terms pd_gdpr_agreement" >
			                <div class="terms_and_conditions gdpr_agreement" styles="text-align:center;margin-top:10px;">
			                		<input type="checkbox" name="gdpr_agreement" id="gdpr_agreement0" value="1" class="inputbox" />                	
									<?php 					 
									echo ' <label class="label_terms" for="gdpr_agreement" id="gdpr_agreement-lbl" >';
										if($par->get('gdpr_agreement_info','')){
											echo $par->get('gdpr_agreement_info','');
										}else{
											echo JText::_('COM_DJCLASSIFIEDS_GDPR_AGREEMENT_LABEL');
										}												
									echo ' </label>';											
									?> *
								<div class="clear_both"></div>
			                </div>			                
			            </div>
					 <?php } ?>									
			</td>
		</tr>			
	</table>
	<?php	$modules_djcf = &JModuleHelper::getModules('djcf-payment-middle');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-payment-middle clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';		
		}	?>
		<?php 	
		if(count($this->plugin_sections)){
			foreach($this->plugin_sections as $extra_section){
				echo $extra_section;
			}
		}	
		$this->dispatcher->trigger( 'onBeforeShowPaymentsList',array(&$par));
		?>						
	<table cellpadding="0" cellspacing="0" width="98%" border="0" class="paymentdetails">
		<tr>
			<td class="td_title">
				<h2><?php echo JText::_("COM_DJCLASSIFIEDS_PAYMENT_METHODS"); ?></h2>
			</td>
		</tr>
		<tr>
			<td class="table_payment">
				<table cellpadding="0" cellspacing="0" width="100%" border="0">
					<?php
						if($par->get('points',0) && $points_total){ ?>
							<tr>
								<td class="payment_td">
									<table width="100%" cellspacing="0" cellpadding="5" border="0">
										<tr>
											<td width="160" align="center" class="td1">
												<img title="<?php echo JText::_('COM_DJCLASSIFIEDS_POINTS')?>" src="<?php echo JURI::base();?>components/com_djclassifieds/assets/images/points.png">
											</td>
											<td class="td2">
												<h2><?php echo JText::_('COM_DJCLASSIFIEDS_POINTS_PACKAGE')?></h2>
												<p style="text-align:left;"><?php echo JText::_('COM_DJCLASSIFIEDS_POINTS_AVAILABLE').': '.$this->user_points;?></p>
											</td>
											<td width="130" align="center" class="td3">
												<?php 
												if($this->user_points>=$points_total){ 
													echo '<a class="button" href="index.php?option=com_djclassifieds&view=payment&task=payPoints&id='.$this->item->id.'" style="text-decoration:none;">'.JText::_('COM_DJCLASSIFIEDS_USE_POINTS').'</a>';	
												}else{ 
													echo '<a target="_blank" class="button" href="'.JRoute::_('index.php?option=com_djclassifieds&view=points'.$itemid,false).'" style="text-decoration:none;">'.JText::_('COM_DJCLASSIFIEDS_BUY_POINTS').'</a>';	
												} ?>
												
											</td>
									</tr>
									</table>
								</td>
							</tr>
						<?php }
						$i = 0;			
						//if($points_a!=2){
							foreach($this->PaymentMethodDetails AS $pminfo)
							{
								if($pminfo=='' || ($points_a==2 && strpos($pminfo,'ptype=djcfAltaUserPoints')==false)){
									continue;
								}
								//$paymentLogoPath = JURI::root()."plugins/djclassifiedspayment/".$this->plugin_info[$i]->name."/images/".$pminfo["logo"];
								?>
									<tr>
										<td class="payment_td">
											<?php echo $pminfo; ?>
										</td>
									</tr>
								<?php
								$i++;
							}
						//}
					?>
				</table>
			</td>
		</tr>
	</table>
		<?php	$modules_djcf = &JModuleHelper::getModules('djcf-payment-bottom');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-payment-bottom clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';		
		}	?>
</div>
<script type="text/javascript">
	jQuery( document ).ready(function() {
	var pbuttons = jQuery('.paymentdetails .td3 a.button'); 
		pbuttons.each(function(i,pbutton){
			jQuery(pbutton).click(function(e) {
				<?php if($par->get('terms',1)>0 && $par->get('terms_article_id',0)>0 && $this->terms_link){ ?>				
					if(!jQuery('#terms_and_conditions0').is(":checked")){
						alert('<?php echo addslashes(JText::_('COM_DJCLASSIFIEDS_PLEASE_ACCEPT_TERMS_AND_CONDITIONS'));?>');
						e.preventDefault();
					}		        			    
				<?php } ?>
				<?php if($par->get('privacy_policy',0)>0 && $par->get('privacy_policy_article_id',0)>0){ ?>				
					if(!jQuery('#privacy_policy0').is(":checked")){
						alert('<?php echo addslashes(JText::_('COM_DJCLASSIFIEDS_PLEASE_ACCEPT_PRIVACY_POLICY'));?>');
						e.preventDefault();
					}		        			    
				<?php } ?>
				<?php if($par->get('gdpr_agreement',1)>0){ ?>						
					if(!jQuery('#gdpr_agreement0').is(":checked")){
						alert('<?php echo addslashes(JText::_('COM_DJCLASSIFIEDS_PLEASE_ACCEPT_GDPR_AGREEMENT'));?>');
						e.preventDefault();
					}		        			    
				<?php } ?>
			});				
		});
	});		
</script>