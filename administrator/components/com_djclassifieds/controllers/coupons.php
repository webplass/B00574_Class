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

defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.controlleradmin');


class DJClassifiedsControllerCoupons extends JControllerAdmin
{
	public function getModel($name = 'Coupon', $prefix = 'DJClassifiedsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function exportCSV(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ids = implode(',', $app->input->get('cid'));
		
		
		$query = $db->getQuery(true);
		$query->select('c.*');
		$query->from('#__djcf_coupons AS c');			
		$query->where('c.id IN ('.$ids.')');
		
		$db->setQuery($query);
		$coupons = $db->loadObjectList();
		
		
			
			$fh = fopen('php://output', 'w');						
			ob_start();					
			foreach ($coupons as $coupon) {
				$line = array($coupon->coupon_code);
				fputcsv($fh, $line);
			}
			
			$csv_content = ob_get_clean();
			
			$filename = 'DJ-Classifieds_coupons_' . date('Y-m-d_His');
			
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private', false);
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $filename . '.csv";');
			header('Content-Transfer-Encoding: binary');

			exit($csv_content);
			
	}
}