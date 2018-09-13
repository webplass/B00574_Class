<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

if($this->countFlexiblock('footer-mod')) : ?>
<div id="jm-footer-mod" class="<?php echo $this->getClass('block#footer-mod') ?>">
	<div class="container-fluid">
		<?php echo $this->renderFlexiblock('footer-mod','jmmodule'); ?>
	</div>
</div>
<?php endif; ?>