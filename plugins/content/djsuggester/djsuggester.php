<?php
/**
 * @package DJ-Suggester
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */

// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.filesystem.file' );

class plgContentDJSuggester extends JPlugin
{
	protected static $component = false;
	
	function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		$fieldsets = $form->getFieldsets();
		
		if ($app->isAdmin() && isset($fieldsets['basic']->ext) && $fieldsets['basic']->ext == 'djsuggester') {
			$this->loadLanguage();
			
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');
			jimport('joomla.application.component.helper');
			
			$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'params';
			$files = JFolder::files($path, '\.xml$');
			
			foreach($files as $file) {
				
				// get the name of the component
				$com = JFile::stripExt($file);
				
				// check whether component exists and it's enabled
				$installed = JFile::exists(JPATH_ROOT.'/components/com_'.$com.'/'.$com.'.php');
				//$this->dd($com.' is'.($installed ? '':' not').' installed');
				if($installed && JComponentHelper::getComponent('com_'.$com, true)->enabled) {
					// add component options tab to the suggester 
					$form->loadFile($path . DIRECTORY_SEPARATOR . $file, false, '//form');
				}
			}
			
			$path = JPATH_ROOT . '/media/djsuggester/themes/custom.css';
			JFile::delete($path);
			$path = JPATH_ROOT . '/media/djsuggester/themes/custom_rtl.css';
			JFile::delete($path);
		}
	}
	
	/**
	 * Plugin that loads DJ-Suggester box
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}
		
		$app = JFactory::getApplication();
		
		// Don't display suggester in component view and only for html format
		if ($app->input->get('tmpl') == 'component' || $app->input->get('format', 'html') != 'html') {
			return true;
		}
		
		$this->loadLanguage();
		require_once (dirname(__FILE__) . '/helpers/helperversion.php');
		
		DJSuggesterHelper::parseParams($this->params);
		
		if($this->params->get('pro')) {
			DJSuggesterHelper::handleModules($article, $this->params);
		}
		
		// check menu filtering for component suggestion
		$menu = $app->getMenu('site');
		$amenu = $menu->getActive();
		$mitems = $this->params->get('itemids',array('0'=>''));
		if($this->params->get('filter_menu',1) && !empty($mitems[0]) && (!$amenu || ($amenu && !in_array($amenu->id, $mitems)))) { // itemid is not included 
			return;
		} else if(!$this->params->get('filter_menu',1) && $amenu && in_array($amenu->id, $mitems) ) { // item id is excluded
			return;
		}
		
		if(self::$component) { // component already suggested
			return;
		// suggest next article
		} else if ($app->input->get('option') == 'com_content' && $context == 'com_content.article' && $this->params->get('articles')) {
			
			self::$component = true;
			
			DJSuggesterHelper::handleArticle($context, $article, $this->params);
			
		// sugesst next djcatalog2 product
		} else if ($app->input->get('option') == 'com_djcatalog2' && $app->input->get('view') == 'item' && $this->params->get('djcatalog2') && JFile::exists(JPATH_ROOT.'/components/com_djcatalog2/djcatalog2.php')) { 
			
			self::$component = true;
			
			DJSuggesterHelper::handleDJCatalog2($article, $this->params);
			
		// sugesst next K2 Item
		} else if($context == 'com_k2.item' && $this->params->get('k2') && JFile::exists(JPATH_ROOT.'/components/com_k2/k2.php')) {
			
			self::$component = true;
			
			DJSuggesterHelper::handleK2Item($article, $this->params);
			
		// sugesst next Easyblog entry
		} else if($this->params->get('easyblog') && $context == 'easyblog.blog' && $app->input->get('option') == 'com_easyblog' && $app->input->get('view') == 'entry' && JFile::exists(JPATH_ROOT.'/components/com_easyblog/easyblog.php')) {
			
			self::$component = true;
			
			DJSuggesterHelper::handleEasyblog($article, $this->params);
			
		// sugesst next djclassifieds ad
		} else if ($app->input->get('option') == 'com_djclassifieds' && $app->input->get('view') == 'item' && $this->params->get('djclassifieds') && JFile::exists(JPATH_ROOT.'/components/com_djclassifieds/djclassifieds.php')) { 
			
			self::$component = true;
			
			DJSuggesterHelper::handleDJClassifieds($article, $this->params);
			
		// sugesst next Zoo Item
		} else if($app->input->get('option') == 'com_zoo' && $app->input->get('task') == 'item' && $this->params->get('zoo') && JFile::exists(JPATH_ROOT.'/components/com_zoo/zoo.php')) {
			
			self::$component = true;
			
			DJSuggesterHelper::handleZooItem($article, $this->params);
		}
			
	}
}

