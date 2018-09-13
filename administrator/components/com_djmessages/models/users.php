<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Messages Component Messages Model
 *
 * @since  1.6
 */
class DJMessagesModelUsers extends JModelList
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
				'name', 'a.name',
				'email', 'a.email',
				'state', 'u.state',
				'visible', 'u.visible'
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
	protected function populateState($ordering = 'a.name', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		
		// List state information.
		parent::populateState($ordering, $direction);
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
		$id .= ':' . $this->getState('filter.state');
		
		return parent::getStoreId($id);
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
		
		$plugin = JPluginHelper::getPlugin('system', 'djmessages');
		
		$defState = 0;
		$defVisible = 0;
		if (!empty($plugin)) {
			$plgParams = new Registry($plugin->params);
			$defVisible = $plgParams->get('default_visible', 0);
			$defState = $plgParams->get('default_state', 0);
		}
		
		$defVisible = $defState == 0 ? 0 : $defVisible;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*, IF (u.state IS NULL, '.$defState.', u.state) AS state, IF (u.visible IS NULL, '.$defVisible.', u.visible) AS visible' 
			)
		);
		$query->from('#__users AS a');
		
		$query->join('left', '#__djmsg_users AS u ON u.user_id=a.id');
		
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			if ($state == 1) {
				$query->where('u.state = 1 OR u.state IS NULL ');
			} else {
				$query->where('u.state = 0');
			}
		}
		
		// Filter by search in subject or message.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else if (stripos($search, '@') !== false) {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.email LIKE ' . $search );
			} else {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.name LIKE ' . $search . ' OR a.username LIKE ' . $search.' OR a.email LIKE ' . $search);
			}
		}
		
		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.name')) . ' ' . $db->escape($this->getState('list.direction', 'asc')));

		return $query;
	}
}
