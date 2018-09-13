<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

/**
 * Messages Component Messages Model
 *
 * @since  1.6
 */
class DJMessagesModelMessages extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'subject', 'a.subject',
				'state', 'a.sender_state', 'a.recipient_state',
				'user_from', 'a.user_from',
				'user_to', 'a.user_to',
				'sent_time', 'a.sent_time',
				'read_time', 'a.read_time',
				'only_me', 'date_from', 'date_to', 'msg_source', 'msg_source_id'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.sent_time', $direction = 'desc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

		$this->setState('filter.sender_state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_sender_state', '', 'cmd'));
		$this->setState('filter.recipient_state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_recipient_state', '', 'cmd'));
		$this->setState('filter.msg_source', $this->getUserStateFromRequest($this->context . '.filter.msg_source', 'filter_msg_source', '', 'cmd'));
		$this->setState('filter.msg_source_id', $this->getUserStateFromRequest($this->context . '.filter.msg_source_id', 'filter_msg_source_id', '', 'cmd'));
		
		//$this->setState('filter.only_me', $this->getUserStateFromRequest($this->context . '.filter.only_me', 'only_me', '0', 'int'));
		
		// List state information.
		parent::populateState($ordering, $direction);
		
		// In order to avoid recursion
		$this->__state_set = true;
		
		// State validation
		$date_from = $this->getState('filter.date_from');
		if (false == DJMessagesHelperMessenger::validateDate($date_from, 'Y-m-d')) {
			JFactory::getApplication()->setUserState($this->context . '.filter.date_from', false);
		}
		$date_to = $this->getState('filter.date_to');
		if (false == DJMessagesHelperMessenger::validateDate($date_to, 'Y-m-d')) {
			JFactory::getApplication()->setUserState($this->context . '.filter.date_to', false);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string    A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.sender_state');
		$id .= ':' . $this->getState('filter.recipient_state');
		$id .= ':' . $this->getState('filter.only_me');
		$id .= ':' . $this->getState('filter.date_from');
		$id .= ':' . $this->getState('filter.date_to');
		$id .= ':' . $this->getState('filter.msg_source');
		$id .= ':' . $this->getState('filter.msg_source_id');
		
		return parent::getStoreId($id);
	}
	
	public function getItems()
	{
		$items = parent::getItems();
		
		$dispatcher = JEventDispatcher::getInstance();
		
		foreach($items as &$item) {
			$results = $dispatcher->trigger('onDJMessagesMessagePrepare', array(&$item));
			$item->_plg_data = '';
			foreach($results as $result) {
				if (!empty($result)) {
					$item->_plg_data .= '<div>'.$result.'</div>';
				}
			}
		}
		
		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*, ' .
					'u1.name AS from_name, u1.email AS from_email, u2.name AS to_name, u2.email AS to_email'
			)
		);
		$query->from('#__djmsg_messages AS a');

		// Join over the users for message owners.
		$query->join('LEFT', '#__users AS u1 ON u1.id = a.user_from');
		$query->join('LEFT', '#__users AS u2 ON u2.id = a.user_to');

		// Filter by published state.
		$sender_state = $this->getState('filter.sender_state');
		if (is_numeric($sender_state))
		{
			$query->where('a.sender_state = ' . (int) $sender_state);
		}
		/*elseif ($sender_state !== '*')
		{
			$query->where('(a.sender_state IN (0, 1))');
		}*/
		
		$recipient_state = $this->getState('filter.recipient_state');
		if (is_numeric($recipient_state))
		{
			$query->where('a.recipient_state = ' . (int) $recipient_state);
		}
		elseif ($recipient_state !== '*')
		{
			$query->where('(a.recipient_state IN (0, 1))');
		}

		// Filter by search in subject or message.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('u1.id = '.(int) substr($search, 3).' OR u2.id = '.(int) substr($search, 3));
			}
			else if (stripos($search, '@') !== false) {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('u1.email LIKE ' . $search . ' OR u2.email LIKE ' . $search);
			} else {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.subject LIKE ' . $search . ' OR a.message LIKE ' . $search);
			}
		}
		
		$only_me = $this->getState('filter.only_me');
		
		switch ($only_me) {
			case '1' : {
				$query->where('(a.user_from = '.$user->id.' OR a.user_to = '.$user->id.')');
				break;
			}
			case '2' : {
				$query->where('(a.user_to = '.$user->id.')');
				break;
			}
			case '3' : {
				$query->where('(a.user_from = '.$user->id.')');
				break;
			}
			case '0' :
			default: {
				break;
			}
		}
		
		$date_from = $this->getState('filter.date_from');
		if ($date_from != '') {
			$query->where('DATE(a.sent_time) >= ' . $db->quote($db->escape(trim($date_from))));
		}
		
		$date_to = $this->getState('filter.date_to');
		if ($date_to != '') {
			$query->where('DATE(a.sent_time) <= ' . $db->quote($db->escape(trim($date_to))));
		}
		
		$msg_source = $this->getState('filter.msg_source');
		$msg_source_id = $this->getState('filter.msg_source_id');
		if ($msg_source != '') {
			$query->where('a.msg_source LIKE ' . $db->quote($db->escape($msg_source)));
			if ($msg_source_id > 0) {
				$query->where('a.msg_source_id = '. (int)$msg_source_id);
			}
		}
		
		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.sent_time')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));
		return $query;
	}
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);
		
		$srcFilters = $this->getSourceFilters();
		
		$xml = 	'<?xml version="1.0" encoding="UTF-8"?>';
		$xml .=	'<form><fields name="filter">';
		
		foreach($srcFilters as $level => $options) {
			$lbl = $level == 0 ? 'COM_DJMESSAGES_FILTER_SELECT_SOURCE' : 'COM_DJMESSAGES_FILTER_SELECT_SOURCE_ID';
			$name = $level == 0 ? 'msg_source' : 'msg_source_id';
			$xml .= '<field name="'.$name.'" type="list" default="" label="'.$lbl.'" hint="'.$lbl.'" onchange="this.form.submit();">';
			foreach($options as $option) {
				$xml .= '<option value="'.$option->value.'">'.$option->text.'</option>';
			}
			$xml .= '</field>';
		}
		
		$xml .=	'</fields><fields name="list"></fields></form>';

		$form->load($xml, false);
		
		$msg_source = $this->getState('filter.msg_source');
		$msg_source_id = $this->getState('filter.msg_source_id');
		
		$form->setValue('msg_source', 'filter', $msg_source);
		$form->setValue('msg_source_id', 'filter', $msg_source_id);
		
		return $form;
	}
	
	public function getSourceFilters() {
		$data = $this->prepareFiltersData();
		$dispatcher = JEventDispatcher::getInstance();
		
		$filters = array();
		
		foreach($data as $k => $filterOptions) {
			$options = array();
			$defaultText = ($k == 0) ? JText::_('COM_DJMESSAGES_FILTER_SELECT_SOURCE') : JText::_('COM_DJMESSAGES_FILTER_SELECT_SOURCE_ID');
			$options[] = JHtmlSelect::option('', $defaultText);
			foreach($filterOptions as $value) {
				$defaultText = $value; // TODO
				$options[] = JHtmlSelect::option($value, $defaultText);
			}
			$filters[] = $options;
		}
		
		$dispatcher->trigger('onDJMessagesSourceFiltersPrepare', array(&$filters, $this->getState('filter.msg_source'), $this->getState('filter.msg_source_id')));
		
		return $filters;
	}
	
	protected function prepareFiltersData() {
		$filters = array();
		
		$msg_source = $this->getState('filter.msg_source');
		$msg_source_id = $this->getState('filter.msg_source_id');
		$user = $this->getState('filter.user', JFactory::getUser()->id);
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('DISTINCT a.msg_source');
		$query->from('#__djmsg_messages AS a');
		$query->where('( a.user_from = '.$user.' OR a.user_to = '.$user.' )')->where('a.msg_source IS NOT NULL');
		$query->order('a.msg_source');
		$db->setQuery($query);
		
		$filters[] = $db->loadColumn();
		
		if ($msg_source != '') {
			$query = $db->getQuery(true);
			$query->select('DISTINCT a.msg_source_id');
			$query->from('#__djmsg_messages AS a');
			$query->where('( a.user_from = '.$user.' OR a.user_to = '.$user.' )');
			$query->where('a.msg_source IS NOT NULL');
			$query->where('a.msg_source LIKE '.$db->quote($db->escape($msg_source)));
			$query->order('a.msg_source_id');
			$db->setQuery($query);
			
			$filters[] = $db->loadColumn();
		}
		
		return $filters;
	}
}
