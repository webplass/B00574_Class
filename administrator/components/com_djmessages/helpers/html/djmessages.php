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
 * JHtml administrator messages class.
 *
 * @since  1.6
 */
class JHtmlDJMessages
{
	/**
	 * Get the HTML code of the state switcher
	 *
	 * @param   int      $value      The state value
	 * @param   int      $i          Row number
	 * @param   boolean  $canChange  Can the user change the state?
	 *
	 * @return  string
	 *
	 * @since   1.6
	 *
	 * @deprecated  4.0  Use JHtmlMessages::status() instead
	 */
	public static function state($value = 0, $i = 0, $canChange = false)
	{
		// Log deprecated message
		JLog::add(
			'JHtmlMessages::state() is deprecated. Use JHtmlMessages::status() instead.',
			JLog::WARNING,
			'deprecated'
		);

		// Note: $i is required but has to be an optional argument in the function call due to argument order
		if (null === $i)
		{
			throw new InvalidArgumentException('$i is a required argument in JHtmlMessages::state');
		}

		// Note: $canChange is required but has to be an optional argument in the function call due to argument order
		if (null === $canChange)
		{
			throw new InvalidArgumentException('$canChange is a required argument in JHtmlMessages::state');
		}

		return static::status($i, $value, $canChange);
	}

	/**
	 * Get the HTML code of the state switcher
	 *
	 * @param   int      $i          Row number
	 * @param   int      $value      The state value
	 * @param   boolean  $canChange  Can the user change the state?
	 *
	 * @return  string
	 *
	 * @since   3.4
	 */
	public static function status($i, $value = 0, $canChange = false)
	{
		// Array of image, task, title, action.
		$states = array(
			-2 => array('trash', 'messages.unpublish', 'JTRASHED', 'COM_MESSAGES_MARK_AS_UNREAD'),
			1 => array('publish', 'messages.unpublish', 'COM_DJMESSAGES_OPTION_READ', 'COM_DJMESSAGES_MARK_AS_UNREAD'),
			0 => array('unpublish', 'messages.publish', 'COM_DJMESSAGES_OPTION_UNREAD', 'COM_DJMESSAGES_MARK_AS_READ'),
		);

		$state = JArrayHelper::getValue($states, (int) $value, $states[0]);
		$icon  = $state[0];
		

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><span class="icon-'	. $icon . '"></span></a>';
		} else {
			$html = '<a href="#" class="disabled btn btn-micro hasTooltip'
					. ($value == 1 ? ' active' : '') . '"><span class="icon-'	. $icon . '"></span></a>';
		}

		return $html;
	}
}
