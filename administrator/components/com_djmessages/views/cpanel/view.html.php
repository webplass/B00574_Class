<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');

class DJMessagesViewCpanel extends JViewLegacy
{
	function display($tpl = null)
	{
		JToolBarHelper::title( JText::_('COM_DJMESSAGES'));
		JToolBarHelper::preferences('com_djmessages');
		
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}
}