<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');


$show_icon = $params->get('show_icon', 0);
$show_button = $params->get('show_button', 1);

$selected_layout = $params->get('selected_layout', 1);

$icon = $params->get('icon', 'fa fa-envelope-o');
$icon_image = $params->get('image_icon', 0);

$label = (count($data) < 1) ? JText::_('MOD_DJMSGNOTIFICATIONS_NO_NEW_MESSAGES') : JText::sprintf('MOD_DJMSGNOTIFICATIONS_YOU_HAVE_X_MESSAGES', count($data));

?>

<div class="mod_djmsgnotifications <?php echo $theme_class; ?> <?php echo (count($data) < 1) ? 'no-messages' : 'has-messages'; ?> <?php echo ( $selected_layout > 1 ) ? 'layout-number' : 'layout-label'; ?>">
	<div class="djmsg_notinfo">
		<a href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute());?>" class="djmsg_link" title="<?php echo $label; ?>">
			<?php if( $show_icon ) { ?>
				<?php if( $icon_image ) { ?>
				<span class="djmsg_icon_image"><img src="<?php echo $icon_image; ?>" alt="<?php echo JText::_('MOD_DJMSGNOTIFICATIONS_ICON_IMAGE_ALT'); ?>"></span>
				<?php } else { ?>
				<span class="djmsg_icon"><span class="<?php echo htmlspecialchars($icon); ?>" aria-hidden="true"></span></span>
				<?php } ?>
			<?php } ?>
			<?php if ( $selected_layout == 3 ) { ?>
				<?php if (count($data) > 0) {?>
					<span class="djmsg_number"><?php echo count($data); ?></span>
				<?php } ?>
			<?php } elseif( $selected_layout == 2 ) { ?>
				<span class="djmsg_number"><?php echo count($data); ?></span>
			<?php } elseif( $selected_layout == 1 ) { ?>
				<span class="djmsg_label"><?php echo $label; ?></span>
			<?php } ?>
		</a>
	</div>

	<?php if ( $show_button ) {?>
	<div class="djmsg_inbox_button">
		<a href="<?php echo JRoute::_(DJMessagesHelperRoute::getMessagesRoute());?>" class="btn btn-primary"><?php echo JText::_('MOD_DJMSGNOTIFICATIONS_INBOX'); ?></a>
	</div>
	<?php } ?>
</div>