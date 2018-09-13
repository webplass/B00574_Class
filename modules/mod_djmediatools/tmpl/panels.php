<?php
/**
 * @version $Id: panels.php 117 2018-01-24 13:58:45Z szymon $
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
defined('_JEXEC') or die ('Restricted access'); ?>

<div style="border: 0px !important;">

	<ul id="djkwicks<?php echo $mid; ?>" class="kwicks kwicks-horizontal dj-slides">
		<?php $key = 0; foreach ($slides as $slide) { ?>
		<li class="djpanel-<?php echo (++$key) . ($params->get('autoplay') && $key==1 ? ' kwicks-selected kwicks-expanded':'') ?>">
			
			<?php 
				$image = '<span class="dj-image" style="background-image: url('.$slide->grayscale_image.')">'
 						.'<span class="dj-image-color" style="background-image: url('.$slide->resized_image.')"></span>'
 						.'</span>';
			?>
			
			<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>
						
			<div class="dj-slide-desc">
				<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
			</div>
					
		</li>
		<?php } ?>
	</ul>
	
</div>
<div style="clear: both"></div>