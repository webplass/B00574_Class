<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');

class DJMessagesHelperAttachment
{
	protected static $attachments_path = 'media/djmessages/attachments';
	static $blacklist = array('php','php3','php4', 'php5', 'php6', 'phtml', 'pht', 'shtml','asa','cer','asax','swf','xap','sh','js');
	
	public static function processFile($file, $userTo = 0) {
		$fileInfo = null;
		
		$targetDir = JPath::clean(JPATH_ROOT.'/'.static::$attachments_path.'/'.$userTo);
		if (!JFolder::exists($targetDir)) {
			JFolder::create($targetDir);
		}
		
		$isUploaded = (bool)(isset($file['tmp_name']));

		$newFile = array(
			'name' => '',
			'ext' => '',
			'size' => 0,
			'path' => '',
			'fullpath' => '',
			'source' => ''
		);
		
		if ($isUploaded) {
			if (!isset($file['error']) || !isset($file['size']) || !isset($file['name'])) {
				return false;
			}
			if ($file['error'] > 0 || $file['size'] == 0 || $file['name'] == '') {
				return false;
			}
			
			$newFile['source'] = $file['tmp_name'];
			
			$newFile['size'] = $file['size'];
			$newFile['name'] = static::createFileName($file['name'], $targetDir);
			$newFile['ext'] = JFile::getExt($newFile['name']);
			$newFile['path'] = static::$attachments_path.'/'.$userTo;
			$newFile['fullpath'] = static::$attachments_path.'/'.$userTo.'/'.$newFile['name'];
		} else {
			if (!isset($file['fullname']) || !isset($file['caption'])) {
				return false;
			}
			
			$newFile['source'] = JPath::clean(JPATH_ROOT . '/media/djmessages/tmp/'.$file['fullname']);
			
			$newFile['size'] = $file['size'];
			$newFile['name'] = static::createFileName($file['caption'] . '.' . JFile::getExt($file['fullname']), $targetDir);
			$newFile['ext'] = JFile::getExt($newFile['name']);
			$newFile['path'] = static::$attachments_path.'/'.$userTo;
			$newFile['fullpath'] = static::$attachments_path.'/'.$userTo.'/'.$newFile['name'];
		}
		
		if (!$newFile['source'] || !$newFile['fullpath'] || JFile::exists($newFile['source']) == false) {
			return false;
		}
		
			
		if (preg_match('/\.(php|shtml|pht|asp)/i', $newFile['name'])) {
			return false;
		}
		
		if (static::isFileInfected($newFile['source'])) {
			return false;
		}
		
		if ($isUploaded) {
			if (JFile::upload($newFile['source'], JPath::clean(JPATH_ROOT.'/'.$newFile['fullpath'])) == false) {
				return false;
			}
		} else {
			if (JFile::move($newFile['source'], JPath::clean(JPATH_ROOT.'/'.$newFile['fullpath'])) == false) {
				return false;
			}
		}
		
		unset($newFile['source']);
		return $newFile;
	}
	
	public static function getFiles($message) {
		$fileInfo = null;
		if (is_object($message) && isset($message->attachments)) {
			$fileInfo = $message->attachments;
		} else if (is_array($message) && isset($message['attachments'])) {
			$fileInfo = $message['attachments'];
		}
		
		if (empty($fileInfo)) {
			return false;
		}
		
		return (is_string($fileInfo)) ? json_decode($fileInfo, true) : $fileInfo;
	}
	
	public static function getFile($message, $name) {
		$attachments = static::getFiles($message);
		if (!$attachments) {
			return false;
		}
		
		foreach($attachments as $file) {
			if (strcmp($name, $file['name']) === 0) {
				return static::getFileByPath(JPath::clean(JPATH_ROOT.'/'.$file['fullpath']));
			}
		}
		
		return false;
	}
	
	public static function createFileName($filename, $path) {
		$lang = JFactory::getLanguage();
		
		$hash = md5($filename);
		$namepart = JFile::stripExt($filename);
		$extpart = JFile::getExt($filename);
		
		$namepart = $lang->transliterate($namepart);
		$namepart = strtolower($namepart);
		$namepart = JFile::makeSafe($namepart);
		$namepart = str_replace(' ', '_', $namepart);
		
		if ($namepart == '') {
			$namepart = $hash;
		}
		
		$filename = $namepart.'.'.$extpart;
		
		if (JFile::exists($path.'/'.$filename)) {
			if (is_numeric(JFile::getExt($namepart)) && count(explode(".", $namepart))>1) {
				$namepart = JFile::stripExt($namepart);
			}
			$iterator = 1;
			$newname = $namepart.'.'.$iterator.'.'.$extpart;
			while (JFile::exists($path.'/'.$newname)) {
				$iterator++;
				$newname = $namepart.'.'.$iterator.'.'.$extpart;
			}
			$filename = $newname;
		}
		
		return $filename;
	}
	
	protected static function isFileInfected($filePath) {
		$infected = 0;
		$fhandler = fopen($filePath, "r");
		while (!feof($fhandler))
		{
			// Get the current line that the file is reading
			$fline = fgets($fhandler) ;
			if(preg_match('/eval(\s)*\(/i', $fline)) {
				$infected++;
				break;
			} else if(stristr($fline, "base64")) {
				$infected++;
				break;
			} else if(stristr($fline, "<?php")) {
				$infected++;
				break;
			}
		}
		fclose($fhandler);
		
		return (bool)($infected > 0);
	}
	
	protected static function getFileByPath($filename, $mime = null) {
		
		if (!JFile::exists($filename)) {
			return false;
		}
		
		$document = JFactory::getDocument();
		$filesize = filesize($filename);
		/*if ($filesize === 0) {
		 return false;
		 }*/
		$parts = pathinfo($filename);
		$ext = strtolower($parts["extension"]);
		//ob_start();
		
		// Required for some browsers
		if(ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');
			
			// Determine Content Type
			$ctype = "application/force-download";
			if ($mime) {
				$ctype = $mime;
			}
			else if (function_exists('mime_content_type')) {
				$ctype = mime_content_type($filename);
			} else {
				switch ($ext) {
					case "pdf": $ctype="application/pdf"; break;
					case "exe": $ctype="application/octet-stream"; break;
					case "zip": $ctype="application/zip"; break;
					case "doc": $ctype="application/msword"; break;
					case "xls": $ctype="application/vnd.ms-excel"; break;
					case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
					case "gif": $ctype="image/gif"; break;
					case "png": $ctype="image/png"; break;
					case "jpeg":
					case "jpg": $ctype="image/jpg"; break;
					case "txt": $ctype="text/plain"; break;
					case "csv": $ctype="text/csv"; break;
					case "apk": $ctype="application/vnd.android.package-archive"; break;
					
					default: $ctype="application/force-download";
				}
			}
			
			if (!count(array_diff(ob_list_handlers(), array('default output handler'))) || ob_get_length()) {
				while(@ob_end_clean());
			}
			
			$document->setMimeEncoding($ctype);
			
			$attachment_name = $parts["basename"];
			
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: ".$ctype);
			header("Content-Disposition: filename=\"".$attachment_name."\";" );
			//header("Content-Disposition: attachment; filename=\"".$parts["basename"]."\";" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$filesize);
			
			return static::readFileChunked($filename);
	}
	
	protected static function readFileChunked($filename, $retbytes = true) {
		$chunksize = 1024*1024;
		$buffer = '';
		$cnt = 0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			@ob_flush();
			@flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt;
		}
		return $status;
	}
	
	public static function upload() {
		
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		
		$params = JComponentHelper::getParams( 'com_djmessages' );
		
		$whitelist = explode(',', $params->get('allowed_attachment_types', 'jpg,png,bmp,gif,pdf,tif,tiff,txt,csv,doc,docx,xls,xlsx,xlt,pps,ppt,pptx,ods,odp,odt,rar,zip,tar,bz2,gz2,7z'));

		foreach($whitelist as $key => $extension) {
			if (!in_array(trim($extension), static::$blacklist)) {
				$whitelist[$key] = strtolower(trim($extension));
			}
		}
		
		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		// Settings
		$targetDir = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'djmessages' . DIRECTORY_SEPARATOR . 'tmp';
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
		
		if (JFolder::exists($targetDir)) {
			JFolder::create($targetDir);
		}
		
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
						
						if (!in_array($ext, $whitelist) || preg_match('/\.(php|shtml|pht|asp)/i', $filePath)) {
							@unlink($filePath);
							jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Wrong file format."}, "id" : "id"}');
						}
						
						if(stristr($filePath, '.jpg') || stristr($filePath, '.png') || stristr($filePath, '.gif') || stristr($filePath, '.jpeg')){
							$imgInfo = getimagesize($filePath);
							if(!isset($imgInfo[2])) { // not an image
								@unlink($filePath);
								jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "File is not an image."}, "id" : "id"}');
							}
						}
						
						$infected = 0;
						$fhandler = fopen($filePath, "r");
						while (!feof($fhandler))
						{
							// Get the current line that the file is reading
							$fline = fgets($fhandler) ;
							if(preg_match('/eval(\s)*\(/i', $fline)) {
								$infected++;
								break;
							} else if(stristr($fline, "base64")) {
								$infected++;
								break;
							} else if(stristr($fline, "<?php")) {
								$infected++;
								break;
							} /*else if(stristr($fline, "<?")) {
							$infected++;
							break;
							}*/
						}
						fclose($fhandler);
						
						if($infected){
							@unlink($filePath);
							jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "File is not accepted."}, "id" : "id"}');
						}
					}
					
					jexit('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
					
	}
}