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
 
// No direct access.
defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params');
foreach ($fieldSets as $name => $fieldSet) :
	?>
	<div class="tab-pane" id="params-<?php echo $name;?>">
	<div class="control-group">
		<div class="control-label"><?php echo JText::_('COM_DJCLASSIFIEDS_PREVIEW'); ?></div>
		<div class="controls"><span id="type_prev"><?php echo $this->item->name;?></span></div>
	</div>
	<?php
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="alert alert-info">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>
			<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endforeach; ?>
	</div>
<?php endforeach; ?>

<script type="text/javascript">
window.addEvent('domready', function(){
	updatePreview();
	
	document.id('jform_name').addEvent('change', function(){
		updatePreview();
	});
	document.id('jform_params_bt_color').addEvent('change', function(){
		updatePreview();
	});
	document.id('jform_params_bt_bg').addEvent('change', function(){
		updatePreview();
	});
	document.id('jform_params_bt_border_color').addEvent('change', function(){
		updatePreview();
	});
	document.id('jform_params_bt_border_size').addEvent('change', function(){
		updatePreview();
	});
	document.id('jform_params_bt_style').addEvent('change', function(){
		updatePreview();
	});
	
});

function updatePreview(){
	var name = document.id('jform_name').value;
	var bt_color = document.id('jform_params_bt_color').value;
	var bt_bg = document.id('jform_params_bt_bg').value;
	var bt_border_color = document.id('jform_params_bt_border_color').value;
	var bt_border_size = document.id('jform_params_bt_border_size').value;
	var bt_style = document.id('jform_params_bt_style').value;

	document.id('type_prev').innerHTML = name;  
	
	var new_style = 'display:inline-block;border:'+bt_border_size+'px solid '+bt_border_color+';background:'+bt_bg+';color:'+bt_color+';'+bt_style; 
	document.id('type_prev').setAttribute('style', new_style);	
}
</script>