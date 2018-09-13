<?php
/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

//get logo and site description
$logo = htmlspecialchars($this->params->get('logo'));
$logotext = htmlspecialchars($this->params->get('logoText'));
$sitedescription = htmlspecialchars($this->params->get('siteDescription'));
$logobox = ($logo != '') or ($logotext != '') or ($sitedescription != '');
$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');

if ($this->checkModules('top-menu-nav') or $this->checkModules('search') or $logobox) : ?>
<header id="jm-header" class="<?php echo $this->getClass('block#header') ?>">
	<div class="container-fluid">
		<?php if ($logobox) : ?>
		<div id="jm-logo-sitedesc" class="pull-left">
			<?php if (($logo != '') or ($logotext != '')) : ?>
			<div id="jm-logo">
				<a href="<?php echo JURI::base(); ?>">
					<?php if ($logo != '') : ?>
					<img src="<?php echo JURI::base(), $logo; ?>" alt="<?php if(!$logotext) { echo $sitename; } else { echo $logotext; }; ?>" />
					<?php else : ?>
					<?php echo '<span>'.$logotext.'</span>';?>
					<?php endif; ?>
				</a>
			</div>
			<?php endif; ?>
			<?php if ($sitedescription != '') : ?>
			<div id="jm-sitedesc">
				<?php echo $sitedescription; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if($this->checkModules('top-menu-nav')) : ?>
		<nav id="jm-top-menu-nav" class="pull-right <?php echo $this->getClass('top-menu-nav') ?>">
			<jdoc:include type="modules" name="<?php echo $this->getPosition('top-menu-nav') ?>" style="jmmoduleraw" />
		</nav>
		<?php endif; ?>
		<?php if($this->checkModules('search')) : ?>
		<div id="jm-search" class="<?php if ($app->isAdmin()) {echo 'pull-left';} ?> <?php echo $this->getClass('search') ?>">
			<jdoc:include type="modules" name="<?php echo $this->getPosition('search') ?>" style="jmmoduleraw" />
		</div>
		<?php endif; ?>
	</div>
</header>
<?php endif; ?>