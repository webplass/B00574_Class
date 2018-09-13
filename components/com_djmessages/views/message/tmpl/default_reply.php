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
$date = JHtml::_('date', $this->item->sent_time, $format);
$sender = ($this->item->from_name) ? $this->item->from_name : $this->item->sender_name;
$recipient = ($this->item->to_name) ? $this->item->to_name : $this->item->recipient_name;

$isSenderBanned = DJMessagesHelperMessenger::isUserBannedByUser($this->item->user_from, $user->id);
$senderUser = DJMessagesHelperMessenger::getUserById($this->item->user_from);

$attachments = DJMessagesHelperAttachment::getFiles($this->item);
?>
<h1 class="djmsg-subject">
	<?php echo $this->escape($this->item->subject); ?>
</h1>
<div class="djmsg-info">
	<dl class="article-info muted">
		<dt><?php echo JText::_('COM_DJMESSAGES_DETAILS');?></dt>
		<dd><?php echo JText::sprintf('COM_DJMESSAGES_SENT_DATE', $date); ?></dd>
		<dd>
			<?php echo JText::sprintf('COM_DJMESSAGES_WRITTEN_BY', $sender); ?>
			<?php if ($this->item->user_from != $user->id) {?>
				<?php if ($isSenderBanned) {?>
					<a class="djmsg-unban" href="<?php echo JRoute::_(DJMessagesHelperRoute::getFormRoute().'&task=messages.unban&format=raw&user_id=' . $this->item->user_from . ($app->input->get('tmpl') == 'component' ? '&tmpl=component' : '')); ?>">
						<?php echo JText::_('COM_DJMESSAGES_UNBAN_BTN'); ?>
					</a>
				<?php } else if ($senderUser && $senderUser->authorise('core.admin') == false){?>
					<a class="djmsg-ban" href="<?php echo JRoute::_(DJMessagesHelperRoute::getFormRoute().'&task=messages.ban&format=raw&user_id=' . $this->item->user_from . ($app->input->get('tmpl') == 'component' ? '&tmpl=component' : '')); ?>">
						<?php echo JText::_('COM_DJMESSAGES_BAN_BTN'); ?>
					</a>
				<?php } ?>
			<?php } ?>
		</dd>
		<dd><?php echo JText::sprintf('COM_DJMESSAGES_SENT_TO', $recipient); ?></dd>
	</dl>
</div>
<div class="djmsg-message">
	<?php echo ($this->item->message == strip_tags($this->item->message)) ? nl2br($this->item->message) : $this->item->message; ?>
</div>
<?php if ($attachments) {?>
<div class="djmsg-attachments">
	<h4><?php echo JText::_('COM_DJMESSAGES_ATTACHMENTS_HEADING'); ?></h4>
	<ul class="inline">
		<?php foreach ($attachments as $file) {?>
		<li>
			<a target="_blank" class="btn btn-mini" href="<?php echo JRoute::_(DJMessagesHelperRoute::getAttachmentDownload($this->item->id, $file['name'])); ?>"><?php echo $this->escape($file['name']); ?></a>
		</li>
		<?php } ?>
	</ul>
</div>
<?php } ?>

<?php if ($this->form && ($user->id != $this->item->user_from) && $this->item->user_from > 0 && $user->authorise('djmsg.reply', 'com_djmessages')) {?>
<?php 
JHtml::_('behavior.formvalidator');
?>
<form action="<?php echo JRoute::_(DJMessagesHelperRoute::getFormRoute().'&task=messages.reply&format=raw' . ($app->input->get('tmpl') == 'component' ? '&tmpl=component' : '')); ?>" method="post" name="djmsg-message-form" id="djmsg-message-form" class="form-validate form">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_DJMESSAGES_REPLY_LEGEND'); ?></legend>
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
				<input type="hidden" name="jform[reply_to]" value="<?php echo $this->item->id; ?>" />
				<button type="submit" class="btn btn-primary validate"><?php echo JText::_('COM_DJMESSAGES_SEND_BTN');?></button>
				<a class="btn djmsg-back-button" href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute())?>"><?php echo JText::_('COM_DJMESSAGES_CANCEL_BTN'); ?></a>
			</div>
		</div>
	</fieldset>
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php } ?>

