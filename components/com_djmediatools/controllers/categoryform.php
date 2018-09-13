<?php
/**
 * @version $Id: category.php 18 2013-10-01 15:04:53Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

$version = new JVersion;
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
	abstract class DJMTControllerForm extends JControllerForm
	{
		protected function postSaveHook(JModel &$model, $validData = array())
		{
			if (method_exists($this, '_postSaveHook')) {
				return $this->_postSaveHook($model, $validData);
			}
		}
		
	}	
} else {
	abstract class DJMTControllerForm extends JControllerForm
	{
		protected function postSaveHook(JModelLegacy $model, $validData = array())
		{
			if (method_exists($this, '_postSaveHook')) {
				return $this->_postSaveHook($model, $validData);
			}
		}
	
	}
}

class DJMediatoolsControllerCategoryform extends DJMTControllerForm {
	
	protected $view_list = 'categories';
	
	protected function _postSaveHook($model, $data) {
		
		if($function = JRequest::getVar('f_name')) {
			
			$app = JFactory::getApplication();
			$album = $model->getItem();
			
			$response = array(
				'id'=>$album->id,
				'image'=>(!empty($album->image) ? $album->image : 'administrator/components/com_djmediatools/assets/icon-album.png'),
				'title'=>$album->title
			);
			
			echo json_encode($response);
			
			$app->close();
			
		}
		
	}
}

?>