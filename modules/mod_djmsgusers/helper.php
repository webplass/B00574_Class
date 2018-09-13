<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */
use Joomla\Registry\Registry;

defined ('_JEXEC') or die('Restricted access');

class ModDJMessagesUsersHelper {
	public static function getData($params, $user) {
		return DJMessagesHelper::getUsers($user->id, $params->get('orderby', 'name-asc'), $params->get('limit', 10), 0);
	}
}