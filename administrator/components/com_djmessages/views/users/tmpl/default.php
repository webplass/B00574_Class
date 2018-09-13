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

$states = array(
	1 => array('unpublish', 'COM_DJMESSAGES_STATE_UNBANNED', 'COM_DJMESSAGES_STATE_BAN', 'COM_DJMESSAGES_STATE_UNBANNED', true, 'publish', 'publish'),
	0 => array('publish', 'COM_DJMESSAGES_STATE_BANNED', 'COM_DJMESSAGES_STATE_UNBAN', 'COM_DJMESSAGES_STATE_BANNED', true, 'unpublish', 'unpublish')
);
$visibility = array(
	1 => array('invisible', 'COM_DJMESSAGES_STATE_VISIBLED', 'COM_DJMESSAGES_STATE_INVISIBLE', 'COM_DJMESSAGES_STATE_VISIBLED', true, 'publish', 'unpublish'),
	0 => array('visible', 'COM_DJMESSAGES_STATE_INVISIBLED', 'COM_DJMESSAGES_STATE_VISIBLE', 'COM_DJMESSAGES_STATE_INVISIBLED', true, 'unpublish', 'publish')
);

?>
<form action="<?php echo JRoute::_('index.php?option=com_djmessages&view=users'); ?>" method="post" name="adminForm" id="adminForm">
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
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_STATUS_ACTIVE', 'u.state', $listDirn, $listOrder); ?>
							&nbsp; / &nbsp;
							<?php echo JHtml::_('searchtools.sort', 'COM_DJMESSAGES_STATUS_VISIBLE', 'u.visible', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="4">
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
							<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->id); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
							<span>(<?php echo $item->username?>, <?php echo $item->email; ?>)</span>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.state', $states, (int)$item->state, $i, 'users.', $canChange); ?>
								<?php echo JHtml::_('jgrid.state', $visibility, (int)$item->visible, $i, 'users.', $canChange); ?>
							</div>
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
