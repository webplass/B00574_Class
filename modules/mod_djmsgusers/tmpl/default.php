<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');
?>
<div id="mod_djmsg-users-<?php echo $mId; ?>" class="mod_djmsg-users <?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8'); ?>">
	<ul class="list unstyled">
	<?php foreach($data['users'] as $user) {?>
		<?php 
		$link = JRoute::_(DJMessagesHelperRoute::getMessageRoute().'&send_to=' . $user->id);
		?>
		<li>
			<a href="<?php echo $link; ?>">
				<?php echo JText::sprintf('MOD_DJMSGUSERS_SEND_TO', $user->name, $user->username); ?>
			</a>
		</li>
	<?php } ?>
	</ul>
	<?php if ($data['total'] > $params->get('limit', 10)) { ?>
	<div class="btn-group">
		<button class="btn btn-mini djmsg-nav djmsg-prev"><?php echo JText::_('MOD_DJMSGUSERS_PREV_BTN'); ?></button>
		<button class="btn btn-mini djmsg-nav djmsg-next"><?php echo JText::_('MOD_DJMSGUSERS_NEXT_BTN'); ?></button>
	</div>
	<script>
		(function($){
			$(document).ready(function(){
				var offset = 0;
				var total = <?php echo $data['total']; ?>;
				var perPage = <?php echo $params->get('limit', 1); ?>;
				var request;
				$('#mod_djmsg-users-<?php echo $mId; ?> .djmsg-nav').click(function(){
					if ($(this).hasClass('djmsg-prev')) {
						offset -= perPage;
					} else {
						offset += perPage;
					}

					if (offset < 0) {
						offset = (Math.ceil(total/perPage)-1) * perPage;
					} else if (offset >= total) {
						offset = 0;
					}
					
					if (request && request.readyState != 4) {
						request.abort();
					}

					request = $.ajax({
						method: 'post',
						url: '<?php echo JRoute::_('index.php?option=com_djmessages&task=getUsers&format=raw', false); ?>',
						data: 'limit=' + perPage + '&offset=' + offset + '&sort=<?php echo $params->get('orderby', 'name-asc'); ?>'
					}).done(function(resp){
						var list = $('#mod_djmsg-users-<?php echo $mId; ?> ul');
						list.find('li').remove();

						var json_resp;
						try {
							var json_resp = JSON.parse(resp);
						} catch(e) {
							console.log('err');
							return;
						}
						
						if (!json_resp) {
							return;
						}

						total = json_resp.total;
						$.each(json_resp.users, function(){
							var element = this;
							var link = '<li><a href="'+this._msg_link+'"><?php echo JText::sprintf('MOD_DJMSGUSERS_SEND_TO', '%1%', '%2%'); ?></a></li>';
							link = link.replace('%1%', element.name).replace('%2%', element.username);
							list.append($(link));
						});
					});
				});
			});
		})(jQuery);
	</script>
	<?php } ?>
</div>