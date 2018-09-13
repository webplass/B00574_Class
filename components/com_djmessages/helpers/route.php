<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.helper');

class DJMessagesHelperRoute
{
	protected static $lookup;

	public static function getMessagesRoute($msg_id = 0)
	{
		$needles = array(
				'messages' => array(0)
		);

		//Create the link
		$link = 'index.php?option=com_djmessages&view=messages';
		if ($msg_id > 0) {
			$link .= '&id='.$msg_id;
		}

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}
	
	public static function getMessageRoute($msg_id = 0)
	{
		$needles = array(
				'messages' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djmessages&view=message';
		if ($msg_id > 0) {
			$link .= '&id='.$msg_id;
		}
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getFormRoute()
	{
		$needles = array(
				'messages' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djmessages&view=form';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getAttachmentDownload($msg_id, $file_name) {
		$link = 'index.php?option=com_djmessages&task=download_attachment&id='.$msg_id.'&file='.base64_encode($file_name).'&format=raw';
		
		$needles = array(
			'messages' => array(0)
		);
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		
		return $link;
	}

	public static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$params = JComponentHelper::getParams('com_djmessages');


		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_djmessages');
			$items		= $menus->getItems('component_id', $component->id);

			if (count($items)) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view']))
					{
						$view = $item->query['view'];

						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}
						self::$lookup[$view][0] = $item->id;
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					if (is_array($ids)) {
						foreach($ids as $id)
						{
							if (isset(self::$lookup[$view][$id])) {
								return self::$lookup[$view][$id];
							}
						}
					} else if (isset(self::$lookup[$view][$ids])) {
						return self::$lookup[$view][$ids];
					}
				}
			}
		}

		return null;
	}
}

?>
