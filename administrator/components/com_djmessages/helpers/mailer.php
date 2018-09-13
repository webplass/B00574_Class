<?php
/**
 * @package DJ-Messages
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
 
defined ('_JEXEC') or die('Restricted access');


class DJMessagesHelperMailer 
{
	
	public static function send($data, $template) {
		$template->body = self::parseTemplate($data, $template);
		
		$app		= JFactory::getApplication();
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');
		
		$mail = JFactory::getMailer();
		
		$mail->addRecipient($data['recipient_email']);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename.': '. $data['subject']);
		$mail->setBody($template->body);
		$mail->isHtml(true);
		
		$attachments = DJMessagesHelperAttachment::getFiles($data);
		
		if (count($attachments) > 0 && is_array($attachments)) {
			foreach($attachments as $file) {
				$mail->addAttachment(JPath::clean(JPATH_ROOT.'/'.$file['fullpath']), $file['name']);
			}
		}
		
		return $mail->Send();
		
	}
	
	protected static function parseTemplate($data, $template) {
		$body = $template->body;
		if (trim($body) == '') {
			$body = '[[message]]';
		}
		
		$juri = str_replace('/administrator','', JUri::base(false));
		
		foreach($data as $key => $value) {
			$body = JString::str_ireplace('[['.$key.']]', $value, $body);
		}
		
		$body = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#', '$1="'.$juri.'$2$3', $body);
		
		return $body;
	}
}