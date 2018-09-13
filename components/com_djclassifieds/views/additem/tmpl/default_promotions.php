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
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.modal');
JHTML::_('behavior.calendar');
$toolTipArray = array('className'=>'djcf_label');
JHTML::_('behavior.tooltip', '.Tips1', $toolTipArray);
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$points_a = $par->get('points',0);


$document	= JFactory::getDocument();
$prom_styles = "#dj-classifieds .dj-additem .prom_rows .djform_prom_label .djform_prom_v{width:100%;padding-bottom:15px;}#dj-classifieds .dj-additem .prom_rows .label{text-align:center;width:39%;}#dj-classifieds .dj-additem .prom_rows .djform_field{width:60%;}";
$document->addStyleDeclaration($prom_styles);

?>


	<div class="prom_rows additem_djform">
		<div class="title_top"><?php echo JText::_('COM_DJCLASSIFIEDS_PROMOTIONS');	?>
			<?php if(count($this->promotions)>1){ ?>
				<div class="promotions_info">
					<?php echo JText::_('COM_DJCLASSIFIEDS_SELECT_EACH_PROMOTION_YOU_WISH_TO_USE')?>
				</div>
			<?php } ?>								
		</div>
		<div class="additem_djform_in">				
		<?php foreach($this->promotions as $prom){
			  $prom_active = '';
			  $prom_expired = '';?>	
		<div class="djform_row">
			<div class="djform_prom_label label" >
	            <label class="" >
	            
	            	<?php 
	            		echo JText::_($prom->label);
	            		/*.'<br /><span>'.JText::_('COM_DJCLASSIFIEDS_PRICE').'&nbsp;';
	            		echo DJClassifiedsTheme::priceFormat($prom->price,$par->get('unit_price'));
						if($points_a && $prom->points>0){
							echo '&nbsp-&nbsp'.$prom->points.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');
						}
	            		echo '</span>';*/
	            	?>		
	            </label>
            	<div class="djform_prom_v" >
					<div class="djform_prom_v_in" >
						<select autocomplete="off" class="inputbox" name="<?php echo $prom->name;?>" >
							<option value="0"><?php echo JText::_('COM_DJCLASSIFIEDS_SELECT'); ?></option>
							<?php foreach($prom->prices as $pp){
								if($pp->days){
										
									$pp_sel='';
									if(isset($this->item_promotions[$pp->prom_id])){										
										//echo '<pre>';print_r($this->item_promotions);die();										
										if($this->item_promotions[$pp->prom_id]->date_exp>=date("Y-m-d H:i:s")){
											/*if($this->item_promotions[$pp->prom_id]->date_exp>=date("Y-m-d H:i:s")){												
												$pp_sel = ' SELECTED ';	
											}*/				
											$prom_active = $this->item_promotions[$pp->prom_id];
										}else{
											$prom_expired = $this->item_promotions[$pp->prom_id];
										}
									}													
									echo '<option value="'.$pp->days.'" '.$pp_sel.' >';
										if($pp->days==1){
											echo $pp->days.'&nbsp;'.JText::_('COM_DJCLASSIFIEDS_DAY');
										}else{
											echo $pp->days.'&nbsp;'.JText::_('COM_DJCLASSIFIEDS_DAYS');	
										} 
										
										if($pp->price !='0.00' && $points_a!=2){	
											echo '&nbsp;-&nbsp;'.DJClassifiedsTheme::priceFormat($pp->price,$par->get('unit_price'));
										}
										if($pp->points>0 && $points_a){
											echo '&nbsp;-&nbsp;'.$pp->points.'&nbsp;'.JTEXT::_('COM_DJCLASSIFIEDS_POINTS_SHORT');	
										}	
									echo '</option>';
								}	
							}?>
						</select>
						<?php
						if($prom_active){ ?>
							<div class="djform_prom_active_det">
								<div class="djform_prom_adet_date_exp">
									<span class="djform_prom_adet_label">
										<?php echo JText::_('COM_DJCLASSIFIEDS_ACTIVE_UNTILL');?>:
									</span>
									<span class="djform_prom_adet_value">
										<?php echo DJClassifiedsTheme::formatDate(strtotime($prom_active->date_exp),'',$par->get('date_format_type',0)); ?>																				
									</span>
								</div>
							</div>
						<?php }else if($prom_expired){ ?>
							<div class="djform_prom_expired_det">
								<div class="djform_prom_adet_date_exp">
									<span class="djform_prom_adet_label">
										<?php echo JText::_('COM_DJCLASSIFIEDS_EXPIRED');?>:
									</span>
									<span class="djform_prom_adet_value">
										<?php echo $prom_expired->date_exp; ?>
									</span>
								</div>
							</div>
						<?php }						
						 /*
						<input type="radio" name="<?php echo $prom->name;?>" value="1" <?php  if(strstr($this->item->promotions, $prom->name)){echo "checked";}?> /><label><?php echo JText::_('JYES'); ?></label>
						<input type="radio" name="<?php echo $prom->name;?>" value="0" <?php  if(!strstr($this->item->promotions, $prom->name)){echo "checked";}?> /><label><?php echo JText::_('JNO'); ?></label> 
						 */ ?>
					</div>
				</div>
			</div>
            <div class="djform_field">				
				<div class="djform_prom_img" >							
					<div class="djform_prom_img_in" >
						<?php 
							$tip_content = '<img src=\''.JURI::base().'/components/com_djclassifieds/assets/images/'.$prom->name.'_h.png\' />'; 
							echo '<img class="Tips2" title="'.$tip_content.'" src="'.JURI::base().'/components/com_djclassifieds/assets/images/'.$prom->name.'.png" alt="'.str_ireplace('"', "''", JText::_($prom->label)).'" />';
						 ?>
					</div>
				</div>
				<div class="djform_prom_desc" >
					<div class="djform_prom_desc_in" >
					<?php echo JText::_($prom->description); ?>
					</div>
				</div>
					
            </div>
            <div style="clear:both"></div>
        </div>
        <?php } ?>
         <?php
		 	if(count($this->plugin_promotions)){
				foreach($this->plugin_promotions as $plugin_promotion){
					echo $plugin_promotion;
				}
			}?>	
        </div>
    </div>
    