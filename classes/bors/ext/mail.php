<?php

/**
	Упрощённая отсылка почты пользователю
*/

class bors_ext_mail extends bors_empty
{
	/** Отправить пользователю $user почту $mail
		$user - объект, содержащий поля $email и $title. Почта будет отправлена на "$title <$email>"
		$mail - объект, содержащий поля $title [Subject], $text [текстовое содержимое письма], $html [html]
		$from - необязательное поле объекта $user отправителя.

		Если $user или $from не являются объектами, то они считаются строкой с именем/email адресата
		$mail->html() может отсутствовать.

		Если $mail - текст, то он считается текстом в автоматически пределяемой разметке:
			- Если первые две строки - заголовок в markdown-разметке, то markdown

		Если $mail - массив, то он считается массивом вида ($subject, $text, $html = NULL, $header = array(), $template_data = array())
	*/
	static function send($user, $mail, $from = NULL)
	{
		require_once('engines/mail.php');

		if(is_array($mail))
		{
			$title = $mail[0];
			$text  = $mail[1];
			$html  = @$mail[2];
			$headers = @$mail[3];
			$template_data = @$mail[4];
		}
		elseif(is_object($mail))
		{
			$title = $mail->title();
			$text  = $mail->text();
			$html  = $mail->html();
			$headers = $mail->get('headers');
		}
		else
		{
			$text = $mail;

			$mail = bors_markup_markdown::factory($text, array('keep_title' => true));

//			if($title)
//				$mail->set_title($title, false);

			$title = $mail->get('title');
			$html = $mail->get('html');
			//TODO: ввести другие виды разметки
			$headers = $mail->get('headers');
		}

		if(empty($template_data))
			$template_data = array();

		$data = [
			'user' => $user,
		];

		$html = preg_replace_callback('!%(\w+)\.(\w+)%!', function($m) use($data){ return $data[$m[1]]->get($m[2]);}, $html);
		$text = preg_replace_callback('!%(\w+)\.(\w+)%!', function($m) use($data){ return $data[$m[1]]->get($m[2]);}, $text);

		require_once("engines/smarty/assign.php");
		//  'xfile:aviaport/mail.txt'
		if($tpl = $mail->get('text_template', config('mail.template.txt')))
			$text = template_assign_data($tpl, array('body' => $text, 'user' => $user));

		if($html)
		{
			if($tpl = $mail->get('html_template', config('mail.template.html'))) // , 'xfile:aviaport/mail.html'
				$html = template_assign_data($tpl, array_merge(array(
					'body' => $html,
					'user' => $user,
					'skip_title' => true,
				), $template_data));
		}

		$attaches = NULL;
		if(is_object($mail))
			foreach($mail->get('mail_attaches', array()) as $a)
			{
				$attaches[] = array(
					'file' => $a,
				);
			}

/*
		echo "send_mail(
			".self::make_recipient($user).",
			$title,
			$text,
			$html,
			".self::make_recipient($from).",
			".print_r($headers, true)."
		);\n"; exit();
*/
		send_mail(
			self::make_recipient($user),
			$title,
			$text,
			$html,
			self::make_recipient($from),
			$headers,
			$attaches
		);
	}

	static function get_email($user)
	{
		if(!$user)
			return NULL;

		if(!is_object($user))
			return $user;

		return $user->email();
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

		return "=?utf-8?B?".base64_encode($name)."?= <$email>";
	}
}
