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
jimport( 'joomla.application.component.view' );

class DJClassifiedsViewPayment extends JViewLegacy{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/payment');
		$par = JComponentHelper::getParams( 'com_djclassifieds' );
		$theme = $par->get('theme','default');
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/payment');
		}
	}

	function display( $tpl = null ){
		$app		= JFactory::getApplication();
		$params 	= $app->getParams();
		$user		= JFactory::getUser();
		$id 		= JRequest::getInt("id","0");
		$layout		= JRequest::getVar('layout', '');
		$result 	= JRequest::getVar("result","");
		$action		= JRequest::getVar('action','');
		$ptype		= JRequest::getVar('ptype','');
		$type		= JRequest::getVar('type','');
		$par 		= JComponentHelper::getParams( 'com_djclassifieds' );
		$model 		= $this->getModel();	
		$user 		= JFactory::getUser();
		JPluginHelper::importPlugin( 'djclassifiedspayment' );
		JPluginHelper::importPlugin( 'djclassifieds' );
		$dispatcher = JDispatcher::getInstance();
				  
		if($layout == 'process'){
			JPluginHelper::importPlugin("djclassifiedspayment");			
			$results = $dispatcher->trigger( 'onProcessPayment');
			if($ptype!='djcfBankTransfer'){
				die();	
				print_r($results);
				$text = trim(implode("\n", $results));
				print_r($text);die();
			}
			
		}else{
			
			$terms_link='';
			if($par->get('terms',1)>0 && $par->get('terms_article_id',0)>0){
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
			$this->assignRef("terms_link",$terms_link);
			
			$privacy_policy_link='';
			if($par->get('privacy_policy',0)>0 && $par->get('privacy_policy_article_id',0)>0){
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
			$this->assignRef('privacy_policy_link',$privacy_policy_link);
			
			
			if($type=='prom_top'){
				if($par->get('promotion_move_top_price','0')==0 && $par->get('promotion_move_top_points','0')==0){
					$model->activateMoveToTopPromotion($id);
				}else{
					$price_total = $par->get('promotion_move_top_price','0');
					$plugin_payments = $dispatcher->trigger('onPreparePaymentList', array (& $id, & $par, $type, &$price_total,$par->get('unit_price','EUR'), '', 1  ));
					$plugin_sections = $dispatcher->trigger('onPreparePaymentSection', array (& $id, & $par, $type, &$price_total,$par->get('unit_price','EUR'), '', 1  ));
					
					$val["id"] = $id;
					$val["total"] = $price_total;
					$val["type"] = 'prom_top';
					$val["direct_payment"] = false;
					$options = array( $val);					
					
					$PaymentMethodDetails = $dispatcher->trigger( 'onPaymentMethodList',$options);
					$plugin_info = JPluginHelper::getPlugin('djclassifiedspayment', $plugin=null);
					$user_points = $model->getUserPoints();
					$item= $model->getUserItem($id);					
					
					
					
					$this->assignRef("PaymentMethodDetails",$PaymentMethodDetails);
					$this->assignRef("plugin_info",$plugin_info);
					$this->assignRef('item', $item);
					$this->assignRef('user_points', $user_points);
					$this->assignRef('price_total', $price_total);
					$this->assignRef('plugin_payments',$plugin_payments);
					$this->assignRef('plugin_sections',$plugin_sections);
					$this->assignRef('dispatcher',$dispatcher);
					$tpl='prom';
				}
			}else if($type=='points'){
				
				if($user->id=='0'){
					$uri = JFactory::getURI();
					$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
					$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
				}
				
				$points= $model->getPoints($id);
				$price_total = $points->price;
				$plugin_payments = $dispatcher->trigger('onPreparePaymentList', array (& $points, & $par, $type, &$price_total,$par->get('unit_price','EUR'), '', 1  ));
				$plugin_sections = $dispatcher->trigger('onPreparePaymentSection', array (& $points, & $par, $type, &$price_total,$par->get('unit_price','EUR'), '', 1  ));
				
				$val["id"] = $id;
				$val["total"] = $price_total;
				$val["type"] = 'points';
				$val["direct_payment"] = false;
				$options = array( $val);
				
				$PaymentMethodDetails = $dispatcher->trigger( 'onPaymentMethodList',$options);
				$plugin_info = JPluginHelper::getPlugin('djclassifiedspayment', $plugin=null);												
				
				
				
				$this->assignRef("PaymentMethodDetails",$PaymentMethodDetails);
				$this->assignRef("plugin_info",$plugin_info);				
				$this->assignRef("points",$points);
				$this->assignRef('plugin_payments',$plugin_payments);
				$this->assignRef('plugin_sections',$plugin_sections);
				$this->assignRef('price_total', $price_total);
				$this->assignRef('dispatcher',$dispatcher);
				
				$tpl='points';
			}else if($type=='plan'){
				
				if($user->id=='0'){
					$uri = JFactory::getURI();
					$login_url = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($uri),false);
					$app->redirect($login_url,JText::_('COM_DJCLASSIFIEDS_PLEASE_LOGIN'));
				}									
				
				$plan= $model->getPlan($id);
				
				if($plan->price==0){
					$app->redirect('index.php?option=com_djclassifieds&view=payment&task=activateFreePlan&id='.$id);
				}
				
				$price_total = $plan->price;
				$plugin_payments = $dispatcher->trigger('onPreparePaymentList', array (& $plan, & $par, $type, &$price_total,$par->get('unit_price','EUR'), '', 1  ));
				$plugin_sections = $dispatcher->trigger('onPreparePaymentSection', array (& $plan, & $par, $type, &$price_total,$par->get('unit_price','EUR'), '', 1  ));
				
				$user_points = $model->getUserPoints();
				$val["id"] = $id;
				$val["total"] = $price_total;
				$val["price_special"] = $plan->price_special;
				$val["type"] = 'plan';			
				$val["direct_payment"] = false;
				$this->assignRef('price_total', $price_total);
				$options = array( $val);
				
				$PaymentMethodDetails = $dispatcher->trigger( 'onPaymentMethodList',$options);
				$plugin_info = JPluginHelper::getPlugin('djclassifiedspayment', $plugin=null);
				
				
			
				$this->assignRef("PaymentMethodDetails",$PaymentMethodDetails);
				$this->assignRef("plugin_info",$plugin_info);
				$this->assignRef("plan",$plan);
				$this->assignRef('user_points', $user_points);
				$this->assignRef('plugin_payments',$plugin_payments);
				$this->assignRef('plugin_sections',$plugin_sections);
				$this->assignRef('dispatcher',$dispatcher);
				$tpl='plan';
			}else if($type=='order'){
				$order = $model->getOrder($id);
				$item= $model->getUserItem($order->item_id);
				//$quantity = JRequest::getInt('quantity',1);
				$quantity = $order->quantity;
				$item_images = DJClassifiedsImage::getAdsImages($item->id);				
				
				$price_total = $order->quantity * $order->price;				
				
				$plugin_payments = $dispatcher->trigger('onPreparePaymentList', array (& $item, & $par, $type, &$price_total, $order,$par->get('unit_price','EUR'), $order->quantity  ));
				$plugin_sections = $dispatcher->trigger('onPreparePaymentSection', array (& $item, & $par, $type, &$price_total,$order,$par->get('unit_price','EUR'), $order->quantity ));
				
				$val["id"] = $id;
				$val["quantity"] = $order->quantity;
				$val["price"] = $order->price;				
				$val["total"] = $price_total;
				$val["type"] = 'order';
				$val["direct_payment"] = true;
				$val["payment_email"] = DJClassifiedsPayment::getUserPaypal($item->user_id);
				$options = array( $val);
				
				$PaymentMethodDetails = $dispatcher->trigger( 'onPaymentMethodList',$options);
				$plugin_info = JPluginHelper::getPlugin('djclassifiedspayment', $plugin=null);
				
																
				
				$this->assignRef("PaymentMethodDetails",$PaymentMethodDetails);
				$this->assignRef("plugin_info",$plugin_info);				
				$this->assignRef("item",$item);
				$this->assignRef("order",$order);				
				$this->assignRef('item_images',$item_images);
				$this->assignRef('quantity',$order->quantity);
				$this->assignRef('price_total',$price_total);	
				$this->assignRef('plugin_payments',$plugin_payments);		
				$this->assignRef('plugin_sections',$plugin_sections);
				$this->assignRef('dispatcher',$dispatcher);
				
				$tpl='order';
			}else if($type=='offer'){
				$offer = $model->getOffer($id);
				$item= $model->getUserItem($offer->item_id);
				//$quantity = JRequest::getInt('quantity',1);
				$quantity = $offer->quantity;
				$item_images = DJClassifiedsImage::getAdsImages($item->id);				
				
				$price_total = $offer->price;

				$plugin_payments = $dispatcher->trigger('onPreparePaymentList', array (& $item, & $par, $type, &$price_total,$par->get('unit_price','EUR'), $offer, $offer->quantity  ));
				$plugin_sections = $dispatcher->trigger('onPreparePaymentSection', array (& $item, & $par, $type, &$price_total,$par->get('unit_price','EUR'), $offer, $offer->quantity));
				
				$val["id"] = $id;
				$val["quantity"] = $offer->quantity;
				$val["price"] = $offer->price;				
				$val["total"] = $price_total;
				$val["type"] = 'offer';
				$val["direct_payment"] = true;
				$val["payment_email"] = DJClassifiedsPayment::getUserPaypal($item->user_id);
				$options = array( $val);
				
				$PaymentMethodDetails = $dispatcher->trigger( 'onPaymentMethodList',$options);
				$plugin_info = JPluginHelper::getPlugin('djclassifiedspayment', $plugin=null);
				
																
				
				$this->assignRef("PaymentMethodDetails",$PaymentMethodDetails);
				$this->assignRef("plugin_info",$plugin_info);				
				$this->assignRef("item",$item);
				$this->assignRef("offer",$offer);				
				$this->assignRef('item_images',$item_images);
				$this->assignRef('quantity',$offer->quantity);
				$this->assignRef('price_total',$price_total);	
				$this->assignRef('plugin_payments',$plugin_payments);		
				$this->assignRef('plugin_sections',$plugin_sections);
				$this->assignRef('dispatcher',$dispatcher);
				
				$tpl='offer';
			}else{					
				$item= $model->getUserItem($id);
				$duration='';
				if($par->get('durations_list',1)>0){
					$duration = $model->getDuration($item->exp_days);				
				}						
				$promotions = $model->getPromotions();
				
					$p_total=0;
					$p_total_special=0;
					if(strstr($item->pay_type, 'cat')){
						$c_price = $item->c_price/100;
						$p_total+=$c_price;
						$p_total_special+=$item->c_price_special;
					}							
					
					$categories = $model->getCategories();
					
					if(strstr($item->pay_type, 'mc')){
						$pay_elems = explode(',', $item->pay_type);
						foreach($pay_elems as $pay_el){
							if(strstr($pay_el, 'mc')){
								$mc_id = str_ireplace('mc', '', $pay_el);
								$mcat = $categories[$mc_id];
								$c_price = $mcat->price/100;
																	
								$p_total+=$c_price;
								$p_total_special+=$mcat->price_special;
							}
						}
					}
					
					
					if(strstr($item->pay_type, 'duration_renew')){
						$p_total+=$duration->price_renew;
						$p_total_special+=$duration->price_renew_special;
					}else if(strstr($item->pay_type, 'duration')){
						$p_total+=$duration->price;
						$p_total_special+=$duration->price_special;
					}
	
					foreach($promotions as $prom){
						$pay_type_a = explode(',', $item->pay_type);
						foreach($pay_type_a as $pay_type_e){
							if(strstr($pay_type_e, $prom->name)){
								$pay_type_ep = explode('_', $pay_type_e);
								if(isset($prom->prices[$pay_type_ep[3]])){
									$p_total+=$prom->prices[$pay_type_ep[3]]->price;
									$p_total_special+=$prom->prices[$pay_type_ep[3]]->price;
								}								
							}	
						}						
					}	
					
					//echo $p_total;die();
					
					$itype= '';
					if(strstr($item->pay_type, 'type,')){
						$itype = DJClassifiedsPayment::getTypePrice($item->user_id,$item->type_id);		
						//echo '<pre>';print_r($item);die();
						$p_total+=$itype->price;
						$p_total_special+=$itype->price_special;
					}
					
										
					$special_prices = $dispatcher->trigger('onItemEditFormSpecialPrices', array ());
					if($special_prices){
						if(is_array($special_prices[0])){$special_prices = $special_prices[0];}
					}					
					
					if(strstr($item->pay_type, 'extra_img')){
						$extraimg = $item->extra_images_to_pay;
						$img_price_special = 0;
						if(strstr($item->pay_type, 'extra_img_renew')){
							$img_price	= $par->get('img_price_renew','0');
							$img_points	= $par->get('img_price_renew_points','0');
							if(isset($duration->img_price_default)){
								if($duration->img_price_default==0){
									$img_price = $duration->img_price_renew;
									$img_points = $duration->img_points_renew;
								}
							}
							if(isset($special_prices['img_price_renew'])){
								$img_price_special	= $special_prices['img_price_renew'];
							}
						}else{
							$img_price	= $par->get('img_price','0');
							$img_points	= $par->get('img_price_points','0');
							if(isset($duration->img_price_default)){
								if($duration->img_price_default==0){
									$img_price = $duration->img_price;
									$img_points = $duration->img_points;
								}
							}
							if(isset($special_prices['img_price'])){
								$img_price_special	= $special_prices['img_price'];
							}
						}
							
						$p_total+=$img_price*$extraimg;
						$p_total_special += $img_price_special*$extraimg;
					}
						
					if(strstr($item->pay_type, 'extra_chars')){
						$extrachar = $item->extra_chars_to_pay;
						$char_price_special = 0;
						if(strstr($item->pay_type, 'extra_chars_renew')){
							if($duration->char_price_default==0){
								$char_price = $duration->char_price_renew;
								$char_points = $duration->char_points_renew;
							}else{
								$char_price	= $par->get('desc_char_price_renew','0');
								$char_points	= $par->get('desc_char_price_renew_points','0');
							}
							if(isset($special_prices['desc_char_price_renew'])){
								$char_price_special	= $special_prices['desc_char_price_renew'];
							}
						}else{
							$char_price	= $par->get('desc_char_price','0');
							$char_points	= $par->get('desc_char_price_points','0');
							if(isset($duration->char_price_default)){
								if($duration->char_price_default==0){
									$char_price = $duration->char_price;
									$char_points = $duration->char_points;
								}								
							}
							if(isset($special_prices['desc_char_price'])){
								$char_price_special	= $special_prices['desc_char_price'];
							}
						}
											
						$p_total+=$char_price*$extrachar;
						$p_total_special += $char_price_special*$extrachar;
					}

					
					
					
					
				
				$plugin_payments = $dispatcher->trigger('onPreparePaymentList', array (& $item, & $par, $type, &$p_total,$par->get('unit_price','EUR'), '', 1 ));
				$plugin_sections = $dispatcher->trigger('onPreparePaymentSection', array (& $item, & $par, $type, &$p_total,$par->get('unit_price','EUR'), '', 1  ));
					
				$val["id"] = $id;
				$val["total"] = $p_total;
				$val["type"] = '';
				$val["price_special"] = $p_total_special;
				$val["direct_payment"] = false;
				$options = array( $val);
				
				$PaymentMethodDetails = $dispatcher->trigger( 'onPaymentMethodList',$options);
				$plugin_info = JPluginHelper::getPlugin('djclassifiedspayment', $plugin=null);
				$PaymentMethodMessage="";
				$message="";
	
				if($action=="showresult")
				{
					$inv_arg["inv_id"] = $id;
					$arg = ($inv_arg);
					$PaymentMethodMessage = $dispatcher->trigger( 'onAfterFailedPayment',$arg);
					$message=$this->getPaymentPluginStatus($PaymentMethodMessage,$plugin_info,$ptype);
				}
												
				$user_points = $model->getUserPoints();	

				
				$this->assignRef('special_prices',$special_prices);
				
				$this->assignRef("PaymentMethodDetails",$PaymentMethodDetails);
				$this->assignRef("plugin_info",$plugin_info);
				$this->assignRef("result",$result);
				$this->assignRef("item",$item);
				$this->assignRef('duration',$duration);
				$this->assignRef('promotions',$promotions);
				$this->assignRef('user_points', $user_points);
				$this->assignRef("itype",$itype);
				$this->assignRef('price_total',$p_total);
				$this->assignRef('plugin_payments',$plugin_payments);
				$this->assignRef('plugin_sections',$plugin_sections);
				$this->assignRef('categories',$categories);
				$this->assignRef('dispatcher',$dispatcher);
				
			}

		}


	//	$this->_prepareDocument();

		parent::display( $tpl );
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		$params 	= $app->getParams();
		$menu		= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JRegistry;
			$menu_params->loadJSON($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	JText::_('COM_JEGROUPBUY_TITLE_PAYMENT_DETAILS_LIST'));
			}
		}
		else {
			$params->set('page_title',	JText::_('COM_JEGROUPBUY_TITLE_PAYMENT_DETAILS_LIST'));
		}

		$title = $params->get('page_title');
		if ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}

		$this->document->setTitle($title);
	}
	function limit_characters ( $str, $n )
	{
		if ( strlen ( $str ) <= $n )
		{
			return $str;
		}
		else {
			return substr ( $str, 0, $n ) . '...';
		}
	}

	function getPaymentPluginStatus($PaymentMethodMessage,$plugin_infos,$ptype)
	{
		$i = 0;
		foreach($PaymentMethodMessage as $PaymentMethodMessages)
		{

			if($plugin_infos[$i]->name == $ptype)
			{
				return $PaymentMethodMessages;
			}
			$i++;
		}
	}
}
