<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Component
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
* 
* 
* DJ Classifieds is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ Classifieds is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ Classifieds. If not, see <http://www.gnu.org/licenses/>.
* 
*/

defined ('_JEXEC') or die('Restricted access');
$par = JComponentHelper::getParams( 'com_djclassifieds' );
$app = JFactory::getApplication();
$config = JFactory::getConfig();
$item = $this->item;

	if((int)$par->get('show_video','0') && $item->video){		
			$video_host = '';		
			$video = '';		
			$video_parts = explode('/',$item->video);	
			//print_r($video_parts);die();
			if(isset($video_parts[2])) {
				if($video_parts[2]=='www.youtube.com' || $video_parts[2]=='youtube.com'){														
					$video_host = 'http://www.youtube.com/embed/';
					if($video_parts[3]=='embed' && isset($video_parts[4])){
						$video=$video_parts[4];
					}else{
						$video = array_pop($video_parts);
						preg_match('/v=([\w\d\-]+)/', $video, $video);
						$video = $video[1].'?rel=0';	
					}										
				}else if($video_parts[2]=='youtu.be' && isset($video_parts[3])){
					$video_host = 'http://www.youtube.com/embed/';
					$video = $video_parts[3];
				}else if($video_parts[2]=='vimeo.com'){	
					$video_host = 'http://player.vimeo.com/video/';
					$video = array_pop($video_parts).'?portrait=0&color=333';
				}else if($video_parts[2]=='www.aparat.com'){
					$video_html = '<div id="djvideo_apart"><script type="text/JavaScript" src="https://www.aparat.com/embed/'.$video_parts[4].'?data[rnddiv]=djvideo_apart&data[responsive]=yes"></script></div>';
				}else if($video_parts[2]=='v.qq.com'){
					$video_host = "http://v.qq.com/iframe/player.html";
					$video_code = explode('.',$video_parts[5]);
					$video = "?vid=".$video_code[0]."&tiny=0&auto=0";
				}
			}			

			if($video_host || $video_html){
				if($config->get('force_ssl',0)==2){
					$video_host = str_ireplace('http://','https://',$video_host);
				}
				?>
			<div class="video_box"><h2><?php echo JText::_('COM_DJCLASSIFIEDS_VIDEO'); ?></h2>
				<div class="row">
					<div class="row_value" >
						<div class="videoWrapper"><div class="videoWrapper-in">	
						<?php if($video_html){
							echo $video_html;
						}else{ ?>					
							<iframe width="560" height="315" src="<?php echo $video_host.$video;?>" allowfullscreen></iframe>
						<?php }?>
						</div></div>				
					</div>
				</div>
			</div>
			<?php }
		}
		?>	