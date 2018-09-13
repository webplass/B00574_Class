<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

if($this->countFlexiblock('bottom4')) : ?>
<div id="jm-bottom4" class="<?php echo $this->getClass('block#bottom4'); ?>">
	<div class="container-fluid">
		<?php echo $this->renderFlexiblock('bottom4','jmmodule'); ?>
	</div>
</div>
<?php endif; ?>