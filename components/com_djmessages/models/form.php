<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 

defined('_JEXEC') or die;

JLoader::register('DJMessagesModelMessage', JPATH_ADMINISTRATOR . '/components/com_djmessages/models/message.php');

/**
 * Private Message model.
 *
 * @since  1.6
 */
class DJMessagesModelForm extends DJMessagesModelMessage
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

		/*$input = JFactory::getApplication()->input;

		$user  = JFactory::getUser();
		$this->setState('user.id', $user->get('id'));

		$messageId = (int) $input->getInt('id');
		$this->setState('message.id', $messageId);

		$replyId = (int) $input->getInt('reply_id');
		$this->setState('reply.id', $replyId);
		
		$sendTo = (int) $input->getInt('send_to_id');
		$this->setState('send_to.id', $sendTo);*/
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
		return false;
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
			// Editing not possible
			$data = new stdClass();
			$item = $this->getItem();
			if ($item) {
				$data->subject = $item->subject == '' ? '' : JText::_('COM_DJMESSAGES_RE').' '.$item->subject;
			}
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
		return parent::publish($pks, $value);
	}
	
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		//return JFactory::getUser()->authorise('core.edit.state', $this->option);
		return (bool)(JFactory::getUser()->id > 0);
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
		
		$options = array('type' => 'reply');
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
