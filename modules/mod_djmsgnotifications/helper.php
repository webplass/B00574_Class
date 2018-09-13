<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

class ModDJMessagesNotificationsHelper {
	public static function getData($params, $user) {
		JModelLegacy::addIncludePath(JPath::clean(JPATH_ROOT.'/components/com_djmessages/models'), 'DJMessagesModel');
		$model = JModelLegacy::getInstance('Messages', 'DJMessagesModel', array('ignore_request'=>true));
		$state = $model->getState();
		
		$model->setState('filter.user', $user->id);
		$model->setState('filter.origin', 'inbox');
		$model->setState('filter.new', true);
		
		$items = $model->getItems();
		
		return $items;
	}
}