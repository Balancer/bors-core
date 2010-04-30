<?php

require_once('Mail.php'); 
require_once('Mail/mime.php'); 

function send_mail($to, $subject, $text, $html = NULL, $from = NULL, $headers = array())
{
	// По умолчанию всю почту шлём в UTF-8. Но можем указать, если что, в параметрах.
	$charset = defval($headers, 'charset', config('mail_charset', 'utf-8'));
	unset($headers['charset']);

	// Перекодируем всё из системной кодировки в целевую.
	foreach(explode(' ', 'to subject text html from') as $x)
		$$x = dc($$x, NULL, $charset);

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
		'head_charset' => $charset,
		'html_charset' => $charset,
		'text_charset' => $charset,
		'head_encoding' => 'base64',
		'text_encoding' => '8bit',
		'html_encoding' => '8bit',
//		'head_encoding' => 'quoted-printable',
//		'text_encoding' => 'quoted-printable',
//		'html_encoding' => 'quoted-printable',
	));

	if(preg_match('/^(.*?) <(.*)>$/', $from, $m))
		$from = $m[2];

	$hdrs = $mime->headers(array_merge($headers, array(
		'From'		=> $from,
		'Subject'	=> $subject,
		'To'		=> $to,
	)));

//	print_d($hdrs); exit();

	$mail = &Mail::factory(config('mail_transport', 'mail'), config('mail_transport_parameters', NULL));
	$mail->send($to, $hdrs, $body);
//	echo "to=$to, body=$body"; var_dump($hdrs); exit();
}
