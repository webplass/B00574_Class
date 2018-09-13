<?php
/**
 * @version $Id: slideshow.php 108 2017-09-20 13:59:40Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die ('Restricted access'); 

$descpos = $params->get('desc_position'); ?>

<div style="border: 0px !important;">
<div id="dj-slideshow<?php echo $mid; ?>" class="dj-slideshow<?php echo $descpos ? ' desc-'.$descpos:''; ?>">

	<?php if($params->get('show_custom_nav') && $params->get('custom_nav_pos')=='above') { ?>
		<div class="dj-indicators <?php echo ($params->get('show_custom_nav')==2 ? 'showOnMouseOver' : ''); ?>">
			<div class="dj-indicators-in">
				<?php for($i = 1; $i <= count($slides); $i++) { ?>
					<a href="#" class="dj-load-button<?php if ($i == 1) echo ' dj-load-button-active'; ?>"><span class="dj-key"><?php echo $i; ?></span></a>
				<?php } ?>
			</div>
        </div>
	<?php } ?>

	<div class="dj-slideshow-in">
		<div class="dj-slides">
        	
          	<?php foreach ($slides as $key => $slide) { ?>
			
				<div class="dj-slide dj-slide-<?php echo ($key+1) . ($key == 0 ? ' dj-active':''); ?>">
					<div class="dj-slide-in">
					
						<?php if($descpos == 'above') { ?>
							<div class="dj-slide-desc">
								<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
							</div>
						<?php } ?>
						
						<?php $image = 	'<img src="'.$params->get('blank').'" data-src="'.$slide->resized_image.'" '
 									.	(!empty($slide->data_srcset) ? ' data-srcset="'.$slide->data_srcset.'" data-sizes="'.$slide->sizes.'" ':'')
 									.	'alt="'.$slide->alt.'" class="dj-image" width="'.$slide->size->w.'" height="'.$slide->size->h.'" '
 									.	(!empty($slide->img_title) ? ' title="'.$slide->img_title.'"':'') . ' />'; ?>
            			
						<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>
						
						<?php if($descpos != 'above' && $descpos != 'tip') { ?>
							<div class="dj-slide-desc">
								<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
							</div>
						<?php } ?>
					</div>
				</div>
								
            <?php } ?>
        	
        </div>
        <div class="dj-navigation">
        	<div class="dj-navigation-in">
        		<?php if($params->get('show_arrows')) { ?>
	        		<a href="#" class="dj-prev <?php echo ($params->get('show_arrows')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->prev; ?>" alt="<?php echo JText::_('Previous'); ?>" /></a>
					<a href="#" class="dj-next <?php echo ($params->get('show_arrows')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->next; ?>" alt="<?php echo JText::_('Next'); ?>" /></a>
				<?php } ?>
				<?php if($params->get('show_buttons')) { ?>
					<a href="#" class="dj-play <?php echo ($params->get('show_buttons')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->play; ?>" alt="<?php echo JText::_('Play'); ?>" /></a>
					<a href="#" class="dj-pause <?php echo ($params->get('show_buttons')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->pause; ?>" alt="<?php echo JText::_('Pause'); ?>" /></a>
        		<?php } ?>
			</div>
		</div>
		<?php if($params->get('show_custom_nav') && ($params->get('custom_nav_pos')=='topin' || $params->get('custom_nav_pos')=='bottomin')) { ?>
		<div class="dj-indicators <?php echo ($params->get('show_custom_nav')==2 ? 'showOnMouseOver' : ''); ?>">
			<div class="dj-indicators-in">
				<?php for($i = 1; $i <= count($slides); $i++) { ?>
					<a href="#" class="dj-load-button<?php if ($i == 1) echo ' dj-load-button-active'; ?>"><span class="dj-key"><?php echo $i; ?></span></a>
				<?php } ?>
			</div>
        </div>
		<?php } ?>
		
		<div class="dj-loader"></div>
	</div>
	
	<?php if($params->get('show_custom_nav') && $params->get('custom_nav_pos')=='below') { ?>
		<div class="dj-indicators <?php echo ($params->get('show_custom_nav')==2 ? 'showOnMouseOver' : ''); ?>">
			<div class="dj-indicators-in">
				<?php for($i = 1; $i <= count($slides); $i++) { ?>
					<a href="#" class="dj-load-button<?php if ($i == 1) echo ' dj-load-button-active'; ?>"><span class="dj-key"><?php echo $i; ?></span></a>
				<?php } ?>
			</div>
        </div>
	<?php } ?>
</div>
<div style="clear: both" class="djslideshow-end"></div>
</div>