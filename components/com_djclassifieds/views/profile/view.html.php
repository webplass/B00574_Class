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
defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pagination');

class DJClassifiedsViewProfile extends JViewLegacy{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/profile');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$theme = $par->get('theme','default');
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/profile');
		}
	}	
	
	function display($tpl = null){
		JHTML::_( 'behavior.modal' );		
		$document   =  JFactory::getDocument();
		$par 	    = JComponentHelper::getParams( 'com_djclassifieds' );
		$app	    = JFactory::getApplication();		
		$user 	    = JFactory::getUser();		
		$model 	    = $this->getModel();
		$dispatcher	= JDispatcher::getInstance();
		
		$uid	  = JRequest::getVar('uid', 0, '', 'int');				
		$order    = JRequest::getCmd('order', $par->get('items_ordering','date_e'));
		$ord_t    = JRequest::getCmd('ord_t', $par->get('items_ordering_dir','desc'));
		$theme 	  = $par->get('theme','default');
		$config  	= JFactory::getConfig();

		if($uid==0 && $user->id==0){
			$uri = JFactory::getURI();
			$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
			$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
		}else{
			if(!$uid){$uid = $user->id;}
			$items= $model->getItems($uid);
			$countitems = $model->getCountItems($uid);
			$custom_fields = $model->getCustomFields();
			$profile = $model->getProfile($uid);				
			
			$regions=$model->getRegions();
			$country='';
			$city='';
			$region_name = '';
				
			
			if(isset($profile['details']->region_id) && $profile['details']->region_id!=0 && $par->get('profile_regions','0')){
				$address='';
				$rid = $profile['details']->region_id;
				if($rid!=0){
					while($rid!=0){
						$r_found = 0;
						foreach($regions as $li){
							if($li->id==$rid){
								$r_found = 1;
								$rid=$li->parent_id;
								$address.=$li->name.', ';
								if($li->country){
									$country =$li->name;
								}
								if($li->city){
									$city =$li->name;
								}
								if(!$region_name){
									$region_name =$li->name;
								}
								break;
							}
						}
						if($rid==$profile['details']->region_id || $r_found==0){break;}
					}
				}
				$address = substr($address, 0, -2);
			}
			
			
			//$document->setMetaData('robots','NOINDEX, FOLLOW');		
			$document->setTitle($profile['name'].' - '.JText::_('COM_DJCLASSIFIEDS_PROFILE'));
			
			if($par->get('show_types','0')){
				$types = $model->getTypes();
				$this->assignRef('types', $types);
			}
			
			if($profile['img']){
				$img_path = $profile['img']->path.$profile['img']->name.'.'.$profile['img']->ext;
				$document->addCustomTag('<meta property="og:image" content="'.JURI::root().ltrim($img_path,'/').'" />');
				$path = JPath::clean(JPATH_ROOT . $img_path);
				$size = @getimagesize($path);
				if(isset($size[0])){
					$document->addCustomTag('<meta property="og:image:width" content="'.$size[0].'" />');
				}
				if(isset($size[1])){
					$document->addCustomTag('<meta property="og:image:height" content="'.$size[1].'" />');
				}
			}
			
			
			$limit	= JRequest::getVar('limit', $par->get('limit_djitem_show'), '', 'int');
			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
			$pagination = new JPagination( $countitems, $limitstart, $limit );				
			
			/* plugins */
			JPluginHelper::importPlugin('djclassifieds');
				
			$profile['event'] = new stdClass();
			$resultsBeforeProfile = $dispatcher->trigger('onBeforeDJClassifiedsDisplayProfile', array (&$profile, & $par, 'item'));
			$profile['event']->onBeforeDJClassifiedsDisplayProfile = trim(implode("\n", $resultsBeforeProfile));
			
			$resultsAfterProfile = $dispatcher->trigger('onAfterDJClassifiedsDisplayProfile', array (&$profile, & $par, 'item'));
			$profile['event']->onAfterDJClassifiedsDisplayProfile = trim(implode("\n", $resultsAfterProfile));
			
			$resultsAfterProfileItems = $dispatcher->trigger('onAfterDJClassifiedsDisplayProfileItems', array (&$profile, & $par, 'item'));
			$profile['event']->onAfterDJClassifiedsDisplayProfileItems = trim(implode("\n", $resultsAfterProfileItems));

			foreach($items as $item){
				$results = $dispatcher->trigger('onPrepareItemDescription', array (& $item, & $par, 'items'));
			
				$item->event = new stdClass();
				$resultsAfterTitle = $dispatcher->trigger('onAfterDJClassifiedsDisplayTitle', array (&$item, & $par, 'items'));
				$item->event->afterDJClassifiedsDisplayTitle = trim(implode("\n", $resultsAfterTitle));
					
				$resultsBeforeContent = $dispatcher->trigger('onBeforeDJClassifiedsDisplayContent', array (&$item, & $par, 'items'));
				$item->event->beforeDJClassifiedsDisplayContent = trim(implode("\n", $resultsBeforeContent));
					
				$resultsAfterContent = $dispatcher->trigger('onAfterDJClassifiedsDisplayContent', array (&$item, & $par, 'items'));
				$item->event->afterDJClassifiedsDisplayContent = trim(implode("\n", $resultsAfterContent));
			}
			
			$se_results_link = JRoute::_(DJClassifiedsSEO::getCategoryRoute('0:all'),false);
			if($config->get('sef')){
				$se_results_link .='?se=1&amp;re=1&amp;se_regs%5B%5D=';
			}else{
				$se_results_link .='&se=1&re=1&se_regs[]=';
			}
			
			$this->assignRef('items', $items);
			$this->assignRef('custom_fields',$custom_fields);
			$this->assignRef('countitems', $countitems);		
			$this->assignRef('profile', $profile);
			$this->assignRef('pagination', $pagination);
			$this->assignRef('theme',$theme);
			$this->assignRef('se_results_link', $se_results_link);
			$this->assignRef('country',$country);
			$this->assignRef('city',$city);
			$this->assignRef('address',$address);
			
	        parent::display($tpl);
		}
	}

}




