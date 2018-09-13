<?php 
/**
 * @version $Id$
 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2017 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MegaMenu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MegaMenu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MegaMenu. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

#dj-suggester {
	position: fixed;
	bottom: 15px;
	right: 15px;
	z-index: 999;
	width: 400px;
}
#dj-suggester-in {
	position: relative;
	overflow: hidden;
	background: <?php echo $params->get('sgbg'); ?>;
	color: <?php echo $params->get('sgcolor'); ?>;
	border-top: 5px solid <?php echo $params->get('sgborder'); ?>;
	padding: 5px 25px 5px 15px;
	-webkit-box-shadow:  0px 0px 15px 0px rgba(0, 0, 0, 0.25);
	box-shadow:  0px 0px 15px 0px rgba(0, 0, 0, 0.25);
}
#dj-suggester-in .dj-close {
	position: absolute;
	top: 5px;
	right: 5px;
	display: block;
	font-size: 0.6em;
	width: 20px;
	height: 20px;
	line-height: 20px;
	text-align: center;
	background: <?php echo $params->get('btnbg'); ?>;
	color: <?php echo $params->get('btncolor'); ?>;
	cursor: pointer;
}
#dj-suggester-in .dj-close:hover {
	background: <?php echo $params->get('btnbghover'); ?>;
	color: <?php echo $params->get('btncolorhover'); ?>;
}
#dj-suggester-in .dj-close:before {
    font-family: 'IcoMoon';
    font-style: normal;
    speak: none;
    content: "\49";
}

.dj-suggester {
	padding: 5px 0;
	text-align: left;
	font-size: 12px;
	line-height: 1.5;
}
.dj-suggester-head {
	font-size: 11px;
	line-height: 20px;
	font-weight: bold;
	color: <?php echo $params->get('headcolor'); ?>;
	text-transform: uppercase;
	margin: 0 0 10px;
}
.dj-suggester-title {
	font-size: 16px;
	line-height: 1.1;
	font-weight: bold;
	margin: 0 0 10px;
}
.dj-suggester-title a:link, .dj-suggester-title a:visited {
	color: <?php echo $params->get('titlecolor'); ?>;
	text-decoration: none;
}
.dj-suggester-title a:hover, .dj-suggester-title a:active, .dj-suggester-title a:focus {
	color: <?php echo $params->get('titlecolorhover'); ?>;
	text-decoration: underline;
	background: none;
}
.dj-suggester-content {	
}
.dj-suggester-image {
	float: left;
	border: 2px solid color: <?php echo $params->get('imgborder'); ?>;
	margin: 2px 20px 5px 0;
}
.dj-suggester img {
	max-width: 100%;
}

<?php if($direction=='rtl') { ?>

<?php } ?>