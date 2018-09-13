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

if (!JFactory::getUser()->authorise('core.manage', 'com_djmessages'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/route.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/attachment.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/messenger.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/mailer.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/djlicense.php';

JPluginHelper::importPlugin('djmessages');

$controller	= JControllerLegacy::getInstance('DJMessages');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
