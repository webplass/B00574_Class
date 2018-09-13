<?php
/**
 * @version $Id: edit_legacy_plgparams.php 99 2017-08-04 10:55:30Z szymon $
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
 
// No direct access.
defined('_JEXEC') or die;

foreach($this->plgParams as $plgParams) {
	
	$name = str_replace('plgParams_','',$plgParams->getName());
	if($this->item->id && $this->item->source != $name) continue;
	$dispatcher	= JDispatcher::getInstance();
	JPluginHelper::importPlugin('djmediatools', $name);
	$results = $dispatcher->trigger('onCheckRequirements', array(&$name));
	$reqMet = true;
	if (isset($results[0])) $reqMet = $results[0];
	
	$fieldSets = $plgParams->getFieldsets('params'); ?>
	<?php if(is_string($reqMet)) { ?>
	<li id="<?php echo $plgParams->getName(); ?>" class="plgParams">
		<span class="spacer">
			<span class="djnotice"><label><?php echo JText::sprintf('COM_DJMEDIATOOLS_PLUGIN_REQUIREMENTS_NOTICE', JText::_(strtoupper('plg_djmediatools_' . $name . '_LABEL')), $reqMet); ?></label></span>						
		</span>
	</li>
	<?php } else { ?>
	<div id="<?php echo $plgParams->getName(); ?>" class="plgParams">
			<ul class="adminformlist">
				<?php if(!JPluginHelper::isEnabled('djmediatools',$name)) { ?>
				<li>
					<span class="spacer">						
						<span class="djnotice"><label>
							<?php echo JText::sprintf('COM_DJMEDIATOOLS_PLUGIN_DISABLED_NOTICE', '<a href="index.php?option=com_plugins&view=plugins&filter_folder=djmediatools" target="_blank">'.JText::_(strtoupper('plg_djmediatools_' . $name)).'</a>'); ?>
						</label></span>						
					</span>
				</li>
				<?php } else { ?>
				<?php foreach ($plgParams->getFieldset('source_settings') as $field) : ?>
					<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
				<?php endforeach; 
				} ?>
			</ul>
		<div style="clear:both"></div>
	</div>
	<?php } ?>
	
<?php } ?>