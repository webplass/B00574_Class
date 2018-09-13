<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Component
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
* 
* 
* DJ Classifieds is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ Classifieds is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ Classifieds. If not, see <http://www.gnu.org/licenses/>.
* 
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class Com_DJClassifiedsInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight( $type, $parent ) {
		$jversion = new JVersion();
		$this->oldversion = $this->getParam('version');
		
		if(version_compare($this->getParam('version'), '2.0', 'lt')) {
			
			$db = JFactory::getDBO();
			$db->setQuery('SELECT extension_id FROM #__extensions WHERE name = "com_djclassifieds"');
			$ext_id = $db->loadResult();
			// adding the schema version before update to 2.0+
			if($ext_id) {
				$db->setQuery("INSERT INTO #__schemas (extension_id, version_id) VALUES (".$ext_id.", '1.1')");
				$db->query();
			}
		}
		
		$spacer = JPATH_ROOT.'/administrator/components/com_djclassifieds/models/fields/djspacer.php';		
		if (JFile::exists($spacer)){
			JFile::delete($spacer);				
		}

	}
	
	function postflight( $type, $parent ) {		
		$app	= JFactory::getApplication();
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element='djcfquickicon'");
		$db->query();
		
		if(version_compare($this->oldversion, '3.6', 'lt')) {							
			$par = JComponentHelper::getParams( 'com_djclassifieds' );
			$exp_days = $par->get('exp_days',7);
		
			$query = "SELECT * FROM #__djcf_promotions ORDER BY id";
			$db->setQuery($query);
			$proms = $db->loadObjectList('name');
		
			$query = "INSERT INTO #__djcf_promotions_prices(`prom_id`,`days`,`price`,`points`) VALUES ";
			foreach($proms as $prom){
				$query .= "('".$prom->id."','".$exp_days."','".$prom->price."','".$prom->points."'), ";
			}
			$query = substr($query, 0, -2);
			$db->setQuery($query);
			$db->query();
		
		
			$query = "SELECT * FROM #__djcf_items WHERE promotions != '' ORDER BY id";
			$db->setQuery($query);
			$items = $db->loadObjectList();
		
			//echo '<pre>';print_r($items);die();
			if(count($items)){
				$query = "INSERT INTO #__djcf_items_promotions(`item_id`,`prom_id`,`date_start`,`date_exp`,`days`) VALUES ";
				foreach($items as $item){
					$item_proms = explode(',', $item->promotions);
					foreach($item_proms as $item_p){
						//echo "'".$item->id."','".$proms[$item_p]->id."','".$item->date_start."','".$item->date_exp."','".$exp_days."'".'<br />';
						if(isset($proms[$item_p]->id)){
							$query .= "('".$item->id."','".$proms[$item_p]->id."','".$item->date_start."','".$item->date_exp."','".$exp_days."'), ";
						}
					}
				}
				$query = substr($query, 0, -2);
				$db->setQuery($query);
				$db->query();
			}
			
			$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_PROMOTIONS_MIGRATED_SUCCESFULLY'));			
			$app->enqueueMessage(JText::_('COM_DJCLASSIFIEDS_ANONYMOUS_INFORMATIONS_MESSAGE'),'Notice');
		
		}
		
		// move shared code
		$src = JPath::clean(JPATH_ROOT.'/media/djclassifieds/djextensions');
		$dst = JPath::clean(JPATH_ROOT.'/media/djextensions');
		JFolder::create($dst);		
		$folders = JFolder::folders($src);		
		foreach($folders as $folder) {
			JFolder::move($src.DIRECTORY_SEPARATOR.$folder, $dst.DIRECTORY_SEPARATOR.$folder);
		}		
		@JFolder::delete($src);
		
		require_once(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djclassifieds/lib/djlicense.php'));
		DJLicense::setUpdateServer('Classifieds');
		
	}
	
	function getParam( $name ) {
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE element = "com_djclassifieds" AND type="component"');
		$manifest = json_decode( $db->loadResult(), true );
		return $manifest[ $name ];
	}
}
