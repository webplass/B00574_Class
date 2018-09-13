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

require_once JPath::clean(JPATH_ROOT.'/components/com_djmessages/helpers/route.php');
require_once JPath::clean(dirname(__FILE__).'/helper.php');

$mId = $module->id;
$data = ModDJMessagesNotificationsHelper::getData($params, $user);
$doc = JFactory::getDocument();

$load_fa = $params->get('load_fontawesome', 0);

if( $load_fa == 1 ) {
	$doc->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
}

$theme = $params->get('theme', 1);
$theme_class = ( $theme == 1 ) ? 'default' : 'override';

if( $theme == 1 ) { //default
	$doc->addStyleSheet(JURI::root(true).'/modules/mod_djmsgnotifications/assets/default.css');
}

require JModuleHelper::getLayoutPath('mod_djmsgnotifications', 'default');