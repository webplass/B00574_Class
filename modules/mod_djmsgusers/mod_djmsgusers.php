<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();

if ($user->guest) {
	return false;
}

JHtmlJquery::framework();
JHtmlBehavior::keepalive();

require_once JPath::clean(JPATH_ROOT.'/components/com_djmessages/helpers/route.php');
require_once JPath::clean(JPATH_ROOT.'/components/com_djmessages/helpers/djmessages.php');
require_once JPath::clean(dirname(__FILE__).'/helper.php');

$mId = $module->id;
$data = ModDJMessagesUsersHelper::getData($params, $user);

require JModuleHelper::getLayoutPath('mod_djmsgusers', 'default');