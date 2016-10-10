<?php

require_once('Mail.php');
require_once('Mail/mime.php');

function send_mail($to, $subject, $text, $html = NULL, $from = NULL, $headers = NULL, $attaches = array())
{
	// По умолчанию всю почту шлём в UTF-8. Но можем указать, если что, в параметрах.
	$charset = defval($headers, 'charset', \B2\Cfg::get('mail_charset', 'utf-8'));
	unset($headers['charset']);

	// Перекодируем всё из системной кодировки в целевую.
	foreach(explode(' ', 'to subject text html from') as $x)
		$$x = dc($$x, $charset);

	$mime = new Mail_mime("\n");

	$mime->setTXTBody($text); 

	if($html)
	{
		if(!preg_match('!<body!', $html))
			$html = "<html><body>{$html}</body></html>";

		$mime->setHTMLBody($html);
	}

	if($attaches)
	{
		foreach($attaches as $a)
		{
			$mime->addAttachment(
				$a['file'],
				defval($a, 'type', 'application/octet-stream'),
				defval($a, 'name', ''),
				defval($a, 'is_file', true)
			);
		}
	}

	if(!$from)
		$from = \B2\Cfg::get('mail_sender_default', 'noreplay@localhost');

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
		if(!preg_match('/^\w+$/', $m[1]))
			$from = "=?$charset?B?".base64_encode($m[1])."?= <{$m[2]}>";

	if(!$headers)
		$headers = array();

	$hdrs = $mime->headers(array_merge($headers, array(
		'From'		=> $from,
		'Subject'	=> $subject,
		'To'		=> $to,
	)));

//	print_d($hdrs); exit();

	$mail = @Mail::factory(\B2\Cfg::get('mail_transport', 'mail'), \B2\Cfg::get('mail_transport_parameters', NULL));
	$mail->send($to, $hdrs, $body);
//	echo "to=$to, body=$body"; var_dump($hdrs); exit();
}
