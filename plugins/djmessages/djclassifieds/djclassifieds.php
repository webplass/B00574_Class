<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
*/
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class plgDJMessagesDJClassifieds extends JPlugin
{
	protected $autoloadLanguage = true;
	
	static $sourceInfos = array();
	
	static $componentLanguage = false;
	
	function onDJMessagesSourceFiltersPrepare(&$filters, $source = '', $source_id = 0) {
		if (count($filters) < 1) return;
		
		$lang = JFactory::getLanguage();
		
		foreach($filters[0] as $key => &$option) {
			if ($option->value == '') continue;
			$constant = 'PLG_DJMESSAGES_DJCLASSIFIEDS_SOURCE_' . JString::strtoupper(str_replace(array('.', '-'), '_', $option->text));
			if ($lang->hasKey($constant)) {
				$option->text = JText::_($constant);
			}
		}
		unset($option);
		
		if (isset($filters[1]) && $source != '') {
			$sourceName =  'sourceOptions' . ucfirst(preg_replace('/[^A-Z0-9_]/i', '', $source));
			if (method_exists($this, $sourceName)) {
				$this->$sourceName($filters[1]);
			}
		}
	}
	
	protected function sourceOptionsCom_djclassifiedsitem(&$options) {
		$ids = array();
		$lang = JFactory::getLanguage();
		
		foreach($options as &$option) {
			if ($option->value > 0) {
				$ids[] = (int)$option->value;
			} else {
				if ($lang->hasKey('PLG_DJMESSAGES_DJCLASSIFIEDS_SOURCE_COM_DJCLASSIFIEDS_ITEM_SELECT')) {
					$option->text = JText::_('PLG_DJMESSAGES_DJCLASSIFIEDS_SOURCE_COM_DJCLASSIFIEDS_ITEM_SELECT');
				}
			}
		}
		unset($option);
		
		if (count($ids) > 0) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, name')->from('#__djcf_items')->where('id IN ('.implode(',', $ids).')');
			$db->setQuery($query);
			$items = $db->loadObjectList('id');
			
			if (!empty($items)) {
				foreach($options as &$option) {
					if ($option->value && isset($items[$option->value])) {
						$option->text = $items[$option->value]->name;
					}
				}
				unset($option);
			}
		}
	}
	
	public function onDJMessagesMessagePrepare(&$message, $page = 'details') {
		$app = JFactory::getApplication();
		
		/*if ($message->msg_source != 'com_djclassifieds.item' || empty($message->msg_source_id)) {
			return;
		}*/
		
		$key = (int)$message->user_from;
		
		if (!$key) {
			return;
		}

		if (isset(static::$sourceInfos[$key])) {
			return static::$sourceInfos[$key];
		}
		
		require_once JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php';
		require_once JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djsocial.php';
		require_once JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djtheme.php';
		$par = JComponentHelper::getParams('com_djclassifieds');
		
		if (!static::$componentLanguage) {
			
			$language = JFactory::getLanguage();
			$c_lang = $language->getTag();
			if($c_lang=='pl-PL' || $c_lang=='en-GB'){
				$language->load('com_djclassifieds', JPATH_ROOT.'/components/com_djclassifieds', null, true);
			} else {
				if(!$language->load('com_djclassifieds', JPATH_ROOT, null, true)){
					$language->load('com_djclassifieds', JPATH_ROOT.'/components/com_djclassifieds', null, true);
				}
			}
			static::$componentLanguage = true;
		}
		
		$profile = array();
		
		$db = JFactory::getDbo();
		
		/*$query = $db->getQuery(true);
		$query->select('*')->from('#__djcf_images')->where('item_id='.(int)$message->user_from.' AND type='.$db->quote('profile'));
		$db->setQuery($query, 0, 1);
		
		$profile['img'] = $db->loadObject();
		*/
		
		$profile['data'] = null;
		$fields = $this->params->get('contact_fields'.($app->isSite() ? '_site' : ''), false);
		JArrayHelper::toInteger($fields);
		if (is_array($fields) && count($fields)) {
			$query = $db->getQuery(true);
			$query->select('f.*, v.value, v.value_date');
			$query->from('#__djcf_fields AS f');
			$query->join('left', '(SELECT * FROM #__djcf_fields_values_profile WHERE user_id='.(int)$message->user_from.') AS v ON v.field_id=f.id');
			$query->where('f.published=1 AND f.source=2 AND f.access=0');
			$query->where('f.id IN ('.implode(',', $fields).')');
			$query->order('f.ordering');
			$db->setQuery($query);
			
			$profile['data'] = $db->loadObjectList();
		}
		
		$profileLink = JUri::root().'index.php?option=com_djclassifieds&view=profile&uid='.$message->user_from;
		
		$output = '';
		if ($this->params->get('profile_link'.($app->isSite() ? '_site' : ''), true)) {
			$output .= '<a href="'.$profileLink.'" target="_blank">'.JText::_('PLG_DJMESSAGES_DJCLASSIFIEDS_PROFILE_LINK').'</a>';
		}
		
		//$output .= '<pre>'.print_r($profile,true).'</pre>';
		
		if ($profile['data']) {
			$output .= '<div class="djmsg-djcf-profile-data">'; 
			
			$profileOutput = '';
			foreach($profile['data'] as $f) {
				//if($par->get('show_empty_cf','1')==0){
					if(!$f->value && ($f->value_date=='' || $f->value_date=='0000-00-00')){
						continue;
					}
				//}
				$tel_tag = '';
				if(strstr($f->name, 'tel')){
					$tel_tag='tel:'.$f->value;
				}
				
				$profileOutput .= '<dt>'.JText::_($f->label).'</dt>';
				
				$profileOutput .= '<dd>';
				
				if ($f->type == 'textarea') {
					$profileOutput .= $f->value;
				} else if ($f->type == 'checkbox') {
					$profileOutput .= str_ireplace(';', ', ', substr($f->value, 1, - 1));
				} else if ($f->type == 'date') {
					$profileOutput .= DJClassifiedsTheme::formatDate(strtotime($f->value_date), '', '', $f->date_format);
				} else if ($f->type == 'link') {
					if (strstr($f->value, 'http://') || strstr($f->value, 'https://')) {
						$profileOutput .= '<a ' . $f->params . ' href="' . $f->value . '">' . str_ireplace(array(
							"http://",
							"https://"
						), array(
							'',
							''
						), $f->value) . '</a>';
						;
					} else {
						$profileOutput .= '<a ' . $f->params . ' href="http://' . $f->value . '">' . $f->value . '</a>';
						;
					}
				} else {
					if ($tel_tag) {
						$profileOutput .= '<a href="' . $tel_tag . '">' . $f->value . '</a>';
					} else {
						$profileOutput .= $f->value;
					}
				}
				
				$profileOutput .= '</dd>';
			}
			
			if ($profileOutput) {
				//$output .= '<strong>'.JText::_('PLG_DJMESSAGES_DJCLASSIFIEDS_PROFILE_DATA').'</strong>';
				$output .= '<dl>'.$profileOutput.'</dl>';
			}
			
			$output .= '</div>';
		}
		
		static::$sourceInfos[$key] = $output;
		
		return static::$sourceInfos[$key];
	}
}