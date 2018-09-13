<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 */
 
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Messages list controller class.
 *
 * @since  1.6
 */
class DJMessagesControllerUsers extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_DJMESSAGES_USR';
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->registerTask('invisible', 'visible');
	}
	
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'User', $prefix = 'DJMessagesModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	
	public function visible()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$data = array('visible' => 1, 'invisible' => 0);
		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');
		
		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
			
			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);
			
			// Publish the items.
			try
			{
				$model->visible($cid, $value);
				$errors = $model->getErrors();
				$ntext = null;
				
				if ($value == 1)
				{
					if ($errors)
					{
						JFactory::getApplication()->enqueueMessage(JText::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
					}
					else
					{
						$ntext = $this->text_prefix . '_N_ITEMS_VISIBLED';
					}
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_INVISIBLED';
				}
				
				if ($ntext !== null)
				{
					$this->setMessage(JText::plural($ntext, count($cid)));
				}
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}
		
		$extension = $this->input->get('extension');
		$extensionURL = $extension ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
	}
}
