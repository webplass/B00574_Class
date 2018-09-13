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
$dispatcher	= JDispatcher::getInstance();
?>
		<form action="index.php" method="post" name="adminForm" id="adminForm" enctype='multipart/form-data'>
			<div class="row-fluid">
			<div class="span12 form-horizontal">
			<fieldset class="adminform">	
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#details" data-toggle="tab"><?php echo empty($this->promotion->id) ? JText::_('COM_DJCLASSIFIEDS_NEW') : JText::_('COM_DJCLASSIFIEDS_EDIT'); ?></a>
					</li>
					<li>
						<a href="#pd" data-toggle="tab"><?php echo JText::_('COM_DJCLASSIFIEDS_PRICES_AND_DURATIONS')?></a>
					</li>					
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="details">
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_LABEL');?></div>
							<div class="controls">
	                			<?php echo JText::_($this->promotion->label); ?>
	                    		<input type="hidden" name="label" id="label" value="<?php echo $this->promotion->label; ?>" />						
							</div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_DESCRIPTION');?></div>
							<div class="controls">
								<?php echo JText::_($this->promotion->description); ?>
	                    		<input type="hidden" name="description" id="description" value="<?php echo $this->promotion->description; ?>" />
							</div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_NAME');?></div>
							<div class="controls">
								<?php echo $this->promotion->name; ?>
	                    		<input type="hidden" name="name" id="name" value="<?php echo $this->promotion->name; ?>" />
							</div>
						</div>
						<?php /* ?>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE');?><br />(11.22)</div>
							<div class="controls">
								<input class="text_area" type="text" name="price" id="price" size="20" value="<?php echo $this->promotion->price; ?>" maxlength="250" />
							</div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE_POINTS');?></div>
							<div class="controls">
								<input class="text_area" type="text" name="points" id="points" size="20" maxlength="250" value="<?php echo $this->promotion->points; ?>" />
							</div>
						</div>
						<?php */ ?>
							<input type="hidden" name="points" id="points" size="20" value="<?php echo $this->promotion->points; ?>" />
							<input type="hidden" name="price" id="price" size="20" value="<?php echo $this->promotion->price; ?>" />
						<?php
								$plugin_fields = $dispatcher->trigger('onAdminPromotionEditFields', array ($this->promotion));
								if(count($plugin_fields)){
									foreach($plugin_fields as $plugin_field){
										echo $plugin_field;
									}
								}
							?>	
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_PUBLISHED');?></div>
							<div class="controls">
								<input autocomplete="off" type="radio" name="published" value="1" <?php  if($this->promotion->published==1 || $this->promotion->id==0){echo "checked";}?> /><span style="float:left;margin:2px 10px 0 5px;"><?php echo JText::_('COM_DJCLASSIFIEDS_YES'); ?></span>				
								<input autocomplete="off" type="radio" name="published" value="0" <?php  if($this->promotion->published==0 && $this->promotion->id>0){echo "checked";}?> /><span style="float:left;margin:2px 10px 0 5px;"><?php echo JText::_('COM_DJCLASSIFIEDS_NO'); ?></span>						
							</div>
						</div>																										
					</div>
					
					
					<div class="tab-pane" id="pd">	
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_PRICES_AND_DURATIONS');?></div>
							<div class="controls">
								<div id="prom_pd_outer">
									<div id="prom_pd">
										<div class="prom_pd_box_header">
											<span class="prom_pd_header" ><?php echo JText::_('COM_DJCLASSIFIEDS_DAYS');?> </span>
											<span class="prom_pd_header" ><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE');?> (11.22)</span>
											<span class="prom_pd_header" ><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE_POINTS');?> </span>											
										</div>
										<?php  if(count($this->promotion_prices)){
											foreach($this->promotion_prices as $pp){ ?>
												<div class="prom_pd_box" >
													<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_days[]" value="<?php echo $pp->days; ?>" /></span>
													<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_price[]" value="<?php echo $pp->price; ?>" /></span>
													<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_points[]" value="<?php echo $pp->points; ?>" /></span>											
													<span class="prom_pd_col prom_pd_col_del" ><span class="button prom_pd_remove"><?php echo JText::_('COM_DJCLASSIFIEDS_DELETE');?></span></span>
												</div>
											<?php }
										} ?>
										<div class="prom_pd_box" >
											<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_days[]" value="" /></span>
											<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_price[]" value="" /></span>
											<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_points[]" value="" /></span>											
											<span class="prom_pd_col prom_pd_col_del" ><span class="button prom_pd_remove"><?php echo JText::_('COM_DJCLASSIFIEDS_DELETE');?></span></span>
										</div>									
									</div>
										<div style="display:none" class="prom_pd_box" id="prom_pd_theme">
											<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_days[]" value="" /></span>
											<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_price[]" value="" /></span>
											<span class="prom_pd_col" ><input type="text" class="inputbox" name="prom_pd_points[]" value="" /></span>											
											<span class="prom_pd_col prom_pd_col_del" ><span class="button prom_pd_remove"><?php echo JText::_('COM_DJCLASSIFIEDS_DELETE');?></span></span>
										</div>
									<div class="prom_pd_button">
										<span id="prom_pd_add" class="button"><?php echo JText::_('COM_DJCLASSIFIEDS_ADD_NEXT');?></span>
									</div>					
								</div>																																
							</div>
						</div>					
					</div>	
					
									
				</div>
				
				</fieldset>
			</div>
			</div>
			<input type="hidden" name="id" value="<?php echo $this->promotion->id; ?>" />
			<input type="hidden" name="option" value="com_djclassifieds" />
			<input type="hidden" name="task" value="promotion" />
			<input type="hidden" name="boxchecked" value="0" />
		</form>			
<?php echo DJCFFOOTER; ?>
<script type="text/javascript">

window.addEvent('domready', function(){    
	    
    document.id('prom_pd').getElements('span.prom_pd_remove').each(function(el){
        el.addEvent('click', function(e) {
            e.preventDefault();
            var src = document.id(e.target);
            var parent = src.getParent('div.prom_pd_box');
            parent.destroy();
        });
    });
    
    document.id('prom_pd_add').addEvent('click', promotionAddRow);    
});

    function promotionAddRow(e){
        e.preventDefault();
        
        var copy = document.id('prom_pd_theme').clone().inject('prom_pd', 'bottom');
        copy.setStyle('display', '');
        copy.getElement('span.prom_pd_remove').addEvent('click', function(evt) {
            evt.preventDefault();
            var src = document.id(evt.target);
            var parent = src.getParent('div.prom_pd_box');
            parent.destroy();
        });
        
        return false;
    }

</script>