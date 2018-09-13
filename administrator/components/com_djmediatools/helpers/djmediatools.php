<?php
/**
 * @version $Id: djmediatools.php 99 2017-08-04 10:55:30Z szymon $
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

defined('_JEXEC') or die;

abstract class DJMediatoolsHelper
{
	
	public static function addSubmenu($vName)
	{
		if($vName=='item' || $vName=='category') return;
		
		JSubMenuHelper::addEntry(
			JText::_('COM_DJMEDIATOOLS_SUBMENU_CPANEL'),
			'index.php?option=com_djmediatools',
			$vName == 'cpanel'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_DJMEDIATOOLS_SUBMENU_CATEGORIES'),
			'index.php?option=com_djmediatools&view=categories',
			$vName == 'categories'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_DJMEDIATOOLS_SUBMENU_SLIDES'),
			'index.php?option=com_djmediatools&view=items',
			$vName == 'items'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_DJMEDIATOOLS_SUBMENU_IMAGES_CACHE'),
			'index.php?option=com_djmediatools&view=images',
			$vName == 'images'
		);
	}
	
}
?>