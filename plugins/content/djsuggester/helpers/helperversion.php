<?php
/**
 * @package DJ-Suggester
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (dirname(__FILE__) . DS . 'helper.php');

class DJSuggesterHelper extends DJSuggesterBaseHelper {
	
	public static function parseParams(&$params) {
	
		// determine if this is a Pro version
		$params->set('pro', 0);
		
		parent::parseParams($params);
	}	
}
