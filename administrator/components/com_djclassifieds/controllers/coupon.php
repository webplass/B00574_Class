<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage 	DJ Classifieds Component
* @copyright 	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license 		http://www.gnu.org/licenses GNU/GPL
* @author 		url: http://design-joomla.eu
* @author 		email contact@design-joomla.eu
* @developer 	Łukasz Ciastek - lukasz.ciastek@design-joomla.eu
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

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class DJClassifiedsControllerCoupon extends JControllerForm {
	public function save($key = null, $urlVar = null) {
		
		$app = JFactory::getApplication();
		$multiple = $app->input->getInt('multiple');
		//echo $multiple;die(); 
		return parent::save($key, $urlVar);
	}
	
	public function postSaveHook($model,$validData){
		$app = JFactory::getApplication();
		$item = $model->getItem();
		$new_coupon_id = $item->get('id');
		$coupon_limit = intval($validData['coupon_code']);		
		$coupon_name = $validData['name'];
		$coupon_name_i = 1;
		
		$multiple = $app->input->getInt('multiple',0);	
		
		if($coupon_limit>1 && $multiple==1){
			
			$table = $model->getTable();
			$table->load($new_coupon_id);
			$table->coupon_code=self::generateCouponCode(6);
			$table->name=$table->name.' 1';
			$table->store();
			
			$coupon_limit--;
			for($ci=0;$ci<$coupon_limit;$ci++){
				$coupon_name_i++;
				$table->id=0;
				$table->name = $coupon_name.' '.$coupon_name_i;
				$table->coupon_code=self::generateCouponCode(6);
				$table->store();
			}
		}
		
		return true;
	}
	
	public function addmultiple() {
		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_djclassifieds&view=coupon&layout=edit&multiple=1');
	}
	
	public function generateCouponCode($limit){
		$characters = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$coupon_code = '';
		for ($p = 0; $p < $limit; $p++) {
			$coupon_code .= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $coupon_code;
	}
}
?>
