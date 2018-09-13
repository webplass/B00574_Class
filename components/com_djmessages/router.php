<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;
defined ('_JEXEC') or die('Restricted access');

class DJMessagesRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_contact component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_djmessages');

		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$mView = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$mId = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];

		$view = isset($query['view']) ? $query['view'] : 'messages';
		
		if ($mView != $view || empty($query['Itemid']) || empty($menuItem) || $menuItem->component != 'com_djmessages')
		{
			if ($view != 'messages') {
				$segments[] = $view;
			}
		}
		
		if ( isset($query['view']) ) {
			unset($query['view']);
		}
		
		if ($mView == 'messages') {
			if (isset($query['id']))
			{
				$segments[] = $query['id'];
				unset($query['id']);
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Get the active menu item.
		$item = $this->menu->getActive();
		$params = JComponentHelper::getParams('com_djmessages');

		$count = count($segments);

		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id'] = $segments[$count - 1];
			return $vars;
		}
		
		if (isset($item->query['view']) && $item->query['view'] == 'messages') {
			if ($count == 1){
				
				if (is_numeric($segments[0])) {
					$vars['id'] = $segments[0];
				} else {
					$vars['view'] = $segments[0];
				}
			}
			else if ($count == 2) {
				$vars['view'] = $segments[0];
				if ($count > 1) {
					$vars['id'] = $segments[1];
				}
			}
		}
		
		return $vars;
	}
}

/**
 * Contact router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function DJMessagesBuildRoute(&$query)
{
	$router = new DJMessagesRouter;

	return $router->build($query);
}

/**
 * Contact router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function DJMessagesParseRoute($segments)
{
	$router = new DJMessagesRouter;

	return $router->parse($segments);
}
