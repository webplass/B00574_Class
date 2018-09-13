<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 

defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			if (task == 'message.cancel' || document.formvalidator.isValid(document.getElementById('message-form')))
			{
				Joomla.submitform(task, document.getElementById('message-form'));
			}
		};
");
?>
<form action="<?php echo JRoute::_('index.php?option=com_djmessages'); ?>" method="post" name="adminForm" id="message-form" class="form-validate form-horizontal">
	<fieldset class="adminform">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('user_to'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('user_to'); ?>
			</div>
		</div>
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
	</fieldset>
	
	<?php echo $this->form->getInput('parent_id'); ?>
	<?php echo $this->form->getInput('reply_to_id'); ?>
	<?php echo $this->form->getInput('msg_source'); ?>
	<?php echo $this->form->getInput('msg_source_id'); ?>
	
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
