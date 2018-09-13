<?php
/**
* @package DJ-Messages
* @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://dj-extensions.com
* @author email contact@dj-extensions.com
*/

defined('_JEXEC') or die;

$items = $this->originItems[$this->origin];
$pagination_data = $this->pagination->getData();

/* Sorting options */
$order = $this->state->get('list.ordering', 'date');
$dir = $this->state->get('list.direction', 'd');
$new_dir = $dir == 'd' ? 'a' : 'd';
$sortClasses = array(
		'name' => array('', ''),
		'subject' => array('', ''),
		'date' => array('', '')
);
$sortClasses[$order][0] = 'active dir-' . $dir;
$sortClasses[$order][1] = $dir == 'd' ? '<i class="fa fa-caret-down"></i>' : '<i class="fa fa-caret-up"></i>';

/* Limit Box */
$limits = array();
for ($i = 5; $i <= 30; $i += 5)
{
	$limits[] = JHtml::_('select.option', $i, $i);
}
$limits[] = JHtml::_('select.option', '50', JText::_('J50'));
$limits[] = JHtml::_('select.option', '100', JText::_('J100'));
$selectedLimit = $this->state->get('list.limit');

$limitBox = JHtml::_('select.genericlist', $limits, 'limit', 'class="input input-mini"', 'value', 'text', $selectedLimit, 'djmsg-list-limit');

$user = JFactory::getUser();
$isSSL = JUri::getInstance()->isSSL();

$format = $this->params->get('date_format', 'd-m-Y H:i');

?>
<form action="<?php echo JRoute::_('&format=')?>" method="post">
	<div class="btn-toolbar msg-ui-search">
		<div class="form-search">
			<input type="text" class="input-medium search-query djmsg-search-input" name="search" placeholder="<?php echo JText::_('COM_DJMESSAGES_SEARCH_PLACEHOLDER')?>" value="<?php echo $this->state->get('filter.search', ''); ?>" />
			<button type="submit" class="btn djmsg-search-btn"><?php echo JText::_('COM_DJMESSAGES_SEARCH_BTN_TEXT')?></button>
		</div>
		<div class="btn-group msg-ui-filters">
			<?php foreach($this->filters as $k => $filter) {?>
				<?php 
				$name = $k == 0 ? 'ms' : 'msid'; 
				$selected = $k == 0 ? $this->state->get('filter.msg_source') : $this->state->get('filter.msg_source_id');
				echo JHtmlSelect::genericlist($filter, $name, 'class="djmsg-filter input"', 'value', 'text', $selected, $this->origin .'-'.$name);
				?>
			<?php } ?>
		</div>
	</div>
	<div class="btn-toolbar msg-ui-buttons">



	</div>
	<table class="table table-striped msg-messages-table">
		<thead>
			<?php /*?><tr>
				<th><input type="checkbox" class="msgs-toggle-all" value="1" /></th>
				<th><a class="djmsg-sort-link <?php echo $sortClasses['subject'][0]; ?>" data-sort="subject" href="<?php echo JRoute::_('&format=&order=subject&dir='.$new_dir); ?>"><?php echo JText::_('COM_DJMESSAGES_SUBJECT'); ?> <?php echo $sortClasses['subject'][1]; ?></a></th>
				<th><a class="djmsg-sort-link <?php echo $sortClasses['name'][0]; ?>" data-sort="name" href="<?php echo JRoute::_('&format=&order=name&dir='.$new_dir); ?>"><?php echo JText::_('COM_DJMESSAGES_NAME'); ?> <?php echo $sortClasses['name'][1]; ?></a></th>
				<th><a class="djmsg-sort-link <?php echo $sortClasses['date'][0]; ?>" data-sort="date" href="<?php echo JRoute::_('&format=&order=date&dir='.$new_dir); ?>"><?php echo JText::_('COM_DJMESSAGES_DATE'); ?> <?php echo $sortClasses['date'][1]; ?></a></th>
			</tr><?php */ ?>
			<tr>
				<th width="10"><span class="msgs-toggle-all-box"><input type="checkbox" class="msgs-toggle-all" value="1" /></span></th>
				<th>
					<?php if ($this->origin == 'inbox') {?>
						<button class="btn btn-mini djmsgs-ui-btn" data-action="read"><i class="fa fa-envelope-open-o"></i> <?php echo JText::_('COM_DJMESSAGES_UI_MARK_READ'); ?></button>
						<button class="btn btn-mini djmsgs-ui-btn" data-action="unread"><i class="fa fa-envelope-o"></i> <?php echo JText::_('COM_DJMESSAGES_UI_MARK_UNREAD'); ?></button>
					<?php } ?>
					<?php if ($this->origin != 'archive') {?>
						<button class="btn btn-mini djmsgs-ui-btn" data-action="archive"><i class="fa fa-download"></i> <?php echo JText::_('COM_DJMESSAGES_UI_ARCHIVE'); ?></button>
					<?php } ?>
					<?php if ($this->origin != 'trash') {?>
						<button class="btn btn-mini djmsgs-ui-btn" data-action="trash"><i class="fa fa-trash"></i> <?php echo JText::_('COM_DJMESSAGES_UI_TRASH'); ?></button>
					<?php } ?>
					<?php if ($this->origin == 'archive' || $this->origin == 'trash') {?>
						<button class="btn btn-mini djmsgs-ui-btn" data-action="read"><i class="fa fa-undo"></i> <?php echo JText::_('COM_DJMESSAGES_UI_RESTORE'); ?></button>
					<?php } ?>
					<button class="btn btn-mini djmsgs-ui-btn" data-action="refresh"><i class="fa fa-refresh"></i> <?php echo JText::_('COM_DJMESSAGES_UI_REFRESH'); ?></button>
				</th>
				<th>
					<a class="djmsg-sort-link btn btn-mini <?php echo $sortClasses['subject'][0]; ?>" data-sort="subject" href="<?php echo JRoute::_('&format=&order=subject&dir='.$new_dir); ?>"><?php echo JText::_('COM_DJMESSAGES_SUBJECT'); ?> <?php echo $sortClasses['subject'][1]; ?></a>
					<a class="djmsg-sort-link btn btn-mini <?php echo $sortClasses['name'][0]; ?>" data-sort="name" href="<?php echo JRoute::_('&format=&order=name&dir='.$new_dir); ?>"><?php echo JText::_('COM_DJMESSAGES_NAME'); ?> <?php echo $sortClasses['name'][1]; ?></a>
					<a class="djmsg-sort-link btn btn-mini <?php echo $sortClasses['date'][0]; ?>" data-sort="date" href="<?php echo JRoute::_('&format=&order=date&dir='.$new_dir); ?>"><?php echo JText::_('COM_DJMESSAGES_DATE'); ?> <?php echo $sortClasses['date'][1]; ?></a>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($items) > 0) { ?>
				<?php foreach($items as $k => $item) {?>
					<?php 
					
						$date = JHtml::_('date', $item->sent_time, $format);
						$sender = ($item->from_name) ? $item->from_name : $item->sender_name;
						$recipient = ($item->to_name) ? $item->to_name : $item->recipient_name;
					
						$user_name = $sender;
						if ($this->origin == 'trash' || $this->origin == 'archive'){
							$user_name = $sender.' &raquo; '.$recipient;
						} else if ($this->origin == 'sent') {
							$user_name = $recipient;
						}
						
						$bold = false;
						if ($item->recipient_state == 0 && $this->origin == 'inbox') {
							$bold = true;
						}
						
						$link = DJMessagesHelperRoute::getMessageRoute($item->id);
					?>
					<tr>
						<td><input type="checkbox" name="msg_id[]" value="<?php echo $item->id; ?>" /></td>
						<td class="djmsg-message-body" colspan="<?php echo ($item->_plg_data == '') ? '2' : '1'; ?>">
							<span class="djmsg-message-time">
								<?php echo $item->sent_time; ?>
							</span>
							<span class="djmsg-message-heading">
								<a data-msg="<?php echo $item->id; ?>" data-url="<?php echo JRoute::_($link.'&tmpl=component', false, $isSSL ? 1 : -1);?>" data-title="<?php echo $this->escape($item->subject); ?>" href="<?php echo JRoute::_($link); ?>" class="djmsg-message-link">
									<span class="djmsg-username"><?php echo $user_name; ?></span></a>
							</span>
							<span class="djmsg-message-title">
								<a data-msg="<?php echo $item->id; ?>" data-url="<?php echo JRoute::_($link.'&tmpl=component', false, $isSSL ? 1 : -1);?>" data-title="<?php echo $this->escape($item->subject); ?>" href="<?php echo JRoute::_($link); ?>" class="djmsg-message-link">
									<span class="djmsg-subject"><?php echo $bold ? '<strong>'.$item->subject.'</strong>' : $item->subject; ?></span>
								</a>
							</span>
							<span class="djmsg-message-intro">
								<a data-msg="<?php echo $item->id; ?>" data-url="<?php echo JRoute::_($link.'&tmpl=component', false, $isSSL ? 1 : -1);?>" data-title="<?php echo $this->escape($item->subject); ?>" href="<?php echo JRoute::_($link); ?>" class="djmsg-message-link">
									<?php echo JHtml::_('string.truncate', $item->message, 100, $noSplit = true, false) ;?>
								</a>
							</span>
						</td>
						<?php if ($item->_plg_data != '') {?>
							<td><?php echo $item->_plg_data; ?></td>
						<?php } ?>
						<?php /*?><td class="djmsg-action-btns" width="1%" nowrap="nowrap">
							<?php if ($this->origin != 'archive') {?>
							<button class="btn btn-mini djmsgs-ui-msg-btn btn-archive" data-action="archive"  data-id="<?php echo $item->id; ?>"><?php echo JText::_('COM_DJMESSAGES_UI_ARCHIVE'); ?></button>
							<?php } ?>
							<?php if ($this->origin != 'trash') {?>
							<button class="btn btn-mini djmsgs-ui-msg-btn btn-trash" data-action="trash"  data-id="<?php echo $item->id; ?>"><?php echo JText::_('COM_DJMESSAGES_UI_TRASH'); ?></button>
							<?php } ?>
							
							<?php if ($this->origin == 'archive' || $this->origin == 'trash') {?>
								<button class="btn btn-mini djmsgs-ui-msg-btn btn-restore" data-action="read" data-id="<?php echo $item->id; ?>"><?php echo JText::_('COM_DJMESSAGES_UI_RESTORE'); ?></button>
							<?php } ?>
						</td><?php */ ?>
					</tr>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	
	<div class="djmsg-messages-pagination">
		<?php if (count($items) > 0) { ?>
			<div>
				<div class="pagination" id="msgs-pagination-<?php echo $this->origin?>">
					<?php if (!empty($pagination_data->pages) && $this->pagination->total > $this->pagination->limit) {?>
					<ul class="pagination-list">
						<?php
						    $pages = array();
						    $pagination_data->previous->text = '&laquo;';
						    $pages[] = $pagination_data->previous;
						    $pages = array_merge($pages, $pagination_data->pages);
						    $pagination_data->next->text = '&raquo;';
						    $pages[] = $pagination_data->next;
						    
						    foreach($pages as $pageno => $page) {
						        ?>
						            <?php if ($page->active || $page->base === null) { ?>
						                <li class="active small">
						                    <a><?php echo $page->text; ?></a>
						                </li>
						            <?php } else {?>
						                <li class="small">
						                    <a class="pagenav djmsg-pagination-link" data-start="<?php echo $page->base; ?>" href="<?php echo JRoute::_('&format=&limitstart=' . $page->base); ?>"><?php echo $page->text; ?></a>
						                </li>
						            <?php } ?>
						        <?php
						    } ?>
					</ul>
					<p><?php echo $this->pagination->getPagesCounter(); ?></p>
					<?php } ?>
						<?php echo $limitBox; ?>
						<noscript><input type="submit" value="<?php echo JText::_('COM_DJMESSAGES_CHANGE_LIMIT'); ?>"></noscript>
				</div>
			</div>
		<?php } else if (count($items) < 1) { ?>
			<div>
				<p class="alert alert-info"><?php echo JText::_('COM_DJMESSAGES_THIS_FOLDER_IS_EMPTY'); ?></p>
			</div>
		<?php } ?>
	</div>
</form>

<?php 
/*
if (JFactory::getApplication()->input->getCmd('format') == 'raw') {
	$scriptState = array(
			'origin' => $this->state->get('filter.origin', 'inbox'),
			'start' => $this->state->get('list.start'),
			'limit' => $this->state->get('list.limit'),
			'total' => $this->pagination->total,
			'pagesTotal' => $this->pagination->pagesTotal,
			'order' => $this->state->get('list.ordering'),
			'dir' => $this->state->get('list.direction')
	); ?>
	<script>
		if (typeof DJMessagesUI != 'undefined') {
			DJMessagesUI.updateState(<?php echo json_encode($scriptState); ?>);
	
			(function($){
				var links = $('#msgs-pagination-<?php echo $this->origin?> a');
				links.click(function(e){
					e.preventDefault();
					DJMessagesUI.requestPage('<?php echo $this->origin; ?>', $(this).attr('href'));
				});
			})(jQuery);
		}
	</script>
<?php }*/ ?>
