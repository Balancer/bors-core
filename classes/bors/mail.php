<?php

require_once('Mail.php');
require_once('Mail/mime.php');

class bors_mail extends bors_page
{
	var $to = NULL;
	var $from = NULL;
	var $reply_to = NULL;
	var $text = NULL;
	var $subject = NULL;
	var $body_html = NULL;
	var $charset = 'utf-8';

	var $data		= array();

	var $attaches	= array();
	var $images		= array();
	var $headers	= array();

	static function factory() { return call_user_func(array(get_called_class(), 'factory_ex'), array()); }

	static function factory_ex($params)
	{
		$mail_class = get_called_class();
		$mail = new $mail_class(NULL);

		$mail->from(config('mail_sender_default', 'noreplay@localhost'));

		foreach($params as $name => $value)
			call_user_func(array($mail, $name), $value);

		return $mail;
	}

	function data($data)
	{
		$this->data = $data;
		return $this;
	}

	function to($to)
	{
		$this->to = $to;
		return $this;
	}

	function to2($email, $title)
	{
		$this->to = self::make_recipient(array($email, $title));
		return $this;
	}

	function reply_to($to)
	{
		$this->reply_to = $to;
		return $this;
	}

	function reply_to2($email, $title)
	{
		$this->reply_to = self::make_recipient(array($email, $title));
		return $this;
	}

	static function make_recipient($user)
	{
		if(!$user)
			return NULL;

		if(is_array($user))
			list($email, $name) = $user;
		elseif(!is_object($user))
			return $user;
		else
		{
			$name  = $user->title();
			$email = $user->email();
		}

		if(preg_match('/^[\w\s]+$/', $name))
			return "$name <$email>";

		return "=?UTF-8?B?".base64_encode($name)."?= <$email>";
	}

	function from($from)
	{
		if(preg_match('/^(.*?) <(.*)>$/', $from, $m))
			if(!preg_match('/^\w+$/', $m[1]))
				$from = "=?$charset?B?".base64_encode($m[1])."?= <{$m[2]}>";

		$this->from = $from;

		return $this;
	}

	function from2($email, $title)
	{
		$this->from = self::make_recipient(array($email, $title));
		return $this;
	}

	function subject($subject)	{ $this->subject	= $subject;	return $this; }
	function text($text)		{ $this->text		= $text;	return $this; }
	function html($html)		{ $this->body_html		= $html;	return $this; }

	function image($file_name)
	{
		$this->images[] = $file_name;
		return $this;
	}

	function body_template($template_name, $data = array())
	{
		// mbfi/callback
		$html = bors_templates_smarty::fetch($template_name, $data);
		if(@$data['cr_type'] == 'save_cr')
			$html = str_replace("\n", "<br/>\n", $html);
		$this->html($html);
		return $this;
	}

	function mailer($mailer)
	{
		$this->headers['X-Mailer'] = $mailer;
		return $this;
	}

	// Добавляем к данным шаблона ещё и опционально переданные нам данные
	function body_data()
	{
		return array_merge(parent::body_data(), $this->data);
	}

	function send()
	{
		$tpl = preg_replace('/\.php$/', '.tpl', $this->class_file());
		if(empty($this->body_html) && file_exists($tpl))
		{
			$this->body_html = call_user_func(array('bors_templates_smarty', 'fetch'), $tpl, $this->body_data());

			if(empty($this->subject))
				$this->subject = $this->title();
		}

		// Перекодируем всё из системной кодировки в целевую.
//		foreach(explode(' ', 'to subject text html from') as $x)
//			$$x = dc($$x, $charset);

		$mime = new Mail_mime("\n");

		$mime->setTXTBody(dc($this->text, $this->charset));

		// Должно быть до setHTMLBody!
		foreach($this->images as $file)
			$mime->addHTMLImage($file, 'image/jpeg');

		if($this->body_html)
		{
			if(!preg_match('!<body!', $this->body_html))
				$this->body_html = "<html><body>{$this->body_html}</body></html>";

//		$this->body_html = preg_replace_callback('!<img[^>]+src="file://([^"]+)"!i', array());

			$mime->setHTMLBody($this->body_html);
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

		if($this->reply_to)
			set_def($headers, 'Reply-To', $this->reply_to);

//		print_r($body); print_r($headers); exit();

		$mail = &Mail::factory(config('mail_transport', 'mail'), config('mail_transport_parameters', NULL));
		$mail->send($this->to, $headers, $body);
//		echo "to=$to, body=$body"; var_dump($hdrs); exit();

		return $this;
	}
}
