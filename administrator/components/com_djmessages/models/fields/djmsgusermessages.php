<?php
/**
 * @version $Id: djmessages.php 4 2017-04-13 13:21:23Z michal $
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 * DJ-Messages is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Messages is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Messages. If not, see <http://www.gnu.org/licenses/>.
 *
 */
 

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('user');

/**
 * Supports an modal select of user that have access to com_messages
 *
 * @since  1.6
 */
class JFormFieldDJMSGUserMessages extends JFormFieldUser
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	public $type = 'DJMSGUserMessages';


	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  array|null array of users to exclude or null to to not exclude them
	 *
	 * @since   1.6
	 */
	protected function getExcluded()
	{
		return array(JFactory::getUser()->id);
	}
}
