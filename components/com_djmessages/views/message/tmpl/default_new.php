<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

$app = JFactory::getApplication();
$user = JFactory::getUser();

$format = $this->params->get('date_format', 'd-m-Y H:i');
$senderUser = DJMessagesHelperMessenger::getUserById($this->item->user_from);

?>
<?php 
JHtml::_('behavior.formvalidator');
?>
<form action="<?php echo JRoute::_(DJMessagesHelperRoute::getFormRoute().'&task=messages.create&format=raw' . ($app->input->get('tmpl') == 'component' ? '&tmpl=component' : '')); ?>" method="post" name="djmsg-message-form" id="djmsg-message-form" class="form-validate form">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_DJMESSAGES_CREATE_LEGEND'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('subject'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('subject'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('message'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('message'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('attachments'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('attachments'); ?>
			</div>
		</div>
		
		<div class="control-group">
			<div class="controls">
				<input type="hidden" name="source" value="self" />
				<input type="hidden" name="jform[send_to_id]" value="<?php echo $app->input->getInt('send_to'); ?>" />
				<button type="submit" class="btn btn-primary validate"><?php echo JText::_('COM_DJMESSAGES_SEND_BTN');?></button>
				<a class="btn djmsg-back-button" href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute())?>"><?php echo JText::_('COM_DJMESSAGES_CANCEL_BTN'); ?></a>
			</div>
		</div>
	</fieldset>
	<?php echo JHtml::_('form.token'); ?>
</form>
