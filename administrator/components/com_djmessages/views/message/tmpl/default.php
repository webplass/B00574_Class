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

JHtml::_('behavior.core');
JHtml::_('formbehavior.chosen', 'select');
$attachments = DJMessagesHelperAttachment::getFiles($this->item);

?>
<form action="<?php echo JRoute::_('index.php?option=com_djmessages'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<fieldset>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_DJMESSAGES_FIELD_USER_ID_FROM_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo $this->item->get('from_name');?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_DJMESSAGES_FIELD_DATE_TIME_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo JHtml::_('date', $this->item->sent_time);?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_DJMESSAGES_FIELD_SUBJECT_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo $this->item->subject;?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_DJMESSAGES_FIELD_MESSAGE_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo ($this->item->message == strip_tags($this->item->message)) ? nl2br($this->item->message) : $this->item->message; ?>
			</div>
		</div>
		
		<?php if ($attachments) {?>
		<h4><?php echo JText::_('COM_DJMESSAGES_ATTACHMENTS_HEADING'); ?></h4>
		<ul class="inline">
			<?php foreach ($attachments as $file) {?>
			<li>
				<a target="_blank" class="btn btn-mini" href="<?php echo JRoute::_(DJMessagesHelperRoute::getAttachmentDownload($this->item->id, $file['name'])); ?>"><?php echo $this->escape($file['name']); ?></a>
			</li>
			<?php } ?>
		</ul>
		<?php } ?>
		
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="reply_id" value="<?php echo $this->item->id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
