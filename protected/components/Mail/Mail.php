<?php
/**
 * @version		$Id: Mail.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Burst.Framework
 * @subpackage	Mail
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

Yii::import('application.vendors.*');
require_once 'phpmailer/phpmailer.php';
require_once 'Mail/MailHelper.php';

class Mail extends PHPMailer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
            // PHPMailer has an issue using the relative path for it's language files
            //$this->SetLanguage('burst', LIBRARIES.'/phpmailer/language/');
	}

	/**
	 * Returns the global email object, only creating it
	 * if it doesn't already exist.
	 *
	 * NOTE: If you need an instance to use that does not have the global configuration
	 * values, use an id string that is not 'Burst'.
	 *
	 * @param	string	$id		The id string for the Mail instance [optional]
	 *
	 * @return	Mail	The global Mail object
	 * @since	1.5
	 */
	public static function getInstance($id = 'Burst')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty($instances[$id])) {
			$instances[$id] = new Mail();
		}

		return $instances[$id];
	}

	/**
	 * Send the Mail
	 *
	 * @return	mixed	True if successful, a Error object otherwise
	 * @since	1.5
	 */
	public function Send()
	{
		if (($this->Mailer == 'Mail') && ! function_exists('Mail')) {
			//return Error::raiseNotice(500, Text::_('LIB_MAIL_FUNCTION_DISABLED'));
		}

		@$result = parent::Send();

		if ($result == false) {
			// TODO: Set an appropriate error number
			//$result = Error::raiseNotice(500, Text::_($this->ErrorInfo));
		}

		return $result;
	}

	/**
	 * Set the email sender
	 *
	 * @param	array	email address and Name of sender
	 *		<pre>
	 *			array([0] => email Address [1] => Name)
	 *		</pre>
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function setSender($from)
	{
		if (is_array($from)) {
			// If $from is an array we assume it has an address and a name
			$this->From	= MailHelper::cleanLine($from[0]);
			$this->FromName = MailHelper::cleanLine($from[1]);

		}
		elseif (is_string($from)) {
			// If it is a string we assume it is just the address
			$this->From = MailHelper::cleanLine($from);

		}
		else {
			// If it is neither, we throw a warning
			//Error::raiseWarning(0, Text::sprintf('LIB_MAIL_INVALID_EMAIL_SENDER', $from));
		}

		return $this;
	}

	/**
	 * Set the email subject
	 *
	 * @param	string	$subject	Subject of the email
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function setSubject($subject)
	{
		$this->Subject = MailHelper::cleanLine($subject);

		return $this;
	}

	/**
	 * Set the email body
	 *
	 * @param	string	$content	Body of the email
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function setBody($content)
	{
		/*
		 * Filter the Body
		 * TODO: Check for XSS
		 */
		$this->Body = MailHelper::cleanText($content);

		return $this;
	}

	/**
	 * Add recipients to the email
	 *
	 * @param	mixed	$recipient	Either a string or array of strings [email address(es)]
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addRecipient($recipient)
	{
		// If the recipient is an aray, add each recipient... otherwise just add the one
		if (is_array($recipient)) {
			foreach ($recipient as $to)
			{
				$to = MailHelper::cleanLine($to);
				$this->AddAddress($to);
			}
		}
		else {
			$recipient = MailHelper::cleanLine($recipient);
			$this->AddAddress($recipient);
		}

		return $this;
	}

	/**
	 * Add carbon copy recipients to the email
	 *
	 * @param	mixed	$cc		Either a string or array of strings [email address(es)]
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addCC($cc, $name = '')
	{
		// If the carbon copy recipient is an aray, add each recipient... otherwise just add the one
		if (isset ($cc)) {
			if (is_array($cc)) {
				foreach ($cc as $to)
				{
					$to = MailHelper::cleanLine($to);
					parent::AddCC($to, $name);
				}
			}
			else {
				$cc = MailHelper::cleanLine($cc);
				parent::AddCC($cc, $name);
			}
		}

		return $this;
	}

	/**
	 * Add blind carbon copy recipients to the email
	 *
	 * @param	mixed	$bcc	Either a string or array of strings [email address(es)]
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addBCC($bcc, $name = '')
	{
		// If the blind carbon copy recipient is an aray, add each recipient... otherwise just add the one
		if (isset($bcc)) {
			if (is_array($bcc)) {
				foreach ($bcc as $to)
				{
					$to = MailHelper::cleanLine($to);
					parent::AddBCC($to, $name);
				}
			}
			else {
				$bcc = MailHelper::cleanLine($bcc);
				parent::AddBCC($bcc, $name);
			}
		}

		return $this;
	}

	/**
	 * Add file attachments to the email
	 *
	 * @param	mixed	$attachment	Either a string or array of strings [filenames]
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addAttachment($attachment, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
	{
		// If the file attachments is an aray, add each file... otherwise just add the one
		if (isset($attachment)) {
			if (is_array($attachment)) {
				foreach ($attachment as $file)
				{
					parent::AddAttachment($file, $name = '', $encoding = 'base64', $type = 'application/octet-stream');
				}
			}
			else {
				parent::AddAttachment($attachment, $name = '', $encoding = 'base64', $type = 'application/octet-stream');
			}
		}

		return $this;
	}

	/**
	 * Add Reply to email address(es) to the email
	 *
	 * @param	array	$replyto	Either an array or multi-array of form
	 *		<pre>
	 *			array([0] => email Address [1] => Name)
	 *		</pre>
	 *
	 * @return	Mail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addReplyTo($replyto, $name = '')
	{
		// Take care of reply email addresses
		if (is_array($replyto[0])) {
			foreach ($replyto as $to)
			{
				$to0 = MailHelper::cleanLine($to[0]);
				$to1 = MailHelper::cleanLine($to[1]);
				parent::AddReplyTo($to0, $to1, $name);
			}
		}
		else {
			$replyto0 = MailHelper::cleanLine($replyto[0]);
			$replyto1 = MailHelper::cleanLine($replyto[1]);
			parent::AddReplyTo($replyto0, $replyto1, $name);
		}

		return $this;
	}

	/**
	 * Use sendmail for sending the email
	 *
	 * @param	string	$sendmail	Path to sendmail [optional]
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function useSendmail($sendmail = null)
	{
		$this->Sendmail = $sendmail;

		if (!empty ($this->Sendmail)) {
			$this->IsSendmail();

			return true;
		}
		else {
			$this->IsMail();

			return false;
		}
	}

	/**
	 * Use SMTP for sending the email
	 *
	 * @param	string	$auth	SMTP Authentication [optional]
	 * @param	string	$host	SMTP Host [optional]
	 * @param	string	$user	SMTP Username [optional]
	 * @param	string	$pass	SMTP Password [optional]
	 * @param			$secure
	 * @param	int		$port
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function useSMTP($auth = null, $host = null, $user = null, $pass = null, $secure = null, $port = 25)
	{
		$this->SMTPAuth = $auth;
		$this->Host		= $host;
		$this->Username = $user;
		$this->Password = $pass;
		$this->Port		= $port;

		if ($secure == 'ssl' || $secure == 'tls') {
			$this->SMTPSecure = $secure;
		}

		if (($this->SMTPAuth !== null && $this->Host !== null && $this->Username !== null && $this->Password !== null)
			|| ($this->SMTPAuth === null && $this->Host !== null)) {
			$this->IsSMTP();

			return true;
		}
		else {
			$this->IsMail();

			return false;
		}
	}

	/**
	 * Function to send an email
	 *
	 * @param	string	$from			From email address
	 * @param	string	$fromName		From name
	 * @param	mixed	$recipient		Recipient email address(es)
	 * @param	string	$subject		email subject
	 * @param	string	$body			Message body
	 * @param	boolean	$mode			false = plain text, true = HTML
	 * @param	mixed	$cc				CC email address(es)
	 * @param	mixed	$bcc			BCC email address(es)
	 * @param	mixed	$attachment		Attachment file name(s)
	 * @param	mixed	$replyTo		Reply to email address(es)
	 * @param	mixed	$replyToName	Reply to name(s)
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function sendMail($from, $fromName, $recipient, $subject, $body, $mode=0,
		$cc=null, $bcc=null, $attachment=null, $replyTo=null, $replyToName=null)
	{
          //$subject = iconv("utf-8", "windows-1251", $subject);
          //$body = iconv("utf-8", "windows-1251", $body);
          //$fromName = iconv("utf-8", "windows-1251", $fromName);
          
		$this->setSender(array($from, $fromName));
		$this->setSubject($subject);
		$this->setBody($body);

		// Are we sending the email as HTML?
		if ($mode) {
			$this->IsHTML(true);
		}

		$this->addRecipient($recipient);
		$this->addCC($cc);
		$this->addBCC($bcc);
		$this->addAttachment($attachment);

		// Take care of reply email addresses
		if (is_array($replyTo)) {
			$numReplyTo = count($replyTo);

			for ($i = 0; $i < $numReplyTo; $i++)
			{
				$this->addReplyTo(array($replyTo[$i], $replyToName[$i]));
			}
		}
		else if (isset($replyTo)) {
			$this->addReplyTo(array($replyTo, $replyToName));
		}

		return  $this->Send();
	}

}