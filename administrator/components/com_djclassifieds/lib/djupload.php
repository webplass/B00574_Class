<?php
/**
 * @version $Id: upload.php 24 2013-12-18 09:34:54Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
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

abstract class DJUploadHelper {
	
	// default uploader settings
	private static $settings = array(
		'max_file_size' => '10mb',
		'chunk_size' => '1mb',
		'resize' => true, // resize image before upload - for html5 runtime image resizing is only possible on Firefox 3.5+ (with fixed quality) and Chrome
		'width' => 1600, // max resize image width
		'height' => 1200, //max resize image height
		'quality' => 90, // resize quality
		'filter' => 'jpg,gif,png,jpeg', // Filter to apply when the user selects files. This is currently file extension filter
		'debug' => false,
		'url' => null,
		// events	
		'onUploadedEvent' => null,
		'onAddedEvent' => null
	);
	
	public static function getUploader($id = 'uploader', $settings = array()) {
		
		$settings = array_merge(self::$settings, $settings);
		$debug = $settings['debug'];
		$config = JFactory::getConfig();
		
		$doc = JFactory::getDocument();
		$doc->addStyleSheet('//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
		$doc->addStyleSheet(JURI::root(true).'/components/com_djclassifieds/assets/upload/jquery.ui.plupload/css/jquery.ui.plupload.css');
		JHTML::_('behavior.framework', true);
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$doc->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
			$doc->addScript(JURI::root(true).'/components/com_djclassifieds/assets/upload/jquery.noconflict.js');
		} else {
			JHtml::_('bootstrap.framework');
		}
		$doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
		//$doc->addScript('//bp.yahooapis.com/2.4.21/browserplus-min.js');
		$doc->addScript(JURI::root(true).'/components/com_djclassifieds/assets/upload/plupload.full.js');
		$doc->addScript(JURI::root(true).'/components/com_djclassifieds/assets/upload/jquery.ui.plupload/jquery.ui.plupload.js');
		
		$component = JRequest::getCmd('option');
		$url = $settings['url'];
		if(!$url) $url = JURI::base(true).'/index.php?option='.$component.'&task=upload&tmpl=component';
		
		$js = "			
			jQuery(function(){
				
				plupload.addI18n({
					'Select files' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_HEADER'))."',
					'Add files to the upload queue and click the start button.' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_DESC'))."',
					'Filename' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_FILENAME'))."',
					'Status' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_STATUS'))."',
					'Size' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_SIZE'))."',
					'Add Files' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_ADD_FILES'))."',
					'Stop current upload' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_STOP_CURRENT_UPLOAD'))."',
					'Start uploading queue' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_START_UPLOADING_QUEUE'))."',
					'Uploaded %d/%d files': '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_UPLOADED_N_FILES'))."',
					'N/A' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_NA'))."',
					'Drag files here.' : '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_DRAG_AND_DROP_TEXT'))."',
					'Stop Upload': '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_STOP_UPLOAD'))."',
					'Start Upload': '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_START_UPLOAD'))."',
					'%d files queued': '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_N_FILES_QUEUED'))."',
					'File extension error.': '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_EXT_ERROR'))."',
					'File size error.': '".addslashes(JText::_('COM_DJCLASSIFIEDS_UPLOADER_SIZE_ERROR'))."'
				});
		";
			if($debug) $js .= "
				function eventlog() {
					var str = '';			
					plupload.each(arguments, function(arg) {
						var row = '';			
						if (typeof(arg) != 'string') {
							plupload.each(arg, function(value, key) {
								// Convert items in File objects to human readable form
								if (arg instanceof plupload.File) {
									// Convert status to human readable
									switch (value) {
										case plupload.QUEUED:
											value = 'QUEUED';
											break;			
										case plupload.UPLOADING:
											value = 'UPLOADING';
											break;			
										case plupload.FAILED:
											value = 'FAILED';
											break;			
										case plupload.DONE:
											value = 'DONE';
											break;
									}
								}			
								if (typeof(value) != 'function') {
									row += (row ? ', ': '') + key + '=' + value;
								}
							});			
							str += row + ' ';
						} else { 
							str += arg + ' ';
						}
					});			
					console.log(str);
				}
				
			";
			$js .= "
				var uploader$id = jQuery('#$id').plupload({
					// General settings
					runtimes : 'gears,browserplus,html5,silverlight,flash,html4',
					url : '$url',
					max_file_size : '".$settings['max_file_size']."',
					chunk_size : '".$settings['chunk_size']."',
					unique_names : true,";
			if($settings['resize']) $js .= "
					// Resize images on clientside if we can
					resize : {width : ".$settings['width'].", height : ".$settings['height'].", quality : ".$settings['quality']."},";
			$js .= "
					// Specify what files to browse for
					filters : [
						{title : 'Allowed files', extensions : '".$settings['filter']."'}
					],

					// Flash settings
					flash_swf_url : '".JURI::root(true)."/components/$component/assets/upload/plupload.flash.swf',

					// Silverlight settings
					silverlight_xap_url : '".JURI::root(true)."/components/$component/assets/upload/plupload.silverlight.xap',";
			
			if($debug) $js .= "
					// PreInit events, bound before any internal events
					preinit: {
						Init: function(up, info) {
							".($debug ? "eventlog('[Init]', 'Info:', info, 'Features:', up.features);":"")."
						},
						UploadFile: function(up, file) {
							".($debug ? "eventlog('[UploadFile]', file);":"")."
			
							// You can override settings before the file is uploaded
							// up.settings.url = 'upload.php?id=' + file.id;
							// up.settings.multipart_params = {param1: 'value1', param2: 'value2'};
						}
					},";
			$js .= "
					// Post init events, bound after the internal events
					init: {
						BeforeUpload: function(up, file) {
					        up.settings.multipart_params = {
					            filename: file.name
					        };
					    },
					
						FilesAdded: function(up, files) {
							// Called when files are added to queue
							".($debug ? "eventlog('[FilesAdded]');":"")."
							".($settings['onAddedEvent'] ? $settings['onAddedEvent'].'(up,files);':'');
			if($debug) $js .= "
							plupload.each(files, function(file) {
								eventlog('  File:', file);
							});";
			$js .= "
						},
						FileUploaded: function(up, file, info) {
							// Called when a file has finished uploading
							".($debug ? "eventlog('[FileUploaded] File:', file, 'Info:', info);":"")."
							".($settings['onUploadedEvent'] ? 'return '.$settings['onUploadedEvent'].'(up,file,info,"'.JURI::root().'","'.@$settings['label_generate'].'");':'')."
						}";
			if($debug) $js .= ",				
						Refresh: function(up) {
							// Called when upload shim is moved
							".($debug ? "eventlog('[Refresh]');":"")."
						},
						StateChanged: function(up) {
							// Called when the state of the queue is changed
							".($debug ? "eventlog('[StateChanged]', up.state == plupload.STARTED ? 'STARTED': 'STOPPED');":"")."
						},
						QueueChanged: function(up) {
							// Called when the files in queue are changed by adding/removing files
							".($debug ? "eventlog('[QueueChanged]');":"")."
						},
						UploadProgress: function(up, file) {
							// Called while a file is being uploaded
							".($debug ? "eventlog('[UploadProgress]', 'File:', file, 'Total:', up.total);":"")."
						},
						FilesRemoved: function(up, files) {
							// Called when files where removed from queue
							".($debug ? "eventlog('[FilesRemoved]');":"")."
			
							plupload.each(files, function(file) {
								".($debug ? "eventlog('  File:', file);":"")."
							});
						},						
						ChunkUploaded: function(up, file, info) {
							// Called when a file chunk has finished uploading
							".($debug ? "eventlog('[ChunkUploaded] File:', file, 'Info:', info);":"")."
						},
						Error: function(up, args) {
							// Called when a error has occured
			
							// Handle file specific error and general error
							if (args.file) {
								".($debug ? "eventlog('[error]', args, 'File:', args.file);":"")."
							} else {
								".($debug ? "eventlog('[error]', args);":"")."
							}
						}";
			$js .= "
					}
				});
			});
		";
		
		$doc->addScriptDeclaration($js);
		
		$html = '<div id="'.$id.'"><p>Unfortunately images multiupload was unable to start, there are probably some script errors on site. Please check this article: <a href="https://dj-extensions.com/faq/dj-classifieds-faq/images-multiupload-doesn-t-work-here-s-the-fix">IMAGES MULTIUPLOAD DOESN\'T WORK? - HERE\'S THE FIX</a>.</p></div>';
		
		return $html;
	}
	
	public static function upload() {
		
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		JPluginHelper::importPlugin('djclassifieds');
		
		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		// Settings
		$targetDir = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'djupload';
		//$targetDir = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'djclassifieds' . DIRECTORY_SEPARATOR . 'tmp';
		//$targetDir = 'uploads';
		
		$cleanupTargetDir = true; // Remove old files 
		$maxFileAge = 12 * 3600; // Temp file age in seconds
		
		// 5 minutes execution time
		@set_time_limit(5 * 60);
		
		// Uncomment this one to fake upload time
		// usleep(5000);
		
		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		$fileNameOrg = isset($_REQUEST["filename"]) ? $_REQUEST["filename"] : '';
		
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
		
			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;
		
			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}
		
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		
		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);
		
		// Remove old temp files
		if ($cleanupTargetDir) {
			if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
				while (($file = readdir($dir)) !== false) {
					$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
		
					// Remove temp file if it is older than the max age and is not the current file
					if (filemtime($tmpfilePath) < time() - $maxFileAge && $tmpfilePath != "{$filePath}.part") {
						@unlink($tmpfilePath);
					}
				}
				closedir($dir);
			} else {
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}
		}
		
		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];				
			
			
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = @fopen($_FILES['file']['tmp_name'], "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						jexit('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					@fclose($in);
					@fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					jexit('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = @fopen("php://input", "rb");
		
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					jexit('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		
				@fclose($in);
				@fclose($out);
			} else
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);									
			
			$arr = explode(".", $filePath);
			$ext = strtolower(end($arr));
			
			$blacklist = array('php','php3','php4', 'php5', 'php6', 'phtml', 'pht', 'shtml','asa','cer','asax','swf','xap','sh','js');
			
			$plugin_files = JPluginHelper::getPlugin('djclassifieds', 'files');
			if ($plugin_files)
			{
				$pluginParams = new JRegistry($plugin_files->params);
				$whitelist = explode(',', $pluginParams->get('allowed_attachment_types', 'jpg,jpeg,png,bmp,gif,pdf,tif,tiff,txt,csv,doc,docx,xls,xlsx,xlt,pps,ppt,pptx,ods,odp,odt,rar,zip,tar,bz2,gz2,7z'));
			}else{
				$whitelist = explode(',', 'jpg,jpeg,png,bmp,gif,pdf,tif,tiff,txt,csv,doc,docx,xls,xlsx,xlt,pps,ppt,pptx,ods,odp,odt,rar,zip,tar,bz2,gz2,7z');
			}
			
			foreach($whitelist as $key => $extension) {
				if (!in_array(trim($extension), $blacklist)) {
					$whitelist[$key] = strtolower(trim($extension));
				}
			}
			
			if (!in_array($ext, $whitelist) || preg_match('/\.(php|shtml|pht|asp)/i', $filePath)) {
				@unlink($filePath);
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Wrong file format."}, "id" : "id"}');
			}
			
			//$org_name = $_FILES['file']['name'];
			$org_name = $fileNameOrg;
			$arr_org = explode(".", $org_name);
			$ext_org = strtolower(end($arr_org));
			
			if (!in_array($ext_org, $whitelist) || preg_match('/\.(php|shtml|pht|asp)/i', $org_name)) {
				@unlink($filePath);
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Wrong file format."}, "id" : "id"}');
			}
			
			/*if(strstr($filePath, '.php')){
				@unlink($filePath);
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Wrong file format."}, "id" : "id"}');
			}*/

			
			 // looking for evel, base64, php inside image					
			$infected = 0;
			$fhandler = fopen($filePath, "r");
			while (!feof($fhandler))
			{
			    // Get the current line that the file is reading
			    $fline = fgets($fhandler) ;
			    if(strstr($fline, "eval(")) {					    	
					$infected++;
					break;
				} else if(strstr($fline, "base64")) {
					$infected++;
					break;
				} else if(strstr($fline, "<?php")) {
					$infected++;
					break;
				}
			}
			fclose($fhandler);
			
			if($infected){
				@unlink($filePath);
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "File is not accepted."}, "id" : "id"}');
			}					
						
			if(stristr($filePath, '.jpg') || stristr($filePath, '.png') || stristr($filePath, '.gif') || stristr($filePath, '.jpeg')){
				$imgInfo = getimagesize($filePath);
				if(!isset($imgInfo[2])) { // not an image
					@unlink($filePath);
					jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "File is not an image."}, "id" : "id"}');
				}
				self::fixOrientation($filePath);
			}			
			
		}
		
		jexit('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
		
	}
	

	static function  fixOrientation($filePath) {
		if(!function_exists('exif_read_data')){ return; }
		$exif = @exif_read_data($filePath);
		if (!empty($exif['Orientation'])) {
			$image = @imagecreatefromjpeg($filePath);
			switch ($exif['Orientation']) {
				case 3:
					$image = @imagerotate($image, 180, 0);
					break;
				case 6:
					$image = @imagerotate($image, -90, 0);
					break;
				case 8:
					$image = @imagerotate($image, 90, 0);
					break;
			}
			 
			@imagejpeg($image, $filePath, 100);
			@imagedestroy($image);
		}
	}	
		
}