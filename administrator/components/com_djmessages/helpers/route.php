<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');


require_once JPATH_ROOT.'/components/com_djmessages/helpers/route.php';

// usage: echo DJMessagesHelperSiteRoute::buildRoute('getCalculatorRoute', array('1'), '&layout=promotion#plan-3', true);

class DJMessagesHelperSiteRoute extends DJMessagesHelperRoute {

	public static function buildRoute($function, $args = array(), $params = '', $xhtml = true) {
		$liveSite = substr(JUri::root(), 0, -1);
		$app    = JApplication::getInstance('Site');
		$router = $app->getRouter();
		$routed = self::call(array('DJMessagesHelperRoute', $function), $args);
		$url = $router->build($liveSite .'/'. $routed . $params)->toString();

		$uri = JUri::getInstance();
		$substitute = $uri->toString(array('scheme', 'host', 'port'));

		$link = str_replace($liveSite . '/administrator', $substitute, $url);

		$link = preg_replace('/\s/u', '%20', $link);

		return ($xhtml) ? htmlspecialchars($link) : $link;
	}

	protected static function call($function, $args) {
		if (!is_callable($function)) {
			throw new InvalidArgumentException('Function not supported', 500);
		}
		$temp = array();
		foreach ($args as &$arg) {
			$temp[] = &$arg;
		}
		return call_user_func_array($function, $temp);
	}
}
