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
class DJMessagesTableUser extends JTable
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
		parent::__construct('#__djmsg_users', 'user_id', $db);
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
		
		$pairs = array();
		foreach($pks as $user_id) {
			$pairs[] = '('.$user_id.', '.$state.')';
		}

		// INSERT INTO jos_djmsg_users (user_id, state) VALUES(821, 1) ON DUPLICATE KEY UPDATE state=0

		// Update the publishing state for rows with the given primary keys.
		$query = 'INSERT INTO  ' . $this->_db->quoteName($this->_tbl)
			. ' ('.$this->_db->quoteName('user_id').', '.$this->_db->quoteName('state').') '
			. ' VALUES ' . implode(', ', $pairs);
		
		if ($state == 0) {
			$query .= ' ON DUPLICATE KEY UPDATE state=' . $state.', visible=0';
		} else {
			$query .= ' ON DUPLICATE KEY UPDATE state=' . $state;
		}
		
		$this->_db->setQuery($query);
		
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
	
	public function visible($pks = null, $visible = 1, $userId = 0)
	{
		$k = $this->_tbl_key;
		
		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$visible  = (int) $visible;
		
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
		
		$pairs = array();
		$state = $visible == 1 ? 1 : 0;
		foreach($pks as $user_id) {
			$pairs[] = '('.$user_id.', '.$state.', '.$visible.')';
		}
		
		
		// Update the publishing state for rows with the given primary keys.
		$query = 'INSERT INTO  ' . $this->_db->quoteName($this->_tbl)
			. ' ('.$this->_db->quoteName('user_id').', '.$this->_db->quoteName('state').', '.$this->_db->quoteName('visible').') '
			. ' VALUES ' . implode(', ', $pairs);
		
			if ($visible == 1) {
				$query .= ' ON DUPLICATE KEY UPDATE visible=' . $visible.', state=1';
			} else {
				$query .= ' ON DUPLICATE KEY UPDATE visible=' . $visible;
			}
			
			$this->_db->setQuery($query);
			
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
				$this->visible = $visible;
			}
			
			$this->setError('');
			
			return true;
	}
}
