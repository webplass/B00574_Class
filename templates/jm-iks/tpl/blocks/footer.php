<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

//get information about 'back to top' button
$backtotop = $this->params->get('backToTop', '1');

?>
<footer id="jm-footer" class="<?php echo $this->getClass('block#footer') ?>">
	<div class="container-fluid jm-footer">
		<?php if($this->checkModules('copyrights')) : ?>
			<div id="jm-copyrights" class="<?php echo $this->getClass('copyrights') ?>">
				<jdoc:include type="modules" name="<?php echo $this->getPosition('copyrights') ?>" style="raw"/>
			</div>
		<?php endif; ?>
		<div id="jm-poweredby">
			<a href="http://www.joomla-monster.com" target="_blank" title="Joomla Templates" rel="nofollow">Joomla Templates</a> by Joomla-Monster.com
		</div>
	</div>
</footer>
<?php if($backtotop == '1') : ?>
	<p id="jm-back-top"><a id="backtotop" href="#"><span>&nbsp;</span></a></p>
<?php endif; ?>
