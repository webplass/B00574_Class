<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

//item view
$app = JFactory::getApplication();
$itemviewmenuitem = in_array($app->input->get('Itemid'), (array)$this->params->get('itemViewMenuItem', array()), true);
$itemview = (((JRequest::getVar('option') == 'com_djclassifieds') && (JRequest::getVar('view') == 'item')) || ((JRequest::getVar('option') == 'com_content') && (JRequest::getVar('view') == 'article'))) ? true : false;
$itemclass = ($itemviewmenuitem && $itemview) ? 'item_view' : '';


// get direction
$direction = $this->params->get('direction', 'ltr');

// responsive
$responsivelayout = $this->params->get('responsiveLayout', '1');
$responsivedisabled = ($responsivelayout != '1') ? 'responsive-disabled' : '';

// custom classes
$stickybar = ($this->params->get('stickyBar', '0')) ? 'sticky-bar' : '';
$topbar = ($this->checkModules('top-bar')) ? 'top-bar' : '';
$topmenu = ($this->checkModules('top-menu-nav')) ? 'top-menu' : '';

//coming soon
$comingsoon = $this->params->get('comingSoon', '0');
$comingsoondate = $this->params->get('comingSoonDate');

$tz = new DateTimeZone(JFactory::getConfig()->get('offset', 'UTC'));
$server_date_cs = JFactory::getDate($comingsoondate, $tz);
$timestamp_cs = $server_date_cs->toUnix();
$server_date_now = JFactory::getDate(null, $tz);
$timestamp_now = $server_date_now->toUnix();
$futuredate = ($timestamp_now > $timestamp_cs) ? '0' : '1';

//offcanvas
// get offcanvas
$offcanvas = $this->params->get('offCanvas', '0');

// get off-canvas position
$offcanvasside = ($offcanvas == '1') ? $this->params->get('offCanvasPosition', $this->defaults->get('offCanvasPosition')) : '';
if ($offcanvasside == 'right') {
	$offcanvasposition = 'off-canvas-right';
} else if ($offcanvasside == 'left') {
	$offcanvasposition = 'off-canvas-left';
} else {
	$offcanvasposition = '';
}

// define default blocks and their default order (can be changed in layout builder)
$default = 'header-mod,top1,top2,top3,system-message,main,bottom1,bottom2,bottom3,bottom4,footer-mod,footer';
$exclude = 'comingsoon';

// check for homepage
$app = JFactory::getApplication();
$menu = $app->getMenu();
$lang = JFactory::getLanguage();
if ($menu->getActive() == $menu->getDefault($lang->getTag())) {
	$is_home = 'homepage';
} else {
	$is_home = 'subpage';
}

$option = JRequest::getVar('option','');
$view = JRequest::getVar('view','');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $direction; ?>">
<head>
	<?php $this->renderBlock('head'); ?>
</head>
<body class="<?php echo $responsivedisabled.' '.$stickybar.' '.$topbar.' '.$topmenu.' '.$offcanvasposition.' '.$itemclass.' '.$is_home.' '.$option.' '.$view; ?>">
	<div id="jm-allpage">
		<?php if(($comingsoon!='0') && (!empty($comingsoondate)) && ($futuredate=='1') && JFactory::getApplication()->isSite() && JFactory::getUser()->guest) {
		$this->renderBlock('comingsoon');
		} else { ?>
			<?php $this->renderBlock('header'); ?>
			<div class="jm-wrapper">
			<?php $this->renderBlocks($default, $exclude); ?>
			<?php if($offcanvas == '1') : ?>
				<?php $this->renderBlock('offcanvas'); ?>
			<?php endif; ?>
			</div>
		<?php } ?>
	</div>
</body>
</html>
