<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');


class DJMessagesHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName = 'cpanel')
	{
		JHtmlSidebar::addEntry(
				JText::_('COM_DJMESSAGES_CPANEL'),
				'index.php?option=com_djmessages&view=cpanel',
				$vName == 'cpanel'
				);
		
		JHtmlSidebar::addEntry(
				JText::_('COM_DJMESSAGES_MESSAGES'),
				'index.php?option=com_djmessages&view=messages',
				$vName == 'messages'
				);
		
		JHtmlSidebar::addEntry(
				JText::_('COM_DJMESSAGES_TEMPLATES'),
				'index.php?option=com_djmessages&view=templates',
				$vName == 'templates'
				);
		
		JHtmlSidebar::addEntry(
				JText::_('COM_DJMESSAGES_USERS'),
				'index.php?option=com_djmessages&view=users',
				$vName == 'users'
				);
	}
}
