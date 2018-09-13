<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();

$scriptState = array(
		'origin' => $this->state->get('filter.origin', 'inbox'),
		'start' => $this->state->get('list.start'),
		'limit' => $this->state->get('list.limit'),
		'total' => $this->pagination->total,
		'pagesTotal' => $this->pagination->pagesTotal,
		'url' => JRoute::_(DJMessagesHelperRoute::getMessagesRoute().'&format=raw', false),
		'order' => $this->state->get('list.ordering'),
		'dir' => $this->state->get('list.direction'),
		'search' => $this->state->get('filter.search', ''),
		'ms' => $this->state->get('filter.msg_source', ''),
		'msid' => $this->state->get('filter.msg_source_id', 0)
);

JHtmlJquery::framework(true);
JHtmlBootstrap::framework();
JFactory::getDocument()->addScript(JUri::base(true).'/components/com_djmessages/assets/js/script.js');
JFactory::getDocument()->addScriptDeclaration('jQuery(document).ready(function(){DJMessagesUI.init('.json_encode($scriptState).');});');

JHtml::_('behavior.keepalive');

$navClasses = array(
		'inbox' => '',
		'sent' => '',
		'trash' => '',
		'archive' => ''
);

$origin = $this->state->get('filter.origin', 'inbox');
$navClasses[$origin] = 'active';

?>

<div id="djmsg-messages-wrapper" class="djmsg-messages-messages">
	<div class="djmsg-nav">
		<ul class="nav nav-tabs">
			<li class="<?php echo $navClasses['inbox']?>">
				<a href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute().'&origin=inbox').'#msg-inbox'; ?>" data-toggle="tab"><?php echo JText::_('COM_DJMESSAGES_INBOX_NAV'); ?></a>
			</li>
			<li class="<?php echo $navClasses['sent']?>">
				<a href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute().'&origin=sent').'#msg-sent'; ?>" data-toggle="tab"><?php echo JText::_('COM_DJMESSAGES_SENT_NAV'); ?></a>
			</li>
			<li class="<?php echo $navClasses['archive']?>">
				<a href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute().'&origin=archive').'#msg-archive'; ?>" data-toggle="tab"><?php echo JText::_('COM_DJMESSAGES_ARCHIVE_NAV'); ?></a>
			</li>
			<li class="<?php echo $navClasses['trash']?>">
				<a href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute().'&origin=trash').'#msg-trash'; ?>" data-toggle="tab"><?php echo JText::_('COM_DJMESSAGES_TRASH_NAV'); ?></a>
			</li>
		</ul>
	</div>
	<div class="">
		<div class="tab-content">
			<div class="tab-pane msg-folder <?php echo $navClasses['inbox']; ?>" id="msg-inbox">
				<?php 
				$this->origin = 'inbox';
				echo $this->loadTemplate('messages'); 
				?>
			</div>
			<div class="tab-pane msg-folder <?php echo $navClasses['sent']; ?>" id="msg-sent">
				<?php 
				$this->origin = 'sent';
				echo $this->loadTemplate('messages'); 
				?>
			</div>
			<div class="tab-pane msg-folder <?php echo $navClasses['archive']; ?>" id="msg-archive">
				<?php 
				$this->origin = 'archive';
				echo $this->loadTemplate('messages'); 
				?>
			</div>
			<div class="tab-pane msg-folder <?php echo $navClasses['trash']; ?>" id="msg-trash">
				<?php 
				$this->origin = 'trash';
				echo $this->loadTemplate('messages'); 
				?>
			</div>
		</div>
	</div>
</div>

<div id="djmsg-message-wrapper" style="display: none">
	<div class="djmsg-frame-body"></div>
</div>

<script>
if (window.self !== window.top) {
	window.parent.location.href = '<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute(), false); ?>';
}
</script>
