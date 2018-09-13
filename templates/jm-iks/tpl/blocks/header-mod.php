<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;
$view = $input->get('view');
$component = $input->get('option');
$itemview = ( $component=='com_djclassifieds' && $view=='item' ) ? true : false;


if($this->countFlexiblock('header') && ($itemview == false)) : ?>


<div id="jm-header-mod" class="<?php echo $this->getClass('block#header-mod'); ?>">
	<div class="container-fluid">
		<?php echo $this->renderFlexiblock('header','jmmodule'); ?>
	</div>
</div>
<?php endif; ?>
