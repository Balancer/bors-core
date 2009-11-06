<?php

require_once('Mail.php'); 
require_once('Mail/mime.php'); 

function send_mail($to, $subject, $text, $html = NULL, $from = NULL, $headers = array())
{
	$mime = &new Mail_mime("\r\n");

	$mime->setTXTBody($text); 

	if($html)
	{
		if(!preg_match('!<body!', $html))
			$html = "<html><body>{$html}</body></html>";

		$mime->setHTMLBody($html);
	}

	if(!$from)
		$from = config('mail_sender_default', 'noreplay@localhost');

	$body = $mime->get(array(
		'head_charset' => 'utf-8',
		'html_charset' => 'utf-8',
		'text_charset' => 'utf-8',
		'head_encoding' => 'base64',
		'text_encoding' => '8bit',
		'html_encoding' => '8bit',
	));

	$hdrs = $mime->headers(array_merge($headers, array(
		'From'		=> $from,
		'Subject'	=> $subject,
		'To'		=> $to,
	)));

	$mail = &Mail::factory(config('mail_transport', 'mail'), config('mail_transport_parameters', NULL));
	$mail->send($to, $hdrs, $body);
}
