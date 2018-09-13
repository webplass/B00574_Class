<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');

?>

<?php if (!empty( $this->sidebar)) {?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php } else {?>
	<div id="j-main-container">
<?php } ?>
		<div class="djc_control_panel clearfix">
			<div class="cpanel-left">
				<div class="cpanel">
					<div class="icon">
						<a href="<?php echo JRoute::_('index.php?option=com_djmessages&view=messages');?>">
							<img alt="<?php echo JText::_('COM_DJMESSAGES_MESSAGES'); ?>" src="<?php echo JURI::base(); ?>components/com_djmessages/assets/images/icon-48-item.png" />
							<span><?php echo JText::_('COM_DJMESSAGES_MESSAGES'); ?></span>
						</a>
					</div>
					<div class="icon">
						<a href="<?php echo JRoute::_('index.php?option=com_djmessages&view=templates');?>">
							<img alt="<?php echo JText::_('COM_DJMESSAGES_TEMPLATES'); ?>" src="<?php echo JURI::base(); ?>components/com_djmessages/assets/images/icon-48-category.png" />
							<span><?php echo JText::_('COM_DJMESSAGES_TEMPLATES'); ?></span>
						</a>
					</div>
					<div class="icon">
						<a href="<?php echo JRoute::_('index.php?option=com_djmessages&view=users');?>">
							<img alt="<?php echo JText::_('COM_DJMESSAGES_USERS'); ?>" src="<?php echo JURI::base(); ?>components/com_djmessages/assets/images/icon-48-users.png" />
							<span><?php echo JText::_('COM_DJMESSAGES_USERS'); ?></span>
						</a>
					</div>
				</div>
			</div>
			<div class="cpanel-right">
				<div class="djlic_cpanel cpanel">
					<div style="float:right;">
						<?php 
						$user = JFactory::getUser();
						if ($user->authorise('core.admin', 'com_djmessages')){
							echo DJLicense::getSubscription('Messages'); 
						}?>
					</div>
				</div>
			</div>
		</div>
		
	<form action="<?php echo JRoute::_('index.php?option=com_djmessages');?>" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
	</form>
</div>