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
 * Private Message model.
 *
 * @since  1.6
 */
class DJMessagesModelUser extends JModelAdmin
{
	/**
	 * Message
	 */
	protected $item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		parent::populateState();

		$input = JFactory::getApplication()->input;

		$id = (int) $input->getInt('id');
		$this->setState('user.id', $id);
	}

	/**
	 * Check that recipient user is the one trying to delete and then call parent delete method
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since  3.1
	 */
	public function delete(&$pks)
	{
		return parent::delete($pks);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'User', $prefix = 'DJMessagesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		return parent::getItem($pk);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_djmessages.user', 'user', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_djmessages.edit.user.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_djmessages.user', $data);

		return $data;
	}

	/**
	 * Checks that the current user matches the message recipient and calls the parent publish method
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function publish(&$pks, $value = 1)
	{
		return parent::publish($pks, $value);
	}
	
	public function visible(&$pks, $value = 1)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;
		
		// Include the plugins for the change of state event.
		JPluginHelper::importPlugin($this->events_map['change_state']);
		
		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();
			
			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
					
					return false;
				}
				
				// If the table is checked out by another user, drop it and report to the user trying to change its state.
				if (property_exists($table, 'checked_out') && $table->checked_out && ($table->checked_out != $user->id))
				{
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), JLog::WARNING, 'jerror');
					
					// Prune items that you can't change.
					unset($pks[$i]);
					
					return false;
				}
			}
		}
		
		// Attempt to change the state of the records.
		if (!$table->visible($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());
			
			return false;
		}
		
		$context = $this->option . '.' . $this->name;
		
		// Trigger the change state event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
		
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());
			
			return false;
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}
}
