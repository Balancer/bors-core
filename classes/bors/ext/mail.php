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

		Если $mail - массив, то он считается массивом вида ($subject, $text)
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

			$mail = bors_markup_markdown::factory($text);

//			if($title)
//				$mail->set_title($title, false);

			$title = $mail->get('title');
			$html = $mail->get('html');
			//TODO: ввести другие виды разметки
			$headers = $mail->get('headers');
		}

		require_once("engines/smarty/assign.php");
		//  'xfile:aviaport/mail.txt'
		if($tpl = config('mail.template.txt'))
			$text = template_assign_data($tpl, array('body' => $text));

		if($html)
		{
			if($tpl = config('mail.template.html')) // , 'xfile:aviaport/mail.html'
				$html = template_assign_data($tpl, array(
					'body' => $html,
					'skip_title' => true,
				));
		}

		send_mail(
			self::make_recipient($user),
			$title,
			$text,
			$html,
			self::make_recipient($from),
			$headers
		);
	}

	static function make_recipient($user)
	{
		if(!$user)
			return NULL;

		if(!is_object($user))
			return $user;

		$name  = $user->title();
		$email = $user->email();
		return "$name <$email>";
	}
}
