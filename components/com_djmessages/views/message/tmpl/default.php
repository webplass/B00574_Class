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

?>
<div class="djmsg-messages-message">
<a class="pull-right djmsg-close-button" href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute())?>"><?php echo JText::_('COM_DJMESSAGES_GO_TO_MESSAGES'); ?></a>
<?php echo $this->item->id > 0 ? $this->loadTemplate('reply') : $this->loadTemplate('new'); ?>
</div>

<script>
(function($){
	if (window.self !== window.top) {
		$(document).ready(function(){
			$(document.body).css('overflow-y', 'visible');
			$(document.body).css('overflow-x', 'hidden');
			$('input[name="source"]').val('frame');
			$('a.djmsg-back-button, a.djmsg-close-button').click(function(e){
				var msgWrapper = $('#djmsg-message-wrapper', parent.document);
				var listWrapper = $('#djmsg-messages-wrapper', parent.document);
				if (msgWrapper.length < 1 || listWrapper.length < 1) {
					return true;
				}
				e.preventDefault();

				window.top.history.replaceState(window.top.DJMessagesUI.state, null, $(this).attr('href'));
				
				//listWrapper.show();
				//msgWrapper.hide();
				window.top.DJMessagesUI.closeMessage();
				return true; 
			});
		});
		
		/*$(window).on('load resize', function(){
			var wrapper = $('#djmsg-message-wrapper', parent.document);
			if (wrapper.length > 0) {
				$(document.body).css('overflow-y', 'visible');
				var height = $(document).height();
				wrapper.find('.iframe').css({
					'height': height + 'px',
					'overflow': 'hidden'
				});
				$(document.body).css('overflow-y', 'hidden');
			}
		});*/
		$(window).on('load resize djmsg:upload djmsg:uploadremove', function(evt){
			//setTimeout(function(){
				var wrapper = $('.djmsg-quick-message-body', parent.document).first();
				if (wrapper.length > 0) {
					$(document.body).css('overflow-y', 'visible');

					if (evt.type == 'djmsg:uploadremove') {
						wrapper.find('.iframe').css({
							'height': 'auto'
						});
					}
					
					var height = $(document).height();
					setTimeout(function(){
						wrapper.find('.iframe').css({
							'height': height + 'px',
							'overflow': 'hidden'
						});
					}, 10);
					$(document.body).css('overflow-y', 'hidden');
					window.top.DJMessagesUI.toggleLoader(false);
				}
			//}, 200);
			
		});
	}

	$(document).ready(function(){
		$('.djmsg-message a').attr('target', '_blank');
	});
})(jQuery);
</script>