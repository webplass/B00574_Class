<?php
/**
 * @version 1.0
 * @package DJ-Classifieds Ajax
 * @copyright Copyright (C) 2016 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Piotr Dobrakowski - piotr.dobrakowski@design-joomla.eu
 *
 * DJ-Classifieds Ajax is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Classifieds Ajax is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Classifieds Ajax. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die;

class plgSystemDjcfajax extends JPlugin{
	
	function onAfterRoute(){
		
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$jinput = $app->input;
		
		if(!$app->isAdmin() && $jinput->get('option')=='com_djclassifieds'){
			
			$enable_in = $this->params->get('enable_in',array());
			
			if(!$enable_in || in_array($app->input->get('Itemid','0'), $enable_in)){

				JHTML::_('jquery.framework');
				
				$loader_path = JURI::root().'components/com_djclassifieds/assets/images/loading.gif';
	
				$document->addScriptDeclaration("var DJAjaxParams=".json_encode($this->params).";var DJAjaxVars={'loader_path':'".$loader_path."','page_just_loaded':true};");
				$document->addScriptVersion(JURI::root(true).'/plugins/system/djcfajax/assets/djajax.js');
				
				if($this->params->get('on_pagination','0')){
					if($this->params->get('items_lazy_loading','0')){
						$document->addScriptVersion(JURI::root(true).'/plugins/system/djcfajax/assets/lazyloading.djajax.js');
						if($this->params->get('blog_grid_layout','0') && $jinput->get('view')=='items' && $jinput->get('layout')=='blog'){
							$document->addScript('https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js');
							//$document->addScript('https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.js');
						}
					}
					$document->addStyleSheetVersion(JURI::root(true).'/plugins/system/djcfajax/assets/djajax.css');
				}
	
				if($this->params->get('progress_bar','0')){
					$document->addScript('https://unpkg.com/nprogress@0.2.0/nprogress.js');
					$document->addStyleSheet('https://unpkg.com/nprogress@0.2.0/nprogress.css');
				}
			
			}
		}
	}
}
