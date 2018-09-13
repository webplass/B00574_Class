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
defined('_JEXEC') or die('Restricted access');

class DJSuggesterBaseHelper {
	
	protected static $version = null;
	protected static $dataAttr = null;
	protected static $active = false;
	
	public static function parseParams(&$params) {
		
		if($params->get('pro')) {
			$params->def('offset','400');
			$params->def('delay','15');
			$params->def('hideintop','0');
			$params->def('show_once','1');
		} else {
			$params->set('djcatalog2','0');
			$params->set('djclassifieds','0');
			$params->set('easyblog','0');
			$params->set('k2','0');
			$params->set('zoo','0');
			$params->set('offset','400');
			$params->set('delay','15');
			$params->set('hideintop','0');
			$params->set('show_once','0');			
			if($params->get('theme')=='_custom') $params->set('theme', 'default');
		}
	}
	
	public static function getDataAttr(&$params) {
		
		if(!self::$dataAttr) {
			
			self::$dataAttr = 
			  ' data-offset="'.$params->get('offset','400')
			.'" data-delay="'.$params->get('delay','15')
			.'" data-hideintop="'.$params->get('hideintop','0')
			.'" data-show-once="'.$params->get('show_once','1')
			.'" data-anim-in="'.$params->get('animation_in','rotateInDownRight')
			.'" data-anim-out="'.$params->get('animation_out','fadeOutDown')
			.'" data-anim-speed="'.$params->get('animation_speed','normal').'"';
		}
		
		return self::$dataAttr;
	}
	
	public static function handleArticle($context, &$article, &$params) {
		
		// don't proceed for filtered categories
		$filter_cats = $params->get('articles_cats',array('0'=>''));
		if( ($params->get('articles_filter_cat',1) && (empty($filter_cats[0]) || in_array($article->catid, $filter_cats))) ||
				(!$params->get('articles_filter_cat',1) && !in_array($article->catid, $filter_cats) ) ){
				
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			$lang = JFactory::getLanguage();
			$nullDate = $db->getNullDate();
			$date	= JFactory::getDate();
			$now = $date->toSql();
			$canPublish = $user->authorise('core.edit.state',$context.$article->id);
			$where = array();
		
			$where[] = 'a.state = ' . (int) $article->state;
			if(!$canPublish) $where[] = 'a.access = ' . (int) $article->access;
			$where[] = '(a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).')';
			$where[] = '(a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')';
			if($params->get('articles_mode')) { // follow category mode
				$where[] = 'a.catid = '.$article->catid;
			} else if($params->get('articles_filter_cat',1)) { // include categories
				if(!empty($filter_cats[0])) {
					$where[] = 'a.catid IN ('.implode(',', $filter_cats).')';
				}
			} else if(!$params->get('articles_filter_cat',1)) { // exclude categories
				if(empty($filter_cats[0])) unset($filter_cats[0]);
				if(count($filter_cats) > 0) $where[] = 'a.catid NOT IN ('.implode(',', $filter_cats).')';
			}
			//djdebug($where);
			$order = $params->get('articles_order');
			$orderby = '';
			switch($order) {
				case 'title':
					$orderby = 'a.title';
					break;
				case 'rtitle':
					$orderby = 'a.title DESC';
					break;
				case 'date':
					$orderby = 'a.created';
					break;
				case 'rdate':
					$orderby = 'a.created DESC';
					break;
				case 'rordering':
					$orderby = 'a.ordering DESC';
					break;
				case 'random':
				case 'ordering':
				default:
					$orderby = 'a.ordering';
					break;
			}
		
			$query = 'SELECT a.id, a.title, a.alias, a.introtext, a.fulltext, a.images, a.catid, c.title as category, c.alias as category_alias, @num := @num + 1 AS position '
					.' FROM #__content a '
					.' JOIN (SELECT @num := 0) n '
							.' LEFT JOIN #__categories as c ON c.id = a.catid '
									.' WHERE ' .implode(' AND ', $where)
									.' ORDER BY ' .$orderby;
		
			$next = self::getNext($query, $article->id, $order == 'random');
		
			if($next) {
					
				$next->slug = $next->id .':'. $next->alias;
				$next->catslug = $next->catid .':'. $next->category_alias;
				$next->link = JRoute::_(ContentHelperRoute::getArticleRoute($next->slug, $next->catslug));
				//$this->djdebug($next);
				$html = '<div class="dj-suggester component content" '.self::getDataAttr($params).' style="display:none;">';
				$html.= '<div class="dj-suggester-head">'.$params->get('articles_header',JText::_('PLG_DJSUGGESTER_HEADER')).'</div>';
				if($params->get('articles_image')) {
					$images = new JRegistry($next->images);
					if($images->get('image_intro')) $next->image = $images->get('image_intro');
					else if($images->get('image_fulltext')) $next->image = $images->get('image_fulltext');
					else $next->image = self::getImageFromText($next->introtext);
					// if no image found in article images and introtext then try fulltext
					if(!$next->image) $next->image = self::getImageFromText($next->fulltext);
					if($next->image) $html.= '<a href="'.$next->link.'"><img src="'.$next->image.'" width="'.$params->get('articles_imagewidth','150').'" alt="'.htmlspecialchars($next->title).'" class="dj-suggester-image" /></a>';
				}
				$html.= '<h4 class="dj-suggester-title"><a href="'.$next->link.'">'. htmlspecialchars($next->title) .'</a></h4>';
				$html.= '<div class="dj-suggester-content">'.self::truncate($next->introtext, $params).'</div>';
				$html.= '</div>';
					
				$article->text .= $html;
				self::activeSuggester($params);
			}
		}
	}
	
	protected static function getNext($query, $current_id, $random) {
	
		$db = JFactory::getDBO();
	
		$next = null;
		if($random) {
			// get random item, but not the current one
			$db->setQuery('SELECT * FROM ('.$query.') as sub WHERE sub.id != '.$current_id.' ORDER BY RAND() LIMIT 1');
			$next = $db->loadObject();
		} else {
			// check the position of current item
			$db->setQuery('SELECT position FROM ('.$query.') as sub WHERE sub.id = '.$current_id.' LIMIT 1');
			$position = $db->loadResult();
			// get the item at the next position
			$db->setQuery('SELECT * FROM ('.$query.') as sub WHERE position='.($position + 1).' LIMIT 1');
			$next = $db->loadObject();
			// if current item is on the last position then get 1st item if it's different than current item
			if(!$next && $position != 1) {
				$db->setQuery('SELECT * FROM ('.$query.') as sub WHERE position=1 LIMIT 1');
				$next = $db->loadObject();
			}
		}
	
		//$this->dd($next);
		// return next item object
		return $next;
	}
	

	protected static function truncate($text, $params) {
	
		// clean content plugins
		$text = preg_replace("/\{.+?\}/", "", $text);
	
		if($params->get('desc_turncate',1)) {
				
			$text = strip_tags($text);
			$limit = $params->get('desc_limit',0);
				
			if($limit && $limit - strlen($text) < 0) {
				// don't cut in the middle of the word unless it's longer than 20 chars
				if($pos = strpos($text, ' ', $limit)) $limit = ($pos - $limit > 20) ? $limit : $pos;
				// cut text and add dots
				$text = substr($text, 0, $limit);
				if(preg_match('/([a-zA-Z0-9])$/', $text)) $text.='&hellip;';
				$text = '<p>'.nl2br($text).'</p>';
			}
		}
	
		return $text;
	}
	

	protected static function activeSuggester($params) {
	
		if(!self::$active) {
				
			self::$active = true;
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			$direction = $doc->direction;
			$defercss = array();
				
			// direction integration with joomla monster templates
			if ($app->input->get('direction') == 'rtl'){
				$direction = 'rtl';
			} else if ($app->input->get('direction') == 'ltr') {
				$direction = 'ltr';
			} else {
				if (isset($_COOKIE['jmfdirection'])) {
					$direction = $_COOKIE['jmfdirection'];
				} else {
					$direction = $app->input->get('jmfdirection', $direction);
				}
			}
			
			// add theme css
			$defercss = self::getTheme($params, $direction);
			
			$defercss['djsuggester_animations_css'] = JURI::root(true).'/plugins/content/djsuggester/assets/animations.css';
			$defercss['animate_min_css'] = JURI::root(true).'/media/djextensions/css/animate.min.css';
			$defercss['animate_ext_css'] = JURI::root(true).'/media/djextensions/css/animate.ext.css';
				
			// optimized css delivery
			$js = "
			(function(){
				var cb = function() {
					var add = function(css, id) {
						if(document.getElementById(id)) return;
	
						var l = document.createElement('link'); l.rel = 'stylesheet'; l.id = id; l.href = css;
						var h = document.getElementsByTagName('head')[0]; h.appendChild(l);
					}";
			foreach($defercss as $id => $css) {
				$js .= "
				add('$css', '$id');";
			}
			$js .= "
				};
				var raf = requestAnimationFrame || mozRequestAnimationFrame || webkitRequestAnimationFrame || msRequestAnimationFrame;
				if (raf) raf(cb);
				else window.addEventListener('load', cb);
			})();";
			$doc->addScriptDeclaration($js);
				
			// defer script load
			$version = new JVersion;
			$canDefer = preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT']) ? false : true;
			if (version_compare($version->getShortVersion(), '3.0.0', '<')) { // Joomla!2.5+
				JHTML::_('behavior.framework');
				$doc->addScript(JURI::root(true).'/plugins/content/djsuggester/assets/djsuggester.js', 'text/javascript', $canDefer);
			} else { // Joomla!3.0+
				JHTML::_('jquery.framework');
				$doc->addScript(JURI::root(true).'/plugins/content/djsuggester/assets/jquery.djsuggester.js', 'text/javascript', $canDefer);
			}
		}
	}
	
	protected static function getImageFromText(&$text, $remove = true)
	{
		$src = '';
		if(preg_match("/<img [^>]*src=\"([^\"]*)\"[^>]*>/", $text, $matches)){
			if($remove) $text = preg_replace("/<img[^>]*>/", '', $text);
			$src = $matches[1];
		}
	
		return $src;
	}
	
	public static function getTheme($params, $direction) {
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		$styles = array();
		
		$ver = self::getVersion($params);
		
		if($params->get('theme')=='_custom') {
			
			$params->set('theme', 'custom');
			$css = 'media/djsuggester/themes/'.$params->get('theme').($direction=='rtl'?'_rtl':'').'.css';
			$path = JPATH_ROOT . DS . 'plugins/content/djsuggester/themes/custom.css.php';
			
			// generate custom theme css if it doesn't already exist or the source file is newer
			if(!JFile::exists(JPATH_ROOT . DS . $css) || filemtime($path) > filemtime(JPath::clean(JPATH_ROOT . DS . $css))) {
				
				ob_start();
				include($path);
				$buffer = ob_get_clean();
				
				JFile::write(JPATH_ROOT . DS . $css, $buffer);
			}
			
			$styles['djsuggester_css'] = JURI::root(true).'/'.$css.'?v='.$ver;
		
		} else {
		
			if($params->get('theme')!='_override') {
				$css = 'plugins/content/djsuggester/themes/'.$params->get('theme','default').'/djsuggester.css';
			} else {
				$params->set('theme', 'override');
				$css = 'templates/'.$app->getTemplate().'/css/djsuggester.css';
			}
			
			// load theme only if file exists or ef4 template in use
			if(JFile::exists(JPATH_ROOT . DS . $css) || defined('JMF_EXEC')) {
				$styles['djsuggester_css'] = JURI::root(true).'/'.$css.'?v='.$ver;
			}
			if($direction == 'rtl') { // load rtl theme css if file exists or ef4 template in use
				$css_rtl = JFile::stripExt($css).'_rtl.css';
				if(JFile::exists(JPATH_ROOT . DS . $css_rtl) || defined('JMF_EXEC')) {
					$styles['djsuggester_rtl_css'] = JURI::root(true).'/'.$css_rtl.'?v='.$ver;
				}
			}
		}
		
		return $styles;
	}
	
	public static function getVersion($params) {
		
		if(!self::$version) {
			
			$db = JFactory::getDBO();
			$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE name='plg_content_djsuggester' LIMIT 1");
			$ver = json_decode($db->loadResult());
			self::$version = $ver->version . ($params->get('pro', 0) ? '.pro' : '.free');
		}
		
		return self::$version;
	}
	

	protected static function debug($array, $type = 'message'){
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($array,true)."</pre>", $type);
	
	}
}

?>