<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/
defined('_JEXEC') or die;

if ($this->checkModules('offcanvas')): ?>
<div id="jm-offcanvas">
	<div id="jm-offcanvas-content" class="jm-offcanvas">
		<jdoc:include type="modules" name="offcanvas" style="jmmodule" />
	</div>
</div>
<?php endif; ?>