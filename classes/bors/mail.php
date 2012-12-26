<?php

require_once('Mail.php');
require_once('Mail/mime.php');

class bors_mail
{
	var $to = NULL;
	var $from = NULL;
	var $text = NULL;
	var $subject = NULL;
	var $html = NULL;
	var $charset = 'utf-8';

	var $attaches	= array();
	var $images		= array();
	var $headers	= array();

	static function factory() { return self::factory_ex(array()); }

	static function factory_ex($params)
	{
		$mail = new bors_mail(NULL);

		$mail->from(config('mail_sender_default', 'noreplay@localhost'));

		foreach($params as $name => $value)
			call_user_func(array($mail, $name), $value);

		return $mail;
	}

	function to($to)			{ $this->to			= $to;		return $this; }
	function from($from)
	{
		if(preg_match('/^(.*?) <(.*)>$/', $from, $m))
			if(!preg_match('/^\w+$/', $m[1]))
				$from = "=?$charset?B?".base64_encode($m[1])."?= <{$m[2]}>";

		$this->from = $from;

		return $this;
	}
	function subject($subject)	{ $this->subject	= $subject;	return $this; }
	function text($text)		{ $this->text		= $text;	return $this; }
	function html($html)		{ $this->html		= $html;	return $this; }

	function image($file_name)
	{
		$this->images[] = $file_name;
		return $this;
	}

	function send()
	{
		// Перекодируем всё из системной кодировки в целевую.
//		foreach(explode(' ', 'to subject text html from') as $x)
//			$$x = dc($$x, $charset);

		$mime = new Mail_mime("\n");

		$mime->setTXTBody(dc($this->text, $this->charset));

		// Должно быть до setHTMLBody!
		foreach($this->images as $file)
			$mime->addHTMLImage($file, 'image/jpeg');

		if($this->html)
		{
			if(!preg_match('!<body!', $this->html))
				$this->html = "<html><body>{$this->html}</body></html>";

//		$this->html = preg_replace_callback('!<img[^>]+src="file://([^"]+)"!i', array());

			$mime->setHTMLBody($this->html);
		}
/*
		if($this->attaches)
		{
			foreach($this->attaches as $a)
			{
				$mime->addAttachment(
					$a['file'],
					defval($a, 'type', 'application/octet-stream'),
					defval($a, 'name', ''),
					defval($a, 'is_file', true)
				);
			}
		}
*/

		$body = $mime->get(array(
			'head_charset' => $this->charset,
			'html_charset' => $this->charset,
			'text_charset' => $this->charset,
			'head_encoding' => 'base64',
			'text_encoding' => '8bit',
			'html_encoding' => '8bit',
//			'head_encoding' => 'quoted-printable',
//			'text_encoding' => 'quoted-printable',
//			'html_encoding' => 'quoted-printable',
		));


		$headers = $mime->headers(array_merge($this->headers, array(
			'From'		=> $this->from,
			'Subject'	=> $this->subject,
			'To'		=> $this->to,
		)));

//		print_dd($body); print_dd($headers); exit();

		$mail = &Mail::factory(config('mail_transport', 'mail'), config('mail_transport_parameters', NULL));
		$mail->send($this->to, $headers, $body);
//		echo "to=$to, body=$body"; var_dump($hdrs); exit();

		return $this;
	}
}
