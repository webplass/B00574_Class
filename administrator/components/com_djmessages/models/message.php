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
class DJMessagesModelMessage extends JModelAdmin
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

		$user  = JFactory::getUser();
		$this->setState('user.id', $user->get('id'));

		$messageId = (int) $input->getInt('id');
		$this->setState('message.id', $messageId);

		$replyId = (int) $input->getInt('reply_id');
		$this->setState('reply.id', $replyId);
		
		$sendTo = (int) $input->getInt('send_to_id');
		$this->setState('send_to.id', $sendTo);
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
		/*$pks   = (array) $pks;
		$table = $this->getTable();
		$user  = JFactory::getUser();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($table->user_to != $user->id)
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}*/

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
	public function getTable($type = 'Message', $prefix = 'DJMessagesTable', $config = array())
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
		if (!isset($this->item))
		{
			if ($this->item = parent::getItem($pk))
			{
				// Prime required properties.
				if (empty($this->item->id))
				{
					// Prepare data for a new record.
					if ($replyId = $this->getState('reply.id'))
					{
						// If replying to a message, preload some data.
						$db    = $this->getDbo();
						$query = $db->getQuery(true)
							->select('*')
							->from($db->quoteName('#__djmsg_messages'))
							->where($db->quoteName('id') . ' = ' . (int) $replyId);

						try
						{
							$message = $db->setQuery($query)->loadObject();
						}
						catch (RuntimeException $e)
						{
							$this->setError($e->getMessage());

							return false;
						}

						$this->item->set('user_to', $message->user_from);
						
						$this->item->set('reply_to_id', $message->id);
						$this->item->set('parent_id', $message->parent_id);
						$this->item->set('msg_source', $message->msg_source);
						$this->item->set('msg_source_id', $message->msg_source_id);
						
						$re = JText::_('COM_DJMESSAGES_RE');

						//if (stripos($message->subject, $re) !== 0)
						//{
							$this->item->set('subject', $re . $message->subject);
						//}
					} else if ($sendTo = $this->getState('send_to.id')) {
						$user = new JUser($sendTo);
						if (!empty($user)) {
							$this->item->set('user_to', $user->id);
						}
					}
				}
				/*elseif ($this->item->user_to != JFactory::getUser()->id)
				{
					$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

					return false;
				}*/
				else
				{
					$user = JFactory::getUser();
					// Mark message read
					if (JFactory::getApplication()->isSite() || $user->id == $this->item->user_to) {
						$db    = $this->getDbo();
						$query = $db->getQuery(true)
						->update($db->quoteName('#__djmsg_messages'))
						->set($db->quoteName('recipient_state') . ' = 1')
						->where($db->quoteName('id') . ' = ' . $this->item->id);
						$db->setQuery($query)->execute();
					}
				}
			}

			// Get the user name for an existing messasge.
			if ($this->item->user_from && $fromUser = new JUser($this->item->user_from))
			{
				$this->item->set('from_name', $fromUser->name);
				$this->item->set('from_email', $fromUser->email);
			} else {
				$this->item->set('from_name', $this->item->sender_name);
				$this->item->set('from_email', $this->item->sender_email);
			}
			
			if ($this->item->user_to && $toUser = new JUser($this->item->user_to))
			{
				$this->item->set('to_name', $toUser->name);
				$this->item->set('to_email', $toUser->email);
			} else {
				$this->item->set('to_name', $this->item->recipient_name);
				$this->item->set('to_email', $this->item->recipient_email);
			}
		}

		return $this->item;
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
		$form = $this->loadForm('com_djmessages.message', 'message', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_djmessages.edit.message.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_djmessages.message', $data);

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
		$user  = JFactory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Check that the recipient matches the current user
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk) && !$user->authorise('core.admin', 'com_djmessages'))
			{
				if ($table->user_to != $user->id && $table->user_from != $user->id)
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					return false;
				}
			}
		}
		
		return parent::publish($pks, $value);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$data['user_from'] = JFactory::getUser()->id;
		
		$attachments = null;
		if (!empty($data['attachments'])) {
			$attachments = json_decode($data['attachments'], true);
		}
		
		$options = array('type' => 'admin_message');
		if (isset($data['reply_to_id'])) {
			$options['reply_to'] = $data['reply_to_id'];
			$options['parent'] = isset($data['parent_id']) ? $data['parent_id'] : $options['reply_to'];
		}
		if (!empty($data['msg_source_id']) && !empty($data['msg_source'])) {
			$options['source'] = $data['msg_source'];
			$options['source_id'] = $data['msg_source_id'];
		}
		
		$messenger = new DJMessagesHelperMessenger();
		$success = $messenger->notify($data['message'], $data['subject'], $data['user_from'], $data['user_to'], $options, $attachments);
		
		if (!$success) {
			$this->setError($messenger->getError());
			return false;
		}
		
		return true;
	}
}
