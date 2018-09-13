<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$nullDate = JFactory::getDbo()->getNullDate();

?>
<form action="<?php echo JRoute::_('index.php?option=com_djmessages&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"></div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th class="title nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_HEADING_FROM', 'a.user_from', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_HEADING_TO', 'a.user_to', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_HEADING_READ', 'a.recipient_state', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-tablet hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_SENT_DATE', 'a.sent_time', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-tablet hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_READ_DATE', 'a.read_time', $listDirn, $listOrder); ?>
						</th>
						<th width="15%">
							<?php echo JText::_('COM_DJMESSAGES_HEADING_PROFILE_INFO_PLG'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="9">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canChange = $user->authorise('core.edit.state', 'com_djmessages');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_djmessages&view=message&id=' . (int) $item->id); ?>">
								<?php echo $this->escape($item->subject); ?></a>
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_djmessages&task=message.send_to&send_to_id='.$item->user_from);?>"><?php echo $item->from_name; ?></a>
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_djmessages&task=message.send_to&send_to_id='.$item->user_to);?>"><?php echo $item->to_name; ?></a>
						</td>
						<td class="center">
							<?php echo JHtml::_('djmessages.status', $i, $item->recipient_state, ($this->state->get('filter.only_me', 0) == 2)); ?>
						</td>
						<td class="hidden-phone hidden-tablet">
							<?php echo ($item->sent_time && $item->sent_time != $nullDate) ? JHtml::_('date', $item->sent_time, JText::_('DATE_FORMAT_LC4')) : ''; ?>
						</td>
						<td class="hidden-phone hidden-tablet">
							<?php echo ($item->read_time && $item->read_time != $nullDate) ? JHtml::_('date', $item->read_time, JText::_('DATE_FORMAT_LC4')) : '-'; ?>
						</td>
						<td>
							<?php echo $item->_plg_data; ?>
						</td>
						<td class="center">
							<?php echo $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
