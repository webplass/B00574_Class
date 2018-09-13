<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class plgContentDJSuggesterInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
		
	function postflight( $type, $parent ) {
		
		if($type == 'update') {
			
			// we need to handle the update server for package here
			require_once(JPath::clean(JPATH_ROOT.'/plugins/content/djsuggester/fields/djupdater.php'));
			JFormFieldDJUpdater::setUpdateServer('Suggester');
		}
		
		// delete old suggester light plugin
		$path = JPath::clean(JPATH_PLUGINS.'/content/djsuggesterlight');
		
		if(JFolder::exists($path)) {
			
			$db = JFactory::getDbo();
			
			// disable old update server
			$db->setQuery("DELETE FROM #__update_sites WHERE name='DJ-Suggester Light'");
			$db->query();
				
			$db->setQuery("DELETE FROM #__extensions WHERE name='plg_content_djsuggesterlight'");
			$db->execute();
				
			JFolder::delete($path);
		}
	}
}
