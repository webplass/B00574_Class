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


class DJClassifiedsViewAdditem extends JViewLegacy{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/additem');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$theme = $par->get('theme','default');
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/additem');
		}
	}
	
	function display($tpl = NULL){
		global $mainframe;
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$session 	= JFactory::getSession();		
		$val 		= $session->get('captcha_sta','0');
		$user 		= JFactory::getUser();
		$app 		= JFactory::getApplication();		
		$token 		= JRequest::getCMD('token', '' );
		$subscr_id 	= JRequest::getInt('subscr_id',0);
		$config  	= JFactory::getConfig();
		$dispatcher	= JDispatcher::getInstance();
		$id			= JRequest::getInt('id', 0);
		//echo $val;
		
		$dispatcher->trigger('onBeforeItemEditForm', array (& $id, &$par ,$subscr_id, $token));
		
		if($par->get('user_type')==1 && $user->id=='0'){
			$uri = JFactory::getURI();
			//$login_url = 'index.php?option=com_users&view=login&return='.base64_encode($uri)
			/*$login_url = JRoute::_('index.php?option=com_users&view=login');
			 			
			if($config->get('sef')){
				$login_url .='?return='.base64_encode($uri);
			}else{
				$login_url .='&return='.base64_encode($uri);
			}*/
			
			//$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri));
			
			$menus = $app->getMenu('site');
			$menu_login_item = $menus->getItems('link','index.php?option=com_users&view=login',1);
			$login_url = JRoute::_('index.php?option=com_users&view=login',false);
			
			if($config->get('sef') && $menu_login_item){
				$login_url .='?return='.base64_encode($uri);
			}else{
				$login_url .='&return='.base64_encode($uri);
			}
			
			$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
		}else{		 
			if($val==0 && $par->get('captcha')==1 && JRequest::getVar('id', 0, '', 'int' )==0){
				parent::display('captcha');	
			}else if($val==0 && $par->get('captcha')==2 && JRequest::getVar('id', 0, '', 'int' )==0 && $user->id==0){
				parent::display('captcha');	
			}else{				
				$model 		= $this->getModel();
				if($user->id>0 && $par->get('adverts_limit','0')>0 && JRequest::getInt('id', 0)==0 && !$token && $subscr_id==0){					
					$user_items_c = $model->getUserItemsCount();	
					if($user_items_c>=$par->get('adverts_limit','0')){
						$app->redirect(JRoute::_(DJClassifiedsSEO::getUserAdsLink(),false),JText::_('COM_DJCLASSIFIEDS_REACHED_LIMIT_OF_ADVERTS'),'error');
					} 				
				}
				$item 			= $model->getItem();				
				$images 	= $model->getItemImages($item->id);
				$cats			= $model->getCategories();

				if($item->id){
					$subscr_id 	= $model->getItemSubscriptionID($item->id);
				}else if($par->get('preselect_last_cat',0)){					
					$inputCookie  = $app->input->cookie;										
					$item->cat_id = $inputCookie->get('djcf_lastcat', '');
					$item = $model->getProfileDefaultValues($item);
				}else{
					$item = $model->getProfileDefaultValues($item);
				}
				
					$cat_path = array();
					$c_name = '';
					if($item->cat_id!=0 && $cats){								
						$id = Array();
						$name = Array();
						$cid = $item->cat_id;
						if($cid!=0){
							while($cid!=0){	
								foreach($cats as $li){
									if($li->id==$cid){
										$cid=$li->parent_id;
										$id[]=$li->id;
										$name[]=$li->name;
										if(!count($cat_path)){
											$c_name = $li->name;
										}
										$cat_path[] = $li->parent_id.','.$li->id;
										break;
									}
								}
								if($cid==$item->cat_id){ break; }								
							}
						}
					}			
				
				
				$regions = $model->getRegions();
				$r_name = '';
				$reg_path = array();
				
				if(!$item->id && !$item->region_id && DJClassifiedsRegion::getDefaultRegion()){
					$item->region_id = DJClassifiedsRegion::getDefaultRegion();
				}				
														
					if($item->region_id!=0){
						$id = Array();
						$name = Array();
						$rid = $item->region_id;
						if($rid!=0){
							while($rid!=0){	
								foreach($regions as $li){
									if($li->id==$rid){
										$rid=$li->parent_id;
										$id[]=$li->id;
										$name[]=$li->name;
										if(!count($reg_path)){
											$r_name = $li->name;
										}
										$reg_path[] = $li->parent_id.','.$li->id;
										break;
									}
								}
								if($rid==$item->region_id){ break; }
							}						
						}						
					}
										
				$terms_link='';
				if($par->get('terms',1)>0 && $par->get('terms_article_id',0)>0 && JRequest::getVar('id', 0, '', 'int' )==0){
 					require_once JPATH_SITE.'/components/com_content/helpers/route.php';
					$terms_article = $model->getTermsLink($par->get('terms_article_id',0));					
					if($terms_article){
						$slug = $terms_article->id.':'.$terms_article->alias;
						$cslug = $terms_article->catid.':'.$terms_article->c_alias;
						$article_link = ContentHelperRoute::getArticleRoute($slug,$cslug);
						if($par->get('terms',0)==2){
							$article_link .='&tmpl=component';
						}
						$terms_link = JRoute::_($article_link,false);											
					}					
				}
				
				$privacy_policy_link='';
				if($par->get('privacy_policy',0)>0 && $par->get('privacy_policy_article_id',0)>0 && JRequest::getVar('id', 0, '', 'int' )==0){
					require_once JPATH_SITE.'/components/com_content/helpers/route.php';
					$privacy_policy_article = $model->getTermsLink($par->get('privacy_policy_article_id',0));
					if($privacy_policy_article){
						$slug = $privacy_policy_article->id.':'.$privacy_policy_article->alias;
						$cslug = $privacy_policy_article->catid.':'.$privacy_policy_article->c_alias;
						$article_link = ContentHelperRoute::getArticleRoute($slug,$cslug);
						if($par->get('terms',0)==2){
							$article_link .='&tmpl=component';
						}
						$privacy_policy_link = JRoute::_($article_link,false);
					}
				}
			
				$days='';
				if($par->get('durations_list',1)>0){
					$days = $model->getDays();				
				}
				
				$promotions = '';
				if($par->get('promotion')=='1'){
					$promotions = $model->getPromotions();
					$item_promotions = $model->getItemPromotions($item->id);
					$this->assignRef('item_promotions',$item_promotions);
				}					

				$custom_contact_fields = $model->getCustomContactFields();
				
				if($par->get('types_display_layout','0')==1){
					$types = DJClassifiedsType::getTypesLabels(true);
				}else{
					$types = DJClassifiedsType::getTypesSelect(true);
				}
				
				$dispatcher->trigger('onItemEditForm', array (& $item, &$par ,$subscr_id,&$promotions,&$cats, &$types));
				$plugin_title = $dispatcher->trigger('onItemEditFormTitle', array (& $item, &$par ,$subscr_id));
				$plugin_category = $dispatcher->trigger('onItemEditFormCategory', array (& $item, &$cats, &$par ,$subscr_id));
				$plugin_rows = $dispatcher->trigger('onItemEditFormRows', array (& $item, &$par ,$subscr_id));
				$plugin_sections = $dispatcher->trigger('onItemEditFormSections', array (& $item, &$par ,$subscr_id));
				$plugin_promotions = $dispatcher->trigger('onItemEditFormPromotions', array (& $item, &$par ,$subscr_id,&$promotions));
				$special_prices = $dispatcher->trigger('onItemEditFormSpecialPrices', array ());
				if(isset($special_prices[0])){$special_prices = $special_prices[0];}
				
				//if($promotions){
					$this->assignRef('promotions',$promotions);
				//}				
				
				if($par->get('buynow','0')==1){
					$item_units = $model->getItemsUnits();
					$this->assignRef('item_units',$item_units);
				}
																
				$this->assignRef('item',$item);
				$this->assignRef('images',$images);
				$this->assignRef('cats',$cats);
				$this->assignRef('cat_path',$cat_path);
				$this->assignRef('regions',$regions);
				$this->assignRef('reg_path',$reg_path);				
				$this->assignRef('r_name',$r_name);				
				$this->assignRef('terms_link',$terms_link);
				$this->assignRef('privacy_policy_link',$privacy_policy_link);
				$this->assignRef('days',$days);
				$this->assignRef('custom_contact_fields',$custom_contact_fields);
				$this->assignRef('plugin_title',$plugin_title);
				$this->assignRef('plugin_category',$plugin_category);
				$this->assignRef('plugin_rows',$plugin_rows);
				$this->assignRef('plugin_sections',$plugin_sections);
				$this->assignRef('plugin_promotions',$plugin_promotions);
				$this->assignRef('par',$par);
				$this->assignRef('special_prices',$special_prices);
				$this->assignRef('subscr_id',$subscr_id);	
				$this->assignRef('types',$types);
        		parent::display();			
			}
		}
	}

}




