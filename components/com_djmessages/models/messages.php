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
				'state', 'a.state',
				'user_from', 'a.user_from',
				'user_to', 'a.user_to',
				'sent_time', 'a.sent_time',
				'read_time', 'a.read_time',
				'only_me', 'date_from', 'date_to', 'search'
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
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		// List state information
		$value = $app->input->get('limit', 5, 'uint');
		$value = min(100, $value);
		$this->setState('list.limit', $value);

		$value = $app->input->get('start', $app->input->get('limitstart', 0, 'uint'), 'uint');
		$this->setState('list.start', $value);
		
		// State validation
		$date_from =  $app->input->getString('date_from');
		if (false == DJMessagesHelperMessenger::validateDate($date_from, 'Y-m-d')) {
			$date_from = null;
		}
		$this->setState('filter.date_from', $date_from);
		
		$date_to =  $app->input->getString('date_to');
		if (false == DJMessagesHelperMessenger::validateDate($date_to, 'Y-m-d')) {
			$date_to = null;
		}
		$this->setState('filter.date_to', $date_to);
		
		$origin = $app->input->getCmd('origin', 'inbox');
		$allowedOrigins = array('*', 'inbox', 'sent', 'trash', 'archive');
		if (false == in_array($origin, $allowedOrigins)) {
			$origin = 'inbox';
		}
		$this->setState('filter.origin', $origin);
		
		$search = $app->input->getString('search', null);
		$this->setState('filter.search', $search);
		
		$msg_source = $app->input->getString('ms', '');
		$this->setState('filter.msg_source', $msg_source);
		
		$msg_source_id = $msg_source != '' ? $app->input->getInt('msid', 0) : 0;
		$this->setState('filter.msg_source_id', $msg_source_id);
		
		$order = $app->input->getString('order', 'date');
		if (!in_array($order, array('name', 'date', 'subject'))) {
			$order = 'date';
		}
		$this->setState('list.ordering', $order);
		
		$dir = $app->input->getString('dir', 'd');
		if (!in_array($dir, array('a', 'd'))) {
			$dir = 'd';
		}
		$this->setState('list.direction', $dir);
		
		//$state = $app->input->getString('state', null);
		//$this->setState('filter.state', $state);
		
		$this->setState('filter.user', $user->id);
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
		//$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.origin');
		$id .= ':' . $this->getState('filter.date_from');
		$id .= ':' . $this->getState('filter.date_to');
		$id .= ':' . $this->getState('filter.user');
		$id .= ':' . $this->getState('filter.new');
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
		$user = $this->setState('filter.user', JFactory::getUser()->id);

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
		/*$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		elseif ($state !== '*')
		{
			$query->where('(a.state IN (0, 1))');
		}*/

		// Filter by search in subject or message.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			/*if (stripos($search, 'id:') === 0) {
				$query->where('u1.id = '.(int) substr($search, 3).' OR u2.id = '.(int) substr($search, 3));
			}
			else*/ if (stripos($search, '@') !== false) {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('u1.email LIKE ' . $search . ' OR u2.email LIKE ' . $search);
			} else {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.subject LIKE ' . $search . ' OR a.message LIKE ' . $search);
			}
		}
		
		$origin = $this->getState('filter.origin');
		$filter_new = $this->getState('filter.new');
		
		switch ($origin) {
			case 'sent' : {
				$query->where('a.user_from = '.$user.' AND a.sender_state IN (0, 1)');
				break;
			}
			case 'trash' : {
				$query->where('((a.user_from = '.$user.' AND a.sender_state=-2) OR (a.user_to = '.$user.' AND a.recipient_state=-2))');
				break;
			}
			case 'archive' : {
				$query->where('((a.user_from = '.$user.' AND a.sender_state=2) OR (a.user_to = '.$user.' AND a.recipient_state=2))');
				//$query->where('(a.user_from = '.$user.' OR a.user_to = '.$user.') AND a.state=2');
				break;
			}
			case '*' : {
				$query->where('(a.user_from = '.$user.' OR a.user_to = '.$user.')');
				break;
			}
			case 'inbox' :
			default: {
				if ($filter_new) {
					$query->where('a.user_to = '.$user.' AND a.recipient_state = 0');
				} else {
					$query->where('a.user_to = '.$user.' AND a.recipient_state IN (0, 1)');
				}
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
		
		
		$order = $this->getState('list.ordering', 'date');
		$dir = $this->getState('list.direction', 'd');
		
		switch ($order) {
			case 'name': {
				if ($origin == 'sent') {
					$order = 'a.recipient_name';
				} else {
					$order = 'a.sender_name';
				}
				break;
			}
			case 'subject': {
				$order = 'a.subject';
				break;
			}
			case 'date': 
			default: {
				$order = 'a.sent_time';
				break;
			}
		}
		
		if ($dir == 'd') {
			$dir = 'desc';
		} else {
			$dir = 'asc';
		}
		
		// Add the list ordering clause.
		$query->order($db->escape($order) . ' ' . $db->escape($dir));
		return $query;
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
