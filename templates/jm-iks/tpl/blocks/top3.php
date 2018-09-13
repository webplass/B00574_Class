<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

if($this->countFlexiblock('top3')) : ?>
<div id="jm-top3" class="<?php echo $this->getClass('block#top3'); ?>">
	<div class="container-fluid">
		<?php echo $this->renderFlexiblock('top3','jmmodule'); ?>
	</div>
</div>
<?php endif; ?>