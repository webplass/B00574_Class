<?php

/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

defined('_JEXEC') or die;

class JMTemplate extends JMFTemplate {
	public function postSetUp() {

		// DJ-Classifieds Theme name
		$classifieds_theme = 'jm-iks';

		// ---------------------------------------------------------
		// LESS MAP
		// ---------------------------------------------------------

		// --------------------------------------
		// BOOTSTRAP
		// --------------------------------------

		$this->lessMap['bootstrap.less'] = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/ltr/accordion.less',
			'override/ltr/alerts.less',
			'override/ltr/breadcrumbs.less',
			'override/ltr/button-groups.less',
			'override/ltr/buttons.less',
			'override/ltr/dropdowns.less',
			'override/ltr/forms.less',
			'override/ltr/labels-badges.less',
			'override/ltr/navbar.less',
			'override/ltr/navs.less',
			'override/ltr/pager.less',
			'override/ltr/pagination.less',
			'override/ltr/scaffolding.less',
			'override/ltr/tables.less',
			'override/ltr/type.less',
			'override/ltr/utilities.less',
			'override/ltr/wells.less'
		);

		$this->lessMap['bootstrap_rtl.less'] = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/rtl/accordion.less',
			'override/rtl/alerts.less',
			'override/rtl/breadcrumbs.less',
			'override/rtl/button-groups.less',
			'override/rtl/buttons.less',
			'override/rtl/dropdowns.less',
			'override/rtl/forms.less',
			'override/rtl/labels-badges.less',
			'override/rtl/navbar.less',
			'override/rtl/navs.less',
			'override/rtl/pager.less',
			'override/rtl/pagination.less',
			'override/rtl/scaffolding.less',
			'override/rtl/tables.less',
			'override/rtl/type.less',
			'override/rtl/utilities.less',
			'override/rtl/wells.less'
		);

		$this->lessMap['bootstrap_responsive.less'] = array(
			'bootstrap_variables.less',
			'override/ltr/responsive-767px-max.less'
		);

		$this->lessMap['bootstrap_responsive_rtl.less'] = array(
			'bootstrap_variables.less',
			'override/rtl/responsive-767px-max.less'
		);

		// --------------------------------------
		// TEMPLATE
		// --------------------------------------

		$this->lessMap['template.less'] = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/ltr/buttons.less',
			'template_mixins.less',
			//template
			'editor.less',
			'joomla.less',
			'layout.less',
			'menus.less',
			'modules.less',
			//extensions
			'djmediatools.less'
		);

		$this->lessMap['template_rtl.less'] = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/rtl/buttons.less',
			'template_mixins.less',
			//extensions
			'djmediatools_rtl.less'
		);

		$this->lessMap['template_responsive.less'] = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/ltr/buttons.less',
			'template_mixins.less',
			//extensions
			'djmediatools_responsive.less'
		);

		// other files
		// ---------------------------

		$common_ltr = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/ltr/buttons.less',
			'template_mixins.less'
		);

		$common_rtl = array(
			'bootstrap_variables.less',
			'template_variables.less',
			'override/rtl/buttons.less',
			'template_mixins.less'
		);

		$this->lessMap['comingsoon.less'] = $common_ltr;
		$this->lessMap['offcanvas.less'] = $common_ltr;
		$this->lessMap['offline.less'] = $common_ltr;
		$this->lessMap['custom.less'] = $common_ltr;

		//extensions
		$this->lessMap['djmegamenu.less'] = $common_ltr;
		$this->lessMap['djmegamenu_rtl.less'] = $common_rtl;

		$this->lessMap['djclassifieds.less'] = $common_ltr;
		$this->lessMap['djclassifieds_rtl.less'] = $common_rtl;
		$this->lessMap['djclassifieds_responsive.less'] = $common_ltr;

		// ---------------------------------------------------------
		// LESS VARIABLES
		// ---------------------------------------------------------

		$bootstrap_vars = array();

		/* Basic Settings */

		$itemviewwidth = $this->params->get('JMitemViewWidth', $this->defaults->get('JMitemViewWidth'));
		$bootstrap_vars['JMitemViewWidth'] = $itemviewwidth;

		/* Template Layout */

		//$parametr = $this->params->get('parametr', $this->defaults->get('parametr'));

		$templatefluidwidth = $this->params->get('JMfluidGridContainerLg', $this->defaults->get('JMfluidGridContainerLg'));
		$bootstrap_vars['JMfluidGridContainerLg'] = $templatefluidwidth;

		//check type
		$checkwidthtype = strstr($templatefluidwidth, '%');
		$checkwidthtypevalue = ($checkwidthtype) ? 'fluid' : 'fixed';
		$bootstrap_vars['JMtemplateWidthType'] = $checkwidthtypevalue;
		$templatewidthtype = $this->params->set('JMtemplateWidthType', $checkwidthtypevalue);

		$gutterwidth = $this->params->get('JMbaseSpace', $this->defaults->get('JMbaseSpace'));
		$bootstrap_vars['JMbaseSpace'] = $gutterwidth;

		//offcanvas
		$offcanvaswidth = $this->params->get('JMoffCanvasWidth', $this->defaults->get('JMoffCanvasWidth'));
		$bootstrap_vars['JMoffCanvasWidth'] = $offcanvaswidth;

		/* Font Modifications */

		//body

		$bodyfontsize = (int)$this->params->get('JMbaseFontSize', $this->defaults->get('JMbaseFontSize'));
		$bootstrap_vars['JMbaseFontSize'] = $bodyfontsize.'px';

		$bodyfonttype = $this->params->get('bodyFontType', '1');
		$bodyfontfamily = $this->params->get('bodyFontFamily', $this->defaults->get('bodyFontFamily'));
		$bodygooglewebfontfamily = $this->params->get("bodyGoogleWebFontFamily", $this->defaults->get('bodyGoogleWebFontFamily'));
		$bodygooglewebfonturl = $this->params->get('bodyGoogleWebFontUrl');
		$generatedwebfontfamily = $this->params->get('bodyGeneratedWebFont');

		switch($bodyfonttype) {
			case "0" : {
				$bootstrap_vars['JMbaseFontFamily'] = $bodyfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMbaseFontFamily'] = $bodygooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMbaseFontFamily'] = $generatedwebfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMbaseFontFamily'] = $this->defaults->get('bodyGoogleWebFontFamily');
				break;
			}
		}

		//top menu horizontal

		$djmenufontsize = (int)$this->params->get('JMtopmenuFontSize', $this->defaults->get('JMtopmenuFontSize'));
		$bootstrap_vars['JMtopmenuFontSize'] = $djmenufontsize.'px';

		$djmenufonttype = $this->params->get('djmenuFontType', '1');
		$djmenufontfamily = $this->params->get('djmenuFontFamily', $this->defaults->get('djmenuFontFamily'));
		$djmenugooglewebfontfamily = $this->params->get("djmenuGoogleWebFontFamily", $this->defaults->get('djmenuGoogleWebFontFamily'));
		$djmenugeneratedwebfontfamily = $this->params->get('djmenuGeneratedWebFont');

		switch($djmenufonttype) {
			case "0" : {
				$bootstrap_vars['JMtopmenuFontFamily'] = $djmenufontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMtopmenuFontFamily'] = $djmenugooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMtopmenuFontFamily'] = $djmenugeneratedwebfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMtopmenuFontFamily'] = $this->defaults->get('djmenuGoogleWebFontFamily');
				break;
			}
		}

		//module title

		$headingsfontsize = (int)$this->params->get('JMmoduleTitleFontSize', $this->defaults->get('JMmoduleTitleFontSize'));
		$bootstrap_vars['JMmoduleTitleFontSize'] = $headingsfontsize.'px';

		$headingsfonttype = $this->params->get('headingsFontType', '1');
		$headingsfontfamily = $this->params->get('headingsFontFamily', $this->defaults->get('headingsFontFamily'));
		$headingsgooglewebfontfamily = $this->params->get("headingsGoogleWebFontFamily", $this->defaults->get('headingsGoogleWebFontFamily'));
		$headingsgeneratedwebfontfamily = $this->params->get('headingsGeneratedWebFont');

		switch($headingsfonttype) {
			case "0" : {
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $headingsfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $headingsgooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $headingsgeneratedwebfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMmoduleTitleFontFamily'] = $this->defaults->get('headingsGoogleWebFontFamily');
				break;
			}
		}

		//blog title

		$blogfontsize = (int)$this->params->get('JMblogTitleFontSize', $this->defaults->get('JMblogTitleFontSize'));
		$bootstrap_vars['JMblogTitleFontSize'] = $blogfontsize.'px';

		$blogfonttype = $this->params->get('blogFontType', '1');
		$blogfontfamily = $this->params->get('blogFontFamily', $this->defaults->get('blogFontFamily'));
		$bloggooglewebfontfamily = $this->params->get("blogGoogleWebFontFamily", $this->defaults->get('blogGoogleWebFontFamily'));
		$bloggeneratedfontfamily = $this->params->get('blogGeneratedWebFont');

		switch($blogfonttype) {
			case "0" : {
				$bootstrap_vars['JMblogTitleFontFamily'] = $blogfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMblogTitleFontFamily'] = $bloggooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMblogTitleFontFamily'] = $bloggeneratedfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMblogTitleFontFamily'] = $this->defaults->get('JMblogTitleFontFamily');
				break;
			}
		}

		//article title

		$articlesfontsize = (int)$this->params->get('JMarticleTitleFontSize', $this->defaults->get('JMarticleTitleFontSize'));
		$bootstrap_vars['JMarticleTitleFontSize'] = $articlesfontsize.'px';

		$articlesfonttype = $this->params->get('articlesFontType', '1');
		$articlesfontfamily = $this->params->get('articlesFontFamily', $this->defaults->get('articlesFontFamily'));
		$articlesgooglewebfontfamily = $this->params->get("articlesGoogleWebFontFamily", $this->defaults->get('articlesGoogleWebFontFamily'));
		$articlesgeneratedfontfamily = $this->params->get('articlesGeneratedWebFont');

		switch($articlesfonttype) {
			case "0" : {
				$bootstrap_vars['JMarticleTitleFontFamily'] = $articlesfontfamily;
				break;
			}
			case "1" :{
				$bootstrap_vars['JMarticleTitleFontFamily'] = $articlesgooglewebfontfamily;
				break;
			}
			case "2" :{
				$bootstrap_vars['JMarticleTitleFontFamily'] = $articlesgeneratedfontfamily;
				break;
			}
			default: {
				$bootstrap_vars['JMarticleTitleFontFamily'] = $this->defaults->get('articlesGoogleWebFontFamily');
				break;
			}
		}

		/* Color Modifications */

		//scheme color
		$JMcolorVersion = $this->params->get('JMcolorVersion', $this->defaults->get('JMcolorVersion'));
		$bootstrap_vars['JMcolorVersion'] = $JMcolorVersion;

		//content for main color scheme
		$JMcolorVersionContent = $this->params->get('JMcolorVersionContent', $this->defaults->get('JMcolorVersionContent'));
		$bootstrap_vars['JMcolorVersionContent'] = $JMcolorVersionContent;

		//complementary color
		$JMcomplementaryColor = $this->params->get('JMcomplementaryColor', $this->defaults->get('JMcomplementaryColor'));
		$bootstrap_vars['JMcomplementaryColor'] = $JMcomplementaryColor;

		//scheme images directory
		$imagesdir = $this->params->get('JMimagesDir', 'scheme1');
		$bootstrap_vars['JMimagesDir'] = $imagesdir;

		// -------------------------------------
		// global
		// -------------------------------------

		//page background
		$JMpageBackground = $this->params->get('JMpageBackground', $this->defaults->get('JMpageBackground'));
		$bootstrap_vars['JMpageBackground'] = $JMpageBackground;

		//border color
		$JMbaseBorderColor = $this->params->get('JMbaseBorderColor', $this->defaults->get('JMbaseBorderColor'));
		$bootstrap_vars['JMbaseBorderColor'] = $JMbaseBorderColor;

		//base font color
		$JMbaseFontColor = $this->params->get('JMbaseFontColor', $this->defaults->get('JMbaseFontColor'));
		$bootstrap_vars['JMbaseFontColor'] = $JMbaseFontColor;

		//headings
		$JMarticleTitleColor = $this->params->get('JMarticleTitleColor', $this->defaults->get('JMarticleTitleColor'));
		$bootstrap_vars['JMarticleTitleColor'] = $JMarticleTitleColor;

		//module title
		$JMmoduleTitleColor = $this->params->get('JMmoduleTitleColor', $this->defaults->get('JMmoduleTitleColor'));
		$bootstrap_vars['JMmoduleTitleColor'] = $JMmoduleTitleColor;

		// -------------------------------------
		// header
		// -------------------------------------

		//background
		$JMheaderBackground = $this->params->get('JMheaderBackground', $this->defaults->get('JMheaderBackground'));
		$bootstrap_vars['JMheaderBackground'] = $JMheaderBackground;

		//font color
		$JMheaderFontColor = $this->params->get('JMheaderFontColor', $this->defaults->get('JMheaderFontColor'));
		$bootstrap_vars['JMheaderFontColor'] = $JMheaderFontColor;

		// -------------------------------------
		// top menu
		// -------------------------------------

		//font color
		$JMtopmenuFontColor = $this->params->get('JMtopmenuFontColor', $this->defaults->get('JMtopmenuFontColor'));
		$bootstrap_vars['JMtopmenuFontColor'] = $JMtopmenuFontColor;

		//font color active
		$JMtopmenuFontColorActive = $this->params->get('JMtopmenuFontColorActive', $this->defaults->get('JMtopmenuFontColorActive'));
		$bootstrap_vars['JMtopmenuFontColorActive'] = $JMtopmenuFontColorActive;

		//submenu background
		$JMtopmenuSubmenuBackground = $this->params->get('JMtopmenuSubmenuBackground', $this->defaults->get('JMtopmenuSubmenuBackground'));
		$bootstrap_vars['JMtopmenuSubmenuBackground'] = $JMtopmenuSubmenuBackground;

		//submenu font color
		$JMtopmenuSubmenuFontColor = $this->params->get('JMtopmenuSubmenuFontColor', $this->defaults->get('JMtopmenuSubmenuFontColor'));
		$bootstrap_vars['JMtopmenuSubmenuFontColor'] = $JMtopmenuSubmenuFontColor;

		// -------------------------------------
		// header-mod
		// -------------------------------------

		//background
		$JMheadermodBackground = $this->params->get('JMheadermodBackground', $this->defaults->get('JMheadermodBackground'));
		$bootstrap_vars['JMheadermodBackground'] = $JMheadermodBackground;

		//title font color
		$JMheadermodTitleFontColor = $this->params->get('JMheadermodTitleFontColor', $this->defaults->get('JMheadermodTitleFontColor'));
		$bootstrap_vars['JMheadermodTitleFontColor'] = $JMheadermodTitleFontColor;

		//font color
		$JMheadermodFontColor = $this->params->get('JMheadermodFontColor', $this->defaults->get('JMheadermodFontColor'));
		$bootstrap_vars['JMheadermodFontColor'] = $JMheadermodFontColor;

		//opacity
		$JMheadermodOpacity = $this->params->get('JMheadermodOpacity', $this->defaults->get('JMheadermodOpacity'));
		$bootstrap_vars['JMheadermodOpacity'] = $JMheadermodOpacity;

		// -------------------------------------
		// top1 & top3 & bottom1 & bottom3
		// -------------------------------------

		//background
		$JMtop1Background = $this->params->get('JMtop1Background', $this->defaults->get('JMtop1Background'));
		$bootstrap_vars['JMtop1Background'] = $JMtop1Background;

		//title font color
		$JMtop1TitleFontColor = $this->params->get('JMtop1TitleFontColor', $this->defaults->get('JMtop1TitleFontColor'));
		$bootstrap_vars['JMtop1TitleFontColor'] = $JMtop1TitleFontColor;

		//font color
		$JMtop1FontColor = $this->params->get('JMtop1FontColor', $this->defaults->get('JMtop1FontColor'));
		$bootstrap_vars['JMtop1FontColor'] = $JMtop1FontColor;

		// -------------------------------------
		// bottom2
		// -------------------------------------

		//background
		$JMbottom2Background = $this->params->get('JMbottom2Background', $this->defaults->get('JMbottom2Background'));
		$bootstrap_vars['JMbottom2Background'] = $JMbottom2Background;

		//title font color
		$JMbottom2TitleFontColor = $this->params->get('JMbottom2TitleFontColor', $this->defaults->get('JMbottom2TitleFontColor'));
		$bootstrap_vars['JMbottom2TitleFontColor'] = $JMbottom2TitleFontColor;

		//font color
		$JMbottom2FontColor = $this->params->get('JMbottom2FontColor', $this->defaults->get('JMbottom2FontColor'));
		$bootstrap_vars['JMbottom2FontColor'] = $JMbottom2FontColor;

		// -------------------------------------
		// footer-mod
		// -------------------------------------

		//background
		$JMfootermodBackground = $this->params->get('JMfootermodBackground', $this->defaults->get('JMfootermodBackground'));
		$bootstrap_vars['JMfootermodBackground'] = $JMfootermodBackground;

		//module title
		$JMfootermodModuleTitle = $this->params->get('JMfootermodModuleTitle', $this->defaults->get('JMfootermodModuleTitle'));
		$bootstrap_vars['JMfootermodModuleTitle'] = $JMfootermodModuleTitle;

		//font color
		$JMfootermodFontColor = $this->params->get('JMfootermodFontColor', $this->defaults->get('JMfootermodFontColor'));
		$bootstrap_vars['JMfootermodFontColor'] = $JMfootermodFontColor;

		// -------------------------------------
		// copyrights
		// -------------------------------------

		//background
		$JMcopyrightsBackground = $this->params->get('JMcopyrightsBackground', $this->defaults->get('JMcopyrightsBackground'));
		$bootstrap_vars['JMcopyrightsBackground'] = $JMcopyrightsBackground;

		//font color
		$JMcopyrightsFontColor = $this->params->get('JMcopyrightsFontColor', $this->defaults->get('JMcopyrightsFontColor'));
		$bootstrap_vars['JMcopyrightsFontColor'] = $JMcopyrightsFontColor;

		// -------------------------------------
		// modules
		// -------------------------------------

		$JMcolor1msBackground = $this->params->get('JMcolor1msBackground', $this->defaults->get('JMcolor1msBackground'));
		$bootstrap_vars['JMcolor1msBackground'] = $JMcolor1msBackground;

		$JMcolor1msTitleFontColor = $this->params->get('JMcolor1msTitleFontColor', $this->defaults->get('JMcolor1msTitleFontColor'));
		$bootstrap_vars['JMcolor1msTitleFontColor'] = $JMcolor1msTitleFontColor;

		$JMcolor2msBackground = $this->params->get('JMcolor2msBackground', $this->defaults->get('JMcolor2msBackground'));
		$bootstrap_vars['JMcolor2msBackground'] = $JMcolor2msBackground;

		$JMcolor2msTitleFontColor = $this->params->get('JMcolor2msTitleFontColor', $this->defaults->get('JMcolor2msTitleFontColor'));
		$bootstrap_vars['JMcolor2msTitleFontColor'] = $JMcolor2msTitleFontColor;

		$JMcolor2msFontColor = $this->params->get('JMcolor2msFontColor', $this->defaults->get('JMcolor2msFontColor'));
		$bootstrap_vars['JMcolor2msFontColor'] = $JMcolor2msFontColor;


		// -------------------------------------
		// offcanvas
		// -------------------------------------

		$offcanvasbackground = $this->params->get('JMoffCanvasBackground', $this->defaults->get('JMoffCanvasBackground'));
		$bootstrap_vars['JMoffCanvasBackground'] = $offcanvasbackground;

		$offcanvasborder = $this->params->get('JMoffCanvasBorder', $this->defaults->get('JMoffCanvasBorder'));
		$bootstrap_vars['JMoffCanvasBorder'] = $offcanvasborder;

		$offcanvasfontcolor = $this->params->get('JMoffCanvasFontColor', $this->defaults->get('JMoffCanvasFontColor'));
		$bootstrap_vars['JMoffCanvasFontColor'] = $offcanvasfontcolor;

		// -------------------------------------
		// extensions
		// -------------------------------------

		// DJ-MEDIATOOLS
		// -------------

		// text color
		$JMmediatoolsDescriptionFontColor = $this->params->get('JMmediatoolsDescriptionFontColor', $this->defaults->get('JMmediatoolsDescriptionFontColor'));
		$bootstrap_vars['JMmediatoolsDescriptionFontColor'] = $JMmediatoolsDescriptionFontColor;

		//description background
		$JMmediatoolsDescriptionBackground = $this->params->get('JMmediatoolsDescriptionBackground', $this->defaults->get('JMmediatoolsDescriptionBackground'));
		$bootstrap_vars['JMmediatoolsDescriptionBackground'] = $JMmediatoolsDescriptionBackground;

		//description background opacity
		$JMmediatoolsDescriptionBackgroundOpacity = $this->params->get('JMmediatoolsDescriptionBackgroundOpacity', $this->defaults->get('JMmediatoolsDescriptionBackgroundOpacity'));
		$bootstrap_vars['JMmediatoolsDescriptionBackgroundOpacity'] = $JMmediatoolsDescriptionBackgroundOpacity;

		// DJ-CLASSIFIEDS
		// -------------

		// box background
		$JMclassifiedsBoxBackground = $this->params->get('JMclassifiedsBoxBackground', $this->defaults->get('JMclassifiedsBoxBackground'));
		$bootstrap_vars['JMclassifiedsBoxBackground'] = $JMclassifiedsBoxBackground;

		// box title color
		$JMclassifiedsBoxTitleFontColor = $this->params->get('JMclassifiedsBoxTitleFontColor', $this->defaults->get('JMclassifiedsBoxTitleFontColor'));
		$bootstrap_vars['JMclassifiedsBoxTitleFontColor'] = $JMclassifiedsBoxTitleFontColor;

		// box border color
		$JMclassifiedsBoxBorder = $this->params->get('JMclassifiedsBoxBorder', $this->defaults->get('JMclassifiedsBoxBorder'));
		$bootstrap_vars['JMclassifiedsBoxBorder'] = $JMclassifiedsBoxBorder;

		// box text color
		$JMclassifiedsBoxFontColor = $this->params->get('JMclassifiedsBoxFontColor', $this->defaults->get('JMclassifiedsBoxFontColor'));
		$bootstrap_vars['JMclassifiedsBoxFontColor'] = $JMclassifiedsBoxFontColor;

		// search background
		$JMclassifiedsSearchBackground = $this->params->get('JMclassifiedsSearchBackground', $this->defaults->get('JMclassifiedsSearchBackground'));
		$bootstrap_vars['JMclassifiedsSearchBackground'] = $JMclassifiedsSearchBackground;

		// search button
		$JMclassifiedsSearchButton = $this->params->get('JMclassifiedsSearchButton', $this->defaults->get('JMclassifiedsSearchButton'));
		$bootstrap_vars['JMclassifiedsSearchButton'] = $JMclassifiedsSearchButton;


		// -------------------------------------
		// end
		// -------------------------------------

		$this->params->set('jm_bootstrap_variables', $bootstrap_vars);

		// -------------------------------------
		// compile LESS
		// -------------------------------------

		$app = JFactory::getApplication();

		// Offline Page
		$this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/offline.less'), true);

		// DJ-Classifieds
		$css_djclassifieds_theme = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djclassifieds.less'), true, true);
		$css_djclassifieds_theme_rtl = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djclassifieds_rtl.less'), true, true);
		$css_djclassifieds_responsive = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djclassifieds_responsive.less'), true, true);

		$less_djclassifieds_theme = 'templates/'.$app->getTemplate().'/less/djclassifieds.less';
		$less_djclassifieds_theme_rtl = 'templates/'.$app->getTemplate().'/less/djclassifieds_rtl.less';
		$less_djclassifieds_responsive = 'templates/'.$app->getTemplate().'/less/djclassifieds_responsive.less';

		// DJ-Megamenu
		$css_djmegamenu_theme = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djmegamenu.less'), true, true);
		$css_djmegamenu_theme_rtl = $this->CompileStyleSheet(JPath::clean(JMF_TPL_PATH.'/less/djmegamenu_rtl.less'), true, true);

		$less_djmegamenu_theme = 'templates/'.$app->getTemplate().'/less/djmegamenu.less';
		$less_djmegamenu_theme_rtl = 'templates/'.$app->getTemplate().'/less/djmegamenu_rtl.less';

		// -------------------------------------
		// extensions themes
		// -------------------------------------

		$themer = (int)$this->params->get('themermode', 0) == 1 ? true : false;
		if ($themer) { // add LESS files when Theme Customizer enabled
			$urlsToRemove = array(
				'templates/'.$app->getTemplate().'/css/djmegamenu.css' => array('url' => $less_djmegamenu_theme, 'type' => 'less'),
				'templates/'.$app->getTemplate().'/css/djmegamenu_rtl.css' => array('url' => $less_djmegamenu_theme_rtl, 'type' => 'less'),
				'components/com_djclassifieds/themes/'.$classifieds_theme.'/css/style.css' => array('url' => $less_djclassifieds_theme, 'type' => 'less'),
				'components/com_djclassifieds/themes/'.$classifieds_theme.'/css/style_rtl.css' => array('url' => $less_djclassifieds_theme_rtl, 'type' => 'less'),
				'components/com_djclassifieds/themes/'.$classifieds_theme.'/css/responsive.css' => array('url' => $less_djclassifieds_responsive, 'type' => 'less')
			);
			$app->set('jm_remove_stylesheets', $urlsToRemove);
		} else { // add CSS files when Theme Customizer disabled
			$urlsToRemove = array(
				'templates/'.$app->getTemplate().'/css/djmegamenu.css' => array('url' => $css_djmegamenu_theme, 'type' => 'css'),
				'templates/'.$app->getTemplate().'/css/djmegamenu_rtl.css' => array('url' => $css_djmegamenu_theme_rtl, 'type' => 'css'),
				'components/com_djclassifieds/themes/'.$classifieds_theme.'/css/style.css' => array('url' => $css_djclassifieds_theme, 'type' => 'css'),
				'components/com_djclassifieds/themes/'.$classifieds_theme.'/css/style_rtl.css' => array('url' => $css_djclassifieds_theme_rtl, 'type' => 'css'),
				'components/com_djclassifieds/themes/'.$classifieds_theme.'/css/responsive.css' => array('url' => $css_djclassifieds_responsive, 'type' => 'css')
			);
			$app->set('jm_remove_stylesheets', $urlsToRemove);
		}
	}
}
