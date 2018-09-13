<?php
/**
 * @version 1.0
 * @package DJ-Classifieds Ajax
 * @copyright Copyright (C) 2016 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Piotr Dobrakowski - piotr.dobrakowski@design-joomla.eu
 *
 * DJ-Classifieds Ajax is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Classifieds Ajax is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Classifieds Ajax. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

class plgsystemdjcfajaxInstallerScript {
	function postflight($type, $parent)
	{
		$db = JFactory::getDBO();
		
		if($type == 'update') {
			$query = "UPDATE #__update_sites SET enabled=0 WHERE name='DJ-Classfieds Ajax Plugin' AND type='extension'";
			$db->setQuery($query);
			$db->query();
		}
	}
}