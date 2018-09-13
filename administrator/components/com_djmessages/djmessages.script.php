<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die('Restricted access');

class com_djmessagesInstallerScript {
	function update($parent) {

	}
	
	function preflight($type, $parent)
	{
	}

	function postflight($type, $parent)
	{
		$extFolder = JPath::clean(JPATH_ROOT.'/media/djextensions');
		if (!JFolder::exists($extFolder)) {
			JFolder::create($extFolder);
		}
		
		$folders = array();
		$folders[] = array(
			'src' => JPath::clean(JPATH_ROOT.'/media/djmessages/magnific'),
			'dst' => JPath::clean(JPATH_ROOT.'/media/djextensions/magnific')
		);
		$folders[] = array(
			'src' => JPath::clean(JPATH_ROOT.'/media/djmessages/jquery.ui'),
			'dst' => JPath::clean(JPATH_ROOT.'/media/djextensions/jquery.ui')
		);
		
		foreach ($folders as $folder) {
			if (JFolder::exists($folder['src'])) {
				JFolder::move($folder['src'], $folder['dst']);
			}
		}
		
		if ($type == 'update') {
			require_once(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmessages/helpers/djlicense.php'));
			DJLicense::setUpdateServer('Messages');
		}
	}
}