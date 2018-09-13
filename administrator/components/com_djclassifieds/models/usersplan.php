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

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class DJClassifiedsModelUsersPlan extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCLASSIFIEDS';

	public function __construct($config = array()) {
		$config['event_after_save'] = 'onProducerAfterSave';
		$config['event_after_delete'] = 'onProducerAfterDelete';
		parent::__construct($config);
	}

	public function getTable($plan = 'UsersPlans', $prefix = 'DJClassifiedsTable', $config = array())
	{
		return JTable::getInstance($plan, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djclassifieds.usersplan', 'usersplan', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djclassifieds.edit.usersplan.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (empty($table->id)) {
			
			
			$db = JFactory::getDbo();
			
			$query = "SELECT p.* FROM #__djcf_plans p "
					."WHERE  p.id=".$table->plan_id;
			$db->setQuery($query);
			$plan=$db->loadObject();
						
				if ($plan) {
					$registry = new JRegistry();
					$registry->loadString($plan->params);
					$plan_params = $registry->toObject();
					
					//echo '<pre>';print_r($plan_params);die();
					$table->adverts_limit = $plan_params->ad_limit;
					$table->adverts_available = $plan_params->ad_limit;
					
					$table->date_start = date("Y-m-d H:i:s");
					if($plan_params->days_limit){
						$date_exp_time = time()+$plan_params->days_limit*24*60*60;
						$table->date_exp = date("Y-m-d H:i:s",$date_exp_time) ;
					}
					
					$table->plan_params = $plan->params;

				}
			
			//echo '<pre>';print_r($table);die();
		}
		
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

}