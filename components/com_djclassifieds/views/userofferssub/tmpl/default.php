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

$app 	= JFactory::getApplication();
$par 	= JComponentHelper::getParams( 'com_djclassifieds' );
$menus	= $app->getMenu('site');
$icon_col_w = $par->get('smallth_width','56')+20;

$r=TRUE;


?>
<div id="dj-classifieds" class="djcftheme-<?php echo $par->get('theme','default');?>">
<div class="offers_submitted">
	<div class="title_top"><h1><?php echo JText::_("COM_DJCLASSIFIEDS_OFFERS_SUBMITTED"); ?></h1></div>
	<div class="dj-items-table2">
	<?php /*?>
		<div class="item_row item_header main_title">								
			<div class="item_col icon first" > </div>
			<div class="item_col name normal" style="text-align:left;"><?php echo JText::_('COM_DJCLASSIFIEDS_ADVERT') ?></div>
			<div class="item_col normal" style="text-align:center;"><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE') ?></div>
			<div class="item_col normal" style="text-align:center;"><?php echo JText::_('COM_DJCLASSIFIEDS_SELLER') ?></div>				
		</div>	
		<?php */

		foreach($this->offers as $offer){
			
			$row = $r==TRUE ? '0' : '1';
			$r=!$r; ?>		
			<div class="item_row row<?php echo $row;?>">
		
			<?php 	
				if($offer->i_name){
					echo '<div class="item_col icon first"  style="width:'.$icon_col_w.'px"  >';						
						echo '<a href="'.DJClassifiedsSEO::getItemRoute($offer->item_id.':'.$offer->i_alias,$offer->cat_id.':'.$offer->c_alias,$offer->region_id.':'.$offer->r_name).'">';
						if(count($offer->images)){
							echo '<img src="'.JURI::base().$offer->images[0]->thumb_s.'"';
							echo ' alt ="'.str_ireplace('"', "'", $offer->images[0]->caption).'" ';
							echo  '/>';
						}else{
							echo '<img src="'.JURI::base().'/components/com_djclassifieds/assets/images/no-image.png" ';						
							echo ' alt ="'.str_ireplace('"', "'", $offer->i_name).'" ';
							echo '/>';
						}
						echo '</a>';
					echo '</div>';
					echo '<div class="item_col name">';					
						echo '<h3><a class="title" href="'.DJClassifiedsSEO::getItemRoute($offer->item_id.':'.$offer->i_alias,$offer->cat_id.':'.$offer->c_alias,$offer->region_id.':'.$offer->r_name).'" >'.$offer->i_name.'</a></h3>';
					echo '</div>';
				}else{
					echo '<div class="item_col icon first"  style="width:'.$icon_col_w.'px"  >';
						echo '<img src="'.JURI::base().'/components/com_djclassifieds/assets/images/no-image.png" ';
						echo ' alt ="'.str_ireplace('"', "'", $offer->item_name).'" ';
						echo '/>';
					echo '</div>';
					echo '<div class="item_col name">';
						echo '<h3>'.$offer->item_name.'</a></h3>';
					echo '</div>';
				} /*?>
				<div class="item_col"> 
					<div class="djcf_prow_desc_row djcf_prow_price">
						<span class="djcf_prow_desc_label" ><?php echo JText::_("COM_DJCLASSIFIEDS_PRICE");?>:</span>
						<span class="djcf_prow_desc_value" ><?php echo DJClassifiedsTheme::priceFormat($offer->price,$offer->currency);?></span>
						<div class="clear_both"></div>
					</div>
					<?php if($offer->quantity>0){?>
						<div class="djcf_prow_desc_row djcf_prow_quantity">
							<span class="djcf_prow_desc_label" ><?php echo JText::_("COM_DJCLASSIFIEDS_QUANTITY");?>:</span>
							<span class="djcf_prow_desc_value" ><?php echo $offer->quantity;?></span>
							<div class="clear_both"></div>
						</div>
					<?php } ?>
					<div class="djcf_prow_desc_row djcf_prow_date">
						<span class="djcf_prow_desc_label" ><?php echo JText::_("COM_DJCLASSIFIEDS_offer_DATE");?>:</span>
						<span class="djcf_prow_desc_value" ><?php echo DJClassifiedsTheme::formatDate(strtotime($offer->date));?></span>
						<div class="clear_both"></div>
					</div>
				</div>	
				<div class="item_col"> 
					<?php 
						$uid_slug = $offer->user_id.':'.DJClassifiedsSEO::getAliasName($offer->username);
						echo '<a class="profile_name" href="index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug.DJClassifiedsSEO::getMainAdvertsItemid().'">'.$offer->username;
							if($offer->i_user_items_count){
								echo ' <span>('.$offer->i_user_items_count.')</span>';
							}
						echo '</a>';
						echo '<div class="profile_email_outer"><a class="profile_email" href="mailto:'.$offer->email.'">'.$offer->email.'</a></div>';							
					?>						
				</div> 
				<?php */ ?>
			</div>
			
			</div><div class="dj-items-table2 dj-items-table2-offer-msg">
				
				<div class="item_row item_row_msg">
					<div class="item_col first">
						<div class="item_message_title" >
							<?php echo JText::_('COM_DJCLASSIFIEDS_YOUR_OFFER'); ?>
						</div>
						<div class="item_message" >													
							<?php echo $offer->message; ?>
						</div>
						<br />
							
						<div class="djcf_prow_desc_row djcf_prow_price">
							<span class="djcf_prow_desc_label" ><b><?php echo JText::_("COM_DJCLASSIFIEDS_PRICE");?>:</b></span><br />
							<span class="djcf_prow_desc_value" ><?php echo DJClassifiedsTheme::priceFormat($offer->price,$offer->currency);?></span>
							<div class="clear_both"></div>							
						</div>
						<br />
						<?php if($offer->quantity>0){?>
							<div class="djcf_prow_desc_row djcf_prow_quantity">
								<span class="djcf_prow_desc_label" ><b><?php echo JText::_("COM_DJCLASSIFIEDS_QUANTITY");?>:</b></span><br />
								<span class="djcf_prow_desc_value" ><?php echo $offer->quantity;?></span>
								<div class="clear_both"></div>
							</div>
							<br />
						<?php } ?>
						<div class="djcf_prow_desc_row djcf_prow_date">
							<span class="djcf_prow_desc_label" ><b><?php echo JText::_("COM_DJCLASSIFIEDS_offer_DATE");?>:</b></span><br />
							<span class="djcf_prow_desc_value" ><?php echo DJClassifiedsTheme::formatDate(strtotime($offer->date));?></span>
							<div class="clear_both"></div>
						</div>
						<br />
						
						<?php 
							$uid_slug = $offer->user_id.':'.DJClassifiedsSEO::getAliasName($offer->username);
							echo '<a class="profile_name" href="index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug.DJClassifiedsSEO::getMainAdvertsItemid().'">'.$offer->username;
								if($offer->i_user_items_count){
									echo ' <span>('.$offer->i_user_items_count.')</span>';
								}
							echo '</a>';
							echo '<div class="profile_email_outer"><a class="profile_email" href="mailto:'.$offer->email.'">'.$offer->email.'</a></div>';							
						?>	
						
					</div>
					<div class="item_col">						
						<div class="item_status">
							<span><?php echo JText::_('COM_DJCLASSIFIEDS_STATUS'); ?>: </span>
							<?php
								if($offer->status==2){
									echo JText::_('COM_DJCLASSIFIEDS_OFFER_DECLINED'); 
								}else if($offer->status==1){
									echo JText::_('COM_DJCLASSIFIEDS_OFFER_ACCEPTED');	
									if($offer->paid==0 && $offer->i_user_id && DJClassifiedsPayment::getUserPaypal($offer->i_user_id) && $par->get('buynow_direct_payment',0)){
										$payment_link = "index.php?option=com_djclassifieds&view=payment&type=offer&id=".$offer->id;
										echo '<a class="payment_button button" href="'.$payment_link.'">'.JText::_('COM_DJCLASSIFIEDS_PAY').'</a>';
									}
									
								}else{
									echo JText::_('COM_DJCLASSIFIEDS_WAITING_FOR_RESPONSE');
								}
							?>
						</div>
						<?php if($offer->status==1 && $offer->i_user_id && DJClassifiedsPayment::getUserPaypal($offer->i_user_id) && $par->get('buynow_direct_payment',0)){ ?>
							<div class="item_status">
								<span><?php echo JText::_('COM_DJCLASSIFIEDS_PAYMENT_STATUS'); ?>: </span>
								<?php 
									if($offer->paid==1){
										echo JText::_('COM_DJCLASSIFIEDS_PAID');	
									}else{
										echo JText::_('COM_DJCLASSIFIEDS_PENDING');
									}
								?>
							</div>	
						<?php } ?>
						<?php if($offer->status==1 && $offer->i_user_id && DJClassifiedsPayment::getUserPaypal($offer->i_user_id) && $par->get('buynow_direct_payment',0)){ ?>
							<div class="item_status">
								<span><?php echo JText::_('COM_DJCLASSIFIEDS_SERVICE_COMPLETE_STATUS'); ?>: </span>
								<?php 								
									if($offer->paid==1){
										if($offer->confirmed==1){
											echo JText::_('COM_DJCLASSIFIEDS_OFFER_CONFIRMED');	
										}else{
											$confirm_link = JURI::base()."index.php?option=com_djclassifieds&view=payment&task=confirmoffer&id=".$offer->id;
											echo '<a class="payment_button button" href="'.$confirm_link.'">'.JText::_('COM_DJCLASSIFIEDS_CONFIRM_SERVICE').'</a>';
										}										
									}else{
										echo JText::_('COM_DJCLASSIFIEDS_WAITING_FOR_PAYMENT');
									}
								
								?>
							</div>	
						<?php } ?>							
						<?php if($offer->status){ ?>
							<div class="item_response">
								<?php echo $offer->response; ?>
							</div>					
						<?php } ?>	
					</div>
				</div>  
				
			</div><div class="dj-items-table2">	
			
	<?php }?>
	</div>
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?> 
	</div>	
</div>
</div>