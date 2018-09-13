<?php
/**
 * @version $Id: image.php 111 2017-11-08 18:19:13Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

abstract class DJImageResizer {

	private static $resized = 0;
	
	public static $widths = array(980, 768, 480, 320, 240, 160, 120, 80); // widths used for srcset attribute, array must be descending 
	
	public static function createThumbnail($image_path, $folder, $width = 0, $height = 0, $mode = 'crop', $quality = 90, $srcset = false, $watermark = false) {
		
		// image resizing is disabled
		if($mode == 'no') return false;
		
		// check if any dimensions was passed
		if ($width == 0 && $height == 0)
			return false;
		
		// don't procced if mode is not set
		if(!in_array($mode,array('crop','toWidth','toHeight'))) return false;
		
		// set name for image thumbnail
		$filename = JFile::getName($image_path);
		$thumb_name = $width . 'x' . $height . '-' . $mode . '-' . $quality . '-' . str_replace(' ', '_', $filename);
		
		// set folder for image thumbnail
		$folder = rtrim(str_replace(array(' ', 'images/djmediatools/', 'https://', 'http://'),  array('_', ''), $folder . '/' . str_replace(array($filename, 'media/djmediatools/cache/'), '', $image_path)), '/');
		
		// set path for image thumbnail
		$path = JPATH_SITE . DS . str_replace('/', DS, $folder);
		// check if the destination folder exists or create it
		if (!JFile::exists($path) || !is_dir($path)) {
			if (!JFolder::create($path))
				return false;
		}
		
		// make image name safe
		$thumb_name = JFile::makeSafe($thumb_name);
		$lang = JFactory::getLanguage();
		$thumb_name = $lang->transliterate($thumb_name);
		//$thumb_name = strtolower($thumb_name);
		
		// if thumb is older than image delete the thumbnail to recreate it
		if(JFile::exists($path . DS . $thumb_name) && strpos($image_path, 'http') !== 0) {
			if(filemtime($path . DS . $thumb_name) < filemtime(JPATH_SITE . DS . str_replace('/', DS, $image_path))) {
				JFile::delete($path . DS . $thumb_name);
			}
		}
		
		$success = true;
		
		// if thumb exists just return the path
		if (!JFile::exists($path . DS . $thumb_name)) {
			
			// Remove php's time limit
			$timeRemoved = false;
			if(function_exists('ini_get') && function_exists('set_time_limit')) {
				if(!ini_get('safe_mode') ) {
					if(@set_time_limit(0)!==FALSE) $timeRemoved = true;
				}
			}
			// Increase php's memory limit
			if(function_exists('ini_set')) {
				@ini_set('memory_limit', '256M');				
			}
			
			// check if passed image exists
			if(strcasecmp(substr($image_path, 0, 4), 'http') === 0) { 
				$image_path = str_replace(' ', '%20', $image_path);
			}
			else if (JFile::exists(JPATH_SITE . DS . str_replace('/', DS, $image_path))) {
				$image_path = JPATH_SITE . DS . str_replace('/', DS, $image_path);
			} else {
				return false;
			}
			
			$app = JFactory::getApplication();
			$config = JFactory::getConfig();
			
			if(!$timeRemoved && ++self::$resized > 50) {
				if($config->get('config.debug')) {
					$app->enqueueMessage('DJ-MEDIATOOLS DEBUG::Redirect after '.(self::$resized-1).' images resized');
				}
				$uri = JFactory::getURI();
				$current = JRoute::_($uri->toString(), false);
				
				$app->redirect($current);
				$app->close();
			}
			
			if($config->get('config.debug')) {
				$app->enqueueMessage('DJ-MEDIATOOLS DEBUG::Creating resized image: '.$thumb_name);
			}
			
			switch($mode) {
				case 'toWidth' :
					$success = self::resizeImage($image_path, $path . DS . $thumb_name, $width, 0, $quality, true, $watermark);
					break;
				case 'toHeight' :
					$success = self::resizeImage($image_path, $path . DS . $thumb_name, 0, $height, $quality, true, $watermark);
					break;
				case 'crop' :
				default :
					$success = self::resizeImage($image_path, $path . DS . $thumb_name, $width, $height, $quality, true, $watermark);
					break;
			}
			
			/**
			 *  if succeed then create different size images for srcset attribute
			 *  This is used for better pagespeed loading and user experience
			 */
			if($srcset) {
				$source = ''; // use the smallest possible image as a source for better memory use 
				foreach(self::$widths as $w) {
					$source = self::resizeImage($path . DS . $source . $thumb_name, $path . DS . '_'.$w.'w' . DS . $thumb_name, $w, 0, $quality, false) ? '_'.$w.'w' . DS : '';
				}
			}
		}

		return $success ? $folder . '/' . $thumb_name : false;
	}
	
	/* grayscale function based on Angela Bradley article http://php.about.com/od/gdlibrary/ss/grayscale_gd.htm */
	public static function grayscaleImage($image_path, $folder){
		
		// check if image exists
		if (!JFile::exists(JPATH_SITE . DS . $image_path)) {
			return false;
		}
		
		// set name for image thumbnail
		$filename = JFile::getName($image_path);
		$thumb_name = 'grayscale-' . $filename;
		
		// remove folder from image path
		if(strpos($image_path, $folder) === 0) $folder = '';
		
		// set folder for image thumbnail
		$folder = rtrim(str_replace(array(' ', $filename),  array('_', ''), (!empty($folder) ? $folder . '/' : '') . $image_path), '/');
		
		// set path for image thumbnail
		$path = JPATH_SITE . DS . str_replace('/', DS, $folder);
		// check if the destination folder exists or create it
		if (!JFile::exists($path) || !is_dir($path)) {
			if (!JFolder::create($path))
				return false;
		}
		// make image name safe
		$lang = JFactory::getLanguage();
		$thumb_name = $lang->transliterate($thumb_name);
		//$thumb_name = strtolower($thumb_name);
		$thumb_name = JFile::makeSafe($thumb_name);
		
		// if thumb is older than image delete the thumbnail to recreate it
		if(JFile::exists($path . DS . $thumb_name)) {
			if(filemtime($path . DS . $thumb_name) < filemtime(JPATH_SITE . DS . str_replace('/', DS, $image_path))) {
				JFile::delete($path . DS . $thumb_name);
			}
		}
		
		$success = true;
		
		// if thumb exists just return the path
		if (!JFile::exists($path . DS . $thumb_name)) {
			
			// Remove php's time limit
			$timeRemoved = false;
			if(function_exists('ini_get') && function_exists('set_time_limit')) {
				if(!ini_get('safe_mode') ) {
					if(@set_time_limit(0)!==FALSE) $timeRemoved = true;
				}
			}
			// Increase php's memory limit
			if(function_exists('ini_set')) {
				@ini_set('memory_limit', '256M');				
			}
			
			// check if passed image exists
			if(strcasecmp(substr($image_path, 0, 4), 'http') === 0) { 
				$image_path = str_replace(' ', '%20', $image_path);
			}
			else if (JFile::exists(JPATH_SITE . DS . str_replace('/', DS, $image_path))) {
				$image_path = JPATH_SITE . DS . str_replace('/', DS, $image_path);
			} else {
				return false;
			}
			
			$app = JFactory::getApplication();
			$config = JFactory::getConfig();
			
			if(!$timeRemoved && ++self::$resized > 50) {
				if($config->get('config.debug')) {
					$app->enqueueMessage('DJ-MEDIATOOLS DEBUG::Redirect after '.(self::$resized-1).' images grayscaled');
				}
				$uri = JFactory::getURI();
				$current = JRoute::_($uri->toString(), false);
				
				$app->redirect($current);
				$app->close();
			}
			
			if($config->get('config.debug')) {
				$app->enqueueMessage('DJ-MEDIATOOLS DEBUG::Creating grayscaled image: '.$thumb_name);
			}
			
			if (!list($width, $height, $type, $attr) = getimagesize($image_path)) {
				return false;
			}
			
			$source = null;
			
			switch($type) {
				case 1 :
					$source = imagecreatefromgif($image_path);
					break;
				case 2 :
					$source = imagecreatefromjpeg($image_path);
					break;
				case 3 :
					$source = imagecreatefrompng($image_path);
					break;
				default :
					return false;
					break;
			}
			
			// Creating the Canvas for grayscale copy
			$bwimage = ImageCreateTrueColor($width, $height);
			
			//Creates the 256 color palette
			for ($c=0;$c<256;$c++) {
				$palette[$c] = imagecolorallocate($bwimage,$c,$c,$c);
			}
			
			//Reads the origonal colors pixel by pixel
			for ($y=0; $y<$height; $y++) {
				for ($x=0; $x<$width; $x++) {
					$rgb = imagecolorat($source,$x,$y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
			
					//This is where we actually use yiq to modify our rbg values, and then convert them to our grayscale palette
					$gs = self::yiq($r,$g,$b);
					imagesetpixel($bwimage,$x,$y,$palette[$gs]);
				}
			}
			
			// Outputs an grayscale image
			if (is_file($path . DS . $thumb_name)) unlink($path . DS . $thumb_name);
			
			imageinterlace($bwimage, 1); // progressive jpeg
			
			switch($type) {
				case 1 :
					$success = imagegif($bwimage, $path . DS . $thumb_name);
					break;
				case 2 :
					$success = imagejpeg($bwimage, $path . DS . $thumb_name);
					break;
				case 3 :
					$success = imagepng($bwimage, $path . DS . $thumb_name);
					break;
			}
			
			ImageDestroy($bwimage);
			ImageDestroy($source);
			
			if($success) {
				$db = JFactory::getDbo();
				$db->setQuery("DELETE FROM #__djmt_resmushit WHERE path=".$db->quote(str_replace(JPATH_ROOT, '', $path . DS . $thumb_name)));
				$db->query();
			}
		}
		
		return $success ? $folder . '/' . $thumb_name : false;
	}
	
	// The YIG formulas for better calculation of gray shades http://en.wikipedia.org/wiki/YIQ
	private static function yiq($r,$g,$b) {
		return (($r*0.299)+($g*0.587)+($b*0.114));
	}
		
	private static function resizeImage($path, $newpath, $nw = 0, $nh = 0, $quality = 90, $enlarge = true, $watermark = false) {
	
		$params = JComponentHelper::getParams( 'com_djmediatools' );
		
		if (!$path || !$newpath)
			return false;
		
		$newfolderpath = str_replace(JFile::getName($newpath), '', $newpath);
		// check if the destination folder exists or create it
		if (!JFile::exists($newfolderpath) || !is_dir($newfolderpath)) {
			if (!JFolder::create($newfolderpath))
				return false;
		}
		
		$size = @getimagesize($path);
		
		if ($size === FALSE) {
			return false;
		}

		list($w, $h, $type) = $size;
		
		$OldImage = null;

		switch($type) {
			case 1 :
				$OldImage = imagecreatefromgif($path);
				break;
			case 2 :
				$OldImage = imagecreatefromjpeg($path);
				break;
			case 3 :
				$OldImage = imagecreatefrompng($path);
				break;
			default :
				return false;
				break;
		}

		if ($nw == 0 && $nh == 0) {
			$nw = 75;
			$nh = (int)(floor(($nw * $h) / $w));
		} elseif ($nw == 0) {
			$nw = (int)(floor(($nh * $w) / $h));
		} elseif ($nh == 0) {
			$nh = (int)(floor(($nw * $h) / $w));
		}
		
		// don't create larger image
		if(!$enlarge && ($nw > $w || $nh > $h)) {
			ImageDestroy($OldImage);
			return false;
		}
		
		// check if ratios match
		$_ratio = array($w / $h, $nw / $nh);
		if ($_ratio[0] != $_ratio[1]) {// crop image

			// find the right scale to use
			$_scale = min((float)($w / $nw), (float)($h / $nh));

			// coords to crop
			$cropX = (float)($w - ($_scale * $nw));
			$cropY = (float)($h - ($_scale * $nh));

			// cropped image size
			$cropW = (float)($w - $cropX);
			$cropH = (float)($h - $cropY);

			$crop = ImageCreateTrueColor($cropW, $cropH);
			if ($type == 3) {
				imagecolortransparent($crop, imagecolorallocate($crop, 0, 0, 0));
				imagealphablending($crop, false);
				imagesavealpha($crop, true);
			}
			ImageCopy($crop, $OldImage, 0, 0, (int)($cropX / 2), (int)($cropY / 2), $cropW, $cropH);
		}
		
		// do the thumbnail
		$NewThumb = ImageCreateTrueColor($nw, $nh);
		if ($type == 3) {
			imagecolortransparent($NewThumb, imagecolorallocate($NewThumb, 0, 0, 0));
			imagealphablending($NewThumb, false);
			imagesavealpha($NewThumb, true);
		}
		if (isset($crop)) {// been cropped
			ImageCopyResampled($NewThumb, $crop, 0, 0, 0, 0, $nw, $nh, $cropW, $cropH);
			ImageDestroy($crop);
		} else {// ratio match, regular resize
			ImageCopyResampled($NewThumb, $OldImage, 0, 0, 0, 0, $nw, $nh, $w, $h);
		}
		
		$watermarkImg = $watermark ? $params->get('watermark_file', false) : false;
		$watermarkPath = JPath::clean(JPATH_ROOT.'/'.$watermarkImg);
		
		if($watermarkImg && JFile::exists($watermarkPath)){
			if (list ($w_w, $w_h, $w_type, $w_attr) = getimagesize($watermarkPath)) {
				$w_size = $params->get('watermark_size', '20');
					
				$nw_w = round($nw * $w_size/100);
				$nw_ratio = $nw_w / $w_w;
				$nw_h= round($w_h * $nw_ratio);
		
				if($nw_w > $w_w || $nw_h > $w_h ){
					$nw_w = $w_w;
					$nw_h = $w_h;
				}
		
				imagealphablending($NewThumb, true);
				imagesavealpha($NewThumb, true);
		
				$OldWatermark = imagecreatefrompng($watermarkPath);
				//imagealphablending($OldWatermark, true);
				//imagesavealpha($OldWatermark, true);
		
				$NewWatermark=ImageCreateTrueColor($nw_w,$nw_h);
				$bg = imagecolortransparent($NewWatermark, imagecolorallocatealpha($NewWatermark, 0, 0, 0,127));
				imagealphablending($NewWatermark, true);
				imagefill($NewWatermark, 0, 0, $bg);
				imagesavealpha($NewWatermark, true);
		
				ImageCopyResampled($NewWatermark,$OldWatermark,0,0,0,0,$nw_w,$nw_h,$w_w,$w_h);
		
				$im = $NewThumb;
		
				// Set the margins for the stamp and get the height/width of the stamp image
				$margin_v = 10;
				$margin_h = 10;
				$sx = imagesx($NewWatermark);
				$sy = imagesy($NewWatermark);
		
				if ($params->get('watermark_alignment_h', 'l') == 'r'){
					$pos_l = $nw - $nw_w - $margin_v;
				} else if ($params->get('watermark_alignment_h', 'l') == 'm'){
					$pos_l = round($nw/2) - round($nw_w/2);
				} else {//left
					$pos_l = $margin_v;
				}
		
				if($params->get('watermark_alignment_v', 'b') == 't'){
					$pos_t = $margin_h;
				} else if ($params->get('watermark_alignment_v', 'b') == 'm'){
					$pos_t = round($nh/2) - round($nw_h/2);
				} else {//bottom
					$pos_t = $nh - $nw_h - $margin_h;
				}
		
				//$pos_l = $nw - $nw_w - $marge_right;
				//$pos_t = $nh - $nw_h - $marge_bottom;
		
				ImageCopy($NewThumb, $NewWatermark, $pos_l , $pos_t , 0, 0, $nw_w, $nw_h);
		
				//header('Content-Type: image/png');imagepng($NewThumb);die();
		
				ImageDestroy($OldWatermark);
				ImageDestroy($NewWatermark);
			}
		}
		
		if (is_file($newpath)) unlink($newpath);
		
		imageinterlace($NewThumb, 1); // progressive jpeg
		
		$saved = false;
		
		switch($type) {
			case 1 :
				$saved = @imagegif($NewThumb, $newpath);
				break;
			case 2 :
				$saved = @imagejpeg($NewThumb, $newpath, $quality);
				break;
			case 3 :
				$saved = @imagepng($NewThumb, $newpath);
				break;
		}

		ImageDestroy($NewThumb);
		ImageDestroy($OldImage);
		
		if($saved) {
			$db = JFactory::getDbo();
			$db->setQuery("DELETE FROM #__djmt_resmushit WHERE path=".$db->quote(str_replace(JPATH_ROOT, '', $newpath)));
			$db->query();
		}
		
		return $saved;
	}

	private static function debug($array, $type = 'message'){
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($array,true)."</pre>", $type);
	
	}
}
?>
