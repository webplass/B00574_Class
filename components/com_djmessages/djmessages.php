<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');


jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once JPATH_COMPONENT . '/helpers/route.php';
require_once JPATH_COMPONENT . '/helpers/djmessages.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/attachment.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/messenger.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/mailer.php';

DJMessagesHelper::loadComponentLanguage();

JPluginHelper::importPlugin('djmessages');

$input = JFactory::getApplication()->input;

$controller = JControllerLegacy::getInstance('DJMessages');
$controller->execute($input->get('task'));
$controller->redirect();
 