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

$mod_attribs = array('style'=>'xhtml');
?>
<div id="dj-classifieds" class="clearfix djcftheme-<?php echo $this->theme;?> <?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php
		$modules_djcf = JModuleHelper::getModules('djcf-top');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-ad-top clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';		
		}
	?>
			
	<div class="dj-item ghost">
		<div class="title_top info">
			<h2><?php echo $this->escape($this->item->name) ?> <small class="badge badge-important"><?php echo JText::_('COM_DJCLASSIFIEDS_GHOST_AD') ?></small></h2>
		</div>
		
		<div class="dj-item-in">
			
			<?php echo JHTML::_('content.prepare', $this->item->content); ?>
			
		</div>
	</div>	
	
	<?php 
		$modules_djcf = &JModuleHelper::getModules('djcf-bottom');
		if(count($modules_djcf)>0){
			echo '<div class="djcf-ad-bottom clearfix">';
			foreach (array_keys($modules_djcf) as $m){
				echo JModuleHelper::renderModule($modules_djcf[$m],$mod_attribs);
			}
			echo'</div>';
		}
	?>
</div>
