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
 * Message Table class
 *
 */
class DJMessagesTableTemplate extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__djmsg_templates', 'id', $db);
	}
	
	public function bind($array, $ignore = '')
	{
		if (empty($array['type'])) {
			$array['type'] = $array['name'];
		}
		$array['type'] = JFilterOutput::stringURLSafe($array['type']);
		$array['type'] = trim(str_replace('-','_',$array['type']));
		if(trim(str_replace('_','',$array['type'])) == '') {
			$array['type'] = JFactory::getDate()->format('Y_m_d_H_i_s');
		}
		
		return parent::bind($array, $ignore);
	}

	/**
	 * Validation and filtering.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function check()
	{
		if (empty($this->subject))
		{
			$this->setError(JText::_('COM_DJMESSAGES_ERROR_INVALID_SUBJECT'));

			return false;
		}
		
		if (empty($this->body))
		{
			$this->setError(JText::_('COM_DJMESSAGES_ERROR_INVALID_MESSAGE'));
		
			return false;
		}

		if (empty($this->type))
		{
			$this->setError(JText::_('COM_DJMESSAGES_ERROR_INVALID_TYPE'));

			return false;
		}

		return true;
	}
	
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
	
		$table = JTable::getInstance('Template', 'DJMessagesTable');
		if ($table->load(array('type'=>$this->type)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('COM_DJMESSAGES_ERROR_INVALID_TYPE'));
			return false;
		}
		return parent::store($updateNulls);
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *					          set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . ' IN (' . implode(',', $pks) . ')';

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE ' . $this->_db->quoteName($this->_tbl)
			. ' SET ' . $this->_db->quoteName('state') . ' = ' . (int) $state
			. ' WHERE (' . $where . ')'
		);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}
}
