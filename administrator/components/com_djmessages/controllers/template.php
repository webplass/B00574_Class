<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */
 

defined('_JEXEC') or die;

/**
 * Messages Component Message Model
 *
 * @since  1.6
 */
class DJMessagesControllerTemplate extends JControllerForm
{
	/**
	 * Method (override) to check if you can save a new or existing record.
	 *
	 * Adjusts for the primary key name and hands off to the parent class.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave($data, $key = 'id')
	{
		return parent::allowSave($data, $key);
	}

	/**
	 * Reply to an existing message.
	 *
	 * This is a simple redirect to the compose form.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */

}
