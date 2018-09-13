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

defined('_JEXEC') or die('Restricted access');

/* This is a template for creating Ghost Ad content
 */
?>

<div class="ghostad_content">
	
	<div class="row-fluid">
		
		<div class="span8">
						
			<div class="ga_section category">
				<span class="muted"><?php echo JText::_('COM_DJCLASSIFIEDS_CATEGORY'); ?></span>
				<h3><?php echo $item->category_path ?></h3>				
			</div>			
			
			<div class="ga_section description">
				<span class="muted"><?php echo JText::_('COM_DJCLASSIFIEDS_DESCRIPTION'); ?></span>
				<div class="intro_desc_content"><?php echo $item->intro_desc ?></div>
				<div class="desc_content"><?php echo $item->description ?></div>
			</div>
			
			<?php if($item->video) { ?>
			<div class="ga_section video">
				<h4><?php echo JText::_('COM_DJCLASSIFIEDS_VIDEO'); ?></h4>
				<p class="ga_section_value">
					<a href="<?php echo $item->video ?>" target="_blank">
						<?php echo $item->video ?></a>
				</p>				
			</div>
			<?php } ?>
			
			<?php if(count($item->fields)) { ?>
			<div class="ga_section fields">
				<h4><?php echo JText::_('COM_DJCLASSIFIEDS_CUSTOM_DETAILS'); ?></h4>
				<?php foreach($item->fields as $field) { ?>
				<div class="ga_row row_<?php echo $field->name;?>">
					<span class="ga_row_label"><?php echo JText::_($field->label); ?>:</span>
					<span class="ga_row_value"><?php echo $field->value_text; ?>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
			
		</div>
		<div class="span4">
			
			<?php if($item->price) { ?>
			<div class="ga_section price">
				<h4><?php echo JText::_('COM_DJCLASSIFIEDS_PRICE'); ?></h4>
				<p class="ga_section_value"><?php echo DJClassifiedsTheme::priceFormat($item->price, $item->currency); ?></p>			
			</div>
			<?php } ?>
			
			<div class="ga_section author">
				<h4><?php echo JText::_('COM_DJCLASSIFIEDS_CREATED_BY'); ?></h4>
				<?php if($item->author) { ?>
					<p class="ga_section_value"><?php echo $item->author; ?></p>
				<?php } else { ?>
					<p class="ga_section_value"><?php echo JText::_('COM_DJCLASSIFIEDS_GUEST'); ?></p>
				<?php } ?>			
			</div>
			
			<div class="ga_section localization">
				<h4><?php echo JText::_('COM_DJCLASSIFIEDS_LOCALIZATION'); ?></h4>
				<p class="ga_section_value"><?php echo $item->region_path ?></p>				
			</div>
			
			<div class="ga_section details">
				<h4><?php echo JText::_('COM_DJCLASSIFIEDS_AD_DETAILS'); ?></h4>
				<div class="ga_row">
					<span class="ga_row_label label"><?php echo JText::_('COM_DJCLASSIFIEDS_AD_ID'); ?>:</span>
					<span class="ga_row_value"><?php echo $item->id; ?></span>
				</div>
				<div class="ga_row">
					<span class="ga_row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_DISPLAYED'); ?>:</span>
					<span class="ga_row_value"><?php echo $item->display; ?></span>
				</div>
				<div class="ga_row">
					<span class="ga_row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_AD_ADDED'); ?></span>
					<span class="ga_row_value">
						<?php echo DJClassifiedsTheme::formatDate(strtotime($item->date_start));  ?>
					</span>
				</div>
				<div class="ga_row">
					<span class="ga_row_label"><?php echo JText::_('COM_DJCLASSIFIEDS_AD_EXPIRES'); ?>:</span>
					<span class="ga_row_value">
						<?php echo DJClassifiedsTheme::formatDate(strtotime($item->date_exp));  ?>
					</span>
				</div>
			</div>
		
		</div>
		
	</div>
</div>

<?php
